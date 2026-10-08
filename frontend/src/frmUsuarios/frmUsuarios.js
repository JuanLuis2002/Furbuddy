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

export async function listarUsuarios(fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir("/api/usuarios", sesion?.token, { method: "GET" }, fetchImpl)
}

export async function crearUsuario(datos, fetchImpl = fetch) {
  const sesion = leerSesion()
  return pedir(
    "/api/usuarios",
    sesion?.token,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos),
    },
    fetchImpl,
  )
}
