export async function cargarEstado(fetchImpl = fetch) {
  const respuesta = await fetchImpl("/api/status")
  if (!respuesta.ok) {
    throw new Error("La API no respondio")
  }
  return respuesta.json()
}
