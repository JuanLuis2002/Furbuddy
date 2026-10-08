from pydantic import BaseModel, Field


class UsuarioSalida(BaseModel):
    id: int
    usuario: str
    nombre: str
    email: str
    rol: str


class UsuarioEntrada(BaseModel):
    nombre: str = Field(min_length=1)
    apellido: str = Field(min_length=1)
    usuario: str = Field(min_length=1)
    email: str = Field(min_length=3, pattern=r"^[^@\s]+@[^@\s]+\.[^@\s]+$")
    password: str = Field(min_length=8)
