from fastapi import APIRouter, HTTPException

from app.frmLogin.schemas import LoginEntrada, LoginSalida
from app.frmLogin.servicio import (
    CredencialesInvalidas,
    SinPermiso,
    TiendaNoDisponible,
    autenticar_wordpress,
)
from app.sesion import SesionNoConfigurada, crear_token

router = APIRouter(prefix="/api", tags=["frmLogin"])


@router.post("/login", response_model=LoginSalida)
def login(datos: LoginEntrada):
    try:
        usuario = autenticar_wordpress(datos.usuario, datos.password)
        token = crear_token(usuario)
    except CredencialesInvalidas as error:
        raise HTTPException(status_code=401, detail="Usuario o contrasena incorrectos") from error
    except SinPermiso as error:
        raise HTTPException(status_code=403, detail="Esta cuenta no puede entrar al panel") from error
    except SesionNoConfigurada as error:
        raise HTTPException(status_code=503, detail="Falta configurar la sesion") from error
    except TiendaNoDisponible as error:
        raise HTTPException(status_code=502, detail="No se pudo contactar la tienda") from error
    return LoginSalida(token=token, **usuario)
