<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue"
import { useRouter } from "vue-router"
import {
  eliminarProducto,
  guardarProducto,
  leerProducto,
  listarProductos,
  proponerProducto,
  subirFoto,
} from "./frmProductos.js"

const router = useRouter()
const productos = ref([])
const total = ref(0)
const pagina = ref(1)
const porPagina = ref(10)
const error = ref("")
const aviso = ref("")
const errorModal = ref("")
const cargando = ref(false)
const guardando = ref(false)
const subiendo = ref(false)
const modal = ref(false)
const modalAi = ref(false)
const pasoAi = ref("tipo")
const tipoAi = ref("simple")
const fotosAi = ref([])
const selectorFotos = ref(null)
const videoCamara = ref(null)
const camaraAbierta = ref(false)
const camaraLista = ref(false)
const capturando = ref(false)
const errorCamara = ref("")
const analizando = ref(false)
let streamCamara = null
const errorAi = ref("")
const desdeAi = ref(false)
const editando = ref(false)
const silencio = ref(false)
const pendiente = ref(null)
const forma = ref(null)
const formulario = ref(vacio())
const columnas = [
  { title: "", key: "foto", sortable: false, width: 72 },
  { title: "Nombre", key: "nombre" },
  { title: "Tipo", key: "tipo" },
  { title: "Precio", key: "precio" },
  { title: "Existencia", key: "existencia" },
  { title: "", key: "acciones", sortable: false, align: "end", width: 120 },
]
const tipos = [
  { title: "Simple", value: "simple" },
  { title: "Variable", value: "variable" },
]
const obligatorio = (valor) => !!String(valor || "").trim() || "Obligatorio"
const precioRegla = (valor) => /^\d+(\.\d{1,2})?$/.test(String(valor || "")) || "Precio no valido"
const exceso = computed(() => combinaciones(formulario.value.atributos) === null)

function vacio() {
  return {
    id: null,
    nombre: "",
    tipo: "simple",
    descripcion: "",
    precio: "",
    existencia: 0,
    sku: "",
    publicado: true,
    fotos: [],
    atributos: [],
    variaciones: [],
  }
}

function combinaciones(atributos) {
  let filas = [[]]
  for (const atributo of atributos) {
    const opciones = (atributo.opciones || []).map((opcion) => String(opcion).trim()).filter(Boolean)
    if (!opciones.length) {
      return []
    }
    const siguiente = []
    for (const fila of filas) {
      for (const opcion of opciones) {
        siguiente.push([...fila, opcion])
      }
    }
    filas = siguiente
    if (filas.length > 50) {
      return null
    }
  }
  return atributos.length ? filas : []
}

function reconstruir() {
  if (silencio.value || formulario.value.tipo !== "variable") {
    return
  }
  const combos = combinaciones(formulario.value.atributos)
  if (combos === null) {
    return
  }
  const previas = new Map(formulario.value.variaciones.map((variacion) => [variacion.valores.join("\u0001"), variacion]))
  const nuevas = combos.map((valores) => {
    const previa = previas.get(valores.join("\u0001"))
    return previa
      ? { ...previa, valores }
      : { id: null, valores, precio: "", existencia: 0, sku: "", foto_id: null, foto: "" }
  })
  const iguales = nuevas.length === formulario.value.variaciones.length
    && nuevas.every((variacion, indice) => variacion.valores.join("\u0001") === formulario.value.variaciones[indice].valores.join("\u0001"))
  if (iguales) {
    return
  }
  formulario.value.variaciones = nuevas
}

watch(modalAi, (abierto) => {
  if (!abierto) {
    cerrarCamara()
    soltarFotosAi()
  }
})
onBeforeUnmount(cerrarCamara)

watch(() => formulario.value.atributos, reconstruir, { deep: true })
watch(() => formulario.value.tipo, (tipo) => {
  if (silencio.value) {
    return
  }
  if (tipo === "variable" && !formulario.value.atributos.length) {
    formulario.value.atributos = [{ id: 0, nombre: "", opciones: [] }]
  }
  if (tipo === "simple") {
    formulario.value.atributos = []
    formulario.value.variaciones = []
  }
})

