<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"
import { crearUsuario, eliminarUsuario, listarUsuarios } from "./frmUsuarios.js"

const router = useRouter()
const usuarios = ref([])
const total = ref(0)
const pagina = ref(1)
const porPagina = ref(10)
const error = ref("")
const aviso = ref("")
const cargando = ref(false)
const modal = ref(false)
const pendiente = ref(null)
const forma = ref(null)
const formulario = ref(vacio())
const columnas = [
  { title: "Usuario", key: "usuario" },
  { title: "Nombre", key: "nombre" },
  { title: "Correo", key: "email" },
  { title: "Rol", key: "rol" },
  { title: "", key: "acciones", sortable: false, align: "end" },
]
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"
const correo = (valor) => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(valor || "") || "Correo no valido"
const clave = (valor) => String(valor || "").length >= 8 || "Minimo 8 caracteres"

function vacio() {
  return { nombre: "", apellido: "", usuario: "", email: "", password: "" }
}

async function cargar({ page, itemsPerPage }) {
  if (!page || itemsPerPage < 1) {
    return
  }
  cargando.value = true
  error.value = ""
  try {
    const datos = await listarUsuarios(page, itemsPerPage)
    usuarios.value = datos.usuarios
    total.value = datos.total
  } catch (err) {
    error.value = err.message
    usuarios.value = []
    total.value = 0
    if (err.message === "La sesion vencio") {
      router.push("/")
    }
  } finally {
    cargando.value = false
  }
}

function abrir() {
  formulario.value = vacio()
  aviso.value = ""
  error.value = ""
  modal.value = true
}

async function crear() {
  const resultado = await forma.value.validate()
  if (!resultado.valid) {
    return
  }
  aviso.value = ""
  error.value = ""
  try {
    await crearUsuario({ ...formulario.value })
    modal.value = false
    aviso.value = "Usuario creado en WordPress"
    await cargar({ page: pagina.value, itemsPerPage: porPagina.value })
  } catch (err) {
    error.value = err.message
  }
}

async function confirmar() {
  aviso.value = ""
  error.value = ""
  try {
    await eliminarUsuario(pendiente.value.id)
    aviso.value = "Usuario eliminado"
    pendiente.value = null
    const quedan = Math.max(0, total.value - 1)
    const ultima = Math.max(1, Math.ceil(quedan / porPagina.value) || 1)
    if (pagina.value > ultima) {
      pagina.value = ultima
      return
    }
    await cargar({ page: pagina.value, itemsPerPage: porPagina.value })
  } catch (err) {
    error.value = err.message
    pendiente.value = null
  }
}
</script>

<template>
  <div>
    <v-row class="mb-2">
      <v-col cols="12" sm="6" md="4">
        <v-card>
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" variant="tonal" class="mr-3">
              <v-icon icon="mdi-account-group" />
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">Total de usuarios</div>
              <div class="text-h5">{{ total }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-card>
      <v-card-item>
        <v-card-title>Usuarios</v-card-title>
        <v-card-subtitle>Estos usuarios viven en WordPress y WooCommerce los administra.</v-card-subtitle>
        <template #append>
          <v-btn color="primary" @click="abrir">Agregar</v-btn>
        </template>
      </v-card-item>
      <v-card-text>
        <v-alert v-if="aviso" type="success" variant="tonal" class="mb-4">{{ aviso }}</v-alert>
        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">{{ error }}</v-alert>
      </v-card-text>

    <v-data-table-server
      v-model:page="pagina"
      v-model:items-per-page="porPagina"
      :headers="columnas"
      :items="usuarios"
      :items-length="total"
      :loading="cargando"
      :items-per-page-options="[5, 10, 25]"
      item-value="id"
      hover
      no-data-text="Todavia no hay usuarios."
      loading-text="Cargando usuarios..."
      @update:options="cargar"
    >
      <template #item.acciones="{ item }">
        <v-btn size="small" variant="tonal" color="error" @click="pendiente = item">Eliminar</v-btn>
      </template>
    </v-data-table-server>
    </v-card>

    <v-dialog v-model="modal" max-width="480">
      <v-card>
        <v-card-title>Agregar usuario</v-card-title>
        <v-card-text>
          <v-form ref="forma" @submit.prevent="crear">
            <v-text-field v-model="formulario.nombre" label="Nombre" :rules="[obligatorio]" />
            <v-text-field v-model="formulario.apellido" label="Apellido" :rules="[obligatorio]" />
            <v-text-field v-model="formulario.usuario" label="Usuario" :rules="[obligatorio]" />
            <v-text-field v-model="formulario.email" label="Correo" :rules="[correo]" />
            <v-text-field
              v-model="formulario.password"
              label="Contrasena"
              type="password"
              :rules="[clave]"
            />
            <div class="d-flex justify-end ga-2">
              <v-btn variant="text" @click="modal = false">Cancelar</v-btn>
              <v-btn color="primary" type="submit">Crear usuario</v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog :model-value="Boolean(pendiente)" max-width="420" @update:model-value="(abierto) => { if (!abierto) pendiente = null }">
      <v-card v-if="pendiente">
        <v-card-title>Eliminar usuario</v-card-title>
        <v-card-text>Se va a borrar a {{ pendiente.usuario }} de WordPress.</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="pendiente = null">Cancelar</v-btn>
          <v-btn color="error" @click="confirmar">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
