from fastapi.testclient import TestClient

from app.main import app
from app.sesion import crear_token

client = TestClient(app)


def preparar_sesion(monkeypatch):
    monkeypatch.setenv("FURBUDDY_SESION_SECRETO", "secreto-de-prueba")


def token():
    return crear_token({"id": 1, "usuario": "admin"})


def test_productos_pide_sesion():
    respuesta = client.get("/api/productos")
    assert respuesta.status_code == 401


def test_productos_lista_woocommerce(monkeypatch):
    preparar_sesion(monkeypatch)
    monkeypatch.setattr(
        "app.frmProductos.router.listar_productos",
        lambda pagina, por_pagina: {
            "productos": [{
                "id": 4,
                "nombre": "Gorra",
                "tipo": "variable",
                "precio": "12",
                "existencia": "En existencia",
                "sku": "",
                "foto": "https://furbuddy.xyz/gorra.webp",
            }],
            "total": 15,
            "pagina": pagina,
            "por_pagina": por_pagina,
        },
    )
    respuesta = client.get(
        "/api/productos?pagina=2&por_pagina=5",
        headers={"Authorization": f"Bearer {token()}"},
    )
    assert respuesta.status_code == 200
    assert respuesta.json()["total"] == 15
    assert respuesta.json()["productos"][0]["tipo"] == "variable"


def test_servicio_crea_producto_simple():
    from app.frmProductos.servicio import guardar_producto

    cliente = Cliente()
    creado = guardar_producto(
        {
            "nombre": "Pelota",
            "tipo": "simple",
            "descripcion": "Suave",
            "precio": "8.50",
            "existencia": 4,
            "sku": "PEL-1",
            "publicado": True,
            "fotos": [3],
            "categorias": [12],
            "atributos": [],
            "variaciones": [],
        },
        cliente=cliente,
    )
    endpoint, cuerpo = cliente.enviados[0]
    assert endpoint == ("post", "products")
    assert cuerpo["regular_price"] == "8.50"
    assert cuerpo["images"] == [{"id": 3}]
    assert cuerpo["categories"] == [{"id": 12}]
    assert cuerpo["description"] == "Suave"
    assert creado["nombre"] == "Pelota"
    assert creado["variaciones"] == []


def test_servicio_crea_variaciones():
    from app.frmProductos.servicio import guardar_producto

    cliente = Cliente()
    creado = guardar_producto(
        {
            "nombre": "Gorra",
            "tipo": "variable",
            "descripcion": "",
            "precio": "",
            "existencia": 0,
            "sku": "",
            "publicado": True,
            "fotos": [1],
            "categorias": [12],
            "atributos": [{"id": 0, "nombre": "Color", "opciones": ["Rojo", "Azul"]}],
            "variaciones": [
                {"id": None, "atributos": {"Color": "Rojo"}, "precio": "10", "existencia": 2, "sku": "", "foto_id": None},
                {"id": None, "atributos": {"Color": "Azul"}, "precio": "11", "existencia": 1, "sku": "AZ", "foto_id": 8},
            ],
        },
        cliente=cliente,
    )
    productos = [item for item in cliente.enviados if item[0] == ("post", "products")]
    variaciones = [item for item in cliente.enviados if item[0] == ("post", "products/5/variations")]
    assert productos[0][1]["type"] == "variable"
    assert productos[0][1]["attributes"][0]["options"] == ["Rojo", "Azul"]
    assert len(variaciones) == 2
    assert variaciones[1][1]["image"] == {"id": 8}
    assert creado["tipo"] == "variable"
    assert len(creado["variaciones"]) == 2


def test_servicio_borra_variacion_que_ya_no_esta():
    from app.frmProductos.servicio import guardar_producto

    cliente = Cliente(variaciones_iniciales=[9, 10])
    guardar_producto(
        {
            "nombre": "Gorra",
            "tipo": "variable",
            "descripcion": "",
            "precio": "",
            "existencia": 0,
            "sku": "",
            "publicado": False,
            "fotos": [],
            "categorias": [12],
            "atributos": [{"id": 2, "nombre": "Color", "opciones": ["Rojo"]}],
            "variaciones": [
                {"id": 9, "atributos": {"Color": "Rojo"}, "precio": "10", "existencia": 2, "sku": "", "foto_id": None},
            ],
        },
        producto_id=5,
        cliente=cliente,
    )
    cuerpo = next(item[1] for item in cliente.enviados if item[0] == ("put", "products/5"))
    assert cuerpo["status"] == "draft"
    assert cuerpo["attributes"][0]["id"] == 2
    assert ("put", "products/5/variations/9") in [item[0] for item in cliente.enviados]
    assert ("delete", "products/5/variations/10") in [item[0] for item in cliente.enviados]


