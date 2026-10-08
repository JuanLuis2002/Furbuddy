import base64
import hashlib
import hmac
import json
import time

from app.configuracion import secreto_sesion

DURACION_SEGUNDOS = 8 * 60 * 60


class SesionNoConfigurada(Exception):
    pass


def crear_token(usuario: dict) -> str:
    secreto = secreto_sesion()
    if not secreto:
        raise SesionNoConfigurada()
    cuerpo = {
        "id": usuario["id"],
        "usuario": usuario["usuario"],
        "exp": int(time.time()) + DURACION_SEGUNDOS,
    }
    raw = json.dumps(cuerpo, separators=(",", ":")).encode()
    firma = hmac.new(secreto.encode(), raw, hashlib.sha256).hexdigest()
    return base64.urlsafe_b64encode(raw).decode() + "." + firma


def leer_token(token: str) -> dict | None:
    secreto = secreto_sesion()
    if not secreto or "." not in token:
        return None
    codificado, firma = token.rsplit(".", 1)
    try:
        raw = base64.urlsafe_b64decode(codificado.encode())
    except ValueError:
        return None
    esperada = hmac.new(secreto.encode(), raw, hashlib.sha256).hexdigest()
    if not hmac.compare_digest(esperada, firma):
        return None
    datos = json.loads(raw.decode())
    if int(datos.get("exp", 0)) < time.time():
        return None
    return datos
