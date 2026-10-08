<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useDisplay } from "vuetify"
import { cerrarSesion, leerSesion } from "./frmLogin/frmLogin.js"

const route = useRoute()
const router = useRouter()
const { mobile } = useDisplay()
const menu = ref(true)
const cuenta = ref(false)
const sesion = ref(leerSesion())
const iniciales = computed(() => {
  const partes = String(sesion.value?.nombre || "").trim().split(/\s+/).filter(Boolean)
  return ((partes[0]?.[0] || "F") + (partes[1]?.[0] || "")).toUpperCase()
})

function refrescarSesion() {
  sesion.value = leerSesion()
}

function salir() {
  cuenta.value = false
  cerrarSesion()
  router.push("/")
}

onMounted(() => window.addEventListener("furbuddy-sesion", refrescarSesion))
onUnmounted(() => window.removeEventListener("furbuddy-sesion", refrescarSesion))
</script>

<template>
  <v-app>
    <template v-if="route.meta.panel">
      <v-app-bar color="primary" flat density="comfortable">
        <v-app-bar-nav-icon v-if="mobile" @click="menu = !menu" />
        <v-avatar color="white" size="32" class="ml-2">
          <v-icon color="primary" icon="mdi-paw" />
        </v-avatar>
        <span class="text-h6 font-weight-medium ml-3 flex-shrink-0">Furbuddy</span>
        <v-spacer />
        <v-menu v-model="cuenta" location="bottom end">
          <template #activator="{ props }">
            <v-btn v-bind="props" variant="text" class="text-none">
              <v-avatar color="white" size="32" class="mr-2">
                <span class="text-caption font-weight-bold text-primary">{{ iniciales }}</span>
              </v-avatar>
              <span class="d-none d-sm-inline mr-1">{{ sesion?.nombre }}</span>
              <v-icon icon="mdi-chevron-down" size="small" />
            </v-btn>
          </template>
          <v-list density="compact" min-width="200">
            <v-list-item to="/configuracion" prepend-icon="mdi-account-edit" title="Editar perfil" @click="cuenta = false" />
            <v-list-item prepend-icon="mdi-logout" title="Salir" @click="salir" />
          </v-list>
        </v-menu>
      </v-app-bar>

      <v-navigation-drawer v-model="menu" :permanent="!mobile" width="240">
        <v-list nav density="compact">
          <v-list-subheader>Panel</v-list-subheader>
          <v-list-item to="/usuarios" prepend-icon="mdi-account-group" title="Usuarios" rounded="lg" />
        </v-list>
        <template #append>
          <v-list nav density="compact">
            <v-list-item to="/configuracion" prepend-icon="mdi-cog" title="Configuración" rounded="lg" />
          </v-list>
        </template>
      </v-navigation-drawer>

      <v-main>
        <v-container>
          <router-view />
        </v-container>
      </v-main>
      <v-footer app border color="surface" class="text-medium-emphasis">Furbuddy</v-footer>
    </template>
    <v-main v-else class="fondo-login">
      <router-view />
    </v-main>
  </v-app>
</template>

<style>
.fondo-login {
  background: #f6f3ee;
  font-family: Georgia, "Times New Roman", serif;
}
</style>
