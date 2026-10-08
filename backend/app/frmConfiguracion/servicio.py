from app.frmUsuarios.servicio import (
    TiendaError,
    cliente_woocommerce,
    mensaje_tienda,
)


def resumir_perfil(item: dict) -> dict:
    return {
        "id": item["id"],
        "usuario": item.get("username") or "",
        "nombre": item.get("first_name") or "",
        "apellido": item.get("last_name") or "",
        "email": item.get("email") or "",
    }


def leer_perfil(usuario_id: int, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    respuesta = api.get(f"customers/{usuario_id}")
    if respuesta.status_code != 200:
        raise TiendaError(mensaje_tienda(respuesta), 502 if respuesta.status_code >= 500 else 400)
    return resumir_perfil(respuesta.json())


def guardar_perfil(usuario_id: int, datos: dict, cliente=None) -> dict:
    api = cliente or cliente_woocommerce()
    cuerpo = {
        "first_name": datos["nombre"],
        "last_name": datos["apellido"],
        "email": datos["email"],
    }
    if datos.get("password"):
        cuerpo["password"] = datos["password"]
    respuesta = api.put(f"customers/{usuario_id}", cuerpo)
    if respuesta.status_code not in (200, 201):
        codigo = 400 if respuesta.status_code < 500 else 502
        raise TiendaError(mensaje_tienda(respuesta), codigo)
    return resumir_perfil(respuesta.json())
