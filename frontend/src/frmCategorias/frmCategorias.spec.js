import { beforeEach, describe, expect, it, vi } from "vitest"
import { crearCategoria, editarCategoria, eliminarCategoria, listarCategorias } from "./frmCategorias.js"

beforeEach(() => {
  const memoria = new Map()
  vi.stubGlobal("sessionStorage", {
    getItem: (clave) => memoria.get(clave) ?? null,
    setItem: (clave, valor) => memoria.set(clave, valor),
    removeItem: (clave) => memoria.delete(clave),
  })
})

describe("frmCategorias", () => {
  it("pide la lista con la sesion guardada", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let encabezado = ""
    const lista = await listarCategorias(2, 5, async (ruta, opciones) => {
      encabezado = opciones.headers.Authorization
      expect(ruta).toBe("/api/categorias?pagina=2&por_pagina=5")
      return {
        ok: true,
        status: 200,
        json: async () => ({ categorias: [{ id: 4, nombre: "Juguetes" }], total: 8 }),
      }
    })
    expect(encabezado).toBe("Bearer abc")
    expect(lista.categorias[0].nombre).toBe("Juguetes")
    expect(lista.total).toBe(8)
  })

  it("envia la categoria nueva a la API", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let cuerpo = ""
    await crearCategoria({ nombre: "Camas" }, async (ruta, opciones) => {
      cuerpo = opciones.body
      expect(ruta).toBe("/api/categorias")
      expect(opciones.method).toBe("POST")
      return { ok: true, status: 201, json: async () => ({ id: 9, nombre: "Camas" }) }
    })
    expect(JSON.parse(cuerpo).nombre).toBe("Camas")
  })

  it("envia la edicion de la categoria a la API", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let cuerpo = ""
    await editarCategoria(9, { nombre: "Camas grandes" }, async (ruta, opciones) => {
      cuerpo = opciones.body
      expect(ruta).toBe("/api/categorias/9")
      expect(opciones.method).toBe("PUT")
      return { ok: true, status: 200, json: async () => ({ id: 9, nombre: "Camas grandes" }) }
    })
    expect(JSON.parse(cuerpo).nombre).toBe("Camas grandes")
  })

  it("pide borrar la categoria en WooCommerce", async () => {
    sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
    let metodo = ""
    await eliminarCategoria(9, async (ruta, opciones) => {
      metodo = opciones.method
      expect(ruta).toBe("/api/categorias/9")
      return { ok: true, status: 204, json: async () => ({}) }
    })
    expect(metodo).toBe("DELETE")
  })
})
