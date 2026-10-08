<script setup>
import { onMounted, ref } from "vue"
import { useRouter } from "vue-router"
import { crearUsuario, listarUsuarios } from "./frmUsuarios.js"

const router = useRouter()
const usuarios = ref([])
const error = ref("")
const aviso = ref("")
const cargando = ref(true)
const formulario = ref({
  nombre: "",
  apellido: "",
  usuario: "",
  email: "",
  password: "",
})

async function cargar() {
  cargando.value = true
  error.value = ""
  try {
    usuarios.value = await listarUsuarios()
  } catch (err) {
    error.value = err.message
    if (err.message === "La sesion vencio") {
      router.push("/")
    }
  } finally {
    cargando.value = false
  }
}

async function crear() {
  aviso.value = ""
  error.value = ""
  try {
    await crearUsuario({ ...formulario.value })
    formulario.value = { nombre: "", apellido: "", usuario: "", email: "", password: "" }
    aviso.value = "Usuario creado en WordPress"
    await cargar()
  } catch (err) {
    error.value = err.message
  }
}

onMounted(cargar)
</script>

<template>
  <section>
    <h1>Usuarios</h1>
    <p class="bajada">Estos usuarios viven en WordPress y WooCommerce los administra.</p>

    <form class="alta" @submit.prevent="crear">
      <h2>Crear usuario</h2>
      <label>Nombre <input v-model="formulario.nombre" required /></label>
      <label>Apellido <input v-model="formulario.apellido" required /></label>
      <label>Usuario <input v-model="formulario.usuario" required /></label>
      <label>Correo <input v-model="formulario.email" type="email" required /></label>
      <label>Contrasena <input v-model="formulario.password" type="password" minlength="8" required /></label>
      <button type="submit">Crear usuario</button>
    </form>

    <p v-if="aviso" class="aviso">{{ aviso }}</p>
    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="cargando">Cargando usuarios...</p>
    <p v-else-if="usuarios.length === 0">Todavia no hay usuarios.</p>
    <table v-else>
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="persona in usuarios" :key="persona.id">
          <td>{{ persona.usuario }}</td>
          <td>{{ persona.nombre }}</td>
          <td>{{ persona.email }}</td>
          <td>{{ persona.rol }}</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<style scoped>
h1 {
  font-size: 2.2rem;
  margin: 0 0 0.4rem;
}

h2 {
  grid-column: 1 / -1;
  margin: 0;
  font-size: 1.2rem;
}

.bajada {
  margin-top: 0;
}

.alta {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.8rem;
  margin: 1.5rem 0;
  padding: 1rem;
  background: white;
  border-radius: 16px;
}

label {
  display: grid;
  gap: 0.3rem;
  font-family: "Segoe UI", sans-serif;
  font-size: 0.9rem;
}

input {
  border: 1px solid #d6d3d1;
  border-radius: 10px;
  padding: 0.65rem 0.75rem;
  font: inherit;
}

button {
  grid-column: 1 / -1;
  justify-self: start;
  border: 0;
  border-radius: 999px;
  padding: 0.7rem 1rem;
  background: #9a3412;
  color: white;
  font: 600 0.95rem "Segoe UI", sans-serif;
  cursor: pointer;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 16px;
  overflow: hidden;
}

th, td {
  padding: 0.75rem 0.9rem;
  text-align: left;
  font-family: "Segoe UI", sans-serif;
}

th {
  background: #fff7ed;
}

.error {
  color: #991b1b;
}

.aviso {
  color: #166534;
}

@media (max-width: 700px) {
  .alta {
    grid-template-columns: 1fr;
  }
}
</style>
