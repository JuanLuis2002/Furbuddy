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
 * MODULO NAVIDENO INTEGRAL: ESTILOS, TIPOGRAFIA Y MUSICA
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
	<!-- Google Font: Comfortaa -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;600;700&display=swap">

	<style id="furbuddy-navidad-styles">
		/* 1. Tipografia Global Comfortaa (Excluyendo Iconos de Estrellas) */
		body, h1, h2, h3, h4, h5, h6, .site-title, nav, .menu, button, input, select, textarea, .woocommerce-Price-amount, .entry-title, p, a, span:not(.star-rating):not(.star-rating *) {
			font-family: 'Comfortaa', cursive, sans-serif !important;
		}

		/* Restauracion de fuente de iconos para estrellas de valoracion (Adios letras S S S S S) */
		.woocommerce .star-rating,
		.woocommerce .star-rating span,
		.woocommerce ul.products li.product .star-rating,
		.star-rating,
		.star-rating span,
		.star-rating::before,
		.star-rating span::before {
			font-family: star !important;
			color: #FFB800 !important;
			letter-spacing: 2px !important;
		}
		.star-rating span::before {
			color: #FFB800 !important;
		}

		/* 2. Reemplazo Global de Naranjas por Paleta Navidena */
		:root {
			--theme-palette-color-1: #E50914 !important; /* Rojo Navideno (reemplaza cualquier naranja) */
			--theme-palette-color-2: #9E1B22 !important; /* Granate suave para hovers */
			--theme-palette-color-3: #17383e !important; /* Gris carbon / Texto */
			--theme-palette-color-4: #0d3820 !important; /* Verde Pino Profundo */
			--wp--preset--color--palette-color-1: #E50914 !important;
			--wp--preset--color--palette-color-2: #9E1B22 !important;
			--wp--preset--color--palette-color-4: #0d3820 !important;
			--theme-button-background-color: #E50914 !important;
			--theme-button-hover-background-color: #9E1B22 !important;
			--theme-link-initial-color: #E50914 !important;
			--theme-link-hover-color: #9E1B22 !important;
		}

		/* 3. Banner Hero Verde Pino Navideno */
		.stk-5cc302c,
		.stk-block-background.stk--has-background-overlay,
		.stk-block-columns.stk--has-background-overlay,
		#section-01, #section-02, #section-03,
		.ct-header-hero, .hero-section,
		[data-id*="hero"], [data-id*="banner"] {
			background: linear-gradient(135deg, #051d11 0%, #0d3820 40%, #155734 70%, #082817 100%) !important;
			background-size: 200% 200% !important;
			animation: furbuddyPineShimmer 10s ease infinite !important;
			position: relative !important;
		}

		@keyframes furbuddyPineShimmer {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}

		.stk-5cc302c:before,
		.stk-block-background.stk--has-background-overlay:before,
		#section-01::before, #section-02::before, #section-03::before {
			content: '' !important;
			position: absolute !important;
			top: 0; left: 0; right: 0; bottom: 0 !important;
			background: transparent !important;
			background-image: 
				radial-gradient(2px 2px at 20px 30px, #ffd700, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 40px 70px, #ffffff, rgba(0,0,0,0)),
				radial-gradient(1px 1px at 90px 40px, #ffe082, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 160px 120px, #ffffff, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 230px 60px, #ffd700, rgba(0,0,0,0)),
				radial-gradient(1.5px 1.5px at 310px 150px, #ffffff, rgba(0,0,0,0)) !important;
			background-repeat: repeat !important;
			background-size: 350px 200px !important;
			opacity: 0.45 !important;
			pointer-events: none !important;
			z-index: 1 !important;
		}

		/* 4. Banner "Sorprende a tu mascota" (Rojo Navideno Vibrante y Tipografia Blanca Nitida) */
		.stk-4754272,
		.stk-4754272.stk-block-background {
			background: linear-gradient(135deg, #E50914 0%, #b80710 100%) !important;
			background-color: #E50914 !important;
			border-radius: 24px !important;
			box-shadow: 0 14px 36px rgba(229, 9, 20, 0.22) !important;
		}
		.stk-4754272:before {
			background: transparent !important;
			background-color: transparent !important;
		}
		.stk-4754272 .stk-8d6006f .stk-block-heading__text,
		.stk-4754272 h6,
		.stk-4754272 .stk-block-heading__text {
			color: #ffffff !important;
			font-weight: 700 !important;
			text-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
		}
		.stk-4754272 .stk-0b75cce .stk-block-text__text,
		.stk-4754272 p {
			color: #fff8f8 !important;
			opacity: 0.95 !important;
		}
		.stk-4754272 .stk-f6cbe4c .stk--svg-wrapper {
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
			width: auto !important;
			height: auto !important;
			margin: 0 !important;
			border-radius: 0 !important;
		}
		.stk-4754272 .stk-f6cbe4c svg,
		.stk-4754272 .stk-f6cbe4c svg path {
			fill: #ffffff !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button {
			background: #ffffff !important;
			border-radius: 50px !important;
			box-shadow: 0 6px 18px rgba(0,0,0,0.15) !important;
			transition: all 0.3s ease !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button .stk-button__inner-text {
			color: #E50914 !important;
			font-weight: 800 !important;
			transition: all 0.3s ease !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button:hover {
			background: #1a1a1a !important;
			border-color: #FFB800 !important;
			transform: translateY(-2px) scale(1.04) !important;
			box-shadow: 0 8px 24px rgba(255, 184, 0, 0.45) !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button:hover .stk-button__inner-text {
			color: #FFB800 !important;
			text-shadow: 0 0 10px rgba(255, 184, 0, 0.6) !important;
		}

		/* 5. Tarjetas de Categorias (Juguetes, Ropa, Camas, Portadoras) - Iconos Vectoriales Modernos Premium */
		.stk-dcb23ef,
		.stk-dcb23ef-container,
		.stk-7154f94,
		.stk-7154f94-container,
		.stk-4652df5,
		.stk-4652df5-container,
		.stk-cf8053e,
		.stk-cf8053e-container,
		.wp-block-stackable-column.stk-block-column .stk-hover-parent {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-radius: 20px !important;
			border: 1.5px solid #edf2f7 !important;
			box-shadow: 0 6px 20px rgba(0,0,0,0.04) !important;
			padding: 32px 16px 26px 16px !important;
			transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
			position: relative !important;
			text-align: center !important;
		}

		.stk-dcb23ef:hover,
		.stk-dcb23ef-container:hover,
		.stk-7154f94:hover,
		.stk-7154f94-container:hover,
		.stk-4652df5:hover,
		.stk-4652df5-container:hover,
		.stk-cf8053e:hover,
		.stk-cf8053e-container:hover,
		.wp-block-stackable-column.stk-block-column .stk-hover-parent:hover {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-color: #FFB800 !important;
			box-shadow: 0 16px 36px rgba(255, 184, 0, 0.22) !important;
			transform: translateY(-6px) !important;
		}

		.stk-dcb23ef:before,
		.stk-dcb23ef-container:before,
		.stk-7154f94:before,
		.stk-7154f94-container:before,
		.stk-4652df5:before,
		.stk-4652df5-container:before,
		.stk-cf8053e:before,
		.stk-cf8053e-container:before,
		.stk-dcb23ef:hover:before,
		.stk-dcb23ef-container:hover:before,
		.stk-7154f94:hover:before,
		.stk-7154f94-container:hover:before,
		.stk-4652df5:hover:before,
		.stk-4652df5-container:hover:before,
		.stk-cf8053e:hover:before,
		.stk-cf8053e-container:hover:before {
			background: transparent !important;
			background-color: transparent !important;
		}

		/* Caja contenedora unificada: Centrado optico perfecto para los 4 iconos */
		.stk-dcb23ef .stk--svg-wrapper,
		.stk-dcb23ef-container .stk--svg-wrapper,
		.stk-7154f94 .stk--svg-wrapper,
		.stk-7154f94-container .stk--svg-wrapper,
		.stk-4652df5 .stk--svg-wrapper,
		.stk-4652df5-container .stk--svg-wrapper,
		.stk-cf8053e .stk--svg-wrapper,
		.stk-cf8053e-container .stk--svg-wrapper,
		.stk-dcb23ef .stk--inner-svg,
		.stk-dcb23ef-container .stk--inner-svg,
		.stk-7154f94 .stk--inner-svg,
		.stk-7154f94-container .stk--inner-svg,
		.stk-4652df5 .stk--inner-svg,
		.stk-4652df5-container .stk--inner-svg,
		.stk-cf8053e .stk--inner-svg,
		.stk-cf8053e-container .stk--inner-svg,
		.stk-block-column .stk--svg-wrapper,
		.stk-hover-parent .stk--svg-wrapper {
			height: 64px !important;
			min-height: 64px !important;
			max-height: 64px !important;
			width: 100% !important;
			margin: 0 auto 12px auto !important;
			border-radius: 0 !important;
			background: transparent !important;
			border: none !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: none !important;
		}

		/* Ocultar iconos antiguos de Stackable */
		.stk-dcb23ef svg.ugb-custom-icon,
		.stk-7154f94 svg.ugb-custom-icon,
		.stk-4652df5 svg.ugb-custom-icon,
		.stk-cf8053e svg.ugb-custom-icon,
		.stk-dcb23ef-container svg.ugb-custom-icon,
		.stk-7154f94-container svg.ugb-custom-icon,
		.stk-4652df5-container svg.ugb-custom-icon,
		.stk-cf8053e-container svg.ugb-custom-icon,
		.stk-7154f94 svg[viewBox="0 0 576 512"],
		.stk-7154f94-container svg[viewBox="0 0 576 512"] {
			display: none !important;
		}

		/* Estilos de los Nuevos Iconos Vectoriales Premium */
		.fb-cat-icon {
			width: 48px !important;
			height: 48px !important;
			max-width: 48px !important;
			max-height: 48px !important;
			display: block !important;
			margin: 0 auto !important;
			color: #E50914 !important;
			stroke: #E50914 !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
		}

		/* Hover sobre la tarjeta: el icono escala suavemente y cambia a Dorado Festivo #FFB800 */
		.stk-dcb23ef:hover .fb-cat-icon,
		.stk-dcb23ef-container:hover .fb-cat-icon,
		.stk-7154f94:hover .fb-cat-icon,
		.stk-7154f94-container:hover .fb-cat-icon,
		.stk-4652df5:hover .fb-cat-icon,
		.stk-4652df5-container:hover .fb-cat-icon,
		.stk-cf8053e:hover .fb-cat-icon,
		.stk-cf8053e-container:hover .fb-cat-icon {
			color: #FFB800 !important;
			stroke: #FFB800 !important;
			transform: scale(1.12) !important;
		}

		/* Textos de Categorias (Juguetes, Ropa, Camas, Portadoras) */
		.stk-dcb23ef .stk-block-heading__text,
		.stk-dcb23ef-container .stk-block-heading__text,
		.stk-7154f94 .stk-block-heading__text,
		.stk-7154f94-container .stk-block-heading__text,
		.stk-4652df5 .stk-block-heading__text,
		.stk-4652df5-container .stk-block-heading__text,
		.stk-cf8053e .stk-block-heading__text,
		.stk-cf8053e-container .stk-block-heading__text,
		.stk-block-column .stk-block-heading__text {
			color: #1e293b !important;
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			margin-top: 8px !important;
			transition: color 0.3s ease !important;
		}

		.stk-dcb23ef:hover .stk-block-heading__text,
		.stk-dcb23ef-container:hover .stk-block-heading__text,
		.stk-7154f94:hover .stk-block-heading__text,
		.stk-7154f94-container:hover .stk-block-heading__text,
		.stk-4652df5:hover .stk-block-heading__text,
		.stk-4652df5-container:hover .stk-block-heading__text,
		.stk-cf8053e:hover .stk-block-heading__text,
		.stk-cf8053e-container:hover .stk-block-heading__text,
		.stk-block-column:hover .stk-block-heading__text,
		.stk-hover-parent:hover .stk-block-heading__text {
			color: #FFB800 !important;
		}

		/* 6. Suavizado del tono Rojo en Botones Hover a Granate Elegante */
		button, input[type="submit"], .button, .btn, .ct-button,
		.woocommerce a.button, .woocommerce button.button, .stk-button {
			background-color: #E50914 !important;
			border-color: #E50914 !important;
			color: #ffffff !important;
			border-radius: 50px !important;
			transition: all 0.3s ease !important;
		}
		button:hover,
		input[type="submit"]:hover,
		.button:hover,
		.button.primary:hover,
		.button.accent:hover,
		.btn:hover,
		.ct-button:hover,
		a.button:hover,
		.woocommerce a.button:hover,
		.woocommerce button.button:hover,
		.stk-button:hover {
			background-color: #9E1B22 !important;
			border-color: #9E1B22 !important;
			color: #ffffff !important;
			box-shadow: 0 6px 18px rgba(158, 27, 34, 0.35) !important;
			transform: translateY(-2px) !important;
		}

		/* 7. Migas de pan (Breadcrumbs) en Preguntas Frecuentes y paginas secundarias */
		.entry-header .ct-breadcrumbs,
		.ct-breadcrumbs a,
		.ct-breadcrumbs span {
			color: #ffffff !important;
			opacity: 0.9 !important;
		}
		.ct-breadcrumbs a:hover {
			color: #ffd700 !important;
		}

		/* 8. Tarjetas de Productos en el Home y Tienda - Limpias, Proporcionales y Elegantes */
		html body [data-products] .product,
		html body ul.products li.product,
		html body .products .product {
			padding: 24px 18px 24px 18px !important;
			border-radius: 20px !important;
			background: #ffffff !important;
			border: 1.5px solid #edf2f7 !important;
			box-shadow: 0 4px 16px rgba(0,0,0,0.05) !important;
			transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
		}
		html body [data-products] .product:hover,
		html body ul.products li.product:hover,
		html body .products .product:hover {
			transform: translateY(-5px) !important;
			box-shadow: 0 14px 30px rgba(13, 56, 32, 0.12) !important;
			border-color: #155734 !important;
		}
		html body .products .product .woocommerce-loop-product__title {
			margin: 12px 0 6px 0 !important;
			font-size: 1.02rem !important;
			font-weight: 600 !important;
			color: #1e293b !important;
			text-align: center !important;
		}
		html body .products .product .price,
		html body .products .product .price * {
			color: #155734 !important;
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			text-align: center !important;
			margin-bottom: 12px !important;
		}
		html body .products .product .button.add_to_cart_button {
			border-radius: 50px !important;
			padding: 10px 22px !important;
			font-weight: 700 !important;
			background: #E50914 !important;
			color: #ffffff !important;
			border: none !important;
			text-transform: none !important;
			display: block !important;
			text-align: center !important;
			margin: 0 auto !important;
		}
		html body .products .product .button.add_to_cart_button:hover {
			background: #9E1B22 !important;
		}

		/* 7. Carrusel Stackable: Escaparate Interactivo y Decorativo Furbuddy */
		.wp-block-stackable-carousel,
		.stk-block-carousel {
			margin: 34px auto !important;
			max-width: 980px !important;
			width: 100% !important;
			position: relative !important;
		}

		/* Ocultar definitivamente el contador expirado en ceros y fondos toscos */
		.stk-block-countdown,
		.stk-0ba44b4,
		.stk-71d0377 .stk-block-countdown,
		.stk-71d0377 .stk-block-card,
		.stk-71d0377 .stk-block-card > .stk-container,
		.stk-ba4f5c1-container,
		.stk-5e7d40b-container {
			display: none !important;
		}

		/* Contenedor del Track del Carrusel */
		.fb-carousel-track {
			position: relative !important;
			width: 100% !important;
			min-height: 360px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
		}

		/* Tarjetas de Diapositiva: Acabado Verde Pino Festivo Sobrio y Elegante (Sin efectos artificiales) */
		.fb-carousel-slide {
			position: absolute !important;
			top: 0 !important;
			left: 0 !important;
			width: 100% !important;
			height: 100% !important;
			opacity: 0 !important;
			visibility: hidden !important;
			transform: scale(0.98) !important;
			transition: opacity 0.45s ease, transform 0.45s ease, visibility 0.45s !important;
			pointer-events: none !important;
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			text-align: center !important;
			padding: 40px 32px !important;
			border-radius: 24px !important;
			box-sizing: border-box !important;
			color: #ffffff !important;
			background: rgba(10, 38, 24, 0.78) !important;
			backdrop-filter: blur(14px) !important;
			-webkit-backdrop-filter: blur(14px) !important;
			border: 1.5px solid rgba(255, 184, 0, 0.28) !important;
			box-shadow: 0 16px 36px rgba(0, 0, 0, 0.3) !important;
			overflow: hidden !important;
		}

		.fb-carousel-slide.fb-active {
			position: relative !important;
			opacity: 1 !important;
			visibility: visible !important;
			transform: scale(1) !important;
			pointer-events: auto !important;
		}

		.fb-carousel-slide:hover {
			border-color: rgba(255, 184, 0, 0.5) !important;
			box-shadow: 0 20px 44px rgba(0, 0, 0, 0.38) !important;
			transform: translateY(-2px) !important;
		}

		/* Insignia / Badge Tematico Superior */
		.fb-carousel-badge {
			display: inline-block !important;
			padding: 6px 18px !important;
			border-radius: 50px !important;
			background: rgba(255, 184, 0, 0.18) !important;
			border: 1px solid #FFB800 !important;
			color: #FFB800 !important;
			font-size: 0.82rem !important;
			font-weight: 700 !important;
			letter-spacing: 0.06em !important;
			text-transform: uppercase !important;
			margin-bottom: 16px !important;
		}

		/* Titulo de la diapositiva */
		.fb-carousel-title {
			font-size: 1.85rem !important;
			font-weight: 800 !important;
			color: #ffffff !important;
			margin: 0 0 14px 0 !important;
			line-height: 1.3 !important;
			text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4) !important;
		}

		/* Descripcion clara y legible */
		.fb-carousel-desc {
			font-size: 1.05rem !important;
			line-height: 1.65 !important;
			color: #f1f5f9 !important;
			max-width: 620px !important;
			margin: 0 auto 24px auto !important;
		}

		/* Boton Principal de Accion e Hipervinculo */
		.fb-carousel-btn {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			background: #E50914 !important;
			color: #ffffff !important;
			font-weight: 700 !important;
			font-size: 0.98rem !important;
			padding: 13px 32px !important;
			border-radius: 50px !important;
			border: 1.5px solid #E50914 !important;
			text-decoration: none !important;
			box-shadow: 0 8px 24px rgba(229, 9, 20, 0.35) !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
			cursor: pointer !important;
		}

		.fb-carousel-btn:hover {
			background: #1a1a1a !important;
			color: #FFB800 !important;
			border-color: #FFB800 !important;
			box-shadow: 0 12px 28px rgba(255, 184, 0, 0.45) !important;
			transform: translateY(-2px) scale(1.03) !important;
		}

		/* Caja Interactiva de Cupon (Slide 3) */
		.fb-coupon-box {
			display: inline-flex !important;
			align-items: center !important;
			gap: 12px !important;
			background: rgba(0, 0, 0, 0.45) !important;
			border: 1.5px dashed #FFB800 !important;
			padding: 8px 18px !important;
			border-radius: 12px !important;
			margin-bottom: 22px !important;
			cursor: pointer !important;
			transition: all 0.25s ease !important;
		}

		.fb-coupon-box:hover {
			background: rgba(255, 184, 0, 0.15) !important;
			transform: scale(1.04) !important;
		}

		.fb-coupon-code {
			font-family: monospace, sans-serif !important;
			font-weight: 800 !important;
			font-size: 1.18rem !important;
			color: #FFB800 !important;
			letter-spacing: 0.12em !important;
		}

		.fb-coupon-tag {
			font-size: 0.78rem !important;
			background: #FFB800 !important;
			color: #1a1a1a !important;
			padding: 3px 9px !important;
			border-radius: 6px !important;
			font-weight: 700 !important;
			text-transform: uppercase !important;
		}

		/* Flechas de Navegacion del Carrusel: Estilizadas y Circulares */
		.stk-block-carousel__button {
			background: rgba(255, 255, 255, 0.18) !important;
			border: 1.5px solid rgba(255, 255, 255, 0.4) !important;
			border-radius: 50% !important;
			width: 44px !important;
			height: 44px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			color: #ffffff !important;
			backdrop-filter: blur(8px) !important;
			transition: all 0.3s ease !important;
			z-index: 10 !important;
		}

		.stk-block-carousel__button:hover {
			background: #FFB800 !important;
			border-color: #FFB800 !important;
			color: #1a1a1a !important;
			transform: scale(1.1) !important;
		}

		.stk-block-carousel__button svg {
			fill: currentColor !important;
			width: 16px !important;
			height: 16px !important;
		}

		/* Indicadores de Puntos (Dots) del Carrusel */
		.stk-block-carousel__dots {
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			gap: 8px !important;
			margin-top: 18px !important;
		}

		.fb-carousel-dot {
			width: 12px !important;
			height: 12px !important;
			border-radius: 50% !important;
			background: rgba(255, 255, 255, 0.35) !important;
			border: none !important;
			cursor: pointer !important;
			transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
			padding: 0 !important;
		}

		.fb-carousel-dot.fb-active {
			width: 28px !important;
			border-radius: 12px !important;
			background: #FFB800 !important;
			box-shadow: 0 0 12px rgba(255, 184, 0, 0.65) !important;
		}

		/* 8. Nieve Navidena Integrada (Blanco Nieve Puro y Destellos Dorados) */
		#furbuddy-snow-layer {
			position: fixed;
			top: 0; left: 0; width: 100%; height: 100%;
			pointer-events: none;
			z-index: 99998;
			overflow: hidden;
		}
		.furbuddy-flake {
			position: absolute;
			top: -20px;
			animation: furbuddyFall linear infinite;
			user-select: none;
			will-change: transform;
			text-shadow: 0 0 6px rgba(255, 215, 0, 0.45), 0 0 3px rgba(255, 255, 255, 0.9);
		}
		@keyframes furbuddyFall {
			0% { transform: translateY(-20px) rotate(0deg); opacity: 0; }
			15% { opacity: 0.95; }
			85% { opacity: 0.9; }
			100% { transform: translateY(105vh) rotate(360deg); opacity: 0; }
		}

		/* 9. Widget Flotante de Musica Navidena */
		#furbuddy-music-widget {
			position: fixed;
			bottom: 22px;
			left: 22px;
			background: linear-gradient(135deg, #155734 0%, #0d3820 100%);
			color: #ffffff;
			border: 2px solid #ffd700;
			padding: 9px 18px;
			border-radius: 30px;
			box-shadow: 0 8px 24px rgba(0,0,0,0.25);
			cursor: pointer;
			z-index: 99999;
			font-size: 13px;
			font-weight: 700;
			display: flex;
			align-items: center;
			gap: 8px;
			transition: all 0.3s ease;
			user-select: none;
		}
		#furbuddy-music-widget:hover {
			background: linear-gradient(135deg, #9E1B22 0%, #731217 100%);
			transform: scale(1.05);
			box-shadow: 0 10px 28px rgba(158, 27, 34, 0.4);
		}
		#furbuddy-music-widget.is-playing {
			border-color: #ffffff;
			animation: furbuddyPulse 2s infinite;
		}
		@keyframes furbuddyPulse {
			0%, 100% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.6); }
			50% { box-shadow: 0 0 0 10px rgba(255, 215, 0, 0); }
		}
	</style>
	<?php
}, 999 );

