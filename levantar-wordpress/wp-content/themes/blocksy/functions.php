<?php
/**
 * Blocksy functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Blocksy
 */

if (version_compare(PHP_VERSION, '5.7.0', '<')) {
	require get_template_directory() . '/inc/php-fallback.php';
	return;
}

require get_template_directory() . '/inc/init.php';

/**
 * =========================================================
 * FURBUDDY: PERSONALIZACIONES CARRITO, CHECKOUT Y NAVIDAD
 * =========================================================
 */

// 1. Eliminar metodo de "Recogida local" (local pickup) en carrito y checkout
add_filter( 'woocommerce_package_rates', function( $rates ) {
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'local_pickup' === $rate->method_id || false !== strpos( $rate_id, 'local_pickup' ) ) {
			unset( $rates[ $rate_id ] );
		}
	}
	return $rates;
}, 100, 1 );

// 2. Ocultar calculadora y selector de direccion en la pagina del carrito
add_filter( 'woocommerce_shipping_calculator_enable', '__return_false' );
add_filter( 'woocommerce_shipping_estimate_html', '__return_empty_string' );

// 3. Eliminar campo "Nombre de la empresa (opcional)" del checkout
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
	if ( isset( $fields['billing']['billing_company'] ) ) {
		unset( $fields['billing']['billing_company'] );
	}
	return $fields;
} );

// 4. Cambiar texto "Shipment" por "Envio" en carrito y checkout
add_filter( 'woocommerce_shipping_package_name', function( $name, $i, $package ) {
	return 'Envio';
}, 10, 3 );

add_filter( 'gettext', function( $translated_text, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Shipment' === $text ) {
		return 'Envio';
	}
	return $translated_text;
}, 20, 3 );

// 5. Logo Navideno en el Header (Opcion 1: Horizontal Completo)
add_filter( 'get_custom_logo', function( $html ) {
	$logo_b64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCACyAUADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9UGDGlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAEHSlpB0paACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP/9k=';
	return sprintf(
		'<a href="%1$s" class="site-logo-container" rel="home"><img src="data:image/jpeg;base64,%2$s" class="default-logo furbuddy-navidad-logo" alt="Furbuddy Navideno" style="max-height: 65px; width: auto; object-fit: contain;" /></a>',
		esc_url( home_url( '/' ) ),
		$logo_b64
	);
}, 99 );

// 6. Favicon Navideno (Gatito exacto con gorro de Santa)
add_action( 'wp_head', function() {
	$fav_b64 = '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';
	echo '<link rel="icon" type="image/png" href="data:image/png;base64,' . $fav_b64 . '" />' . "\n";
	echo '<link rel="apple-touch-icon" href="data:image/png;base64,' . $fav_b64 . '" />' . "\n";
}, 1 );

