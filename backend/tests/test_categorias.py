from fastapi.testclient import TestClient

from app.frmCategorias.servicio import TiendaError
from app.main import app
from app.sesion import crear_token

client = TestClient(app)


def preparar_sesion(monkeypatch):
    monkeypatch.setenv("FURBUDDY_SESION_SECRETO", "secreto-de-prueba")


def token():
    return crear_token({"id": 1, "usuario": "admin"})


def test_categorias_pide_sesion():
    respuesta = client.get("/api/categorias")
    assert respuesta.status_code == 401


def test_categorias_lista(monkeypatch):
    preparar_sesion(monkeypatch)

    def pagina(numero, tamano):
        assert numero == 2
        assert tamano == 5
        return {
            "categorias": [{"id": 4, "nombre": "Juguetes", "productos": 3}],
            "total": 8,
            "pagina": numero,
            "por_pagina": tamano,
        }

    monkeypatch.setattr("app.frmCategorias.router.listar_categorias", pagina)
    respuesta = client.get(
        "/api/categorias?pagina=2&por_pagina=5",
        headers={"Authorization": f"Bearer {token()}"},
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["total"] == 8
    assert respuesta.json()["categorias"][0]["nombre"] == "Juguetes"


def test_categorias_crea(monkeypatch):
    preparar_sesion(monkeypatch)
    monkeypatch.setattr(
        "app.frmCategorias.router.crear_categoria",
        lambda datos: {"id": 9, "nombre": datos["nombre"], "productos": 0},
    )
    respuesta = client.post(
        "/api/categorias",
        headers={"Authorization": f"Bearer {token()}"},
        json={"nombre": "Camas"},
    )
    assert respuesta.status_code == 201
    assert respuesta.json()["nombre"] == "Camas"


def test_categorias_muestra_error_de_tienda(monkeypatch):
    preparar_sesion(monkeypatch)

    def fallar(datos):
        raise TiendaError("Esa categoria ya existe", 400)

    monkeypatch.setattr("app.frmCategorias.router.crear_categoria", fallar)
    respuesta = client.post(
        "/api/categorias",
        headers={"Authorization": f"Bearer {token()}"},
        json={"nombre": "Camas"},
    )
    assert respuesta.status_code == 400
    assert respuesta.json()["detail"] == "Esa categoria ya existe"


def test_categorias_actualiza(monkeypatch):
    preparar_sesion(monkeypatch)
    monkeypatch.setattr(
        "app.frmCategorias.router.actualizar_categoria",
        lambda categoria_id, datos: {"id": categoria_id, "nombre": datos["nombre"], "productos": 2},
    )
    respuesta = client.put(
        "/api/categorias/9",
        headers={"Authorization": f"Bearer {token()}"},
        json={"nombre": "Camas grandes"},
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["nombre"] == "Camas grandes"
    assert respuesta.json()["id"] == 9


def test_categorias_elimina(monkeypatch):
    preparar_sesion(monkeypatch)
    borradas = []
    monkeypatch.setattr("app.frmCategorias.router.eliminar_categoria", lambda categoria_id: borradas.append(categoria_id))
    respuesta = client.delete("/api/categorias/9", headers={"Authorization": f"Bearer {token()}"})
    assert respuesta.status_code == 204
    assert borradas == [9]


def test_servicio_lista_crea_edita_y_borra():
    from app.frmCategorias.servicio import (
        actualizar_categoria,
        crear_categoria,
        eliminar_categoria,
        listar_categorias,
    )

    class Cliente:
        def __init__(self):
            self.enviados = []

        def get(self, endpoint, params=None):
            self.enviados.append(("get", endpoint, params))
            return Respuesta(200, [{"id": 4, "name": "Juguetes", "count": 3}], {"X-WP-Total": "8"})

        def post(self, endpoint, data):
            self.enviados.append(("post", endpoint, data))
            return Respuesta(201, {"id": 9, "name": data["name"], "count": 0})

        def put(self, endpoint, data):
            self.enviados.append(("put", endpoint, data))
            return Respuesta(200, {"id": 9, "name": data["name"], "count": 2})

        def delete(self, endpoint, params=None):
            self.enviados.append(("delete", endpoint, params))
            return Respuesta(200, {})

    cliente = Cliente()
    lista = listar_categorias(2, 5, cliente)
    creada = crear_categoria({"nombre": "  Camas  "}, cliente)
    editada = actualizar_categoria(9, {"nombre": "Camas grandes"}, cliente)
    eliminar_categoria(9, cliente)
    assert lista["total"] == 8
    assert lista["categorias"][0] == {"id": 4, "nombre": "Juguetes", "productos": 3}
    assert cliente.enviados[0][2]["page"] == 2
    assert cliente.enviados[0][2]["hide_empty"] is False
    assert creada == {"id": 9, "nombre": "Camas", "productos": 0}
    assert cliente.enviados[1] == ("post", "products/categories", {"name": "Camas"})
    assert editada["nombre"] == "Camas grandes"
    assert cliente.enviados[2] == ("put", "products/categories/9", {"name": "Camas grandes"})
    assert cliente.enviados[3] == ("delete", "products/categories/9", {"force": True})


class Respuesta:
    def __init__(self, status_code, cuerpo, headers=None):
        self.status_code = status_code
        self._cuerpo = cuerpo
        self.headers = headers or {}

    def json(self):
        return self._cuerpo
