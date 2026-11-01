import json
import re
import time
from concurrent.futures import ThreadPoolExecutor

from app.configuracion import clave_gemini, clave_groq, claves_woocommerce, url_tienda
from app.frmUsuarios.servicio import (
    TiendaError,
    TiendaNoConfigurada,
    cliente_woocommerce,
    mensaje_tienda,
)

MAXIMO_VARIACIONES = 50
MODELO_GEMINI = "gemini-3.8-flash"
MODELO_GROQ = "qwen/qwen3.8-27b"
MAXIMO_FOTOS_AI = 6
MAXIMO_FOTOS_GROQ = 3


class GeminiNoConfigurado(Exception):
    pass


def fallo_pasajero(error: Exception) -> bool:
    codigo = getattr(error, "code", None)
    if codigo in (400, 401, 403, 404):
        return False
    return True


def fallo_pasajero_http(codigo: int) -> bool:
    return codigo not in (400, 401, 403, 404)


def texto_plano(html: str) -> str:
    return re.sub(r"<[^>]+>", "", html or "").replace("&nbsp;", " ").strip()


def texto_existencia(item: dict) -> str:
    estados = {
        "instock": "En existencia",
        "outofstock": "Agotado",
        "onbackorder": "Bajo pedido",
    }
    if item.get("type") == "variable":
        return estados.get(item.get("stock_status") or "", "")
    if item.get("manage_stock"):
        return str(item.get("stock_quantity") or 0)
    return estados.get(item.get("stock_status") or "", "")


def resumir_producto(item: dict) -> dict:
    imagenes = item.get("images") or []
    return {
        "id": item["id"],
        "nombre": item.get("name") or "",
        "tipo": item.get("type") or "simple",
        "precio": item.get("price") or item.get("regular_price") or "",
        "existencia": texto_existencia(item),
        "sku": item.get("sku") or "",
        "foto": imagenes[0].get("src") if imagenes else "",
    }


def fotos_de(item: dict) -> list[dict]:
    return [
        {"id": imagen["id"], "src": imagen.get("src") or ""}
        for imagen in item.get("images") or []
        if imagen.get("id")
    ]


def detalle_producto(item: dict, variaciones: list[dict] | None = None) -> dict:
    fotos = fotos_de(item)
    atributos = []
    for atributo in item.get("attributes") or []:
        if not atributo.get("variation"):
            continue
        atributos.append(
            {
                "id": atributo.get("id") or 0,
                "nombre": atributo.get("name") or "",
                "opciones": atributo.get("options") or [],
            }
        )
    salida = []
    for variacion in variaciones or []:
        pares = {
            (atributo.get("name") or ""): atributo.get("option") or ""
            for atributo in variacion.get("attributes") or []
        }
        imagen = variacion.get("image") or {}
        salida.append(
            {
                "id": variacion["id"],
                "atributos": pares,
                "precio": variacion.get("regular_price") or variacion.get("price") or "",
                "existencia": int(variacion.get("stock_quantity") or 0),
                "sku": variacion.get("sku") or "",
                "foto_id": imagen.get("id") or None,
                "foto": imagen.get("src") or "",
            }
        )
    base = resumir_producto(item)
    descripcion = texto_plano(item.get("short_description") or "") or texto_plano(item.get("description") or "")
    base.update(
        {
            "descripcion": descripcion,
            "publicado": item.get("status") == "publish",
            "fotos": fotos,
            "categorias": [
                {"id": categoria["id"], "nombre": categoria.get("name") or ""}
                for categoria in item.get("categories") or []
                if categoria.get("id")
            ],
            "atributos": atributos,
            "variaciones": salida,
        }
    )
    return base


