import { beforeEach, describe, expect, it, vi } from "vitest"
import { guardarPerfil, leerPerfil } from "./frmConfiguracion.js"

beforeEach(() => {
  const memoria = new Map()
  vi.stubGlobal("sessionStorage", {
    getItem: (clave) => memoria.get(clave) ?? null,
    setItem: (clave, valor) => memoria.set(clave, valor),
    removeItem: (clave) => memoria.delete(clave),
  })
})

describe("frmConfiguracion", () => {
  it("pide el perfil con la sesion", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let encabezado = ""
    const perfil = await leerPerfil(async (ruta, opciones) => {
      encabezado = opciones.headers.Authorization
      expect(ruta).toBe("/api/perfil")
      return {
        ok: true,
        status: 200,
        json: async () => ({ id: 1, nombre: "Samuel", apellido: "Enrique", email: "samuel@furbuddy.xyz" }),
      }
    })
    expect(encabezado).toBe("Bearer abc")
    expect(perfil.nombre).toBe("Samuel")
  })

  it("envia el perfil editado", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let cuerpo = ""
    await guardarPerfil({ nombre: "Samuel", apellido: "Enrique", email: "samuel@furbuddy.xyz", password: "" }, async (_ruta, opciones) => {
      cuerpo = opciones.body
      expect(opciones.method).toBe("PUT")
      return { ok: true, status: 200, json: async () => ({ nombre: "Samuel" }) }
    })
    expect(JSON.parse(cuerpo).email).toBe("samuel@furbuddy.xyz")
  })
})
