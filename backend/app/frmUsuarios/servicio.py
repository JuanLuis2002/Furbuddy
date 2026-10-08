from app.configuracion import claves_woocommerce, url_tienda


class TiendaNoConfigurada(Exception):
    pass


class TiendaError(Exception):
    def __init__(self, mensaje: str, codigo: int = 502):
        super().__init__(mensaje)
        self.mensaje = mensaje
        self.codigo = codigo


def cliente_woocommerce():
    clave, secreto = claves_woocommerce()
    if not clave or not secreto:
        raise TiendaNoConfigurada()
    from woocommerce import API

    return API(
        url=url_tienda(),
        consumer_key=clave,
        consumer_secret=secreto,
        version="wc/v3",
        timeout=20,
    )


def resumir(item: dict) -> dict:
    nombre = f"{item.get('first_name', '')} {item.get('last_name', '')}".strip()
    return {
        "id": item["id"],
        "usuario": item.get("username") or "",
        "nombre": nombre,
        "email": item.get("email") or "",
        "rol": item.get("role") or "customer",
    }


def mensaje_tienda(respuesta) -> str:
    try:
        cuerpo = respuesta.json()
    except ValueError:
        return "WooCommerce no respondio"
    if isinstance(cuerpo, dict) and cuerpo.get("message"):
        return str(cuerpo["message"])
    return "WooCommerce rechazo la operacion"


def listar_usuarios(pagina: int = 1, por_pagina: int = 10, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.get(
        "customers",
        params={"per_page": por_pagina, "role": "all", "page": pagina},
    )
    if respuesta.status_code != 200:
        raise TiendaError(mensaje_tienda(respuesta), 502)
    lote = respuesta.json()
    if not isinstance(lote, list):
        lote = []
    total = int(respuesta.headers.get("X-WP-Total") or len(lote))
    return {
        "usuarios": [resumir(item) for item in lote],
        "total": total,
        "pagina": pagina,
        "por_pagina": por_pagina,
    }


def crear_usuario(datos: dict, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.post(
        "customers",
        {
            "email": datos["email"],
            "first_name": datos["nombre"],
            "last_name": datos["apellido"],
            "username": datos["usuario"],
            "password": datos["password"],
        },
    )
    if respuesta.status_code not in (200, 201):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    return resumir(respuesta.json())


def eliminar_usuario(usuario_id: int, cliente=None) -> None:
    api = cliente or cliente_woocommerce()
    respuesta = api.delete(f"customers/{usuario_id}", params={"force": True})
    if respuesta.status_code not in (200, 204):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
