import { beforeEach, describe, expect, it, vi } from "vitest"
import { guardarProducto, listarProductos, proponerProducto, subirFoto } from "./frmProductos.js"

beforeEach(() => {
  const memoria = new Map()
  vi.stubGlobal("sessionStorage", {
    getItem: (clave) => memoria.get(clave) ?? null,
    setItem: (clave, valor) => memoria.set(clave, valor),
    removeItem: (clave) => memoria.delete(clave),
  })
  sessionStorage.setItem("furbuddy-sesion", JSON.stringify({ token: "abc" }))
})

describe("frmProductos", () => {
  it("pide la pagina de productos", async () => {
    const lista = await listarProductos(2, 5, async (ruta, opciones) => {
      expect(ruta).toBe("/api/productos?pagina=2&por_pagina=5")
      expect(opciones.headers.Authorization).toBe("Bearer abc")
      return {
        ok: true,
        status: 200,
        json: async () => ({ productos: [{ id: 4, nombre: "Gorra" }], total: 15 }),
      }
    })
    expect(lista.total).toBe(15)
  })

  it("envia el producto variable a editar", async () => {
    let cuerpo = ""
    await guardarProducto({ nombre: "Gorra", tipo: "variable" }, 4, async (ruta, opciones) => {
      cuerpo = opciones.body
      expect(ruta).toBe("/api/productos/4")
      expect(opciones.method).toBe("PUT")
      return { ok: true, status: 200, json: async () => ({ id: 4 }) }
    })
    expect(JSON.parse(cuerpo).tipo).toBe("variable")
  })

  it("envia el tipo y las fotos a la AI", async () => {
    const primera = new File(["foto"], "gorra.webp", { type: "image/webp" })
    const segunda = new File(["detalle"], "detalle.jpg", { type: "image/jpeg" })
    await proponerProducto("variable", [primera, segunda], async (ruta, opciones) => {
      expect(ruta).toBe("/api/productos/ai")
      expect(opciones.body).toBeInstanceOf(FormData)
      expect(opciones.body.get("tipo")).toBe("variable")
      expect(opciones.body.getAll("archivos")).toHaveLength(2)
      return {
        ok: true,
        status: 200,
        json: async () => ({ nombre: "Gorra", tipo: "variable" }),
      }
    })
  })

  it("sube la foto como archivo", async () => {
    const archivo = new File(["foto"], "gorra.webp", { type: "image/webp" })
    await subirFoto(archivo, async (ruta, opciones) => {
      expect(ruta).toBe("/api/productos/fotos")
      expect(opciones.body).toBeInstanceOf(FormData)
      expect(opciones.headers["Content-Type"]).toBeUndefined()
      return { ok: true, status: 200, json: async () => ({ id: 3, src: "https://furbuddy.xyz/gorra.webp" }) }
    })
  })
})
