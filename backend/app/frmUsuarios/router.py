from fastapi import APIRouter, Depends, Header, HTTPException

from app.frmUsuarios.schemas import UsuarioEntrada, UsuarioSalida
from app.frmUsuarios.servicio import TiendaError, TiendaNoConfigurada, crear_usuario, listar_usuarios
from app.sesion import leer_token

router = APIRouter(prefix="/api", tags=["frmUsuarios"])


def sesion_actual(authorization: str = Header(default="")) -> dict:
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Debes iniciar sesion")
    datos = leer_token(authorization.removeprefix("Bearer ").strip())
    if not datos:
        raise HTTPException(status_code=401, detail="La sesion no es valida")
    return datos


@router.get("/usuarios", response_model=list[UsuarioSalida])
def usuarios(sesion: dict = Depends(sesion_actual)):
    try:
        return listar_usuarios()
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
