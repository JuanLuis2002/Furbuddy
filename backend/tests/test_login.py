from fastapi.testclient import TestClient

from app.frmLogin.servicio import CredencialesInvalidas, SinPermiso
from app.frmUsuarios.servicio import TiendaError
from app.main import app
from app.sesion import crear_token

client = TestClient(app)


def preparar_sesion(monkeypatch):
    monkeypatch.setenv("FURBUDDY_SESION_SECRETO", "secreto-de-prueba")


def test_login_devuelve_token(monkeypatch):
    preparar_sesion(monkeypatch)
    monkeypatch.setattr(
        "app.frmLogin.router.autenticar_wordpress",
        lambda usuario, password: {
            "id": 4,
            "usuario": usuario,
            "nombre": "Ana",
            "email": "ana@furbuddy.xyz",
        },
    )

    respuesta = client.post("/api/login", json={"usuario": "ana", "password": "clave-larga"})

    assert respuesta.status_code == 200
    cuerpo = respuesta.json()
    assert cuerpo["usuario"] == "ana"
    assert cuerpo["token"]


def test_login_rechaza_credenciales(monkeypatch):
    preparar_sesion(monkeypatch)

    def fallar(usuario, password):
        raise CredencialesInvalidas()

    monkeypatch.setattr("app.frmLogin.router.autenticar_wordpress", fallar)
    respuesta = client.post("/api/login", json={"usuario": "ana", "password": "mala"})
    assert respuesta.status_code == 401


def test_login_rechaza_cliente(monkeypatch):
    preparar_sesion(monkeypatch)

    def fallar(usuario, password):
        raise SinPermiso()

    monkeypatch.setattr("app.frmLogin.router.autenticar_wordpress", fallar)
    respuesta = client.post("/api/login", json={"usuario": "cliente", "password": "clave-larga"})
    assert respuesta.status_code == 403


def test_usuarios_pide_sesion():
    respuesta = client.get("/api/usuarios")
    assert respuesta.status_code == 401


def test_usuarios_lista_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 1, "usuario": "admin"})
    def pagina(numero, tamano):
        assert numero == 2
        assert tamano == 5
        return {
            "usuarios": [{
                "id": 9,
                "usuario": "luz",
                "nombre": "Luz",
                "apellido": "Perez",
                "email": "luz@furbuddy.xyz",
                "rol": "customer",
            }],
            "total": 12,
            "pagina": numero,
            "por_pagina": tamano,
        }

    monkeypatch.setattr("app.frmUsuarios.router.listar_usuarios", pagina)

    respuesta = client.get(
        "/api/usuarios?pagina=2&por_pagina=5",
        headers={"Authorization": f"Bearer {token}"},
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["total"] == 12
    assert respuesta.json()["usuarios"][0]["usuario"] == "luz"


def test_usuarios_crea_en_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 1, "usuario": "admin"})
    monkeypatch.setattr(
        "app.frmUsuarios.router.crear_usuario",
        lambda datos: {
            "id": 10,
            "usuario": datos["usuario"],
            "nombre": datos["nombre"],
            "apellido": datos["apellido"],
            "email": datos["email"],
            "rol": "customer",
        },
    )

    respuesta = client.post(
        "/api/usuarios",
        headers={"Authorization": f"Bearer {token}"},
        json={
            "nombre": "Leo",
            "apellido": "Diaz",
            "usuario": "leo",
            "email": "leo@furbuddy.xyz",
            "password": "clave-larga",
        },
    )
    assert respuesta.status_code == 201
    assert respuesta.json()["email"] == "leo@furbuddy.xyz"


def test_crear_usuario_muestra_error_de_tienda(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 1, "usuario": "admin"})

    def fallar(datos):
        raise TiendaError("El correo ya existe", 400)

    monkeypatch.setattr("app.frmUsuarios.router.crear_usuario", fallar)
    respuesta = client.post(
        "/api/usuarios",
        headers={"Authorization": f"Bearer {token}"},
        json={
            "nombre": "Leo",
            "apellido": "Diaz",
            "usuario": "leo",
            "email": "leo@furbuddy.xyz",
            "password": "clave-larga",
        },
    )
    assert respuesta.status_code == 400