def test_servicio_lista_y_crea_categoria():
    from app.frmProductos.servicio import crear_categoria, listar_categorias

    class Api:
        def get(self, endpoint, params=None):
            assert endpoint == "products/categories"
            return Respuesta(200, [{"id": 4, "name": "Juguetes"}], {"X-WP-Total": "1"})

        def post(self, endpoint, data):
            assert endpoint == "products/categories"
            assert data["name"] == "Camas"
            return Respuesta(201, {"id": 9, "name": "Camas"})

    api = Api()
    assert listar_categorias(cliente=api) == [{"id": 4, "nombre": "Juguetes"}]
    assert crear_categoria("Camas", cliente=api) == {"id": 9, "nombre": "Camas"}


def test_categorias_lista(monkeypatch):
    preparar_sesion(monkeypatch)
    monkeypatch.setattr(
        "app.frmProductos.router.listar_categorias",
        lambda: [{"id": 4, "nombre": "Juguetes"}],
    )
    respuesta = client.get(
        "/api/productos/categorias",
        headers={"Authorization": f"Bearer {token()}"},
    )
    assert respuesta.status_code == 200
    assert respuesta.json() == [{"id": 4, "nombre": "Juguetes"}]


def test_foto_rechaza_un_archivo_que_no_es_imagen(monkeypatch):
    preparar_sesion(monkeypatch)
    respuesta = client.post(
        "/api/productos/fotos",
        headers={"Authorization": f"Bearer {token()}"},
        files={"archivo": ("notas.txt", b"hola", "text/plain")},
    )
    assert respuesta.status_code == 400


def test_interpreta_la_propuesta_variable():
    from app.frmProductos.servicio import interpretar_propuesta

    propuesta = interpretar_propuesta(
        '{"detectado":true,"aviso":"","nombre":"Gorra deportiva","descripcion":"Comoda para salir.","precio":"8.50","categoria":"Accesorios","atributos":[{"nombre":"Color","opciones":["Rojo","Azul","Rojo"]}]}',
        "variable",
    )
    assert propuesta["nombre"] == "Gorra deportiva"
    assert propuesta["categoria"] == "Accesorios"
    assert propuesta["precio"] == "8.5"
    assert propuesta["atributos"][0]["opciones"] == ["Rojo", "Azul"]


def test_producto_normal_no_guarda_caracteristicas():
    from app.frmProductos.servicio import interpretar_propuesta

    propuesta = interpretar_propuesta(
        '{"detectado":true,"aviso":"","nombre":"Pelota","descripcion":"Rebota.","precio":"$12","categoria":"Juguetes","atributos":[{"nombre":"Color","opciones":["Rojo"]}]}',
        "simple",
    )
    assert propuesta["tipo"] == "simple"
    assert propuesta["atributos"] == []
    assert propuesta["categoria"] == "Juguetes"
    assert propuesta["precio"] == "12"


def test_aviso_si_no_detecta_la_imagen():
    from app.frmProductos.servicio import TiendaError, interpretar_propuesta

    try:
        interpretar_propuesta(
            '{"detectado":false,"aviso":"La foto esta negra y no se ve el producto","nombre":"","descripcion":"","precio":"","categoria":"","atributos":[]}',
            "simple",
        )
    except TiendaError as error:
        assert error.codigo == 422
        assert "No se pudo detectar la imagen" in error.mensaje
        assert "negra" in error.mensaje
    else:
        raise AssertionError("Debia avisar que no detecto la imagen")


def test_variable_sin_caracteristicas_avisa():
    from app.frmProductos.servicio import TiendaError, interpretar_propuesta

    try:
        interpretar_propuesta(
            '{"detectado":true,"aviso":"","nombre":"Cama","descripcion":"Suave.","precio":"20","categoria":"Camas","atributos":[]}',
            "variable",
        )
    except TiendaError as error:
        assert error.codigo == 422
        assert "No se pudo detectar la imagen" in error.mensaje
    else:
        raise AssertionError("Debia avisar que faltan caracteristicas")


