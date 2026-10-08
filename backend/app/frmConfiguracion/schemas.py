from pydantic import BaseModel, Field, field_validator


class PerfilSalida(BaseModel):
    id: int
    usuario: str
    nombre: str
    apellido: str
    email: str


class PerfilEntrada(BaseModel):
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