def test_servicio_crea_customer_de_woocommerce():
    from app.frmUsuarios.servicio import crear_usuario

    class Cliente:
        def __init__(self):
            self.enviado = None

        def post(self, endpoint, data):
            self.enviado = (endpoint, data)
            return Respuesta(201, {
                "id": 3,
                "username": data["username"],
                "first_name": data["first_name"],
                "last_name": data["last_name"],
                "email": data["email"],
                "role": "customer",
            })

    cliente = Cliente()
    creado = crear_usuario(
        {
            "nombre": "Leo",
            "apellido": "Diaz",
            "usuario": "leo",
            "email": "leo@furbuddy.xyz",
            "password": "clave-larga",
        },
        cliente,
    )
    assert cliente.enviado[0] == "customers"
    assert cliente.enviado[1]["email"] == "leo@furbuddy.xyz"
    assert creado["nombre"] == "Leo"
    assert creado["apellido"] == "Diaz"
    assert creado["rol"] == "customer"


def test_usuarios_actualiza_en_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 1, "usuario": "admin"})
    monkeypatch.setattr(
        "app.frmUsuarios.router.actualizar_usuario",
        lambda usuario_id, datos: {
            "id": usuario_id,
            "usuario": "leo",
            "nombre": datos["nombre"],
            "apellido": datos["apellido"],
            "email": datos["email"],
            "rol": "customer",
        },
    )

    respuesta = client.put(
        "/api/usuarios/10",
        headers={"Authorization": f"Bearer {token}"},
        json={
            "nombre": "Leo",
            "apellido": "Diaz",
            "email": "leo@furbuddy.xyz",
            "password": "",
        },
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["apellido"] == "Diaz"


def test_servicio_actualiza_customer_de_woocommerce():
    from app.frmUsuarios.servicio import actualizar_usuario

    class Cliente:
        def __init__(self):
            self.enviado = None

        def put(self, endpoint, data):
            self.enviado = (endpoint, data)
            return Respuesta(200, {
                "id": 10,
                "username": "leo",
                "first_name": data["first_name"],
                "last_name": data["last_name"],
                "email": data["email"],
                "role": "customer",
            })

    cliente = Cliente()
    actualizado = actualizar_usuario(10, {
        "nombre": "Leo",
        "apellido": "Diaz",
        "email": "leo@furbuddy.xyz",
        "password": "clave-larga",
    }, cliente)
    assert cliente.enviado[0] == "customers/10"
    assert cliente.enviado[1]["password"] == "clave-larga"
    assert "username" not in cliente.enviado[1]
    assert actualizado["nombre"] == "Leo"
    actualizar_usuario(10, {
        "nombre": "Leo",
        "apellido": "Diaz",
        "email": "leo@furbuddy.xyz",
        "password": "",
    }, cliente)
    assert "password" not in cliente.enviado[1]


def test_usuarios_elimina_en_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    token = crear_token({"id": 1, "usuario": "admin"})
    borrados = []
    monkeypatch.setattr("app.frmUsuarios.router.eliminar_usuario", lambda usuario_id: borrados.append(usuario_id))

    respuesta = client.delete("/api/usuarios/9", headers={"Authorization": f"Bearer {token}"})
    assert respuesta.status_code == 204
    assert borrados == [9]


def test_servicio_lista_y_borra_customers():
    from app.frmUsuarios.servicio import eliminar_usuario, listar_usuarios

    class Cliente:
        def __init__(self):
            self.borrado = None

        def get(self, endpoint, params):
            assert endpoint == "customers"
            assert params["role"] == "all"
            assert params["page"] == 2
            assert params["per_page"] == 5
            return Respuesta(200, [{
                "id": 9,
                "username": "luz",
                "first_name": "Luz",
                "last_name": "Perez",
                "email": "luz@furbuddy.xyz",
                "role": "administrator",
            }], {"X-WP-Total": "12"})

        def delete(self, endpoint, params):
            self.borrado = (endpoint, params)
            return Respuesta(200, {})

    cliente = Cliente()
    lista = listar_usuarios(2, 5, cliente)
    eliminar_usuario(9, cliente)
    assert lista["total"] == 12
    assert lista["usuarios"][0]["nombre"] == "Luz"
    assert lista["usuarios"][0]["apellido"] == "Perez"
    assert lista["usuarios"][0]["rol"] == "administrator"
    assert cliente.borrado == ("customers/9", {"force": True})


class Respuesta:
    def __init__(self, status_code, cuerpo, headers=None):
        self.status_code = status_code
        self._cuerpo = cuerpo
        self.headers = headers or {}

    def json(self):
        return self._cuerpo
