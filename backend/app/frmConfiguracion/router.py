from fastapi import APIRouter, Depends, Header, HTTPException

from app.frmConfiguracion.schemas import PerfilEntrada, PerfilSalida
from app.frmConfiguracion.servicio import guardar_perfil, leer_perfil
from app.frmUsuarios.servicio import TiendaError, TiendaNoConfigurada
from app.sesion import leer_token

router = APIRouter(prefix="/api", tags=["frmConfiguracion"])


def sesion_actual(authorization: str = Header(default="")) -> dict:
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Debes iniciar sesion")
    datos = leer_token(authorization.removeprefix("Bearer ").strip())
    if not datos:
        raise HTTPException(status_code=401, detail="La sesion no es valida")
    return datos


@router.get("/perfil", response_model=PerfilSalida)
def perfil(sesion: dict = Depends(sesion_actual)):
    try:
        return leer_perfil(sesion["id"])
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error


@router.put("/perfil", response_model=PerfilSalida)
def actualizar_perfil(datos: PerfilEntrada, sesion: dict = Depends(sesion_actual)):
    try:
        return guardar_perfil(sesion["id"], datos.model_dump())
    except TiendaNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar WooCommerce") from error
    except TiendaError as error:
        raise HTTPException(status_code=error.codigo, detail=error.mensaje) from error