function etiqueta(variacion) {
  return variacion.valores.filter(Boolean).join(" / ")
}

async function cargar({ page, itemsPerPage }) {
  if (!page || itemsPerPage < 1) {
    return
  }
  cargando.value = true
  error.value = ""
  try {
    const datos = await listarProductos(page, itemsPerPage)
    productos.value = datos.productos
    total.value = datos.total
  } catch (err) {
    error.value = err.message
    productos.value = []
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
  silencio.value = true
  formulario.value = vacio()
  silencio.value = false
  desdeAi.value = false
  aviso.value = ""
  error.value = ""
  preparar(false)
}

function soltarFotosAi() {
  for (const foto of fotosAi.value) {
    URL.revokeObjectURL(foto.vista)
  }
  fotosAi.value = []
}

function abrirAi() {
  pasoAi.value = "tipo"
  tipoAi.value = "simple"
  soltarFotosAi()
  cerrarCamara()
  errorAi.value = ""
  modalAi.value = true
}

function elegirTipoAi(tipo) {
  tipoAi.value = tipo
  pasoAi.value = "foto"
  errorAi.value = ""
}

function agregarFotosAi(archivos) {
  const lista = Array.isArray(archivos) ? archivos : archivos ? [archivos] : []
  if (!lista.length) {
    return
  }
  errorAi.value = ""
  for (const archivo of lista) {
    if (fotosAi.value.length >= 6) {
      errorAi.value = "Puedes enviar hasta 6 fotos"
      break
    }
    fotosAi.value.push({ archivo, vista: URL.createObjectURL(archivo) })
  }
}

function quitarFotoAi(indice) {
  const [foto] = fotosAi.value.splice(indice, 1)
  if (foto) {
    URL.revokeObjectURL(foto.vista)
  }
}

function alElegirFotos(evento) {
  agregarFotosAi([...evento.target.files])
  evento.target.value = ""
}

function cuadroVacio(lienzo) {
  const pixeles = lienzo.getContext("2d", { willReadFrequently: true }).getImageData(0, 0, lienzo.width, lienzo.height).data
  const paso = Math.max(4, Math.floor(Math.sqrt((lienzo.width * lienzo.height) / 4000)))
  let suma = 0
  let sumaCuadrada = 0
  let cuenta = 0
  for (let y = 0; y < lienzo.height; y += paso) {
    for (let x = 0; x < lienzo.width; x += paso) {
      const indice = (y * lienzo.width + x) * 4
      const luz = (pixeles[indice] + pixeles[indice + 1] + pixeles[indice + 2]) / 3
      suma += luz
      sumaCuadrada += luz * luz
      cuenta += 1
    }
  }
  if (!cuenta) {
    return true
  }
  const media = suma / cuenta
  const varianza = sumaCuadrada / cuenta - media * media
  return media < 12 && varianza < 30
}

async function abrirCamara() {
  errorAi.value = ""
  errorCamara.value = ""
  camaraLista.value = false
  if (!navigator.mediaDevices?.getUserMedia) {
    errorAi.value = "Este navegador no abre la camara."
    return
  }
  try {
    try {
      streamCamara = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: "environment" } },
        audio: false,
      })
    } catch {
      streamCamara = await navigator.mediaDevices.getUserMedia({ video: true, audio: false })
    }
    camaraAbierta.value = true
    await nextTick()
    const video = videoCamara.value
    video.srcObject = streamCamara
    const vigilarCuadro = () => {
      if (video.videoWidth > 0) {
        camaraLista.value = true
        return
      }
      video.requestVideoFrameCallback?.(vigilarCuadro)
    }
    video.onloadeddata = vigilarCuadro
    video.requestVideoFrameCallback?.(vigilarCuadro)
    await video.play()
  } catch {
    errorAi.value = "No se pudo abrir la camara. Permite el acceso en el navegador."
  }
}

