from pydantic import BaseModel, Field


class CategoriaSalida(BaseModel):
    id: int
    nombre: str
    productos: int


class PaginaCategorias(BaseModel):
    categorias: list[CategoriaSalida]
    total: int
    pagina: int
    por_pagina: int


class CategoriaEntrada(BaseModel):
    nombre: str = Field(min_length=1, max_length=80)
