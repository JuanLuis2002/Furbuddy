export const paleta = [
  {
    nombre: "Azul",
    valor: "#1867C0",
    light: { background: "#f4f6f8", surface: "#ffffff" },
    dark: { background: "#161b22", surface: "#21262e" },
  },
  {
    nombre: "Naranja",
    valor: "#FB8C00",
    light: { background: "#faf7f4", surface: "#ffffff" },
    dark: { background: "#1b1433", surface: "#261c40" },
  },
  {
    nombre: "Verde",
    valor: "#3E6B4F",
    light: { background: "#f4f3ec", surface: "#fbfaf6" },
    dark: { background: "#1a221c", surface: "#243028" },
  },
  {
    nombre: "Rojo",
    valor: "#C62828",
    light: { background: "#faf6f6", surface: "#ffffff" },
    dark: { background: "#241418", surface: "#321c22" },
  },
  {
    nombre: "Cafe",
    valor: "#9A3412",
    light: { background: "#f6f3ee", surface: "#ffffff" },
    dark: { background: "#241812", surface: "#32241c" },
  },
]

export function aparienciaGuardada() {
  const color = localStorage.getItem("furbuddy-color") || paleta[0].valor
  const modo = localStorage.getItem("furbuddy-tema") === "dark" ? "dark" : "light"
  return { color, modo }
}

function fondos(color, modo) {
  const tono = paleta.find((item) => item.valor.toLowerCase() === String(color).toLowerCase())
  if (tono) {
    return tono[modo]
  }
  if (modo === "dark") {
    return { background: "#1a1d24", surface: "#242830" }
  }
  return { background: "#f4f6f8", surface: "#ffffff" }
}

export function temasIniciales() {
  const { color, modo } = aparienciaGuardada()
  return {
    modo,
    light: { dark: false, colors: { primary: color, ...fondos(color, "light") } },
    dark: { dark: true, colors: { primary: color, ...fondos(color, "dark") } },
  }
}

export function aplicarApariencia(theme, color, modo) {
  for (const nombre of ["light", "dark"]) {
    const destino = theme.themes.value[nombre].colors
    const base = fondos(color, nombre)
    destino.primary = color
    destino.background = base.background
    destino.surface = base.surface
  }
  theme.global.name.value = modo
  localStorage.setItem("furbuddy-color", color)
  localStorage.setItem("furbuddy-tema", modo)
}
