<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"
import GatoLogin from "./GatoLogin.vue"
import { guardarSesion, iniciarSesion } from "./frmLogin.js"

const router = useRouter()
const usuario = ref("")
const password = ref("")
const error = ref("")
const enviando = ref(false)
const forma = ref(null)
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"

async function entrar() {
  const resultado = await forma.value.validate()
  if (!resultado.valid) {
    return
  }
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
  <v-row no-gutters class="login-pagina align-center">
    <v-col cols="12" md="5" class="d-flex align-center pa-4 pa-md-10">
      <v-card class="mx-auto pa-2" max-width="440" width="100%" elevation="2">
        <v-card-text>
          <div class="d-flex align-center ga-3 mb-6">
            <v-avatar color="primary" size="44">
              <v-icon icon="mdi-paw" size="26" />
            </v-avatar>
            <div>
              <div class="text-h6 font-weight-medium">Furbuddy Panel</div>
              <div class="text-body-2 text-medium-emphasis">Panel de la tienda</div>
            </div>
          </div>
          <h1 class="text-h5 font-weight-medium mb-2">Entrar</h1>
          <p class="text-body-1 text-medium-emphasis mb-6">Usa tu usuario de WordPress. WooCommerce lee esos mismos usuarios.</p>
          <v-form ref="forma" @submit.prevent="entrar">
            <v-text-field
              v-model="usuario"
              label="Usuario o correo"
              name="usuario"
              autocomplete="username"
              :rules="[obligatorio]"
            />
            <v-text-field
              v-model="password"
              label="Contrasena"
              name="password"
              type="password"
              autocomplete="current-password"
              :rules="[obligatorio]"
            />
            <v-alert v-if="error" type="error" variant="tonal" class="mb-4">{{ error }}</v-alert>
            <v-btn color="primary" type="submit" block size="large" :loading="enviando">Entrar</v-btn>
          </v-form>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" md="7" class="d-flex align-center pa-2 pa-md-6">
      <GatoLogin />
    </v-col>
  </v-row>
</template>

<style scoped>
.login-pagina {
  min-height: 100vh;
}
</style>
