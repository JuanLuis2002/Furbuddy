import { leerSesion } from "../frmLogin/frmLogin.js"

const LADO_MAXIMO_AI = 1280
const CALIDAD_AI = 0.82
const PESO_MINIMO_AI = 350 * 1024

export async function aligerarFoto(archivo) {
  if (!archivo || !String(archivo.type || "").startsWith("image/")) {
    return archivo
  }
  if (archivo.type === "image/gif") {
    return archivo
  }
  try {
    const mapa = await createImageBitmap(archivo)
    try {
      const lado = Math.max(mapa.width, mapa.height)
      const escala = Math.min(1, LADO_MAXIMO_AI / lado)
      if (escala >= 1 && archivo.size <= PESO_MINIMO_AI && archivo.type !== "image/png") {
        return archivo
      }
      const ancho = Math.max(1, Math.round(mapa.width * escala))
      const alto = Math.max(1, Math.round(mapa.height * escala))
      const lienzo = document.createElement("canvas")
      lienzo.width = ancho
      lienzo.height = alto
      const pintura = lienzo.getContext("2d")
      if (!pintura) {
        return archivo
      }
      pintura.drawImage(mapa, 0, 0, ancho, alto)
      const blob = await new Promise((resolver) => {
        lienzo.toBlob(resolver, "image/jpeg", CALIDAD_AI)
      })
      if (!blob || blob.size >= archivo.size) {
        return archivo
      }
      const base = String(archivo.name || "foto").replace(/\.[^.]+$/, "")
      return new File([blob], `${base}.jpg`, { type: "image/jpeg", lastModified: Date.now() })
    } finally {
      mapa.close?.()
    }
  } catch {
    return archivo
  }
}

export async function aligerarFotos(archivos) {
  return Promise.all((archivos || []).map((archivo) => aligerarFoto(archivo)))
}

async function pedir(ruta, token, opciones, fetchImpl, avisoCaida) {
  const respuesta = await fetchImpl(ruta, {
    ...opciones,
    headers: {
      Authorization: `Bearer ${token}`,
      ...(opciones.headers || {}),
    },
  })
  const datos = await respuesta.json().catch(() => ({}))
  if (respuesta.status === 401) {
    throw new Error("La sesion vencio")
  }
  if (!respuesta.ok) {
    const detalle = typeof datos.detail === "string" ? datos.detail : ""
    if (!detalle && avisoCaida && (respuesta.status === 502 || respuesta.status === 504)) {
      throw new Error(avisoCaida)
    }
    throw new Error(detalle || "No se pudo completar la operacion")
  }
  return datos
}

export async function listarCategorias(fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir("/api/productos/categorias", sesion?.token, { method: "GET" }, fetchImpl)
}

export async function crearCategoria(nombre, fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir(
    "/api/productos/categorias",
    sesion?.token,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ nombre }),
    },
    fetchImpl,
  )
}

export async function listarProductos(pagina, porPagina, fetchImpl = fetch) {
  const sesion = leerSesion()
  const ruta = `/api/productos?pagina=${pagina}&por_pagina=${porPagina}`
  return pedir(ruta, sesion?.token, { method: "GET" }, fetchImpl)
}

export async function leerProducto(id, fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir(`/api/productos/${id}`, sesion?.token, { method: "GET" }, fetchImpl)
}

export async function guardarProducto(datos, id, fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir(
    id ? `/api/productos/${id}` : "/api/productos",
    sesion?.token,
    {
      method: id ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos),
    },
    fetchImpl,
  )
}

export async function eliminarProducto(id, fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir(`/api/productos/${id}`, sesion?.token, { method: "DELETE" }, fetchImpl)
}

export async function proponerProducto(tipo, archivos, fetchImpl = fetch) {
  const sesion = leerSesion()
  const livianos = await aligerarFotos(archivos)
  const cuerpo = new FormData()
  cuerpo.append("tipo", tipo)
  for (const archivo of livianos) {
    cuerpo.append("archivos", archivo)
  }
  return pedir(
    "/api/productos/ai",
    sesion?.token,
    { method: "POST", body: cuerpo },
    fetchImpl,
    "El analisis tardo demasiado. Pulsa Analizar otra vez.",
  )
}

export async function subirFoto(archivo, fetchImpl = fetch) {
  const sesion = leerSesion()
  const cuerpo = new FormData()
  cuerpo.append("archivo", archivo)
  return pedir("/api/productos/fotos", sesion?.token, { method: "POST", body: cuerpo }, fetchImpl)
}
