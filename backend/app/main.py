from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse

from app.frmCategorias.router import router as router_categorias
from app.frmConfiguracion.router import router as router_configuracion
from app.frmLogin.router import router as router_login
from app.frmProductos.router import router as router_productos
from app.frmUsuarios.router import router as router_usuarios

app = FastAPI(title="Furbuddy API", docs_url=None)

SCALAR_HTML = """<!doctype html>
<html>
  <head>
    <title>Furbuddy API</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  </head>
  <body>
    <script id="api-reference" data-url="/openapi.json"></script>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
  </body>
</html>
"""

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(router_login)
app.include_router(router_usuarios)
app.include_router(router_productos)
app.include_router(router_categorias)
app.include_router(router_configuracion)


@app.get("/docs", include_in_schema=False)
def scalar_docs():
    return HTMLResponse(SCALAR_HTML)


@app.get("/api/status")
def status():
    return {"ok": True, "servicio": "furbuddy-api"}
