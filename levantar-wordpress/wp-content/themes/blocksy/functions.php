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

// 6. Paleta global navideña vibrante y ambiente festivo para toda la tienda
add_action( 'wp_head', function() {
	echo '<style id="furbuddy-navidad-paleta">
		:root {
			/* Rojo Navidad Radiante */
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

		/* 1. TODOS los botones principales de la tienda (WooCommerce + Blocksy) en Rojo Navidad */
		.wc-proceed-to-checkout .checkout-button,
		#place_order,
		.single_add_to_cart_button,
		.add_to_cart_button,
		.woocommerce-form-login__submit,
		.ct-cart-content .checkout,
		button.button.alt,
		.button[type="submit"]:not([name="apply_coupon"]):not([name="update_cart"]) {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border-color: #E50914 !important;
			font-weight: 700 !important;
			box-shadow: 0 4px 14px rgba(229, 9, 20, 0.35) !important;
			transition: all 0.2s ease-in-out !important;
		}
		.wc-proceed-to-checkout .checkout-button:hover,
		#place_order:hover,
		.single_add_to_cart_button:hover,
		.add_to_cart_button:hover,
		.woocommerce-form-login__submit:hover,
		.ct-cart-content .checkout:hover,
		button.button.alt:hover,
		.button[type="submit"]:not([name="apply_coupon"]):not([name="update_cart"]):hover {
			background-color: #B8000B !important;
			border-color: #B8000B !important;
			box-shadow: 0 6px 18px rgba(184, 0, 11, 0.45) !important;
		}

		/* 2. Botones de Stackable (Portada y secciones de inicio) */
		.stk-button,
		a.stk-button,
		.wp-block-stackable-button a,
		.stk-block-button .stk-button {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border-color: #E50914 !important;
		}
		.stk-button:before {
			border-color: #E50914 !important;
		}
		.stk-button .stk-button__inner-text,
		.stk-button span {
			color: #ffffff !important;
		}
		.stk-button:hover,
		a.stk-button:hover,
		.wp-block-stackable-button a:hover {
			background-color: #B8000B !important;
			border-color: #B8000B !important;
		}
		/* Iconos svg dentro de botones de portada */
		.stk-button .stk--inner-svg svg:last-child,
		.stk-button .stk--inner-svg svg:last-child * {
			fill: #FFB800 !important;
		}

		/* 3. Botones secundarios (Cupones, Actualizar carrito) en Verde Pino Navideño */
		.woocommerce-cart-form .coupon button.button,
		.woocommerce-cart-form button[name="update_cart"] {
			background-color: #0E8A42 !important;
			color: #ffffff !important;
			border-color: #0E8A42 !important;
			font-weight: 600 !important;
			box-shadow: 0 4px 12px rgba(14, 138, 66, 0.25) !important;
		}
		.woocommerce-cart-form .coupon button.button:hover,
		.woocommerce-cart-form button[name="update_cart"]:hover {
			background-color: #085F2C !important;
			border-color: #085F2C !important;
		}

		/* 4. Insignias de Oferta Navideña y Badges con Dorado */
		.onsale,
		[class*="ct-woo-badge-"] {
			background-color: #E50914 !important;
			color: #ffffff !important;
			font-weight: 700 !important;
			border: 1px solid #FFB800 !important;
			box-shadow: 0 2px 8px rgba(229, 9, 20, 0.4) !important;
		}

		/* 5. Precios y detalles festivos */
		.price,
		.amount,
		.woocommerce-Price-amount {
			color: #0E8A42 !important;
			font-weight: 700 !important;
		}
		.price del,
		.price del .amount {
			color: #8C98A4 !important;
			font-weight: normal !important;
		}

		/* 6. Carrito del Header y contador */
		.ct-header-cart .ct-cart-item-count {
			background-color: #E50914 !important;
			color: #ffffff !important;
		}

		/* 7. Nieve sutil y elegante en el fondo (Ambiente Navideño) */
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
			top: -10px;
			color: #ffffff;
			opacity: 0.85;
			user-select: none;
			font-size: 14px;
			filter: drop-shadow(0 0 3px rgba(14, 138, 66, 0.4));
			animation: furbuddy-fall linear infinite;
		}
		@keyframes furbuddy-fall {
			0% { transform: translateY(0) rotate(0deg); opacity: 0.9; }
			100% { transform: translateY(105vh) rotate(360deg); opacity: 0.2; }
		}
	</style>';
}, 99 );

// 7. Inyectar copos de nieve suaves en el footer (sin impacto de rendimiento)
add_action( 'wp_footer', function() {
	echo '<div class="furbuddy-snow-layer" aria-hidden="true">
		<span class="furbuddy-snowflake" style="left:5%;animation-duration:10s;animation-delay:0s;font-size:16px;">❄</span>
		<span class="furbuddy-snowflake" style="left:15%;animation-duration:14s;animation-delay:2s;font-size:12px;">❅</span>
		<span class="furbuddy-snowflake" style="left:25%;animation-duration:9s;animation-delay:4s;font-size:18px;">❄</span>
		<span class="furbuddy-snowflake" style="left:38%;animation-duration:12s;animation-delay:1s;font-size:14px;">•</span>
		<span class="furbuddy-snowflake" style="left:50%;animation-duration:11s;animation-delay:3s;font-size:20px;">❄</span>
		<span class="furbuddy-snowflake" style="left:62%;animation-duration:15s;animation-delay:5s;font-size:13px;">❅</span>
		<span class="furbuddy-snowflake" style="left:75%;animation-duration:10s;animation-delay:2s;font-size:17px;">❄</span>
		<span class="furbuddy-snowflake" style="left:88%;animation-duration:13s;animation-delay:0s;font-size:15px;">•</span>
		<span class="furbuddy-snowflake" style="left:95%;animation-duration:12s;animation-delay:4s;font-size:14px;">❄</span>
	</div>';
} );


