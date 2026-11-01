import os


def url_tienda() -> str:
    return os.environ.get("WOOCOMMERCE_URL", "https://furbuddy.xyz").rstrip("/")


def claves_woocommerce() -> tuple[str, str]:
    return os.environ.get("WOOCOMMERCE_KEY", ""), os.environ.get("WOOCOMMERCE_SECRET", "")


def secreto_sesion() -> str:
    return os.environ.get("FURBUDDY_SESION_SECRETO", "")


def clave_gemini() -> str:
    return os.environ.get("GEMINI_API_KEY", "")


def clave_groq() -> str:
    return os.environ.get("GROQ_API_KEY", "")
