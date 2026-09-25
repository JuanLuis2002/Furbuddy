<script setup>
import { onMounted, ref } from "vue"
import { cargarEstado } from "./status.js"

const mensaje = ref("Conectando con la API...")
const conectada = ref(false)

onMounted(async () => {
  try {
    const datos = await cargarEstado()
    conectada.value = datos.ok === true
    mensaje.value = conectada.value ? "API conectada" : "API no disponible"
  } catch {
    conectada.value = false
    mensaje.value = "No se pudo contactar la API"
  }
})
</script>

<template>
  <main>
    <p class="marca">Furbuddy</p>
    <h1>Accesorios para mascotas</h1>
    <p class="bajada">La tienda nueva se conecta con la API de Python.</p>
    <p class="estado" :data-ok="conectada">{{ mensaje }}</p>
  </main>
</template>

<style>
:root {
  color: #1f2933;
  background: #f6f3ee;
  font-family: Georgia, "Times New Roman", serif;
}

body {
  margin: 0;
}

main {
  max-width: 40rem;
  margin: 0 auto;
  padding: 4rem 1.5rem;
}

.marca {
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: "Segoe UI", sans-serif;
  font-size: 0.8rem;
  color: #9a3412;
}

h1 {
  font-size: 3rem;
  line-height: 1.05;
  margin: 0.4rem 0 1rem;
}

.bajada {
  font-size: 1.15rem;
}

.estado {
  display: inline-block;
  margin-top: 1.5rem;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: #fee2e2;
  font-family: "Segoe UI", sans-serif;
  font-size: 0.95rem;
}

.estado[data-ok="true"] {
  background: #dcfce7;
}
</style>
