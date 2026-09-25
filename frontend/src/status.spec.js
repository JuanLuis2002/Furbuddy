import { describe, expect, it } from "vitest"
import { cargarEstado } from "./status.js"

describe("cargarEstado", () => {
  it("devuelve el estado de la API", async () => {
    const datos = await cargarEstado(async () => ({
      ok: true,
      json: async () => ({ ok: true, servicio: "furbuddy-api" }),
    }))

    expect(datos.ok).toBe(true)
    expect(datos.servicio).toBe("furbuddy-api")
  })
})