async function capturarFoto() {
  const video = videoCamara.value
  const pista = streamCamara?.getVideoTracks?.()[0]
  if (!video?.videoWidth && !pista) {
    return
  }
  capturando.value = true
  errorCamara.value = ""
  const candidatos = []
  const copiar = (origen, ancho, alto) => {
    if (!ancho || !alto) {
      return
    }
    const lienzo = document.createElement("canvas")
    lienzo.width = ancho
    lienzo.height = alto
    lienzo.getContext("2d").drawImage(origen, 0, 0)
    candidatos.push(lienzo)
  }
  if (pista && typeof ImageCapture === "function") {
    try {
      const mapa = await new ImageCapture(pista).grabFrame()
      copiar(mapa, mapa.width, mapa.height)
      mapa.close()
    } catch {
      /* El navegador sigue con el cuadro del video. */
    }
  }
  copiar(video, video?.videoWidth, video?.videoHeight)
  const lienzo = candidatos.find((item) => !cuadroVacio(item))
  if (!lienzo) {
    capturando.value = false
    errorCamara.value = "La foto salio negra. Espera a ver la imagen en la camara y toma otra."
    return
  }
  lienzo.toBlob((blob) => {
    capturando.value = false
    if (blob) {
      agregarFotosAi([new File([blob], `foto-${Date.now()}.jpg`, { type: "image/jpeg" })])
    }
    cerrarCamara()
  }, "image/jpeg", 0.92)
}

function cerrarCamara() {
  camaraAbierta.value = false
  camaraLista.value = false
  capturando.value = false
  if (streamCamara) {
    for (const pista of streamCamara.getTracks()) {
      pista.stop()
    }
    streamCamara = null
  }
  if (videoCamara.value) {
    videoCamara.value.srcObject = null
  }
}

async function analizarAi() {
  if (!fotosAi.value.length) {
    errorAi.value = "Agrega al menos una foto"
    return
  }
  analizando.value = true
  errorAi.value = ""
  try {
    const datos = await proponerProducto(tipoAi.value, fotosAi.value.map((foto) => foto.archivo))
    silencio.value = true
    formulario.value = {
      ...vacio(),
      nombre: datos.nombre,
      tipo: datos.tipo,
      descripcion: datos.descripcion,
      precio: "",
      existencia: 0,
      fotos: datos.fotos || [],
      atributos: datos.atributos?.length
        ? datos.atributos
        : datos.tipo === "variable"
          ? [{ id: 0, nombre: "", opciones: [] }]
          : [],
    }
    silencio.value = false
    reconstruir()
    desdeAi.value = true
    modalAi.value = false
    preparar(false)
  } catch (err) {
    errorAi.value = err.message
  } finally {
    analizando.value = false
  }
}

async function abrirEdicion(item) {
  desdeAi.value = false
  aviso.value = ""
  error.value = ""
  errorModal.value = ""
  try {
    const datos = await leerProducto(item.id)
    silencio.value = true
    formulario.value = {
      id: datos.id,
      nombre: datos.nombre,
      tipo: datos.tipo === "variable" ? "variable" : "simple",
      descripcion: datos.descripcion,
      precio: datos.precio,
      existencia: Number(datos.existencia) || 0,
      sku: datos.sku,
      publicado: datos.publicado,
      fotos: datos.fotos,
      atributos: datos.atributos.map((atributo) => ({ ...atributo, opciones: [...atributo.opciones] })),
      variaciones: datos.variaciones.map((variacion) => ({
        id: variacion.id,
        valores: datos.atributos.map((atributo) => variacion.atributos[atributo.nombre] || ""),
        precio: variacion.precio,
        existencia: variacion.existencia,
        sku: variacion.sku,
        foto_id: variacion.foto_id,
        foto: variacion.foto,
      })),
    }
    silencio.value = false
    preparar(true)
  } catch (err) {
    error.value = err.message
  }
}