def validar_producto(datos: dict) -> None:
    if not datos.get("categorias"):
        raise TiendaError("Elige o crea una categoria", 400)
    if datos["tipo"] == "simple":
        if not str(datos.get("precio") or "").strip():
            raise TiendaError("El producto simple necesita un precio", 400)
        return
    atributos = datos.get("atributos") or []
    if not atributos:
        raise TiendaError("El producto variable necesita al menos una caracteristica", 400)
    nombres = []
    total = 1
    for atributo in atributos:
        nombre = atributo["nombre"].strip()
        if nombre in nombres:
            raise TiendaError("Hay dos caracteristicas con el mismo nombre", 400)
        nombres.append(nombre)
        opciones = [opcion.strip() for opcion in atributo["opciones"] if str(opcion).strip()]
        if not opciones:
            raise TiendaError(f"{nombre} necesita al menos una opcion", 400)
        total *= len(opciones)
    if total > MAXIMO_VARIACIONES:
        raise TiendaError(f"Hay demasiadas combinaciones. El maximo es {MAXIMO_VARIACIONES}.", 400)
    if len(datos.get("variaciones") or []) != total:
        raise TiendaError("Falta el precio de alguna combinacion", 400)
    for variacion in datos["variaciones"]:
        if not str(variacion.get("precio") or "").strip():
            raise TiendaError("Cada combinacion necesita un precio", 400)


def cuerpo_producto(datos: dict, incluir_descripcion_larga: bool) -> dict:
    cuerpo = {
        "name": datos["nombre"].strip(),
        "type": datos["tipo"],
        "short_description": datos.get("descripcion") or "",
        "status": "publish" if datos.get("publicado", True) else "draft",
        "images": [{"id": foto_id} for foto_id in datos.get("fotos") or []],
        "categories": [{"id": int(categoria_id)} for categoria_id in datos.get("categorias") or []],
    }
    if incluir_descripcion_larga:
        cuerpo["description"] = datos.get("descripcion") or ""
    if datos["tipo"] == "simple":
        existencia = int(datos.get("existencia") or 0)
        cuerpo.update(
            {
                "regular_price": str(datos["precio"]),
                "manage_stock": True,
                "stock_quantity": existencia,
                "stock_status": "instock" if existencia > 0 else "outofstock",
                "sku": datos.get("sku") or "",
            }
        )
        return cuerpo
    cuerpo["attributes"] = []
    for atributo in datos["atributos"]:
        item = {
            "name": atributo["nombre"].strip(),
            "visible": True,
            "variation": True,
            "options": [opcion.strip() for opcion in atributo["opciones"] if str(opcion).strip()],
        }
        if atributo.get("id"):
            item["id"] = atributo["id"]
        cuerpo["attributes"].append(item)
    return cuerpo


def cuerpo_variacion(datos: dict) -> dict:
    existencia = int(datos.get("existencia") or 0)
    cuerpo = {
        "regular_price": str(datos["precio"]),
        "manage_stock": True,
        "stock_quantity": existencia,
        "stock_status": "instock" if existencia > 0 else "outofstock",
        "sku": datos.get("sku") or "",
        "attributes": [
            {"name": nombre, "option": opcion}
            for nombre, opcion in datos["atributos"].items()
        ],
        "image": {"id": datos["foto_id"]} if datos.get("foto_id") else None,
    }
    return cuerpo


def exigir(respuesta, ok=(200, 201)):
    if respuesta.status_code not in ok:
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    if respuesta.status_code == 204:
        return None
    return respuesta.json()


def listar_variaciones(producto_id: int, cliente) -> list[dict]:
    pagina = 1
    acumulado = []
    while True:
        respuesta = cliente.get(
            f"products/{producto_id}/variations",
            params={"per_page": 100, "page": pagina},
        )
        lote = exigir(respuesta)
        if not isinstance(lote, list) or not lote:
            break
        acumulado.extend(lote)
        total = int(respuesta.headers.get("X-WP-Total") or len(acumulado))
        if len(lote) < 100 or len(acumulado) >= total:
            break
        pagina += 1
    return acumulado


