from fastapi import APIRouter, Depends, Header, HTTPException, Query

from app.frmCategorias.schemas import CategoriaEntrada, CategoriaSalida, PaginaCategorias
from app.frmCategorias.servicio import (
    TiendaError,
    TiendaNoConfigurada,
    actualizar_categoria,
    crear_categoria,
    eliminar_categoria,
    listar_categorias,
)
from app.sesion import leer_token

router = APIRouter(prefix="/api", tags=["frmCategorias"])


def sesion_actual(authorization: str = Header(default="")) -> dict:
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Debes iniciar sesion")
    datos = leer_token(authorization.removeprefix("Bearer ").strip())
    if not datos:
        raise HTTPException(status_code=401, detail="La sesion no es valida")
    return datos


@router.get("/categorias", response_model=PaginaCategorias)
def categorias(
    pagina: int = Query(default=1, ge=1),
    por_pagina: int = Query(default=10, ge=1, le=100),
    sesion: dict = Depends(sesion_actual),
):
    try:
        return listar_categorias(pagina, por_pagina)
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.post("/categorias", response_model=CategoriaSalida, status_code=201)
def nueva_categoria(datos: CategoriaEntrada, sesion: dict = Depends(sesion_actual)):
    try:
        return crear_categoria(datos.model_dump())
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.put("/categorias/{categoria_id}", response_model=CategoriaSalida)
def cambiar_categoria(categoria_id: int, datos: CategoriaEntrada, sesion: dict = Depends(sesion_actual)):
    try:
        return actualizar_categoria(categoria_id, datos.model_dump())
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.delete("/categorias/{categoria_id}", status_code=204)
def quitar_categoria(categoria_id: int, sesion: dict = Depends(sesion_actual)):
    try:
        eliminar_categoria(categoria_id)
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error