add_action( 'wp_footer', function() {
	?>
	<!-- Capa de Nieve Multitono de Alto Contraste -->
	<div id="furbuddy-snow-layer"></div>

	<!-- Widget Flotante de Musica Navidena Suave -->
	<div id="furbuddy-music-widget" onclick="toggleFurbuddyMusic()" title="Activar / Pausar Musica Navidena">
		<span>&#128276;</span>
		<span>M&uacute;sica Navide&ntilde;a</span>
		<span id="furbuddy-music-status">&#9654;</span>
	</div>

	<!-- Elemento de Audio Nativo con el MP3 Navideno Oficial -->
	<audio id="furbuddy-audio-player" preload="auto" loop style="display:none;">
		<source src="<?php echo esc_url( get_template_directory_uri() . '/furbuddy-navidad.mp3' ); ?>" type="audio/mpeg">
	</audio>

	<script id="furbuddy-navidad-scripts">
		// Generador de copos de nieve integrados (Blanco y Oro)
		(function() {
			var container = document.getElementById('furbuddy-snow-layer');
			if (!container) return;
			var flakeChars = ['\u2744', '\u2745', '\u2746', '\u2022', '\u2726', '\u2727'];
			var flakeColors = ['#ffffff', '#ffd700', '#fff9e6', '#fef08a', '#fffaed'];
			var flakeCount = 42;

			for (var i = 0; i < flakeCount; i++) {
				var f = document.createElement('span');
				f.className = 'furbuddy-flake';
				f.textContent = flakeChars[Math.floor(Math.random() * flakeChars.length)];
				f.style.left = (Math.random() * 100) + 'vw';
				f.style.fontSize = (11 + Math.random() * 19) + 'px';
				f.style.color = flakeColors[Math.floor(Math.random() * flakeColors.length)];
				f.style.animationDuration = (5.5 + Math.random() * 7) + 's';
				f.style.animationDelay = (Math.random() * 8) + 's';
				container.appendChild(f);
			}
		})();

		// Reproductor de Audio Navideno Furbuddy (MP3 Oficial)
		(function() {
			var audio = document.getElementById('furbuddy-audio-player');
			var status = document.getElementById('furbuddy-music-status');
			var container = document.getElementById('furbuddy-music-widget');
			var AMBIENT_VOLUME = 0.12; // Nivel ambiental suave, calido y nada invasivo (12%)

			if (audio) {
				audio.volume = AMBIENT_VOLUME;
			}

			window.toggleFurbuddyMusic = function() {
				if (!audio) {
					audio = document.getElementById('furbuddy-audio-player');
				}
				if (!audio) return;
				audio.volume = AMBIENT_VOLUME;

				if (!audio.paused) {
					audio.pause();
					if (status) status.textContent = '\u25B6';
					if (container) container.classList.remove('is-playing');
				} else {
					var playPromise = audio.play();
					if (playPromise !== undefined) {
						playPromise.then(function() {
							if (status) status.textContent = '\u23F8';
							if (container) container.classList.add('is-playing');
						}).catch(function(err) {
							console.log('Reproduccion de audio Furbuddy requiere interaccion:', err);
						});
					}
				}
			};

			if (audio) {
				audio.addEventListener('ended', function() {
					audio.currentTime = 0;
					audio.play();
				});
				audio.addEventListener('pause', function() {
					if (status) status.textContent = '\u25B6';
					if (container) container.classList.remove('is-playing');
				});
				audio.addEventListener('play', function() {
					if (status) status.textContent = '\u23F8';
					if (container) container.classList.add('is-playing');
				});
			}
		})();

		// Inyeccion del Escaparate Interactivo para el Carrusel (Fase 1 - 4 Diapositivas Reales)
		(function() {
			function initFurbuddyCarouselShowcase() {
				var carouselBlock = document.querySelector('.wp-block-stackable-carousel, .stk-block-carousel');
				if (!carouselBlock || carouselBlock.getAttribute('data-fb-ready') === 'true') return;

				var slider = carouselBlock.querySelector('.stk-block-carousel__slider');
				if (!slider) return;

				var slidesData = [
					{
						theme: 'fb-theme-recetas',
						badge: '100% Natural y Nutritivo',
						title: 'Premios y Recetas Caseras para Mascotas',
						desc: 'Consiente a tu mejor amigo con galletas y snacks nutritivos hechos en casa. Ingredientes sencillos, aprobados y saludables.',
						btnText: 'Ver Recetas Caseras',
						btnLink: 'https://furbuddy.xyz/recetas-caseras/'
					},
					{
						theme: 'fb-theme-moda',
						badge: 'Coleccion de Temporada',
						title: 'Moda, Abrigos y Disfraces para Mascotas',
						desc: 'Prendas suaves, confortables y abrigadas para paseos y momentos especiales en familia. La mejor proteccion contra el frio.',
						btnText: 'Explorar Coleccion',
						btnLink: 'https://furbuddy.xyz/product-category/disfraces/'
					},
					{
						theme: 'fb-theme-comunidad',
						badge: 'Comunidad Furbuddy',
						title: 'Unete a Nuestra Manada en Redes Sociales',
						desc: 'Siguenos en Instagram, comparte los mejores momentos de tu mascota y aprovecha un 10% de descuento en tu compra.',
						isCoupon: true,
						coupon: 'DIVERSION20',
						btnText: 'Seguir en Instagram',
						btnLink: 'https://instagram.com'
					},
					{
						theme: 'fb-theme-blog',
						badge: 'Guias de Bienestar',
						title: 'Estimulacion Mental y Cuidado Diario',
						desc: 'Aprende tecnicas sencillas para reducir el estres, evitar el aburrimiento y fortalecer la felicidad de tu mascota.',
						btnText: 'Leer Articulos del Blog',
						btnLink: 'https://furbuddy.xyz/blog/'
					}
				];

				var slidesHtml = slidesData.map(function(d, index) {
					var couponHtml = '';
					if (d.isCoupon) {
						couponHtml = '<div class="fb-coupon-box" onclick="navigator.clipboard.writeText(\'' + d.coupon + '\'); this.querySelector(\'.fb-coupon-tag\').textContent = \'Copiado!\'; setTimeout(function(el){ el.querySelector(\'.fb-coupon-tag\').textContent = \'Copiar\'; }.bind(null, this), 2000);">' +
							'<span class="fb-coupon-code">' + d.coupon + '</span>' +
							'<span class="fb-coupon-tag">Copiar</span>' +
							'</div>';
					}
					return '<div class="fb-carousel-slide ' + d.theme + (index === 0 ? ' fb-active' : '') + '">' +
						'<span class="fb-carousel-badge">' + d.badge + '</span>' +
						'<h3 class="fb-carousel-title">' + d.title + '</h3>' +
						'<p class="fb-carousel-desc">' + d.desc + '</p>' +
						couponHtml +
						'<a href="' + d.btnLink + '" class="fb-carousel-btn">' + d.btnText + '</a>' +
					'</div>';
				}).join('');

				slider.innerHTML = '<div class="fb-carousel-track">' + slidesHtml + '</div>';

				var dotsContainer = carouselBlock.querySelector('.stk-block-carousel__dots');
				if (dotsContainer) {
					dotsContainer.innerHTML = slidesData.map(function(_, i) {
						return '<button class="fb-carousel-dot' + (i === 0 ? ' fb-active' : '') + '" aria-label="Diapositiva ' + (i + 1) + '" data-slide="' + i + '"></button>';
					}).join('');
				}

				var currentSlide = 0;
				var totalSlides = slidesData.length;
				var slides = slider.querySelectorAll('.fb-carousel-slide');
				var dots = dotsContainer ? dotsContainer.querySelectorAll('.fb-carousel-dot') : [];
				var timer = null;

				function goToSlide(index) {
					currentSlide = (index + totalSlides) % totalSlides;
					for (var i = 0; i < slides.length; i++) {
						if (i === currentSlide) {
							slides[i].classList.add('fb-active');
						} else {
							slides[i].classList.remove('fb-active');
						}
					}
					for (var j = 0; j < dots.length; j++) {
						if (j === currentSlide) {
							dots[j].classList.add('fb-active');
						} else {
							dots[j].classList.remove('fb-active');
						}
					}
				}

				var prevBtn = carouselBlock.querySelector('.stk-block-carousel__button__prev');
				var nextBtn = carouselBlock.querySelector('.stk-block-carousel__button__next');

				if (prevBtn) {
					prevBtn.onclick = function(e) {
						e.preventDefault();
						e.stopPropagation();
						goToSlide(currentSlide - 1);
						resetAutoPlay();
					};
				}
				if (nextBtn) {
					nextBtn.onclick = function(e) {
						e.preventDefault();
						e.stopPropagation();
						goToSlide(currentSlide + 1);
						resetAutoPlay();
					};
				}

				if (dotsContainer) {
					dotsContainer.onclick = function(e) {
						var dot = e.target.closest('.fb-carousel-dot');
						if (dot) {
							var sIdx = parseInt(dot.getAttribute('data-slide'), 10);
							goToSlide(sIdx);
							resetAutoPlay();
						}
					};
				}

				function startAutoPlay() {
					timer = setInterval(function() {
						goToSlide(currentSlide + 1);
					}, 6000);
				}

				function resetAutoPlay() {
					clearInterval(timer);
					startAutoPlay();
				}

				carouselBlock.onmouseenter = function() { clearInterval(timer); };
				carouselBlock.onmouseleave = function() { startAutoPlay(); };

				startAutoPlay();
				carouselBlock.setAttribute('data-fb-ready', 'true');
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initFurbuddyCarouselShowcase);
			} else {
				initFurbuddyCarouselShowcase();
			}
		})();

		// Inyeccion de Iconos Vectoriales Modernos Premium para Categorias
		(function() {
			function injectModernCategoryIcons() {
				var icons = {
					'.stk-dcb23ef': '<svg class="fb-cat-icon fb-icon-juguetes" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16a4 4 0 0 0-4-4 4 4 0 0 0-4 4 4 4 0 0 0 4 4c-2.2 0-4 1.8-4 4s1.8 4 4 4a4 4 0 0 0 4-4h24a4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4c2.2 0 4-1.8 4-4s-1.8-4-4-4a4 4 0 0 0-4 4H12z"/><circle cx="24" cy="20" r="1.5" fill="currentColor"/></svg>',
					'.stk-7154f94': '<svg class="fb-cat-icon fb-icon-ropa" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 12c2 3 12 3 14 0"/><path d="M17 12l-7 8c-1 1-1 3 0 4l4 2 2-3v14c0 1.5 1.5 3 3 3h10c1.5 0 3-1.5 3-3V23l2 3 4-2c1-1 1-3 0-4l-7-8"/><path d="M20 28h8"/><path d="M20 33h8"/></svg>',
					'.stk-4652df5': '<svg class="fb-cat-icon fb-icon-camas" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 26c0-6.6 7.2-12 16-12s16 5.4 16 12-7.2 12-16 12-16-5.4-16-12z"/><path d="M13 27c0-4 4.9-7.5 11-7.5s11 3.5 11 7.5-4.9 7.5-11 7.5-11-3.5-11-7.5z"/><path d="M18 24c2 2 10 2 12 0"/></svg>',
					'.stk-cf8053e': '<svg class="fb-cat-icon fb-icon-portadoras" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14v-4c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v4"/><rect x="7" y="14" width="34" height="24" rx="6"/><circle cx="24" cy="26" r="6"/><path d="M21 26h6"/><path d="M24 23v6"/></svg>'
				};

				for (var sel in icons) {
					var cols = document.querySelectorAll(sel);
					for (var i = 0; i < cols.length; i++) {
						var col = cols[i];
						var wrapper = col.querySelector('.stk--svg-wrapper') || col.querySelector('.stk-block-icon');
						if (wrapper && !wrapper.querySelector('.fb-cat-icon')) {
							wrapper.innerHTML = icons[sel];
						}
					}
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', injectModernCategoryIcons);
			} else {
				injectModernCategoryIcons();
			}
		})();

	</script>
	<?php
}, 999 );

