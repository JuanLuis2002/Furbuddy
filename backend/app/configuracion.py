import os


def url_tienda() -> str:
    return os.environ.get("WOOCOMMERCE_URL", "https://furbuddy.xyz").rstrip("/")


def claves_woocommerce() -> tuple[str, str]:
    return os.environ.get("WOOCOMMERCE_KEY", ""), os.environ.get("WOOCOMMERCE_SECRET", "")


def secreto_sesion() -> str:
    return os.environ.get("FURBUDDY_SESION_SECRETO", "")
