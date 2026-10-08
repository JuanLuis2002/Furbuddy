from fastapi.testclient import TestClient

from app.main import app
from app.sesion import crear_token

client = TestClient(app)


def preparar_sesion(monkeypatch):
    monkeypatch.setenv("FURBUDDY_SESION_SECRETO", "secreto-de-prueba")


def test_perfil_pide_sesion():
    respuesta = client.get("/api/perfil")
    assert respuesta.status_code == 401


def test_perfil_guarda_en_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 4, "usuario": "Samuel"})

    def guardar(usuario_id, datos):
        assert usuario_id == 4
        assert datos["email"] == "samuel@furbuddy.xyz"
        assert datos["password"] == ""
        return {
            "id": usuario_id,
            "usuario": "Samuel",
            "nombre": datos["nombre"],
            "apellido": datos["apellido"],
            "email": datos["email"],
        }

    monkeypatch.setattr("app.frmConfiguracion.router.guardar_perfil", guardar)
    respuesta = client.put(
        "/api/perfil",
        headers={"Authorization": f"Bearer {token}"},
        json={
            "nombre": "Samuel",
            "apellido": "Enrique",
            "email": "samuel@furbuddy.xyz",
            "password": "",
        },
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["apellido"] == "Enrique"


def test_servicio_actualiza_customer():
    from app.frmConfiguracion.servicio import guardar_perfil

    class Cliente:
        def __init__(self):
            self.enviado = None

        def put(self, endpoint, data):
            self.enviado = (endpoint, data)
            return Respuesta(200, {
                "id": 4,
                "username": "Samuel",
                "first_name": data["first_name"],
                "last_name": data["last_name"],
                "email": data["email"],
            })

    cliente = Cliente()
    perfil = guardar_perfil(4, {
        "nombre": "Samuel",
        "apellido": "Enrique",
        "email": "samuel@furbuddy.xyz",
        "password": "clave-larga",
    }, cliente)
    assert cliente.enviado[0] == "customers/4"
    assert cliente.enviado[1]["password"] == "clave-larga"
    assert perfil["apellido"] == "Enrique"


class Respuesta:
    def __init__(self, status_code, cuerpo):
        self.status_code = status_code
        self._cuerpo = cuerpo

    def json(self):
        return self._cuerpo
