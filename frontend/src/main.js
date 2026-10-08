import { createApp } from "vue"
import { createRouter, createWebHistory } from "vue-router"
import App from "./App.vue"
import FrmLogin from "./frmLogin/FrmLogin.vue"
import FrmUsuarios from "./frmUsuarios/FrmUsuarios.vue"
import { haySesion } from "./frmLogin/frmLogin.js"

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", component: FrmLogin },
    { path: "/usuarios", component: FrmUsuarios, meta: { panel: true } },
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

createApp(App).use(router).mount("#app")
