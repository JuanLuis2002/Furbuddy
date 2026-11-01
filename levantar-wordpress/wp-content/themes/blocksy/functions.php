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
 * Personalizaciones Furbuddy: Carrito y Checkout
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

// 4. CSS de soporte para ocultar selector de destino en carrito
add_action( 'wp_head', function() {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		echo '<style>
			.woocommerce-shipping-destination,
			.shipping-calculator-button,
			.shipping-calculator-form {
				display: none !important;
			}
		</style>';
	}
} );

// 5. Cambiar texto "Shipment" por "Envío" en carrito y checkout
add_filter( 'woocommerce_shipping_package_name', function( $name, $i, $package ) {
	return 'Envío';
}, 10, 3 );

add_filter( 'gettext', function( $translated_text, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Shipment' === $text ) {
		return 'Envío';
	}
	return $translated_text;
}, 20, 3 );

// 6. Paleta global navideña "Invierno Mágico & Nieve" para toda la tienda
add_action( 'wp_head', function() {
	echo '<style id="furbuddy-navidad-paleta">
		:root {
			--theme-palette-color-1: #D32F2F;
			--theme-palette-color-2: #B71C1C;
			--theme-palette-color-3: #1E232A;
			--theme-palette-color-4: #3E4856;
			--theme-palette-color-5: #E2E8F0;
			--theme-palette-color-6: #F8FAFC;
			--theme-palette-color-7: #FFFFFF;
			--theme-palette-color-8: #FFFFFF;

			--theme-button-background-color: #D32F2F;
			--theme-button-hover-background-color: #B71C1C;
			--theme-link-initial-color: #D32F2F;
			--theme-link-hover-color: #B71C1C;
		}

		/* Botones principales de compra y finalizar pedido */
		.wc-proceed-to-checkout .checkout-button,
		#place_order,
		.single_add_to_cart_button,
		.woocommerce-form-login__submit,
		.ct-cart-content .checkout {
			background-color: #D32F2F !important;
			color: #ffffff !important;
			border-color: #D32F2F !important;
		}
		.wc-proceed-to-checkout .checkout-button:hover,
		#place_order:hover,
		.single_add_to_cart_button:hover,
		.woocommerce-form-login__submit:hover,
		.ct-cart-content .checkout:hover {
			background-color: #B71C1C !important;
			border-color: #B71C1C !important;
		}

		/* Boton de aplicar cupon y acciones secundarias en Verde Acebo Festivo */
		.woocommerce-cart-form .coupon button.button,
		.woocommerce-cart-form button[name="update_cart"] {
			background-color: #2E6F40 !important;
			color: #ffffff !important;
			border-color: #2E6F40 !important;
		}
		.woocommerce-cart-form .coupon button.button:hover,
		.woocommerce-cart-form button[name="update_cart"]:hover {
			background-color: #1E5631 !important;
			border-color: #1E5631 !important;
		}

		/* Insignias de ofertas */
		.onsale, [class*="ct-woo-badge-"] {
			background-color: #D32F2F !important;
			color: #ffffff !important;
		}
	</style>';
}, 99 );

