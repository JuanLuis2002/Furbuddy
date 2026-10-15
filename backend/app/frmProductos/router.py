from fastapi import APIRouter, Depends, File, Form, Header, HTTPException, Query, UploadFile

from app.frmProductos.schemas import FotoSalida, PaginaProductos, ProductoDetalle, ProductoEntrada, PropuestaAi
from app.frmProductos.servicio import (
    GeminiNoConfigurado,
    TiendaError,
    TiendaNoConfigurada,
    eliminar_producto,
    guardar_producto,
    leer_producto,
    listar_productos,
    proponer_producto,
    subir_foto,
)
from app.sesion import leer_token

router = APIRouter(prefix="/api", tags=["frmProductos"])
TIPOS_FOTO = {"image/jpeg", "image/png", "image/webp", "image/gif"}
PESO_MAXIMO = 8 * 1024 * 1024


def sesion_actual(authorization: str = Header(default="")) -> dict:
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Debes iniciar sesion")
    datos = leer_token(authorization.removeprefix("Bearer ").strip())
    if not datos:
        raise HTTPException(status_code=401, detail="La sesion no es valida")
    return datos


def responder(accion):
    try:
        return accion()
    except GeminiNoConfigurado as error:
        raise HTTPException(status_code=503, detail="Falta configurar Gemini") from error
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.get("/productos", response_model=PaginaProductos)
def productos(
    pagina: int = Query(default=1, ge=1),
    por_pagina: int = Query(default=10, ge=1, le=100),
    sesion: dict = Depends(sesion_actual),
):
    return responder(lambda: listar_productos(pagina, por_pagina))


@router.post("/productos/ai", response_model=PropuestaAi)
async def producto_con_ai(
    tipo: str = Form(),
    archivo: UploadFile = File(),
    sesion: dict = Depends(sesion_actual),
):
    if tipo not in ("simple", "variable"):
        raise HTTPException(status_code=400, detail="Elige un producto normal o variable")
    tipo_mime = archivo.content_type or ""
    if tipo_mime not in TIPOS_FOTO:
        raise HTTPException(status_code=400, detail="Usa JPG, PNG, WEBP o GIF")
    contenido = await archivo.read()
    if not contenido:
        raise HTTPException(status_code=400, detail="La foto esta vacia")
    if len(contenido) > PESO_MAXIMO:
        raise HTTPException(status_code=400, detail="La foto pesa mas de 8 MB")
    return responder(
        lambda: proponer_producto(archivo.filename or "foto", contenido, tipo_mime, tipo)
    )


@router.post("/productos/fotos", response_model=FotoSalida)
async def nueva_foto(archivo: UploadFile = File(), sesion: dict = Depends(sesion_actual)):
    tipo = archivo.content_type or ""
    if tipo not in TIPOS_FOTO:
        raise HTTPException(status_code=400, detail="Usa JPG, PNG, WEBP o GIF")
    contenido = await archivo.read()
    if not contenido:
        raise HTTPException(status_code=400, detail="La foto esta vacia")
    if len(contenido) > PESO_MAXIMO:
        raise HTTPException(status_code=400, detail="La foto pesa mas de 8 MB")
    return responder(lambda: subir_foto(archivo.filename or "foto", contenido, tipo))


@router.get("/productos/{producto_id}", response_model=ProductoDetalle)
def producto(producto_id: int, sesion: dict = Depends(sesion_actual)):
    return responder(lambda: leer_producto(producto_id))


@router.post("/productos", response_model=ProductoDetalle, status_code=201)
def nuevo_producto(datos: ProductoEntrada, sesion: dict = Depends(sesion_actual)):
    return responder(lambda: guardar_producto(datos.model_dump()))


@router.put("/productos/{producto_id}", response_model=ProductoDetalle)
def cambiar_producto(producto_id: int, datos: ProductoEntrada, sesion: dict = Depends(sesion_actual)):
    return responder(lambda: guardar_producto(datos.model_dump(), producto_id))


@router.delete("/productos/{producto_id}", status_code=204)
def quitar_producto(producto_id: int, sesion: dict = Depends(sesion_actual)):
    responder(lambda: eliminar_producto(producto_id))
