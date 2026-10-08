from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def test_status_responde_ok():
    respuesta = client.get("/api/status")
    assert respuesta.status_code == 200
    cuerpo = respuesta.json()
    assert cuerpo["ok"] is True
    assert cuerpo["servicio"] == "furbuddy-api"


def test_docs_abre_scalar():
    respuesta = client.get("/docs")
    assert respuesta.status_code == 200
    cuerpo = respuesta.text
    assert "@scalar/api-reference" in cuerpo
    assert "/openapi.json" in cuerpo
    assert "swagger-ui" not in cuerpo
