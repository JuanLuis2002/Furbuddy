from pydantic import BaseModel, Field, field_validator


class UsuarioSalida(BaseModel):
    id: int
    usuario: str
    nombre: str
    apellido: str
    email: str
    rol: str


class PaginaUsuarios(BaseModel):
    usuarios: list[UsuarioSalida]
    total: int
    pagina: int
    por_pagina: int


class UsuarioEntrada(BaseModel):
    nombre: str = Field(min_length=1)
    apellido: str = Field(min_length=1)
    usuario: str = Field(min_length=1)
    email: str = Field(min_length=3, pattern=r"^[^@\s]+@[^@\s]+\.[^@\s]+$")
    password: str = Field(min_length=8)


class UsuarioEdicion(BaseModel):
    nombre: str = Field(min_length=1)
    apellido: str = Field(min_length=1)
    email: str = Field(min_length=3, pattern=r"^[^@\s]+@[^@\s]+\.[^@\s]+$")
    password: str = ""

    @field_validator("password")
    @classmethod
    def clave_opcional(cls, valor: str) -> str:
        if valor and len(valor) < 8:
            raise ValueError("Minimo 8 caracteres")
        return valor