function agregarAtributo() {
  formulario.value.atributos.push({ id: 0, nombre: "", opciones: [] })
}

function quitarAtributo(indice) {
  formulario.value.atributos.splice(indice, 1)
}

async function agregarFotos(archivos) {
  const lista = Array.isArray(archivos) ? archivos : archivos ? [archivos] : []
  if (!lista.length) {
    return
  }
  subiendo.value = true
  errorModal.value = ""
  try {
    for (const archivo of lista) {
      formulario.value.fotos.push(await subirFoto(archivo))
    }
  } catch (err) {
    errorModal.value = err.message
  } finally {
    subiendo.value = false
  }
}

function quitarFoto(indice) {
  formulario.value.fotos.splice(indice, 1)
}

async function fotoVariacion(variacion, archivos) {
  const archivo = Array.isArray(archivos) ? archivos[0] : archivos
  if (!archivo) {
    return
  }
  subiendo.value = true
  errorModal.value = ""
  try {
    const foto = await subirFoto(archivo)
    variacion.foto_id = foto.id
    variacion.foto = foto.src
  } catch (err) {
    errorModal.value = err.message
  } finally {
    subiendo.value = false
  }
}

function datosParaGuardar() {
  const base = {
    nombre: formulario.value.nombre,
    tipo: formulario.value.tipo,
    descripcion: formulario.value.descripcion,
    precio: formulario.value.tipo === "simple" ? String(formulario.value.precio) : "",
    existencia: formulario.value.tipo === "simple" ? Number(formulario.value.existencia) || 0 : 0,
    sku: formulario.value.sku,
    publicado: formulario.value.publicado,
    fotos: formulario.value.fotos.map((foto) => foto.id),
    atributos: [],
    variaciones: [],
  }
  if (formulario.value.tipo !== "variable") {
    return base
  }
  base.atributos = formulario.value.atributos.map((atributo) => ({
    id: atributo.id || 0,
    nombre: atributo.nombre.trim(),
    opciones: atributo.opciones.map((opcion) => String(opcion).trim()).filter(Boolean),
  }))
  base.variaciones = formulario.value.variaciones.map((variacion) => ({
    id: variacion.id,
    atributos: Object.fromEntries(base.atributos.map((atributo, indice) => [atributo.nombre, variacion.valores[indice]])),
    precio: String(variacion.precio),
    existencia: Number(variacion.existencia) || 0,
    sku: variacion.sku,
    foto_id: variacion.foto_id,
  }))
  return base
}

