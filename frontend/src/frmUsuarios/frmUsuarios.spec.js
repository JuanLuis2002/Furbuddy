import { beforeEach, describe, expect, it, vi } from "vitest"
import { crearUsuario, editarUsuario, eliminarUsuario, listarUsuarios } from "./frmUsuarios.js"

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
    const lista = await listarUsuarios(2, 5, async (ruta, opciones) => {
      encabezado = opciones.headers.Authorization
      expect(ruta).toBe("/api/usuarios?pagina=2&por_pagina=5")
      return {
        ok: true,
        status: 200,
        json: async () => ({ usuarios: [{ id: 1, usuario: "luz" }], total: 12 }),
      }
    })
    expect(encabezado).toBe("Bearer abc")
    expect(lista.usuarios[0].usuario).toBe("luz")
    expect(lista.total).toBe(12)
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

  it("envia la edicion del usuario a la API", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let metodo = ""
    let cuerpo = ""
    await editarUsuario(9, { nombre: "Leo", apellido: "Diaz", email: "leo@furbuddy.xyz", password: "" }, async (ruta, opciones) => {
      metodo = opciones.method
      cuerpo = opciones.body
      expect(ruta).toBe("/api/usuarios/9")
      return { ok: true, status: 200, json: async () => ({ id: 9, nombre: "Leo" }) }
    })
    expect(metodo).toBe("PUT")
    expect(JSON.parse(cuerpo).apellido).toBe("Diaz")
    expect(JSON.parse(cuerpo).password).toBe("")
  })

  it("pide borrar el usuario en WooCommerce", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let metodo = ""
    await eliminarUsuario(9, async (ruta, opciones) => {
      metodo = opciones.method
      expect(ruta).toBe("/api/usuarios/9")
      return { ok: true, status: 204, json: async () => ({}) }
    })
    expect(metodo).toBe("DELETE")
  })
})