def test_resuelve_categoria_existente():
    from app.frmProductos.servicio import resolver_categoria_ai

    existentes = [{"id": 3, "nombre": "Accesorios"}, {"id": 5, "nombre": "Juguetes"}]
    assert resolver_categoria_ai("accesorios", existentes)["id"] == 3
    assert resolver_categoria_ai("juguete", existentes)["id"] == 5


def test_crea_categoria_si_no_existe(monkeypatch):
    from app.frmProductos.servicio import resolver_categoria_ai

    monkeypatch.setattr(
        "app.frmProductos.servicio.crear_categoria",
        lambda nombre, cliente=None: {"id": 88, "nombre": nombre},
    )
    resultado = resolver_categoria_ai("Relojes", [{"id": 3, "nombre": "Accesorios"}])
    assert resultado == {"id": 88, "nombre": "Relojes"}

def test_ai_pide_el_tipo(monkeypatch):
    preparar_sesion(monkeypatch)
    respuesta = client.post(
        "/api/productos/ai",
        headers={"Authorization": f"Bearer {token()}"},
        data={"tipo": "otro"},
        files=[("archivos", ("gorra.webp", b"abc", "image/webp"))],
    )
    assert respuesta.status_code == 400


def test_ai_devuelve_la_propuesta(monkeypatch):
    preparar_sesion(monkeypatch)

    def proponer(fotos, tipo_producto):
        assert tipo_producto == "variable"
        assert [foto[1] for foto in fotos] == [b"abc", b"def"]
        return {
            "tipo": "variable",
            "nombre": "Gorra",
            "descripcion": "Ligera.",
            "atributos": [{"id": 0, "nombre": "Color", "opciones": ["Rojo"]}],
            "precio": "9.99",
            "categorias": [{"id": 3, "nombre": "Accesorios"}],
            "fotos": [
                {"id": 3, "src": "https://furbuddy.xyz/gorra.webp"},
                {"id": 4, "src": "https://furbuddy.xyz/gorra-2.webp"},
            ],
        }

    monkeypatch.setattr("app.frmProductos.router.proponer_producto", proponer)
    respuesta = client.post(
        "/api/productos/ai",
        headers={"Authorization": f"Bearer {token()}"},
        data={"tipo": "variable"},
        files=[
            ("archivos", ("gorra.webp", b"abc", "image/webp")),
            ("archivos", ("detalle.jpg", b"def", "image/jpg")),
        ],
    )
    assert respuesta.status_code == 200
    assert [foto["id"] for foto in respuesta.json()["fotos"]] == [3, 4]
    assert respuesta.json()["atributos"][0]["nombre"] == "Color"


def _categorias_ai_fijas(monkeypatch):
    monkeypatch.setattr(
        "app.frmProductos.servicio.listar_categorias",
        lambda cliente=None: [{"id": 3, "nombre": "Accesorios"}],
    )
    monkeypatch.setattr(
        "app.frmProductos.servicio.crear_categoria",
        lambda nombre, cliente=None: {"id": 88, "nombre": nombre},
    )


def test_reintenta_si_gemini_falla_una_vez(monkeypatch):
    from app.frmProductos.servicio import analizar_fotos

    _categorias_ai_fijas(monkeypatch)
    llamadas = {"n": 0}

    class Respuesta:
        text = '{"detectado":true,"aviso":"","nombre":"Pelota","descripcion":"Rebota suave.","precio":"5.00","categoria":"Juguetes","atributos":[]}'

    class Modelos:
        @staticmethod
        def generate_content(**kwargs):
            llamadas["n"] += 1
            if llamadas["n"] == 1:
                raise RuntimeError("Cannot send a request, as the client has been closed.")
            return Respuesta()

    class Cliente:
        def __init__(self, api_key):
            self.models = Modelos()

        def close(self):
            return None

    creados = {"n": 0}

    class ClienteContado(Cliente):
        def __init__(self, api_key):
            creados["n"] += 1
            super().__init__(api_key)

    monkeypatch.delenv("GROQ_API_KEY", raising=False)
    monkeypatch.setenv("GEMINI_API_KEY", "AQ.prueba")
    monkeypatch.setattr("google.genai.Client", ClienteContado)
    monkeypatch.setattr("app.frmProductos.servicio.time.sleep", lambda segundos: None)
    propuesta = analizar_fotos([(b"abc", "image/webp")], "simple")
    assert llamadas["n"] == 2
    assert creados["n"] == 1
    assert propuesta["nombre"] == "Pelota"
    assert propuesta["categorias"][0]["nombre"] == "Juguetes"