async function guardar() {
  const resultado = await forma.value.validate()
  if (!resultado.valid || exceso.value) {
    return
  }
  if (formulario.value.tipo === "variable" && formulario.value.variaciones.some((variacion) => !/^\d+(\.\d{1,2})?$/.test(String(variacion.precio || "")))) {
    errorModal.value = "Cada combinacion necesita un precio"
    return
  }
  guardando.value = true
  aviso.value = ""
  errorModal.value = ""
  try {
    await guardarProducto(datosParaGuardar(), formulario.value.id)
    aviso.value = editando.value ? "Producto actualizado" : "Producto creado en la tienda"
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
    await eliminarProducto(pendiente.value.id)
    aviso.value = "Producto eliminado"
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
              <v-icon icon="mdi-package-variant" />
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">Total de productos</div>
              <div class="text-h5">{{ total }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-card>
      <v-card-item>
        <v-card-title>Productos</v-card-title>
        <v-card-subtitle>Estos productos viven en la tienda de WooCommerce.</v-card-subtitle>
        <template #append>
          <div class="d-flex flex-wrap justify-end ga-2">
            <v-btn variant="tonal" prepend-icon="mdi-auto-fix" @click="abrirAi">Crear producto con AI</v-btn>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="abrir">Agregar</v-btn>
          </div>
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
        :items="productos"
        :items-length="total"
        :loading="cargando"
        :items-per-page-options="[5, 10, 25]"
        item-value="id"
        hover
        no-data-text="Todavia no hay productos."
        loading-text="Cargando productos..."
        @update:options="cargar"
      >
        <template #item.foto="{ item }">
          <v-avatar rounded="lg" size="42" color="surface-variant">
            <v-img v-if="item.foto" :src="item.foto" cover />
            <v-icon v-else icon="mdi-image-outline" />
          </v-avatar>
        </template>
        <template #item.tipo="{ item }">
          {{ item.tipo === "variable" ? "Variable" : "Simple" }}
        </template>
        <template #item.precio="{ item }">
          {{ item.precio ? `$${item.precio}` : "" }}
        </template>
        <template #item.acciones="{ item }">
          <v-btn icon="mdi-pencil" variant="text" aria-label="Editar producto" @click="abrirEdicion(item)" />
          <v-btn icon="mdi-delete" variant="text" color="error" aria-label="Eliminar producto" @click="pendiente = item" />
        </template>
      </v-data-table-server>
    </v-card>

    <v-dialog v-model="modal" max-width="920" scrollable>
      <v-card>
        <v-card-item>
          <template #prepend>
            <v-icon :icon="editando ? 'mdi-package-variant' : 'mdi-package-variant-plus'" size="28" />
          </template>
          <v-card-title class="text-h6">{{ editando ? "Editar producto" : "Agregar producto" }}</v-card-title>
        </v-card-item>
        <v-form ref="forma" @submit.prevent="guardar">
          <v-card-text class="pt-2">
            <v-alert v-if="desdeAi" type="info" variant="tonal" class="mb-4">
              Revisa el nombre y la descripcion. Confirma el precio y la existencia. Al guardar, el producto se publica en la tienda.
            </v-alert>
            <v-alert v-if="errorModal" type="error" variant="tonal" class="mb-4">{{ errorModal }}</v-alert>
            <v-row>
              <v-col cols="12" md="8">
                <v-text-field
                  v-model="formulario.nombre"
                  label="Nombre*"
                  prepend-inner-icon="mdi-tag"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[obligatorio]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="formulario.tipo"
                  :items="tipos"
                  label="Tipo*"
                  prepend-inner-icon="mdi-shape"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="formulario.descripcion"
                  label="Descripcion"
                  prepend-inner-icon="mdi-text"
                  variant="outlined"
                  rows="2"
                  auto-grow
                  hide-details="auto"
                />
              </v-col>
              <v-col v-if="formulario.tipo === 'simple'" cols="12" md="4">
                <v-text-field
                  v-model="formulario.precio"
                  label="Precio*"
                  prefix="$"
                  prepend-inner-icon="mdi-cash"
                  variant="outlined"
                  hide-details="auto"
                  :rules="[precioRegla]"
                />
              </v-col>
              <v-col v-if="formulario.tipo === 'simple'" cols="12" md="4">
                <v-text-field
                  v-model.number="formulario.existencia"
                  label="Existencia*"
                  type="number"
                  min="0"
                  prepend-inner-icon="mdi-cube"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
              <v-col v-if="formulario.tipo === 'simple'" cols="12" md="4">
                <v-text-field
                  v-model="formulario.sku"
                  label="SKU"
                  prepend-inner-icon="mdi-barcode"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
            </v-row>

            <div class="text-subtitle-1 mt-4 mb-2">Fotos</div>
            <div class="d-flex flex-wrap ga-3 mb-3">
              <v-sheet v-for="(foto, indice) in formulario.fotos" :key="foto.id" class="position-relative" rounded="lg" border>
                <v-img :src="foto.src" width="88" height="88" cover />
                <v-btn
                  icon="mdi-close"
                  size="x-small"
                  color="error"
                  class="position-absolute"
                  style="top: 4px; right: 4px"
                  aria-label="Quitar foto"
                  @click="quitarFoto(indice)"
                />
              </v-sheet>
            </div>
            <v-file-input
              label="Subir fotos"
              prepend-icon="mdi-camera"
              variant="outlined"
              accept="image/jpeg,image/png,image/webp,image/gif"
              multiple
              hide-details="auto"
              :loading="subiendo"
              @update:model-value="agregarFotos"
            />

            <template v-if="formulario.tipo === 'variable'">
              <div class="d-flex align-center mt-6 mb-2">
                <div class="text-subtitle-1">Caracteristicas</div>
                <v-spacer />
                <v-btn variant="text" prepend-icon="mdi-plus" @click="agregarAtributo">Agregar</v-btn>
              </div>
              <v-row v-for="(atributo, indice) in formulario.atributos" :key="indice">
                <v-col cols="12" md="4">
                  <v-text-field v-model="atributo.nombre" label="Caracteristica*" placeholder="Color" variant="outlined" hide-details="auto" :rules="[obligatorio]" />
                </v-col>
                <v-col cols="12" md="7">
                  <v-combobox
                    v-model="atributo.opciones"
                    label="Opciones*"
                    placeholder="Rojo"
                    variant="outlined"
                    multiple
                    chips
                    closable-chips
                    hide-details="auto"
                    :rules="[(valor) => (valor || []).length > 0 || 'Agrega una opcion']"
                  />
                </v-col>
                <v-col cols="12" md="1" class="d-flex align-center">
                  <v-btn icon="mdi-delete" variant="text" color="error" aria-label="Quitar caracteristica" @click="quitarAtributo(indice)" />
                </v-col>
              </v-row>
              <v-alert v-if="exceso" type="warning" variant="tonal" class="mt-3">
                Hay demasiadas combinaciones. El maximo es 50.
              </v-alert>

              <div class="text-subtitle-1 mt-6 mb-2">Combinaciones</div>
              <v-sheet v-for="variacion in formulario.variaciones" :key="variacion.valores.join('|')" border rounded="lg" class="pa-3 mb-3">
                <div class="text-subtitle-2 mb-3">{{ etiqueta(variacion) }}</div>
                <v-row>
                  <v-col cols="12" sm="4">
                    <v-text-field v-model="variacion.precio" label="Precio*" prefix="$" variant="outlined" hide-details="auto" />
                  </v-col>
                  <v-col cols="12" sm="4">
                    <v-text-field v-model.number="variacion.existencia" label="Existencia*" type="number" min="0" variant="outlined" hide-details="auto" />
                  </v-col>
                  <v-col cols="12" sm="4">
                    <v-text-field v-model="variacion.sku" label="SKU" variant="outlined" hide-details="auto" />
                  </v-col>
                  <v-col cols="12" class="d-flex align-center ga-3">
                    <v-avatar v-if="variacion.foto" rounded="lg" size="48">
                      <v-img :src="variacion.foto" cover />
                    </v-avatar>
                    <v-file-input
                      label="Foto de esta combinacion"
                      prepend-icon="mdi-image"
                      variant="outlined"
                      accept="image/jpeg,image/png,image/webp,image/gif"
                      hide-details
                      density="comfortable"
                      @update:model-value="(archivos) => fotoVariacion(variacion, archivos)"
                    />
                  </v-col>
                </v-row>
              </v-sheet>
            </template>

            <v-switch v-model="formulario.publicado" label="Publicado en la tienda" color="primary" hide-details class="mt-2" />
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

    <v-dialog v-model="modalAi" max-width="520">
      <v-card>
        <v-card-item>
          <template #prepend>
            <v-icon icon="mdi-auto-fix" size="28" />
          </template>
          <v-card-title class="text-h6">Crear producto con AI</v-card-title>
        </v-card-item>
        <v-card-text>
          <v-alert v-if="errorAi" type="error" variant="tonal" class="mb-4">{{ errorAi }}</v-alert>
          <template v-if="pasoAi === 'tipo'">
            <p class="mb-4">Antes de la foto, elige si el producto es normal o variable.</p>
            <div class="d-flex flex-wrap ga-2">
              <v-btn variant="outlined" prepend-icon="mdi-package-variant" @click="elegirTipoAi('simple')">Normal</v-btn>
              <v-btn variant="outlined" prepend-icon="mdi-shape" @click="elegirTipoAi('variable')">Variable</v-btn>
            </div>
          </template>
          <template v-else>
            <p class="mb-4">
              Agrega una o varias fotos del producto {{ tipoAi === "variable" ? "variable" : "normal" }}.
              Puedes tomarlas o elegirlas. Cuando esten listas, pulsa Analizar.
            </p>
            <input
              ref="selectorFotos"
              type="file"
              accept="image/jpeg,image/png,image/webp,image/gif"
              multiple
              hidden
              @change="alElegirFotos"
            />
            <div class="d-flex flex-wrap ga-2 mb-4">
              <v-btn prepend-icon="mdi-camera" variant="outlined" :disabled="analizando" @click="abrirCamara">Camara</v-btn>
              <v-btn prepend-icon="mdi-image-multiple" variant="outlined" :disabled="analizando" @click="selectorFotos.click()">Seleccionar imagenes</v-btn>
            </div>
            <div v-if="fotosAi.length" class="d-flex flex-wrap ga-3">
              <v-sheet
                v-for="(foto, indice) in fotosAi"
                :key="foto.vista"
                class="position-relative"
                rounded="lg"
                border
              >
                <v-img :src="foto.vista" width="88" height="88" cover />
                <v-btn
                  icon="mdi-close"
                  size="x-small"
                  color="error"
                  class="position-absolute"
                  style="top: 4px; right: 4px"
                  aria-label="Quitar foto"
                  :disabled="analizando"
                  @click="quitarFotoAi(indice)"
                />
              </v-sheet>
            </div>
          </template>
        </v-card-text>
        <v-divider />
        <v-card-actions class="px-4 py-3">
          <v-btn v-if="pasoAi === 'foto'" variant="text" :disabled="analizando" @click="pasoAi = 'tipo'">Atras</v-btn>
          <v-spacer />
          <v-btn variant="text" :disabled="analizando" @click="modalAi = false">Cerrar</v-btn>
          <v-btn
            v-if="pasoAi === 'foto'"
            color="primary"
            variant="flat"
            :loading="analizando"
            :disabled="!fotosAi.length"
            @click="analizarAi"
          >
            Analizar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog :model-value="camaraAbierta" max-width="640" @update:model-value="(abierto) => { if (!abierto) cerrarCamara() }">
      <v-card>
        <v-card-title class="text-h6">Camara</v-card-title>
        <v-card-text>
          <v-alert v-if="errorCamara" type="warning" variant="tonal" class="mb-3">{{ errorCamara }}</v-alert>
          <video ref="videoCamara" autoplay playsinline muted style="width: 100%; min-height: 220px; max-height: 360px; background: #111; border-radius: 8px; object-fit: cover" />
          <p class="text-body-2 mt-3 mb-0">Espera a ver la imagen. Al tomar la foto, se agrega a la lista y la camara se cierra.</p>
        </v-card-text>
        <v-divider />
        <v-card-actions class="px-4 py-3">
          <v-spacer />
          <v-btn color="primary" prepend-icon="mdi-camera" :disabled="!camaraLista" :loading="capturando" @click="capturarFoto">Tomar foto</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog :model-value="Boolean(pendiente)" max-width="480" @update:model-value="(abierto) => { if (!abierto) pendiente = null }">
      <v-card v-if="pendiente">
        <v-card-item>
          <template #prepend>
            <v-avatar color="error" variant="tonal">
              <v-icon icon="mdi-package-variant-remove" />
            </v-avatar>
          </template>
          <v-card-title class="text-h6">Eliminar producto</v-card-title>
          <v-card-subtitle>{{ pendiente.nombre }}</v-card-subtitle>
        </v-card-item>
        <v-card-text>Se va a borrar este producto de la tienda.</v-card-text>
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
