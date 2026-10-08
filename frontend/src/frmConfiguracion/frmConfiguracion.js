import { leerSesion } from "../frmLogin/frmLogin.js"

async function pedir(ruta, opciones, fetchImpl) {
  const sesion = leerSesion()
  const respuesta = await fetchImpl(ruta, {
    ...opciones,
    headers: {
      Authorization: `Bearer ${sesion?.token}`,
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

export function leerPerfil(fetchImpl = fetch) {
  return pedir("/api/perfil", { method: "GET" }, fetchImpl)
}

export function guardarPerfil(datos, fetchImpl = fetch) {
  return pedir(
    "/api/perfil",
    {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos),
    },
    fetchImpl,
  )
}
