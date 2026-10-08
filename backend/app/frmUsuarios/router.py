from fastapi import APIRouter, Depends, Header, HTTPException, Query

from app.frmUsuarios.schemas import PaginaUsuarios, UsuarioEntrada, UsuarioSalida
from app.frmUsuarios.servicio import (
    TiendaError,
    TiendaNoConfigurada,
    crear_usuario,
    eliminar_usuario,
    listar_usuarios,
)
from app.sesion import leer_token

router = APIRouter(prefix="/api", tags=["frmUsuarios"])


def sesion_actual(authorization: str = Header(default="")) -> dict:
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Debes iniciar sesion")
    datos = leer_token(authorization.removeprefix("Bearer ").strip())
    if not datos:
        raise HTTPException(status_code=401, detail="La sesion no es valida")
    return datos


@router.get("/usuarios", response_model=PaginaUsuarios)
def usuarios(
    pagina: int = Query(default=1, ge=1),
    por_pagina: int = Query(default=10, ge=1, le=100),
    sesion: dict = Depends(sesion_actual),
):
    try:
        return listar_usuarios(pagina, por_pagina)
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.post("/usuarios", response_model=UsuarioSalida, status_code=201)
def nuevo_usuario(datos: UsuarioEntrada, sesion: dict = Depends(sesion_actual)):
    try:
        return crear_usuario(datos.model_dump())
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.delete("/usuarios/{usuario_id}", status_code=204)
def quitar_usuario(usuario_id: int, sesion: dict = Depends(sesion_actual)):
    try:
        eliminar_usuario(usuario_id)
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error
