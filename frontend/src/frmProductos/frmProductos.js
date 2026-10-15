import { leerSesion } from "../frmLogin/frmLogin.js"

async function pedir(ruta, token, opciones, fetchImpl) {
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
    throw new Error(datos.detail || "No se pudo completar la operacion")
  }
  return datos
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

export async function proponerProducto(tipo, archivo, fetchImpl = fetch) {
  const sesion = leerSesion()
  const cuerpo = new FormData()
  cuerpo.append("tipo", tipo)
  cuerpo.append("archivo", archivo)
  return pedir("/api/productos/ai", sesion?.token, { method: "POST", body: cuerpo }, fetchImpl)
}

export async function subirFoto(archivo, fetchImpl = fetch) {
  const sesion = leerSesion()
  const cuerpo = new FormData()
  cuerpo.append("archivo", archivo)
  return pedir("/api/productos/fotos", sesion?.token, { method: "POST", body: cuerpo }, fetchImpl)
}
