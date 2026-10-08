import { createApp } from "vue"
import { createRouter, createWebHistory } from "vue-router"
import { createVuetify } from "vuetify"
import { aliases, mdi } from "vuetify/iconsets/mdi"
import { es } from "vuetify/locale"
import App from "./App.vue"
import FrmConfiguracion from "./frmConfiguracion/FrmConfiguracion.vue"
import FrmLogin from "./frmLogin/FrmLogin.vue"
import FrmUsuarios from "./frmUsuarios/FrmUsuarios.vue"
import { haySesion } from "./frmLogin/frmLogin.js"
import { temasIniciales } from "./tema.js"
import "vuetify/styles"
import "@mdi/font/css/materialdesignicons.css"

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", component: FrmLogin },
    { path: "/usuarios", component: FrmUsuarios, meta: { panel: true } },
    { path: "/configuracion", component: FrmConfiguracion, meta: { panel: true } },
  ],
})

router.beforeEach((to) => {
  if (to.meta.panel && !haySesion()) {
    return "/"
  }
  if (to.path === "/" && haySesion()) {
    return "/usuarios"
  }
})

const inicial = temasIniciales()

const vuetify = createVuetify({
  locale: { locale: "es", messages: { es } },
  icons: { defaultSet: "mdi", aliases, sets: { mdi } },
  theme: {
    defaultTheme: inicial.modo,
    themes: {
      light: inicial.light,
      dark: inicial.dark,
    },
  },
})

createApp(App).use(router).use(vuetify).mount("#app")
