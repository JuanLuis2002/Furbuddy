<script setup>
import { nextTick, ref } from "vue"
import { useRouter } from "vue-router"
import { crearUsuario, editarUsuario, eliminarUsuario, listarUsuarios } from "./frmUsuarios.js"

const router = useRouter()
const usuarios = ref([])
const total = ref(0)
const pagina = ref(1)
const porPagina = ref(10)
const error = ref("")
const aviso = ref("")
const errorModal = ref("")
const cargando = ref(false)
const guardando = ref(false)
const modal = ref(false)
const editando = ref(false)
const verClave = ref(false)
const pendiente = ref(null)
const forma = ref(null)
const formulario = ref(vacio())
const columnas = [
  { title: "Usuario", key: "usuario" },
  { title: "Nombre", key: "nombre" },
  { title: "Correo", key: "email" },
  { title: "Rol", key: "rol" },
  { title: "", key: "acciones", sortable: false, align: "end", width: 120 },
]
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"
const correo = (valor) => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(valor || "") || "Correo no valido"
const clave = (valor) => {
  if (editando.value && !String(valor || "")) {
    return true
  }
  return String(valor || "").length >= 8 || "Minimo 8 caracteres"
}
const confirmarClave = (valor) => String(valor || "") === String(formulario.value.password || "") || "Las contrasenas no coinciden"

function vacio() {
  return { id: null, nombre: "", apellido: "", usuario: "", email: "", password: "", confirmar: "" }
}

function nombreCompleto(item) {
  return [item.nombre, item.apellido].filter(Boolean).join(" ")
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

async function preparar(edicion) {
  editando.value = edicion
  verClave.value = false
  errorModal.value = ""
  modal.value = true
  await nextTick()
  forma.value?.resetValidation()
}

function abrir() {
  formulario.value = vacio()
  aviso.value = ""
  error.value = ""
  preparar(false)
}

function abrirEdicion(item) {
  formulario.value = {
    id: item.id,
    nombre: item.nombre || "",
    apellido: item.apellido || "",
    usuario: item.usuario || "",
    email: item.email || "",
    password: "",
    confirmar: "",
  }
  aviso.value = ""
  error.value = ""
  preparar(true)
}

async function guardar() {
  const resultado = await forma.value.validate()
  if (!resultado.valid) {
    return
  }
  guardando.value = true
  aviso.value = ""
  errorModal.value = ""
  try {
    if (editando.value) {
      await editarUsuario(formulario.value.id, {
        nombre: formulario.value.nombre,
        apellido: formulario.value.apellido,
        email: formulario.value.email,
        password: formulario.value.password,
      })
      aviso.value = "Usuario actualizado"
    } else {
      await crearUsuario({
        nombre: formulario.value.nombre,
        apellido: formulario.value.apellido,
        usuario: formulario.value.usuario,
        email: formulario.value.email,
        password: formulario.value.password,
      })
      aviso.value = "Usuario creado en WordPress"
    }
    modal.value = false
    await cargar({ page: pagina.value, itemsPerPage: porPagina.value })
  } catch (err) {
    errorModal.value = err.message
  } finally {
    guardando.value = false
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
        <v-card-title>Usuarios de la plataforma</v-card-title>
        <v-card-subtitle>Estos usuarios viven en WordPress y WooCommerce los administra.</v-card-subtitle>
        <template #append>
          <v-btn color="primary" prepend-icon="mdi-account-plus" @click="abrir">Agregar</v-btn>
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
        <template #item.nombre="{ item }">
          {{ nombreCompleto(item) }}
        </template>
        <template #item.acciones="{ item }">
          <v-btn icon="mdi-pencil" variant="text" aria-label="Editar usuario" @click="abrirEdicion(item)" />
          <v-btn icon="mdi-delete" variant="text" color="error" aria-label="Eliminar usuario" @click="pendiente = item" />
        </template>
      </v-data-table-server>
    </v-card>

    <v-dialog v-model="modal" max-width="860">
      <v-card>
        <v-card-item>
          <template #prepend>
            <v-icon :icon="editando ? 'mdi-account-edit' : 'mdi-account'" size="28" />
          </template>
          <v-card-title class="text-h6">{{ editando ? "Editar usuario" : "Agregar usuario" }}</v-card-title>
        </v-card-item>
        <v-form ref="forma" @submit.prevent="guardar">
          <v-card-text class="pt-2">
            <v-alert v-if="errorModal" type="error" variant="tonal" class="mb-4">{{ errorModal }}</v-alert>
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.nombre"
                  label="Nombre*"
                  prepend-inner-icon="mdi-account"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[obligatorio]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.apellido"
                  label="Apellido*"
                  prepend-inner-icon="mdi-account-outline"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[obligatorio]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.usuario"
                  label="Usuario*"
                  prepend-inner-icon="mdi-account-circle"
                  variant="outlined"
                  hide-details="auto"
                  :disabled="editando"
                  :hint="editando ? 'El usuario no se puede cambiar' : ''"
                  :persistent-hint="editando"
                  :rules="[obligatorio]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.email"
                  label="Correo*"
                  prepend-inner-icon="mdi-email"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[correo]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.password"
                  :label="editando ? 'Contrasena' : 'Contrasena*'"
                  :type="verClave ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock"
                  :append-inner-icon="verClave ? 'mdi-eye-off' : 'mdi-eye'"
                  variant="outlined"
                  hide-details="auto"
                  :hint="editando ? 'Dejala vacia si no quieres cambiarla' : ''"
                  :persistent-hint="editando"
                  :rules="[clave]"
                  @click:append-inner="verClave = !verClave"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formulario.confirmar"
                  :label="editando ? 'Confirmar contrasena' : 'Confirmar contrasena*'"
                  :type="verClave ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-check"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[confirmarClave]"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider />
          <v-card-actions class="px-4 py-3">
            <span class="text-caption text-medium-emphasis">* indica un campo obligatorio</span>
            <v-spacer />
            <v-btn variant="text" type="button" @click="modal = false">Cerrar</v-btn>
            <v-btn color="primary" variant="flat" type="submit" :loading="guardando">Guardar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <v-dialog :model-value="Boolean(pendiente)" max-width="480" @update:model-value="(abierto) => { if (!abierto) pendiente = null }">
      <v-card v-if="pendiente">
        <v-card-item>
          <template #prepend>
            <v-avatar color="error" variant="tonal">
              <v-icon icon="mdi-account-remove" />
            </v-avatar>
          </template>
          <v-card-title class="text-h6">Eliminar usuario</v-card-title>
          <v-card-subtitle>{{ pendiente.usuario }}</v-card-subtitle>
        </v-card-item>
        <v-card-text>Se va a borrar a {{ nombreCompleto(pendiente) || pendiente.usuario }} de WordPress.</v-card-text>
        <v-divider />
        <v-card-actions class="px-4 py-3">
          <v-spacer />
          <v-btn variant="text" @click="pendiente = null">Cerrar</v-btn>
          <v-btn color="error" variant="flat" @click="confirmar">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