def test_usa_groq_antes_que_gemini(monkeypatch):
    from app.frmProductos.servicio import analizar_fotos

    _categorias_ai_fijas(monkeypatch)
    monkeypatch.setenv("GROQ_API_KEY", "gsk_prueba")
    monkeypatch.setenv("GEMINI_API_KEY", "AQ.prueba")

    def groq(fotos, tipo_producto, categorias_existentes=None):
        assert len(fotos) == 1
        return '{"detectado":true,"aviso":"","nombre":"Collar","descripcion":"De tela.","precio":"7.5","categoria":"Accesorios","atributos":[]}'

    def gemini(fotos, tipo_producto, categorias_existentes=None):
        raise AssertionError("No debia llamar a Gemini")

    monkeypatch.setattr("app.frmProductos.servicio.texto_groq", groq)
    monkeypatch.setattr("app.frmProductos.servicio.texto_gemini", gemini)
    propuesta = analizar_fotos([(b"abc", "image/webp")], "simple")
    assert propuesta["nombre"] == "Collar"
    assert propuesta["categorias"][0]["id"] == 3


def test_si_groq_falla_usa_gemini(monkeypatch):
    from app.frmProductos.servicio import analizar_fotos

    _categorias_ai_fijas(monkeypatch)
    monkeypatch.setenv("GROQ_API_KEY", "gsk_prueba")
    monkeypatch.setenv("GEMINI_API_KEY", "AQ.prueba")

    def groq(fotos, tipo_producto, categorias_existentes=None):
        raise RuntimeError("rate limit")

    def gemini(fotos, tipo_producto, categorias_existentes=None):
        return '{"detectado":true,"aviso":"","nombre":"Pelota","descripcion":"Rebota.","precio":"4","categoria":"Juguetes","atributos":[]}'

    monkeypatch.setattr("app.frmProductos.servicio.texto_groq", groq)
    monkeypatch.setattr("app.frmProductos.servicio.texto_gemini", gemini)
    propuesta = analizar_fotos([(b"abc", "image/webp")], "simple")
    assert propuesta["nombre"] == "Pelota"


def test_proponer_sube_fotos_en_paralelo(monkeypatch):
    from app.frmProductos.servicio import proponer_producto

    orden = []

    def analizar(fotos, tipo_producto):
        return {
            "tipo": tipo_producto,
            "nombre": "Gorra",
            "descripcion": "Ligera.",
            "precio": "8",
            "atributos": [],
            "categorias": [{"id": 3, "nombre": "Accesorios"}],
        }
    def subir(nombre, contenido, tipo):
        orden.append(nombre)
        return {"id": len(orden), "src": f"https://furbuddy.xyz/{nombre}"}

    monkeypatch.setattr("app.frmProductos.servicio.analizar_fotos", analizar)
    monkeypatch.setattr("app.frmProductos.servicio.subir_foto", subir)
    propuesta = proponer_producto(
        [("a.webp", b"1", "image/webp"), ("b.webp", b"2", "image/webp"), ("c.webp", b"3", "image/webp")],
        "simple",
    )
    assert [foto["id"] for foto in propuesta["fotos"]] == [1, 2, 3]
    assert set(orden) == {"a.webp", "b.webp", "c.webp"}


def test_ai_rechaza_mas_de_seis_fotos(monkeypatch):
    preparar_sesion(monkeypatch)
    respuesta = client.post(
        "/api/productos/ai",
        headers={"Authorization": f"Bearer {token()}"},
        data={"tipo": "simple"},
        files=[("archivos", (f"{indice}.webp", b"abc", "image/webp")) for indice in range(7)],
    )
    assert respuesta.status_code == 400


