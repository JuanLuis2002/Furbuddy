<script setup>
import { nextTick, ref } from "vue"
import { useRouter } from "vue-router"
import { crearCategoria, editarCategoria, eliminarCategoria, listarCategorias } from "./frmCategorias.js"

const router = useRouter()
const categorias = ref([])
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
const pendiente = ref(null)
const forma = ref(null)
const formulario = ref(vacio())
const columnas = [
  { title: "Nombre", key: "nombre" },
  { title: "Productos", key: "productos" },
  { title: "", key: "acciones", sortable: false, align: "end", width: 120 },
]
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"

function vacio() {
  return { id: null, nombre: "" }
}

async function cargar({ page, itemsPerPage }) {
  if (!page || itemsPerPage < 1) {
    return
  }
  cargando.value = true
  error.value = ""
  try {
    const datos = await listarCategorias(page, itemsPerPage)
    categorias.value = datos.categorias
    total.value = datos.total
  } catch (err) {
    error.value = err.message
    categorias.value = []
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
  formulario.value = { id: item.id, nombre: item.nombre || "" }
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
    const datos = { nombre: formulario.value.nombre.trim() }
    if (editando.value) {
      await editarCategoria(formulario.value.id, datos)
      aviso.value = "Categoria actualizada"
    } else {
      await crearCategoria(datos)
      aviso.value = "Categoria creada en la tienda"
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
    await eliminarCategoria(pendiente.value.id)
    aviso.value = "Categoria eliminada"
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
              <v-icon icon="mdi-shape-outline" />
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">Total de categorias</div>
              <div class="text-h5">{{ total }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-card>
      <v-card-item>
        <v-card-title>Categorias</v-card-title>
        <v-card-subtitle>Estas categorias se usan en los productos de WooCommerce.</v-card-subtitle>
        <template #append>
          <v-btn color="primary" prepend-icon="mdi-plus" @click="abrir">Agregar</v-btn>
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
        :items="categorias"
        :items-length="total"
        :loading="cargando"
        :items-per-page-options="[5, 10, 25]"
        item-value="id"
        hover
        no-data-text="Todavia no hay categorias."
        loading-text="Cargando categorias..."
        @update:options="cargar"
      >
        <template #item.acciones="{ item }">
          <v-btn icon="mdi-pencil" variant="text" aria-label="Editar categoria" @click="abrirEdicion(item)" />
          <v-btn icon="mdi-delete" variant="text" color="error" aria-label="Eliminar categoria" @click="pendiente = item" />
        </template>
      </v-data-table-server>
    </v-card>

    <v-dialog v-model="modal" max-width="520">
      <v-card>
        <v-card-item>
          <template #prepend>
            <v-icon :icon="editando ? 'mdi-pencil' : 'mdi-shape-outline'" size="28" />
          </template>
          <v-card-title class="text-h6">{{ editando ? "Editar categoria" : "Agregar categoria" }}</v-card-title>
        </v-card-item>
        <v-form ref="forma" @submit.prevent="guardar">
          <v-card-text class="pt-2">
            <v-alert v-if="errorModal" type="error" variant="tonal" class="mb-4">{{ errorModal }}</v-alert>
            <v-text-field
              v-model="formulario.nombre"
              label="Nombre*"
              prepend-inner-icon="mdi-tag"
              variant="outlined"
              hide-details="auto"
              :rules="[obligatorio]"
            />
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
              <v-icon icon="mdi-delete" />
            </v-avatar>
          </template>
          <v-card-title class="text-h6">Eliminar categoria</v-card-title>
          <v-card-subtitle>{{ pendiente.nombre }}</v-card-subtitle>
        </v-card-item>
        <v-card-text>Se va a borrar {{ pendiente.nombre }} de la tienda. Los productos no se borran.</v-card-text>
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
