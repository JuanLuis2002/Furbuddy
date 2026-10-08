from pydantic import BaseModel, Field


class LoginEntrada(BaseModel):
    usuario: str = Field(min_length=1)
    password: str = Field(min_length=1)


class LoginSalida(BaseModel):
    token: str
    id: int
    usuario: str
    nombre: str
    email: str
