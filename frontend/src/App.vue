<script setup>
import { useRoute, useRouter } from "vue-router"
import { cerrarSesion, leerSesion } from "./frmLogin/frmLogin.js"

const route = useRoute()
const router = useRouter()

function salir() {
  cerrarSesion()
  router.push("/")
}
</script>

<template>
  <div>
    <header v-if="route.meta.panel" class="panel">
      <p class="marca">Furbuddy</p>
      <nav>
        <router-link to="/usuarios">Usuarios</router-link>
      </nav>
      <p class="quien">{{ leerSesion()?.nombre }}</p>
      <button type="button" @click="salir">Salir</button>
    </header>
    <router-view />
  </div>
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

.panel {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1.5rem;
  background: white;
  font-family: "Segoe UI", sans-serif;
}

.marca {
  margin: 0;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-size: 0.8rem;
  color: #9a3412;
}

nav {
  display: flex;
  gap: 0.8rem;
}

a {
  color: #1f2933;
  font-weight: 600;
}

.quien {
  margin: 0 0 0 auto;
}

button {
  border: 0;
  border-radius: 999px;
  padding: 0.45rem 0.8rem;
  background: #fff7ed;
  cursor: pointer;
}

section, .login {
  max-width: 52rem;
}
</style>
