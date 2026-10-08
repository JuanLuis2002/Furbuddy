<script setup>
import { onMounted, ref } from "vue"
import { useRouter } from "vue-router"
import { useTheme } from "vuetify"
import { guardarSesion, leerSesion } from "../frmLogin/frmLogin.js"
import { aparienciaGuardada, aplicarApariencia, paleta } from "../tema.js"
import { guardarPerfil, leerPerfil } from "./frmConfiguracion.js"

const router = useRouter()
const theme = useTheme()
const guardada = aparienciaGuardada()
const colorActual = ref(guardada.color)
const oscuro = ref(guardada.modo === "dark")
const personalizado = ref(guardada.color)
const error = ref("")
const aviso = ref("")
const cargando = ref(true)
const forma = ref(null)
const perfil = ref({ nombre: "", apellido: "", email: "", password: "" })
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"
const correo = (valor) => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(valor || "") || "Correo no valido"
const clave = (valor) => !valor || String(valor).length >= 8 || "Minimo 8 caracteres"

function pintar(color, modoOscuro) {
  colorActual.value = color
  oscuro.value = modoOscuro
  aplicarApariencia(theme, color, modoOscuro ? "dark" : "light")
}

function esPredefinido(color) {
  return paleta.some((tono) => tono.valor.toLowerCase() === String(color).toLowerCase())
}

async function cargar() {
  cargando.value = true
  error.value = ""
  try {
    const datos = await leerPerfil()
    perfil.value = { nombre: datos.nombre, apellido: datos.apellido, email: datos.email, password: "" }
  } catch (err) {
    error.value = err.message
    if (err.message === "La sesion vencio") {
      router.push("/")
    }
  } finally {
    cargando.value = false
  }
}

async function guardar() {
  const resultado = await forma.value.validate()
  if (!resultado.valid) {
    return
  }
  aviso.value = ""
  error.value = ""
  try {
    const datos = await guardarPerfil({ ...perfil.value })
    const actual = leerSesion() || {}
    guardarSesion({
      ...actual,
      nombre: `${datos.nombre} ${datos.apellido}`.trim(),
      email: datos.email,
    })
    perfil.value.password = ""
    aviso.value = "Perfil actualizado"
  } catch (err) {
    error.value = err.message
  }
}

onMounted(cargar)
</script>

<template>
  <v-row>
    <v-col cols="12" md="6">
      <v-card>
        <v-card-title>Perfil</v-card-title>
        <v-card-subtitle>Estos datos son tu usuario de WordPress.</v-card-subtitle>
        <v-card-text>
          <v-progress-linear v-if="cargando" indeterminate color="primary" class="mb-4" />
          <v-form ref="forma" @submit.prevent="guardar">
            <v-text-field v-model="perfil.nombre" label="Nombre" :rules="[obligatorio]" />
            <v-text-field v-model="perfil.apellido" label="Apellido" :rules="[obligatorio]" />
            <v-text-field v-model="perfil.email" label="Correo" :rules="[correo]" />
            <v-text-field
              v-model="perfil.password"
              label="Nueva contrasena"
              type="password"
              hint="Dejala vacia si no quieres cambiarla"
              persistent-hint
              :rules="[clave]"
            />
            <v-btn class="mt-4" color="primary" type="submit">Guardar perfil</v-btn>
          </v-form>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" md="6">
      <v-card>
        <v-card-title>Apariencia</v-card-title>
        <v-card-subtitle>Elige el color del panel y si se ve en blanco o en oscuro.</v-card-subtitle>
        <v-card-text>
          <div class="d-flex align-center flex-wrap ga-3 mb-6">
            <button
              v-for="tono in paleta"
              :key="tono.valor"
              type="button"
              class="punto"
              :style="{ background: tono.valor }"
              :aria-label="tono.nombre"
              @click="pintar(tono.valor, oscuro)"
            >
              <v-icon v-if="colorActual.toLowerCase() === tono.valor.toLowerCase()" icon="mdi-check" size="18" color="white" />
            </button>
            <label class="punto punto-mas" aria-label="Color personalizado">
              <input v-model="personalizado" type="color" @input="pintar(personalizado, oscuro)" />
              <v-icon v-if="!esPredefinido(colorActual)" icon="mdi-check" size="18" color="white" />
              <v-icon v-else icon="mdi-plus" size="18" />
            </label>
            <v-btn
              icon
              variant="tonal"
              :aria-label="oscuro ? 'Modo blanco' : 'Modo oscuro'"
              @click="pintar(colorActual, !oscuro)"
            >
              <v-icon :icon="oscuro ? 'mdi-white-balance-sunny' : 'mdi-weather-night'" />
            </v-btn>
          </div>
          <v-switch
            :model-value="oscuro"
            color="primary"
            hide-details
            :label="oscuro ? 'Modo oscuro' : 'Modo blanco'"
            @update:model-value="pintar(colorActual, $event)"
          />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
  <v-alert v-if="aviso" type="success" variant="tonal" class="mt-4">{{ aviso }}</v-alert>
  <v-alert v-if="error" type="error" variant="tonal" class="mt-4">{{ error }}</v-alert>
</template>

<style scoped>
.punto {
  width: 36px;
  height: 36px;
  border: 2px solid rgba(127, 127, 127, 0.35);
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.punto-mas {
  position: relative;
  background: transparent;
  overflow: hidden;
}

.punto-mas input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}
</style>
