import re
from typing import Literal

from pydantic import BaseModel, Field, field_validator


def precio_valido(valor: str) -> str:
    if valor and not re.fullmatch(r"\d+(\.\d{1,2})?", valor):
        raise ValueError("Precio no valido")
    return valor


class FotoSalida(BaseModel):
    id: int
    src: str


class CategoriaSalida(BaseModel):
    id: int
    nombre: str


class CategoriaEntrada(BaseModel):
    nombre: str = Field(min_length=1, max_length=80)


class AtributoEntrada(BaseModel):
    id: int = 0
    nombre: str = Field(min_length=1)
    opciones: list[str] = Field(min_length=1)


class AtributoSalida(BaseModel):
    id: int = 0
    nombre: str
    opciones: list[str]


class VariacionEntrada(BaseModel):
    id: int | None = None
    atributos: dict[str, str]
    precio: str
    existencia: int = Field(ge=0)
    sku: str = ""
    foto_id: int | None = None

    @field_validator("precio")
    @classmethod
    def validar_precio(cls, valor: str) -> str:
        return precio_valido(valor)


class VariacionSalida(BaseModel):
    id: int
    atributos: dict[str, str]
    precio: str
    existencia: int
    sku: str
    foto_id: int | None = None
    foto: str = ""


class ProductoEntrada(BaseModel):
    nombre: str = Field(min_length=1)
    tipo: Literal["simple", "variable"]
    descripcion: str = ""
    precio: str = ""
    existencia: int = Field(default=0, ge=0)
    sku: str = ""
    publicado: bool = True
    fotos: list[int] = []
    categorias: list[int] = Field(min_length=1)
    atributos: list[AtributoEntrada] = []
    variaciones: list[VariacionEntrada] = []

    @field_validator("precio")
    @classmethod
    def validar_precio(cls, valor: str) -> str:
        return precio_valido(valor)


class ProductoResumen(BaseModel):
    id: int
    nombre: str
    tipo: str
    precio: str
    existencia: str
    sku: str
    foto: str


class ProductoDetalle(ProductoResumen):
    descripcion: str
    publicado: bool
    fotos: list[FotoSalida]
    categorias: list[CategoriaSalida]
    atributos: list[AtributoSalida]
    variaciones: list[VariacionSalida]


class PropuestaAi(BaseModel):
    tipo: Literal["simple", "variable"]
    nombre: str
    descripcion: str
    precio: str = ""
    precio_enlace: str = ""
    atributos: list[AtributoSalida]
    categorias: list[CategoriaSalida] = []
    fotos: list[FotoSalida]


class PaginaProductos(BaseModel):
    productos: list[ProductoResumen]
    total: int
    pagina: int
    por_pagina: int