def listar_productos(pagina: int = 1, por_pagina: int = 10, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.get(
        "products",
        params={"per_page": por_pagina, "page": pagina, "status": "any"},
    )
    lote = exigir(respuesta)
    if not isinstance(lote, list):
        lote = []
    total = int(respuesta.headers.get("X-WP-Total") or len(lote))
    return {
        "productos": [resumir_producto(item) for item in lote],
        "total": total,
        "pagina": pagina,
        "por_pagina": por_pagina,
    }


def leer_producto(producto_id: int, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    producto = exigir(api.get(f"products/{producto_id}"))
    variaciones = []
    if producto.get("type") == "variable":
        variaciones = listar_variaciones(producto_id, api)
    return detalle_producto(producto, variaciones)


def quitar_variaciones(producto_id: int, cliente) -> None:
    for variacion in listar_variaciones(producto_id, cliente):
        exigir(
            cliente.delete(f"products/{producto_id}/variations/{variacion['id']}", params={"force": True}),
            ok=(200, 204),
        )


def sincronizar_variaciones(producto_id: int, variaciones: list[dict], cliente) -> None:
    existentes = {variacion["id"] for variacion in listar_variaciones(producto_id, cliente)}
    conservadas = set()
    for variacion in variaciones:
        cuerpo = cuerpo_variacion(variacion)
        variacion_id = variacion.get("id")
        if variacion_id and variacion_id in existentes:
            exigir(cliente.put(f"products/{producto_id}/variations/{variacion_id}", cuerpo))
            conservadas.add(variacion_id)
        else:
            exigir(cliente.post(f"products/{producto_id}/variations", cuerpo))
    for sobrante in existentes - conservadas:
        exigir(
            cliente.delete(f"products/{producto_id}/variations/{sobrante}", params={"force": True}),
            ok=(200, 204),
        )


def guardar_producto(datos: dict, producto_id: int | None = None, cliente=None) -> dict:
    validar_producto(datos)
    api = cliente or cliente_woocommerce()
    cuerpo = cuerpo_producto(datos, incluir_descripcion_larga=producto_id is None)
    if producto_id and datos["tipo"] == "simple":
        actual = exigir(api.get(f"products/{producto_id}"))
        if actual.get("type") == "variable":
            quitar_variaciones(producto_id, api)
    if producto_id:
        producto = exigir(api.put(f"products/{producto_id}", cuerpo))
    else:
        producto = exigir(api.post("products", cuerpo))
        producto_id = producto["id"]
    if datos["tipo"] == "variable":
        sincronizar_variaciones(producto_id, datos["variaciones"], api)
        producto = exigir(api.get(f"products/{producto_id}"))
        return detalle_producto(producto, listar_variaciones(producto_id, api))
    return detalle_producto(producto, [])


def listar_categorias(cliente=None) -> list[dict]:
    api = cliente or cliente_woocommerce()
    pagina = 1
    acumulado = []
    while pagina <= 5:
        respuesta = api.get(
            "products/categories",
            params={"per_page": 100, "page": pagina, "orderby": "name", "order": "asc"},
        )
        lote = exigir(respuesta)
        if not isinstance(lote, list) or not lote:
            break
        acumulado.extend(lote)
        total = int(respuesta.headers.get("X-WP-Total") or len(acumulado))
        if len(lote) < 100 or len(acumulado) >= total:
            break
        pagina += 1
    return [
        {"id": item["id"], "nombre": item.get("name") or ""}
        for item in acumulado
        if item.get("id")
    ]


def crear_categoria(nombre: str, cliente=None) -> dict:
    nombre = nombre.strip()
    if not nombre:
        raise TiendaError("Escribe el nombre de la categoria", 400)
    api = cliente or cliente_woocommerce()
    creada = exigir(api.post("products/categories", {"name": nombre}))
    return {"id": creada["id"], "nombre": creada.get("name") or nombre}


def eliminar_producto(producto_id: int, cliente=None) -> None:
    api = cliente or cliente_woocommerce()
    exigir(api.delete(f"products/{producto_id}", params={"force": True}), ok=(200, 204))


def limpiar_json(texto: str) -> str:
    texto = (texto or "").strip()
    if texto.startswith("```"):
        texto = texto.split("\n", 1)[-1].rsplit("```", 1)[0].strip()
    inicio = texto.find("{")
    fin = texto.rfind("}")
    if inicio >= 0 and fin > inicio:
        return texto[inicio : fin + 1]
    return texto


def aviso_sin_producto(aviso: str) -> str:
    causa = (aviso or "").strip() or "No se ve un producto claro en la foto"
    return f"No se pudo detectar la imagen: {causa}"


def interpretar_propuesta(texto: str, tipo: str) -> dict:
    try:
        datos = json.loads(limpiar_json(texto))
    except json.JSONDecodeError as error:
        raise TiendaError("Gemini no devolvio datos del producto en JSON", 502) from error
    if not isinstance(datos, dict):
        raise TiendaError("Gemini no devolvio datos del producto en JSON", 502)
    nombre = str(datos.get("nombre") or "").strip()[:120]
    descripcion = str(datos.get("descripcion") or "").strip()[:500]
    aviso = str(datos.get("aviso") or "").strip()[:240]
    detectado = datos.get("detectado")
    if detectado is False or not nombre:
        raise TiendaError(aviso_sin_producto(aviso), 422)
    atributos = []
    if tipo == "variable":
        for item in datos.get("atributos") or []:
            if not isinstance(item, dict):
                continue
            nombre_atributo = str(item.get("nombre") or "").strip()[:40]
            opciones = []
            for opcion in item.get("opciones") or []:
                texto_opcion = str(opcion).strip()[:40]
                if texto_opcion and texto_opcion not in opciones:
                    opciones.append(texto_opcion)
            if nombre_atributo and opciones:
                atributos.append({"id": 0, "nombre": nombre_atributo, "opciones": opciones[:4]})
            if len(atributos) == 2:
                break
        if not atributos:
            raise TiendaError(
                aviso_sin_producto(
                    aviso or "En la foto no se ven caracteristicas para un producto variable"
                ),
                422,
            )
    return {
        "tipo": tipo,
        "nombre": nombre,
        "descripcion": descripcion,
        "atributos": atributos,
    }


def instrucciones_gemini(tipo_producto: str) -> str:
    if tipo_producto == "variable":
        regla_tipo = (
            "El tipo pedido es variable. En atributos pon como maximo 2 caracteristicas "
            "visibles en las fotos (por ejemplo Color o Talla), cada una con hasta 4 opciones. "
            "Solo opciones que se vean o se deduzcan de las fotos. Si no hay caracteristicas "
            "claras, detectado debe ser false y aviso debe explicar por que."
        )
    else:
        regla_tipo = (
            "El tipo pedido es normal (simple). atributos debe ser siempre una lista vacia []."
        )
    return (
        "Eres el asistente de la tienda Furbuddy, de accesorios y productos para mascotas. "
        "Recibes una o varias fotos del mismo producto. Analiza las imagenes con cuidado. "
        "Describe solo lo que se ve. No inventes un collar, una cama, un juguete ni otro "
        "accesorio si no aparece. No inventes precio, existencia, categorias ni sku. "
        "Responde SOLO un JSON valido con estas claves exactas:\n"
        '- "detectado": true si las fotos muestran un producto usable para la tienda; '
        "false si la foto esta negra, vacia, borrosa, cortada, es texto, una persona, "
        "o no se reconoce un producto.\n"
        '- "aviso": si detectado es false, explica la causa en espanol en una frase corta. '
        'Si detectado es true, usa "".\n'
        '- "nombre": titulo corto del producto visible, con color si se ve. Max 120 caracteres. '
        'Vacio "" si detectado es false.\n'
        '- "descripcion": dos oraciones sobre forma, material o uso visible, sin HTML. '
        'Max 500 caracteres. Vacia "" si detectado es false.\n'
        '- "atributos": lista de objetos {"nombre": string, "opciones": [string, ...]}.\n'
        + regla_tipo
    )


ESQUEMA_PROPUESTA_AI = {
    "type": "object",
    "properties": {
        "detectado": {"type": "boolean"},
        "aviso": {"type": "string"},
        "nombre": {"type": "string"},
        "descripcion": {"type": "string"},
        "atributos": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "nombre": {"type": "string"},
                    "opciones": {"type": "array", "items": {"type": "string"}},
                },
                "required": ["nombre", "opciones"],
            },
        },
    },
    "required": ["detectado", "aviso", "nombre", "descripcion", "atributos"],
}


