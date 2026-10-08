export async function iniciarSesion(usuario, password, fetchImpl = fetch) {
  const respuesta = await fetchImpl("/api/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ usuario, password }),
  })
  const datos = await respuesta.json().catch(() => ({}))
  if (!respuesta.ok) {
    throw new Error(datos.detail || "No se pudo iniciar sesion")
  }
  return datos
}

const LLAVE = "furbuddy-sesion"

export function guardarSesion(datos) {
  sessionStorage.setItem(LLAVE, JSON.stringify(datos))
}

export function leerSesion() {
  const texto = sessionStorage.getItem(LLAVE)
  if (!texto) {
    return null
  }
  try {
    return JSON.parse(texto)
  } catch {
    return null
  }
}

export function cerrarSesion() {
  sessionStorage.removeItem(LLAVE)
}

export function haySesion() {
  return Boolean(leerSesion()?.token)
}
