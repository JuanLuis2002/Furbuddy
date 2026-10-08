import { describe, expect, it } from "vitest"
import { iniciarSesion } from "./frmLogin.js"

describe("iniciarSesion", () => {
  it("devuelve el token cuando la API acepta el usuario", async () => {
    const datos = await iniciarSesion("ana", "clave-larga", async () => ({
      ok: true,
      json: async () => ({ token: "abc", usuario: "ana", nombre: "Ana" }),
    }))

    expect(datos.token).toBe("abc")
    expect(datos.usuario).toBe("ana")
  })

  it("explica cuando las credenciales fallan", async () => {
    await expect(iniciarSesion("ana", "mala", async () => ({
      ok: false,
      json: async () => ({ detail: "Usuario o contrasena incorrectos" }),
    }))).rejects.toThrow("Usuario o contrasena incorrectos")
  })
})