def texto_groq(fotos: list[tuple[bytes, str]], tipo_producto: str) -> str:
    import base64

    import requests

    clave = clave_groq()
    if not clave:
        raise RuntimeError("Sin clave Groq")
    partes = [{"type": "text", "text": instrucciones_gemini(tipo_producto)}]
    for contenido, tipo_mime in fotos[:MAXIMO_FOTOS_GROQ]:
        datos = base64.b64encode(contenido).decode("ascii")
        partes.append(
            {
                "type": "image_url",
                "image_url": {"url": f"data:{tipo_mime};base64,{datos}"},
            }
        )
    respuesta = requests.post(
        "https://api.groq.com/openai/v1/chat/completions",
        headers={
            "Authorization": f"Bearer {clave}",
            "Content-Type": "application/json",
        },
        json={
            "model": MODELO_GROQ,
            "temperature": 0.2,
            "response_format": {"type": "json_object"},
            "messages": [{"role": "user", "content": partes}],
        },
        timeout=45,
    )
    if respuesta.status_code in (401, 403):
        raise TiendaError("La clave de Groq no es valida", 502)
    if respuesta.status_code >= 400:
        if not fallo_pasajero_http(respuesta.status_code):
            raise TiendaError("Groq no pudo analizar las fotos", 502)
        raise RuntimeError(f"Groq HTTP {respuesta.status_code}")
    cuerpo = respuesta.json()
    try:
        texto = cuerpo["choices"][0]["message"]["content"]
    except (KeyError, IndexError, TypeError) as error:
        raise TiendaError("Groq no devolvio datos del producto en JSON", 502) from error
    texto = (texto or "").strip()
    if not texto:
        raise TiendaError("Groq no devolvio datos del producto en JSON", 502)
    return texto


