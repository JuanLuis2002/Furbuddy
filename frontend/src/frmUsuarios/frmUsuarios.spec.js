import { beforeEach, describe, expect, it, vi } from "vitest"
import { crearUsuario, listarUsuarios } from "./frmUsuarios.js"

beforeEach(() => {
  const memoria = new Map()
  vi.stubGlobal("sessionStorage", {
    getItem: (clave) => memoria.get(clave) ?? null,
    setItem: (clave, valor) => memoria.set(clave, valor),
    removeItem: (clave) => memoria.delete(clave),
  })
})

describe("frmUsuarios", () => {
  it("pide la lista con la sesion guardada", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let encabezado = ""
    const lista = await listarUsuarios(async (ruta, opciones) => {
      encabezado = opciones.headers.Authorization
      expect(ruta).toBe("/api/usuarios")
      return { ok: true, status: 200, json: async () => [{ id: 1, usuario: "luz" }] }
    })
    expect(encabezado).toBe("Bearer abc")
    expect(lista[0].usuario).toBe("luz")
  })

  it("envia el usuario nuevo a la API", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let cuerpo = ""
    await crearUsuario({ usuario: "leo", email: "leo@furbuddy.xyz" }, async (_ruta, opciones) => {
      cuerpo = opciones.body
      return { ok: true, status: 201, json: async () => ({ id: 2, usuario: "leo" }) }
    })
    expect(JSON.parse(cuerpo).usuario).toBe("leo")
  })
})
