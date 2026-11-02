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

/**
 * =========================================================
 * FURBUDDY: AJUSTE DE DISENO COMPACTO - ABOUT US (/about-us/)
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
	<style id="furbuddy-about-us-compacto">
		/* Diseno compacto para la seccion de Contacto y Ofertas en About Us */
		.stk-7386da5 {
			padding-top: 25px !important;
			padding-bottom: 25px !important;
			align-items: center !important;
		}
		.stk-815200a,
		.stk-18000f1 {
			margin-bottom: 12px !important;
		}
		.stk-815200a .stk-block-heading__text,
		.stk-18000f1 .stk-block-heading__text {
			font-size: 1.6rem !important;
			margin-bottom: 0 !important;
		}
		/* Limitar imagen del producto en el carrusel de ofertas */
		.stk-55d950f ul.products li.product .woocommerce-loop-product__link img,
		.stk-55d950f ul.products li.product img {
			max-height: 220px !important;
			width: auto !important;
			margin: 0 auto 10px auto !important;
			object-fit: contain !important;
			display: block !important;
		}
		.stk-55d950f ul.products li.product {
			margin-bottom: 8px !important;
			padding: 12px !important;
		}
		.stk-55d950f ul.products li.product .woocommerce-loop-product__title {
			font-size: 1rem !important;
			margin-bottom: 4px !important;
			line-height: 1.3 !important;
		}
		.stk-55d950f ul.products li.product .price {
			margin-bottom: 6px !important;
			font-size: 1rem !important;
		}
		.stk-55d950f ul.products li.product .button {
			padding: 6px 16px !important;
			font-size: 0.85rem !important;
		}
		/* Reducir espaciador entre producto y contador */
		.stk-6fd09d5,
		[data-block-id="6fd09d5"] {
			height: 8px !important;
			min-height: 8px !important;
			margin: 0 !important;
		}
		/* Contador regresivo compacto */
		.stk-2710f29 {
			margin-top: 4px !important;
			margin-bottom: 0 !important;
		}
		.stk-2710f29 .stk-block-countdown__digit {
			font-size: 1.5rem !important;
			line-height: 1 !important;
		}
		.stk-2710f29 .stk-block-countdown__label {
			font-size: 0.75rem !important;
			margin-top: 2px !important;
		}
		/* Formulario WPForms compacto */
		.stk-7386da5 .wpforms-container {
			margin-bottom: 0 !important;
		}
		.stk-7386da5 .wpforms-field {
			padding: 5px 0 !important;
		}
		.stk-7386da5 textarea.wpforms-field-medium,
		.stk-7386da5 .wpforms-field textarea {
			height: 80px !important;
			min-height: 80px !important;
		}
	</style>
	<?php
}, 999 );

