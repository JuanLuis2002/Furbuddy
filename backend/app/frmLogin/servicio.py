import json
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

from app.configuracion import url_tienda


class CredencialesInvalidas(Exception):
    pass


class SinPermiso(Exception):
    pass


class TiendaNoDisponible(Exception):
    pass


def autenticar_wordpress(usuario: str, password: str) -> dict:
    cuerpo = json.dumps({"usuario": usuario, "password": password}).encode()
    peticion = Request(
        f"{url_tienda()}/wp-json/furbuddy/v1/login",
        data=cuerpo,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with urlopen(peticion, timeout=15) as respuesta:
            return json.loads(respuesta.read().decode())
    except HTTPError as error:
        if error.code == 401:
            raise CredencialesInvalidas() from error
        if error.code == 403:
            raise SinPermiso() from error
        raise TiendaNoDisponible() from error
    except URLError as error:
        raise TiendaNoDisponible() from error