/**
 * =========================================================
 * FURBUDDY: ESTILO MODERNO PARA PREGUNTAS FRECUENTES Y RECETAS CASERAS
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
	<style id="furbuddy-faq-accordions">
		/* =========================================================
		   1. TITULOS ESTILO PILDORA MODERNA (FAQ Y RECETAS CASERAS)
		   ========================================================= */
		.stk-93afa3a,
		[data-block-id="93afa3a"],
		.stk-7eeaf3c,
		[data-block-id="7eeaf3c"],
		#heading-placeholder {
			text-align: center !important;
			margin-bottom: 32px !important;
		}

		.stk-93afa3a .stk-block-heading__text,
		[data-block-id="93afa3a"] .stk-block-heading__text,
		.stk-7eeaf3c .stk-block-heading__text,
		[data-block-id="7eeaf3c"] .stk-block-heading__text,
		#heading-placeholder .stk-block-heading__text {
			display: inline-block !important;
			color: #ffffff !important;
			font-size: 1.1rem !important;
			font-weight: 700 !important;
			letter-spacing: 2px !important;
			text-transform: uppercase !important;
			background: linear-gradient(135deg, #0d3820 0%, #155734 100%) !important;
			border: 1.5px solid rgba(255, 255, 255, 0.55) !important;
			border-radius: 50px !important;
			padding: 10px 34px !important;
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22), inset 0 0 12px rgba(255, 255, 255, 0.1) !important;
			text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
			backdrop-filter: blur(8px) !important;
			-webkit-backdrop-filter: blur(8px) !important;
			transition: all 0.3s ease !important;
		}

		.stk-93afa3a .stk-block-heading__text:hover,
		[data-block-id="93afa3a"] .stk-block-heading__text:hover,
		.stk-7eeaf3c .stk-block-heading__text:hover,
		[data-block-id="7eeaf3c"] .stk-block-heading__text:hover,
		#heading-placeholder .stk-block-heading__text:hover {
			border-color: #ffd700 !important;
			color: #ffd700 !important;
			box-shadow: 0 6px 20px rgba(255, 215, 0, 0.35) !important;
			transform: translateY(-2px) !important;
		}

		/* =========================================================
		   2. ELIMINACION TOTAL DE LA TARJETA EXTERIOR
		      (Para evitar a 100% que existan dos tarjetas)
		   ========================================================= */
		.stk-e839a25 .stk-block-accordion,
		.stk-block-accordion {
			background: transparent !important;
			background-color: transparent !important;
			border: none !important;
			box-shadow: none !important;
			border-radius: 0 !important;
			padding: 0 !important;
			margin: 0 0 16px 0 !important;
			overflow: visible !important;
		}

		/* =========================================================
		   3. LA TARJETA UNICA ES LA CABECERA (SUMMARY)
		      Acento Furbuddy verde, limpia y moderna
		   ========================================================= */
		.stk-block-accordion > summary,
		.stk-e839a25 .stk-block-accordion > summary {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-radius: 14px !important;
			border: none !important;
			border-left: 5px solid #0E8A42 !important;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
			padding: 16px 20px !important;
			cursor: pointer !important;
			list-style: none !important;
			outline: none !important;
			box-sizing: border-box !important;
			width: 100% !important;
			min-height: 58px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			transition: transform 0.25s ease, box-shadow 0.25s ease, border-left-color 0.25s ease !important;
			position: relative !important;
		}

		.stk-block-accordion > summary:hover,
		.stk-e839a25 .stk-block-accordion > summary:hover {
			transform: translateY(-2px) !important;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
			border-left-color: #0b7336 !important;
		}

		/* Al estar abierto, redondear solo arriba para conectar con la respuesta */
		.stk-block-accordion[open] > summary {
			border-radius: 14px 14px 0 0 !important;
			border-left-color: #0E8A42 !important;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
		}

		.stk-block-accordion > summary::-webkit-details-marker {
			display: none !important;
		}

		/* =========================================================
		   4. ANULAR CUALQUIER BORDE, FONDO O PADDING INTERNO
		      (Garantiza que no haya bordes amarillos ni cajas flotantes)
		   ========================================================= */
		.stk-block-accordion summary *,
		.stk-block-accordion summary *:before,
		.stk-block-accordion summary *:after,
		.stk-block-accordion summary .stk-column-wrapper,
		.stk-block-accordion summary .stk-container,
		.stk-block-accordion summary .stk-hover-parent,
		.stk-block-accordion summary .stk-block-content,
		.stk-block-accordion summary .stk-block-icon-label {
			background: transparent !important;
			background-color: transparent !important;
			border: none !important;
			border-color: transparent !important;
			box-shadow: none !important;
			border-radius: 0 !important;
			padding: 0 !important;
			margin: 0 !important;
			transform: none !important;
			outline: none !important;
		}

		.stk-block-accordion summary:hover *,
		.stk-block-accordion summary *:hover {
			background: transparent !important;
			border: none !important;
			border-color: transparent !important;
			box-shadow: none !important;
			transform: none !important;
		}

		/* =========================================================
		   5. ALINEACION VERTICAL PERFECTA (TEXTO Y FLECHA)
		   ========================================================= */
		.stk-block-accordion summary .stk-column-wrapper,
		.stk-block-accordion summary .stk-block-content,
		.stk-block-accordion summary .stk-block-icon-label,
		.stk-block-accordion summary .stk-row {
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}

		/* Titulo de la pregunta / receta: Siempre oscuro nitido (no amarillo) */
		.stk-block-accordion summary .stk-block-heading,
		.stk-block-accordion summary .stk-block-heading *,
		.stk-block-accordion summary .stk-block-heading__text,
		.stk-block-accordion summary h2,
		.stk-block-accordion summary h3,
		.stk-block-accordion summary h4,
		.stk-block-accordion summary span,
		.stk-block-accordion summary .stk-highlight {
			font-size: 0.98rem !important;
			font-weight: 700 !important;
			line-height: 1.4 !important;
			color: #14171A !important;
			margin: 0 !important;
			padding: 0 !important;
			text-align: left !important;
			text-decoration: none !important;
		}

		.stk-block-accordion summary .stk-block-heading {
			flex: 1 1 auto !important;
			display: flex !important;
			align-items: center !important;
		}

		/* =========================================================
		   6. FLECHA / CHEVRON: CIRCULO PERFECTO Y CENTRADO
		   ========================================================= */
		.stk-block-accordion summary .stk-block-icon,
		.stk-e839a25 summary .stk-block-icon {
			width: 34px !important;
			height: 34px !important;
			min-width: 34px !important;
			min-height: 34px !important;
			max-width: 34px !important;
			max-height: 34px !important;
			box-sizing: border-box !important;
			aspect-ratio: 1 / 1 !important;
			border-radius: 50% !important;
			background: #f0f4f6 !important;
			border: none !important;
			box-shadow: none !important;
			align-self: center !important;
			flex: 0 0 34px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			margin: 0 0 0 16px !important;
			padding: 0 !important;
			transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
		}

		.stk-block-accordion[open] summary .stk-block-icon {
			background: #f0f4f6 !important;
			transform: rotate(180deg) !important;
		}

		.stk-block-accordion summary .stk-block-icon .stk--svg-wrapper,
		.stk-block-accordion summary .stk-block-icon .stk--inner-svg {
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 100% !important;
			height: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
			border-radius: 0 !important;
		}

		.stk-block-accordion summary .stk-block-icon svg,
		.stk-block-accordion summary .stk-block-icon svg path,
		.stk-block-accordion[open] summary .stk-block-icon svg,
		.stk-block-accordion[open] summary .stk-block-icon svg path,
		.stk-block-accordion summary:hover .stk-block-icon svg,
		.stk-block-accordion summary:hover .stk-block-icon svg path {
			width: 12px !important;
			height: 12px !important;
			min-width: 12px !important;
			max-width: 12px !important;
			min-height: 12px !important;
			max-height: 12px !important;
			fill: #365b6c !important;
			color: #365b6c !important;
			display: block !important;
			margin: auto !important;
			transform: none !important;
			transition: none !important;
		}

		/* =========================================================
		   7. CONTENIDO DE LA RESPUESTA / RECETA AL DESPLEGAR
		   ========================================================= */
		.stk-block-accordion .stk-block-accordion__content,
		.stk-block-accordion > :not(summary) {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-radius: 0 0 14px 14px !important;
			border: none !important;
			border-left: 5px solid #0E8A42 !important;
			box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
			padding: 14px 22px 22px 22px !important;
			margin: 0 !important;
			display: block !important;
		}

		/* Textos dentro de la respuesta/receta: oscuros, legibles y nítidos */
		.stk-block-accordion .stk-block-accordion__content *,
		.stk-block-accordion .stk-block-accordion__content p,
		.stk-block-accordion .stk-block-accordion__content span,
		.stk-block-accordion .stk-block-accordion__content strong,
		.stk-block-accordion .stk-block-accordion__content li,
		.stk-block-accordion .stk-block-accordion__content .stk-highlight,
		.stk-block-accordion > :not(summary) *,
		.stk-block-accordion > :not(summary) p,
		.stk-block-accordion > :not(summary) span,
		.stk-block-accordion > :not(summary) strong,
		.stk-block-accordion > :not(summary) li,
		.stk-block-accordion > :not(summary) .stk-highlight {
			color: #334155 !important;
			font-size: 0.94rem !important;
			line-height: 1.65 !important;
			opacity: 1 !important;
			visibility: visible !important;
		}

		.stk-block-accordion .stk-block-accordion__content strong,
		.stk-block-accordion > :not(summary) strong {
			font-weight: 700 !important;
			color: #0f172a !important;
		}

		.stk-block-accordion .stk-block-accordion__content ul,
		.stk-block-accordion .stk-block-accordion__content ol,
		.stk-block-accordion > :not(summary) ul,
		.stk-block-accordion > :not(summary) ol {
			padding-left: 20px !important;
			margin: 10px 0 !important;
		}

		.stk-block-accordion .stk-block-accordion__content li,
		.stk-block-accordion > :not(summary) li {
			margin-bottom: 6px !important;
		}
	</style>
	<?php
}, 99999 );

