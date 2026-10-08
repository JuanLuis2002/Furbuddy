<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"
import { guardarSesion, iniciarSesion } from "./frmLogin.js"

const router = useRouter()
const usuario = ref("")
const password = ref("")
const error = ref("")
const enviando = ref(false)

async function entrar() {
  error.value = ""
  enviando.value = true
  try {
    const datos = await iniciarSesion(usuario.value.trim(), password.value)
    guardarSesion(datos)
    router.push("/usuarios")
  } catch (err) {
    error.value = err.message
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <main class="login">
    <p class="marca">Furbuddy</p>
    <h1>Entrar al panel</h1>
    <p class="bajada">Usa tu usuario de WordPress. WooCommerce lee esos mismos usuarios.</p>
    <form @submit.prevent="entrar">
      <label>
        Usuario o correo
        <input v-model="usuario" name="usuario" autocomplete="username" required />
      </label>
      <label>
        Contrasena
        <input v-model="password" name="password" type="password" autocomplete="current-password" required />
      </label>
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="enviando">{{ enviando ? "Entrando..." : "Entrar" }}</button>
    </form>
  </main>
</template>

<style scoped>
.login {
  max-width: 28rem;
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
  font-size: 2.4rem;
  line-height: 1.05;
  margin: 0.4rem 0 0.8rem;
}

.bajada {
  margin-top: 0;
}

form {
  display: grid;
  gap: 0.9rem;
  margin-top: 1.5rem;
}

label {
  display: grid;
  gap: 0.35rem;
  font-family: "Segoe UI", sans-serif;
  font-size: 0.92rem;
}

input {
  border: 1px solid #d6d3d1;
  border-radius: 10px;
  padding: 0.7rem 0.8rem;
  font: inherit;
}

button {
  border: 0;
  border-radius: 999px;
  padding: 0.75rem 1rem;
  background: #9a3412;
  color: white;
  font: 600 1rem "Segoe UI", sans-serif;
  cursor: pointer;
}

button:disabled {
  opacity: 0.7;
}

.error {
  margin: 0;
  color: #991b1b;
  font-family: "Segoe UI", sans-serif;
}
</style>