def texto_gemini(fotos: list[tuple[bytes, str]], tipo_producto: str) -> str:
    clave = clave_gemini()
    if not clave:
        raise GeminiNoConfigurado()
    from google import genai
    from google.genai import types

    partes = [
        types.Part.from_bytes(data=contenido, mime_type=tipo_mime)
        for contenido, tipo_mime in fotos
    ]
    partes.append(instrucciones_gemini(tipo_producto))
    texto = ""
    cliente = genai.Client(api_key=clave)
    try:
        for intento in range(3):
            try:
                respuesta = cliente.models.generate_content(
                    model=MODELO_GEMINI,
                    contents=partes,
                    config=types.GenerateContentConfig(
                        response_mime_type="application/json",
                        response_schema=ESQUEMA_PROPUESTA_AI,
                    ),
                )
                texto = (respuesta.text or "").strip()
                if not texto:
                    raise TiendaError("Gemini no devolvio datos del producto en JSON", 502)
                break
            except TiendaError:
                raise
            except Exception as error:
                if not fallo_pasajero(error) or intento == 2:
                    raise TiendaError("Gemini no pudo analizar las fotos", 502) from error
                time.sleep(0.4 * (intento + 1))
    finally:
        cliente.close()
    return texto


def analizar_fotos(fotos: list[tuple[bytes, str]], tipo_producto: str) -> dict:
    if not fotos:
        raise TiendaError("Agrega al menos una foto", 400)
    if len(fotos) > MAXIMO_FOTOS_AI:
        raise TiendaError("Puedes enviar hasta 6 fotos", 400)
    if tipo_producto not in ("simple", "variable"):
        raise TiendaError("Elige un producto normal o variable", 400)
    if not clave_groq() and not clave_gemini():
        raise GeminiNoConfigurado()
    if clave_groq():
        try:
            return interpretar_propuesta(texto_groq(fotos, tipo_producto), tipo_producto)
        except TiendaError as error:
            if error.codigo == 422 or not clave_gemini():
                raise
        except Exception:
            if not clave_gemini():
                raise TiendaError("Groq no pudo analizar las fotos", 502)
    return interpretar_propuesta(texto_gemini(fotos, tipo_producto), tipo_producto)


def proponer_producto(fotos: list[tuple[str, bytes, str]], tipo_producto: str) -> dict:
    propuesta = analizar_fotos([(contenido, tipo_mime) for _, contenido, tipo_mime in fotos], tipo_producto)
    if len(fotos) == 1:
        nombre, contenido, tipo_mime = fotos[0]
        propuesta["fotos"] = [subir_foto(nombre, contenido, tipo_mime)]
        return propuesta
    with ThreadPoolExecutor(max_workers=min(MAXIMO_FOTOS_AI, len(fotos))) as pool:
        propuesta["fotos"] = list(
            pool.map(
                lambda foto: subir_foto(foto[0], foto[1], foto[2]),
                fotos,
            )
        )
    return propuesta


def subir_foto(nombre: str, contenido: bytes, tipo: str) -> dict:
    clave, secreto = claves_woocommerce()
    if not clave or not secreto:
        raise TiendaNoConfigurada()
    import requests

    respuesta = requests.post(
        f"{url_tienda()}/wp-json/furbuddy/v1/media",
        files={"archivo": (nombre, contenido, tipo)},
        auth=(clave, secreto),
        timeout=40,
    )
    if respuesta.status_code not in (200, 201):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    cuerpo = respuesta.json()
    return {"id": cuerpo["id"], "src": cuerpo.get("src") or ""}