/**
 * =========================================================
 * FURBUDDY: ESTILOS PERSONALIZADOS PARA POP-UP DE OFERTA (#pum-1414)
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
	<style id="furbuddy-popup-styles">
		/* Contenedor del Popup #pum-1414 */
		#pum-1414.pum-container,
		#pum-1414 .pum-container {
			width: 360px !important;
			max-width: calc(100vw - 32px) !important;
			padding: 24px 20px 22px !important;
			background: #ffffff !important;
			border-radius: 24px !important;
			border: 1px solid #e2e8f0 !important;
			box-shadow: 0 20px 45px -10px rgba(0, 71, 62, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
			overflow: hidden !important;
			position: relative !important;
			box-sizing: border-box !important;
		}

		/* Barra decorativa superior verde bosque */
		#pum-1414 .pum-container::before {
			content: "" !important;
			position: absolute !important;
			top: 0 !important;
			left: 0 !important;
			right: 0 !important;
			height: 5px !important;
			background: #00473e !important;
		}

		/* Botón de cierre redondeado */
		#pum-1414 .pum-close {
			top: 12px !important;
			right: 12px !important;
			width: 30px !important;
			height: 30px !important;
			background: #f1f5f9 !important;
			color: #64748b !important;
			border-radius: 50% !important;
			border: none !important;
			font-size: 14px !important;
			line-height: 30px !important;
			font-weight: 700 !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: none !important;
			text-shadow: none !important;
			transition: all 0.2s ease !important;
			cursor: pointer !important;
		}

		#pum-1414 .pum-close:hover {
			background: #00473e !important;
			color: #ffffff !important;
			transform: scale(1.08) !important;
		}

		/* Contenedor interno del popup */
		#pum-1414 .fb-popup-wrapper {
			text-align: center !important;
			box-sizing: border-box !important;
		}

		#pum-1414 .fb-badge {
			display: inline-block !important;
			font-size: 10px !important;
			font-weight: 800 !important;
			letter-spacing: 0.08em !important;
			text-transform: uppercase !important;
			color: #00473e !important;
			background: #ecfdf5 !important;
			border: 1px solid #a7f3d0 !important;
			padding: 3px 11px !important;
			border-radius: 50px !important;
			margin-bottom: 6px !important;
		}

		#pum-1414 .fb-title {
			font-size: 21px !important;
			font-weight: 800 !important;
			color: #00473e !important;
			line-height: 1.25 !important;
			margin: 4px 0 2px 0 !important;
			text-align: center !important;
			letter-spacing: -0.02em !important;
		}

		#pum-1414 .fb-subtitle {
			font-size: 12px !important;
			color: #64748b !important;
			text-align: center !important;
			margin: 0 0 14px 0 !important;
		}

		/* Caja de Beneficios */
		#pum-1414 .fb-benefits-box {
			background: #f8fafc !important;
			border: 1px solid #e2e8f0 !important;
			border-radius: 14px !important;
			padding: 12px 14px !important;
			margin-bottom: 13px !important;
			text-align: left !important;
		}

		#pum-1414 .fb-benefit-item {
			display: flex !important;
			align-items: flex-start !important;
			gap: 8px !important;
			font-size: 12.5px !important;
			line-height: 1.45 !important;
			color: #334155 !important;
			margin-bottom: 8px !important;
		}

		#pum-1414 .fb-benefit-item:last-child {
			margin-bottom: 0 !important;
		}

		#pum-1414 .fb-check {
			color: #059669 !important;
			font-weight: 800 !important;
			font-size: 13px !important;
			line-height: 1.3 !important;
			flex-shrink: 0 !important;
		}

		/* Caja de Cupón */
		#pum-1414 .fb-coupon-card {
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			background: #f0fdf4 !important;
			border: 1.5px dashed #34d399 !important;
			border-radius: 12px !important;
			padding: 9px 12px !important;
			margin-bottom: 6px !important;
		}

		#pum-1414 .fb-coupon-info {
			text-align: left !important;
		}

		#pum-1414 .fb-coupon-label {
			display: block !important;
			font-size: 10px !important;
			font-weight: 700 !important;
			text-transform: uppercase !important;
			color: #065f46 !important;
			letter-spacing: 0.05em !important;
		}

		#pum-1414 .fb-coupon-code {
			display: block !important;
			font-size: 17px !important;
			font-weight: 900 !important;
			font-family: monospace, monospace !important;
			color: #00473e !important;
			letter-spacing: 2px !important;
		}

		#pum-1414 .fb-coupon-copy {
			background: #00473e !important;
			color: #ffffff !important;
			border: none !important;
			border-radius: 8px !important;
			padding: 6px 14px !important;
			font-size: 12px !important;
			font-weight: 700 !important;
			cursor: pointer !important;
			transition: all 0.2s ease !important;
		}

		#pum-1414 .fb-coupon-copy:hover {
			background: #059669 !important;
		}

		#pum-1414 .fb-coupon-copy.copied {
			background: #10b981 !important;
		}

		#pum-1414 .fb-coupon-terms {
			font-size: 10.5px !important;
			color: #94a3b8 !important;
			text-align: center !important;
			font-style: italic !important;
			margin-bottom: 14px !important;
		}

		/* Botón CTA Principal */
		#pum-1414 .fb-cta-btn {
			display: block !important;
			width: 100% !important;
			background: #00473e !important;
			color: #ffffff !important;
			font-size: 14px !important;
			font-weight: 700 !important;
			text-align: center !important;
			text-decoration: none !important;
			padding: 13px 16px !important;
			border-radius: 12px !important;
			box-shadow: 0 6px 16px -2px rgba(0, 71, 62, 0.35) !important;
			transition: all 0.2s ease !important;
			box-sizing: border-box !important;
		}

		#pum-1414 .fb-cta-btn:hover {
			background: #065f46 !important;
			color: #ffffff !important;
			transform: translateY(-1px) !important;
			box-shadow: 0 10px 20px -2px rgba(0, 71, 62, 0.45) !important;
		}
	</style>
	<?php
}, 99999 );