add_filter( 'get_site_icon_url', function( $url ) {
	$fav_b64 = 'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAEENSURBVHhe7V0HWJNXF0ZmgJDBdHbY1oWCCO6FCgqCWxFnh1urtlqtEwVRcVar1frbOuuqe+9dd92yVwLZi4SwR97/uTeEEaa2tWp9n+dK/L4vX77kvPecc885914TnU63T6fTRb9v/81motPpHuM9/rMgBLhtfPA9/jt4T4D/ON4T4D+O9wT4j+M9Af7jeE+A/jvcE+I/jvcE+I/j3SCAzvjAe9QU7wYBKAPes+BV8I4Q4D1eFe8cAd7rgZfDO0MAna4QWeoE6ApzjU+9RxV4ZwhQmK+F5NE6FGSJjM681wlV4a0mQGnR5qmeIuXUIGQKr1Z4/s1BxU9V8dF/Hm81AQhIz88QXoXkxgzwD7SB6PJkaHjnkJ+jML70jYSuIBsF+RnQ/Y0U0OmAvIKCGt3zrSdAbrYC4oc/gHfEB6kHW4N/uAcEd8KRkZZsfOkbiQzpAyiTzxgf/kvIycuDWJmGgsJC41Pl8NYTgKJQC038AfAPdYHq2SZiD/5FVoNih7L8HSKZ5shvDkDKMwpc/xVkJWTC5U2AxlZOUiVKpGdmw9VegZy8/ONLy3GO0EA8qMV5sggvD4N+ekJxcfeBkhvz0Lq6QEozJEYn6oxCgoKUajTITsvH3ypAiKVBqlyFURKDVJkKuTlF9DzhYXlf5V3ggAEusJsyBNOQpevNT71xiJL/hCpJwMgOOYNLe+Y8ekaQ52RCYUmg75Oz8pCkliBJJECPIkKmdl59Lhco4U2u/wQ+Z0hAOnzBfk5eg+oUpQ+V9V1VeFV36cHjVconkL+cBVST/ZHyu9tITraBaKzAyD/cwkyJTehK9QLrVIUPUJefh7yCvKhzshCqkxZ7PQpNVrwxEpoMrPp//MLC6lG0GbnICc/H/kFBcW3eocI8GooyMiAJC4WvFu3kHT+PJJOnUTi6VNIungR/Hv3IEtIQGG2/of8O5CflwnB/XVIPtAFybubQ3CwPcTHvCE40h4px70huheG7Eyp8dsqhCYjEyliOUQKNVIkimLBZuXmUkLkFeidwNz8AmoKpGnp1ERkZOv9DYL/JgEKC8C/cxe31/+IgyNGYEWTZgh1ckEY1xHh9o5YQpqDE5a41MEPLdxwbMwY3Nn0EwQPHxrf6eU1Ah2jpSFDdBuim7PBP9gBwqOdIL4xGen809DlEuFX770TEC9fqclAkkiGJJEcGTl6FZ+dm0cFnl9EAG1WDhJFcvClKqgysqg/YMA7RYBqRVFYiOSrV3FowgSs/rQxQixtsNiCgXAGE8ut7RBhw8JyWzYibFlYbmOH5dZMLGcwscSSgVAGE+td3XB02jdIun6N3utVUPoZC7PFEF0cCf5xH+Qo7pU6UzPk5uVT284Ty5EolCNVpkJWbh71/gXE+SssRGZOHlKkKiQJ5fSvQptJYwQGvAMEqFbsFNqUFJydMxcRH32MxRZWWGJpjWXWTKzl2OOXug1w8NNGOO3aHOfd3HG+hRuON22GPR99gs3OdbDGjoMVhBBWtgi3ssWKhp/i5KxZUMTFGX9MtTB+WvnD5RBcGg1dvsboTPVQqNOp8GVp6VBrsyAmpkCqgFSlhUCWBrk6HXyJgo4G1NpMyNI01FSkZ2YV3+MdIED1SLp2FT97d0OIBQNhRPC2TGx2dsFFj1aI7OEDSf8ByAwORs7wYcgdPhy5w4YhJzgY6kGDwPPzw8NOnXG8qSs2OjhhlS0HKxi2lEAb27bHs4MHjT/upaCKPwTpw9UVUKN65OTl02bQ6ES1K7WZSBYraOOJFVCmZ6CgaPhH/iVa4z/lBD7at5/22BBTC4TZsPCDoxMueXlB1KcPcocMgW7IEOT06Yf0nn5I8/GFppc/Mvv0RVa//sgLGgLdiOHAiJHIHBIEnr8/zrm5YwMhApON5WYMLHOug7OLFiEv3dCDKxJkRcf0yNEkQCt7pP+Prqory4OO7Y1GPZqsbCSJ5dQnICQw+AUG6HQ62gx4IwhQ/Zeu/oqK8OeO7Qh1cMZiSxuEszjY07gx4v16IaN3ANLadwavYWPE1/kQ8c4NEO9YD/FO9ZDgVJ8eS/i4EUQeXlD38EH2wIHQDQ+Gbthw5AYHg+ffG4c++wwrWRxEWNkg1MoGe0aOhDo1tfizjePwlX8DcmXlZ6sCifIRlZ+mzaS2X6pOR7JEDqGcmAIlBPI08CRKqLXZVFOk0eGiisYKDHgjCECQnpldZcjyZfF4334s4jhgobkllto74GzLlhB39YbY1R0JzvURz3ZBAqcO4rl1kWBvaHX0jVsXieQc2wXxjvWR0tgVym7dkB00BPnBQ6EbNgyZQUH4o107/ODghGUMJsJMLfFzDx+IIl+UeoqaC7bmV5aAhH6laRra0/U9XglNRjYN/hAnkIz5SYCIBoXECjoKkKu1yMl/Q0wAUUUkcUFCmYSt2sxcOmbNziNqi/wkr/KzAAnXrmFZ/Q8QUssSyzkOuNikGQRNW4DnVB9xLCfEc+sgwaFecUskzbF+xc2hLuI5tal2SHF1hyYgAHnBQSgYNhSFI4YjxtcXW+vWxzJrO4SZmGND6zbgPbhPn6Pk6Uv1cfqiOl1f5clikKuI85csVCBZpIC2KPCTlZOHVKmiOBlESEBGCcQhTDeKafyrBCjQEcErIVNrIVSo6YMSFUUcl5dG0W+mSk7ChrbtEFLLHCvsODjnVA/JtT9EErcOEklvdzC0IgI41qct0bEKEpBz3LqII8Sp3xCKLl2RGxyEfEKC4cMh6tsX+z75jA4dl5ha4QcPT6Q8LrLrpR/ubwZR/cliGVJlaXSISxC4MQfENBjiAKST8aVKCJVq8MQyZGb9y4GgvHwSvszUMzgzE4lCGZLESqrGyBchyQsSzCAOzcugIDsHh8aOwwJTSyxnsnDM3hmJDvXBI8J3qEtfF/d6Ivii3p9QpfDLNmIe4hzqQ+TZFtlBg5E3LBiFw0ZAOXAQDjVqjGW2dgirZYEN7TpAFhNr/IiV4NUIQkxARnY2te1CeVrxXdK0GXR4aIj4kd9TIFdRh5Ca2rySUPNrJYBBqZMH50kUNFBB/k/sUrxADp5IiewcvR+g0GghUek960JdzYIuj3btxiIWF0ts7LCb44QYp/pIpkImwi/q9VTYpQVu/P8aNPv6iOPWRUrzlsgaOBB5wcEoCB4GFSHBZ40QQTRBLUtsC+iLdKHQ+DH/dqjSM6FQ65NgmTk54BOfgAR+ZErqB5DfmPyWRPjGeK0EIPZdptYgLT2TqiQSsyYgqosvUUGhziy+VqRUQ6xS00gXyXFXhzQ+D2vcPRDCsMFGrj0eOtYDr1SPr7HNr2FLsq+HOE5t8Js0R8aAgcgdFoyCYcFQDBiAvR83xAoSQTSzwuHJU1BQyusujVfr9+VBxvaklxPNKpAR71+FVGkaUuRpECmIaSigv73BJJTGayUAETQRLHFGiGeqLopIEWdQIFcjTav/PyWEVEXj10RTaMuZgvI/3dmQEMwzt8IKrj3OO9RGcpGKNxZ80sv29kpagmMDJFISuIDXuDky+w/Qa4LhwRD37Ytt9etjhZUNwm3ZuLVuvfHj/iPQZmZBokyjsX+hIg0ZuaQySIUc6lRXjNdKAILsvDwIFBrEpUhpxsowJBEpNFBn6AWt1GQiQSBDglBGI1sFRsELYwJIY2Kw9KOGWGhlg+32etWfRIWuV/tU8A51EetYHw/sayOamIQKhPoqjdw/lu0CXpMWyBo8CHlFjmGinx82O9fGCnMGVtb/APEXLpR5ZgPKU/lVoDeuhYVFhSG5eRDKVdTJJv+vKs7wWgmQnZNLVT/p3SReTcqWCAm0WbmUAKr0LGrPyLBGpEyn8WxCAlma1ugrlP3f2blzMdfEDBEsNm461AbPkQhfP9QjvdRg/y9wnLGXyUG0Qz2qIYyF+VKNmBfH+jSARGIIMWwXpLp7Iid4KNUEJHr43Nsb6zn2WGZmgS2durwWf4CAaFAyFCzbaSrG6yUAqVHTZlGGkkcjNkuq0oAvUZY0qQoamrIEtVnkNRnuGIc8Df/TpKZiTaMmCLGwwi6OA3XokkhQp5TaT7Cvh1vc2vjJlo2DbEcq/CRjgb5KKyZBfRpbiOXWgbxTFxQOH4aCoUOBYSPwh1cbrGZxsczKBofHjkP0hQuQREVDV1gSjDEm9OvEayVARSCClSjVNFBB/IKKPFU9yjswBLc3bcZCMysss2PjBun99nqBl/T++njArYMDLEestLXDJXtn8B3+KgGISdE7mTwDmUjAiFsHj+t/jMft2uMPDw/cdvfAzZae2ODkgpVsLlaw7bGqTj2s+qgh9o3+HNLYGOOv89rxWglAPFUSv87MyS2qXS8sDmWSmHWySJ/TzsjKpX2CaADiGOpjBuV7SUFuDnYGBGJ+LTP8j22PGEfDeF/fku3r4alDHVzgOmOjHRfrmWzcsa8NPnUGjYVa0ih5igRb0XVEgyQ71UeUcwM8cqqPJ871Een8AS441MUGjiOW23ERYcfCSjs21nAdsNLBCSvtHbGCycJyBgPLGAyEWjCwrn17iKOjSn2jUt+x/NetAHrb/1fwWglAhisGH0BMK1bTqNontWskl00igCQayCdlTko19QFIkIjktStC4q0/EO7kgiU2TJzhkp5d4vGTHhprXwfXOc44wnbGUiYLv7Ls8ci+DpIrGPsbhE5NBn1dcj7JoeivI+nxDfDCuQFO2DvjV449NnMcsIXriO32zvjB3pEmnUJtmFjGdUSEg/5zSXAonMvFSnd3bOzVCyubNEGEHRshZubYPmQw8tIr/n7GyM7PRbxGiBhlCsi0j78Dr';
	return 'data:image/png;base64,' . $fav_b64;
}, 99 );