def test_servicio_sube_foto_a_wordpress(monkeypatch):
    from app.frmProductos.servicio import subir_foto

    monkeypatch.setenv("WOOCOMMERCE_KEY", "ck_prueba")
    monkeypatch.setenv("WOOCOMMERCE_SECRET", "cs_prueba")
    enviados = {}

    def post(url, files, auth, timeout):
        enviados["url"] = url
        enviados["nombre"] = files["archivo"][0]
        enviados["auth"] = auth
        return Respuesta(201, {"id": 15, "src": "https://furbuddy.xyz/foto.webp"})

    monkeypatch.setattr("requests.post", post)
    foto = subir_foto("gorra.webp", b"abc", "image/webp")
    assert enviados["url"] == "https://furbuddy.xyz/wp-json/furbuddy/v1/media"
    assert enviados["nombre"] == "gorra.webp"
    assert enviados["auth"] == ("ck_prueba", "cs_prueba")
    assert foto["id"] == 15


class Respuesta:
    def __init__(self, status_code, cuerpo, headers=None):
        self.status_code = status_code
        self._cuerpo = cuerpo
        self.headers = headers or {}

    def json(self):
        return self._cuerpo


class Cliente:
    def __init__(self, variaciones_iniciales=None):
        self.enviados = []
        self.producto = None
        self.variaciones = [
            {
                "id": variacion_id,
                "regular_price": "10",
                "price": "10",
                "sku": "",
                "manage_stock": True,
                "stock_quantity": 1,
                "stock_status": "instock",
                "attributes": [{"name": "Color", "option": "Rojo"}],
                "image": {},
            }
            for variacion_id in (variaciones_iniciales or [])
        ]

    def _producto(self, data, producto_id=5):
        return {
            "id": producto_id,
            "name": data.get("name", "Gorra"),
            "type": data.get("type", "variable"),
            "status": data.get("status", "publish"),
            "description": data.get("description", ""),
            "short_description": data.get("short_description", ""),
            "regular_price": data.get("regular_price", ""),
            "price": data.get("regular_price", ""),
            "sku": data.get("sku", ""),
            "manage_stock": data.get("manage_stock", False),
            "stock_quantity": data.get("stock_quantity"),
            "stock_status": data.get("stock_status", "instock"),
            "images": [{"id": imagen["id"], "src": "https://furbuddy.xyz/foto.webp"} for imagen in data.get("images") or []],
            "attributes": [
                {"id": atributo.get("id") or 0, "name": atributo["name"], "variation": True, "options": atributo["options"]}
                for atributo in data.get("attributes") or []
            ],
        }

    def _variacion(self, data, variacion_id):
        return {
            "id": variacion_id,
            "regular_price": data["regular_price"],
            "price": data["regular_price"],
            "sku": data.get("sku") or "",
            "manage_stock": True,
            "stock_quantity": data["stock_quantity"],
            "stock_status": data["stock_status"],
            "attributes": [{"name": atributo["name"], "option": atributo["option"]} for atributo in data["attributes"]],
            "image": data.get("image") or {},
        }

    def get(self, endpoint, params=None):
        self.enviados.append((("get", endpoint), params))
        if endpoint.endswith("/variations"):
            return Respuesta(200, list(self.variaciones), {"X-WP-Total": str(len(self.variaciones))})
        return Respuesta(200, self.producto or self._producto({"name": "Gorra", "type": "variable"}))

    def post(self, endpoint, data):
        self.enviados.append((("post", endpoint), data))
        if endpoint.endswith("/variations"):
            creada = self._variacion(data, 20 + len(self.variaciones))
            self.variaciones.append(creada)
            return Respuesta(201, creada)
        self.producto = self._producto(data)
        return Respuesta(201, self.producto)

    def put(self, endpoint, data):
        self.enviados.append((("put", endpoint), data))
        if "/variations/" in endpoint:
            return Respuesta(200, self._variacion(data, int(endpoint.rsplit("/", 1)[-1])))
        self.producto = self._producto(data)
        return Respuesta(200, self.producto)

    def delete(self, endpoint, params=None):
        self.enviados.append((("delete", endpoint), params))
        variacion_id = int(endpoint.rsplit("/", 1)[-1])
        self.variaciones = [variacion for variacion in self.variaciones if variacion["id"] != variacion_id]
        return Respuesta(200, {})
