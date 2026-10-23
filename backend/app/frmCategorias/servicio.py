from app.frmUsuarios.servicio import (
    TiendaError,
    TiendaNoConfigurada,
    cliente_woocommerce,
    mensaje_tienda,
)

__all__ = ["TiendaError", "TiendaNoConfigurada"]


def resumir(item: dict) -> dict:
    return {
        "id": item["id"],
        "nombre": item.get("name") or "",
        "productos": int(item.get("count") or 0),
    }


def nombre_limpio(datos: dict) -> str:
    nombre = str(datos.get("nombre") or "").strip()
    if not nombre:
        raise TiendaError("Escribe el nombre de la categoria", 400)
    return nombre


def listar_categorias(pagina: int = 1, por_pagina: int = 10, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.get(
        "products/categories",
        params={
            "per_page": por_pagina,
            "page": pagina,
            "orderby": "name",
            "order": "asc",
            "hide_empty": False,
        },
    )
    if respuesta.status_code != 200:
        raise TiendaError(mensaje_tienda(respuesta), 502)
    lote = respuesta.json()
    if not isinstance(lote, list):
        lote = []
    total = int(respuesta.headers.get("X-WP-Total") or len(lote))
    return {
        "categorias": [resumir(item) for item in lote],
        "total": total,
        "pagina": pagina,
        "por_pagina": por_pagina,
    }


def crear_categoria(datos: dict, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.post("products/categories", {"name": nombre_limpio(datos)})
    if respuesta.status_code not in (200, 201):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    return resumir(respuesta.json())


def actualizar_categoria(categoria_id: int, datos: dict, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.put(f"products/categories/{categoria_id}", {"name": nombre_limpio(datos)})
    if respuesta.status_code not in (200, 201):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    return resumir(respuesta.json())


def eliminar_categoria(categoria_id: int, cliente=None) -> None:
    api = cliente or cliente_woocommerce()
    respuesta = api.delete(f"products/categories/{categoria_id}", params={"force": True})
    if respuesta.status_code not in (200, 204):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