// 7. Estilos Globales Navidenos, Fuente Comfortaa y Diseno Equilibrado
add_action( 'wp_head', function() {
	echo '<style id="furbuddy-navidad-completo">
		@import url("https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600;700&display=swap");

		:root {
			--theme-palette-color-1: #E50914 !important;
			--theme-palette-color-2: #B8000B !important;
			--theme-palette-color-3: #14171A !important;
			--theme-palette-color-4: #2D3748 !important;
			--theme-palette-color-5: #E2E8F0 !important;
			--theme-palette-color-6: #F8FBFA !important;
			--theme-palette-color-7: #FFFFFF !important;
			--theme-palette-color-8: #FFFFFF !important;

			--theme-button-background-color: #E50914 !important;
			--theme-button-hover-background-color: #B8000B !important;
			--theme-link-initial-color: #E50914 !important;
			--theme-link-hover-color: #B8000B !important;
		}

		/* Tipografia Comfortaa general */
		body, h1, h2, h3, h4, h5, h6,
		.ct-header, .menu-item a,
		.stk-button, a.stk-button, .button,
		.woocommerce-loop-product__title,
		.price, .amount, .entry-title, .shop_table {
			font-family: "Comfortaa", cursive, sans-serif !important;
		}

		/* Header y Logo */
		.site-logo-container img.furbuddy-navidad-logo {
			max-height: 65px !important;
			width: auto !important;
			object-fit: contain !important;
		}
		.ct-header-cart .ct-cart-item-count {
			background-color: #E50914 !important;
			color: #ffffff !important;
		}

		/* Migas de pan (Breadcrumbs) */
		.entry-breadcrumbs a, .ct-breadcrumbs a, .breadcrumb-trail a {
			color: #FFB800 !important;
			font-weight: 700 !important;
		}

		/* Seccion de 3 columnas (01, 02, 03): Fondo Verde Bosque Navideno en vez de rojo plano */
		.stk-block-columns.stk--has-background-overlay,
		.stk-5cc302c,
		[data-block-id="5cc302c"] {
			background: linear-gradient(135deg, #093822 0%, #0E5231 60%, #062817 100%) !important;
			border-radius: 16px !important;
		}
		.stk-block-columns.stk--has-background-overlay:before,
		.stk-5cc302c:before {
			opacity: 0.1 !important;
		}
		.stk-block-columns h2,
		.stk-block-columns [class*="heading"] {
			color: #FFB800 !important;
			text-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
		}
		.stk-block-columns p,
		.stk-block-columns span.stk-block-text__text {
			color: #FFFFFF !important;
		}
		.stk-block-columns .stk-button,
		.stk-block-columns a.stk-button {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border: 1px solid #FFB800 !important;
			box-shadow: 0 4px 14px rgba(229, 9, 20, 0.45) !important;
		}
		.stk-block-columns .stk-button:hover,
		.stk-block-columns a.stk-button:hover {
			background-color: #B8000B !important;
			box-shadow: 0 6px 18px rgba(255, 184, 0, 0.5) !important;
		}

		/* Botones de Preguntas Frecuentes y Portada */
		.stk-block-button:first-child .stk-button,
		.stk-block-button a:not(.is-style-ghost),
		.stk-button:not(.is-style-ghost) {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border: none !important;
			box-shadow: 0 4px 12px rgba(229, 9, 20, 0.35) !important;
		}
		.stk-block-button:first-child .stk-button:hover,
		.stk-block-button a:not(.is-style-ghost):hover,
		.stk-button:not(.is-style-ghost):hover {
			background-color: #B8000B !important;
		}
		/* Boton secundario / ghost (Inicio / Ver mas) en Verde Pino con borde */
		.stk-block-button.is-style-ghost .stk-button,
		.stk-button.is-style-ghost,
		a.is-style-ghost {
			background-color: #ffffff !important;
			color: #0E8A42 !important;
			border: 2px solid #0E8A42 !important;
		}
		.stk-block-button.is-style-ghost .stk-button:hover,
		.stk-button.is-style-ghost:hover,
		a.is-style-ghost:hover {
			background-color: #0E8A42 !important;
			color: #ffffff !important;
		}

		/* Botones WooCommerce (Carrito, Checkout, Anadir al carrito) */
		.wc-proceed-to-checkout .checkout-button,
		#place_order,
		.single_add_to_cart_button,
		.add_to_cart_button,
		.woocommerce-form-login__submit,
		.ct-cart-content .checkout {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border-color: #E50914 !important;
			font-weight: 700 !important;
			box-shadow: 0 4px 14px rgba(229, 9, 20, 0.35) !important;
		}
		.wc-proceed-to-checkout .checkout-button:hover,
		#place_order:hover,
		.single_add_to_cart_button:hover,
		.add_to_cart_button:hover,
		.woocommerce-form-login__submit:hover,
		.ct-cart-content .checkout:hover {
			background-color: #B8000B !important;
			border-color: #B8000B !important;
		}

		/* Boton de cupon en Verde Pino */
		.woocommerce-cart-form .coupon button.button,
		.woocommerce-cart-form button[name="update_cart"] {
			background-color: #0E8A42 !important;
			color: #ffffff !important;
			border-color: #0E8A42 !important;
			font-weight: 600 !important;
		}
		.woocommerce-cart-form .coupon button.button:hover,
		.woocommerce-cart-form button[name="update_cart"]:hover {
			background-color: #085F2C !important;
			border-color: #085F2C !important;
		}

		/* Precios e Insignias */
		.price, .amount, .woocommerce-Price-amount {
			color: #0E8A42 !important;
			font-weight: 700 !important;
		}
		.onsale, [class*="ct-woo-badge-"] {
			background-color: #E50914 !important;
			color: #ffffff !important;
			font-weight: 700 !important;
			border: 1px solid #FFB800 !important;
		}

		/* Ocultar elementos sobrantes en carrito */
		.woocommerce-shipping-destination,
		.shipping-calculator-button,
		.shipping-calculator-form {
			display: none !important;
		}

		/* Capa de copos de nieve visibles en fondo blanco */
		.furbuddy-snow-layer {
			position: fixed;
			top: 0;
			left: 0;
			width: 100vw;
			height: 100vh;
			pointer-events: none;
			z-index: 999999;
			overflow: hidden;
		}
		.furbuddy-snowflake {
			position: absolute;
			top: -15px;
			user-select: none;
			animation: furbuddy-fall linear infinite;
		}
		.snow-ice {
			color: #64B5F6 !important;
			filter: drop-shadow(0 2px 4px rgba(33, 150, 243, 0.5)) !important;
		}
		.snow-gold {
			color: #FFD54F !important;
			filter: drop-shadow(0 2px 4px rgba(255, 179, 0, 0.5)) !important;
		}
		.snow-frost {
			color: #B3E5FC !important;
			filter: drop-shadow(0 2px 5px rgba(2, 136, 209, 0.6)) !important;
		}
		@keyframes furbuddy-fall {
			0% { transform: translateY(0) rotate(0deg); opacity: 0.95; }
			100% { transform: translateY(105vh) rotate(360deg); opacity: 0.25; }
		}
	</style>';
}, 99 );

// 8. Copos de Nieve Visibles en Footer
add_action( 'wp_footer', function() {
	echo '<div class="furbuddy-snow-layer" aria-hidden="true">
		<span class="furbuddy-snowflake snow-ice" style="left:4%;animation-duration:9s;animation-delay:0s;font-size:18px;">❄</span>
		<span class="furbuddy-snowflake snow-gold" style="left:14%;animation-duration:13s;animation-delay:2s;font-size:14px;">❅</span>
		<span class="furbuddy-snowflake snow-frost" style="left:26%;animation-duration:10s;animation-delay:4s;font-size:20px;">❄</span>
		<span class="furbuddy-snowflake snow-ice" style="left:39%;animation-duration:12s;animation-delay:1s;font-size:16px;">•</span>
		<span class="furbuddy-snowflake snow-gold" style="left:52%;animation-duration:11s;animation-delay:3s;font-size:22px;">❄</span>
		<span class="furbuddy-snowflake snow-frost" style="left:64%;animation-duration:14s;animation-delay:5s;font-size:15px;">❅</span>
		<span class="furbuddy-snowflake snow-ice" style="left:76%;animation-duration:10s;animation-delay:2s;font-size:19px;">❄</span>
		<span class="furbuddy-snowflake snow-gold" style="left:87%;animation-duration:12s;animation-delay:0s;font-size:16px;">•</span>
		<span class="furbuddy-snowflake snow-frost" style="left:96%;animation-duration:11s;animation-delay:4s;font-size:15px;">❄</span>
	</div>';
} );
