<?php
/**
 * Furbuddy - Modulo de Personalizacion Navidena Completa
 * Incluye: Logo horizontal en header, Favicon del gatito, Fuente Comfortaa,
 * Banner animado verde pino con estrellas y nieve, Iconos de categorias navidenos,
 * Espaciado estetico de productos, Carrusel centrado y Musica de fondo.
 */

require_once __DIR__ . '/furbuddy-navidad-assets.php';

// 1. Filtro para reemplazar el Logo nativo de WordPress / Blocksy
add_filter( 'get_custom_logo', function( $html ) {
	$logo = furbuddy_navidad_logo_b64();
	return sprintf(
		'<a href="%1$s" class="site-logo-container" rel="home"><img src="data:image/jpeg;base64,%2$s" class="default-logo furbuddy-navidad-logo" alt="Furbuddy Navideno" style="max-height: 70px; width: auto; object-fit: contain;" /></a>',
		esc_url( home_url( '/' ) ),
		$logo
	);
}, 999 );

add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size, $icon ) {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id && (int) $attachment_id === (int) $logo_id ) {
		return [
			'data:image/jpeg;base64,' . furbuddy_navidad_logo_b64(),
			300,
			80,
			false
		];
	}
	return $image;
}, 999, 4 );

// 2. Favicon Filters
add_filter( 'get_site_icon_url', function( $url ) {
	return 'data:image/png;base64,' . furbuddy_navidad_fav_b64();
}, 999 );

add_filter( 'site_icon_attachment_image', function( $html ) {
	return '<link rel="icon" href="data:image/png;base64,' . furbuddy_navidad_fav_b64() . '" />';
}, 999 );

// 3. Inyeccion de Head: Favicon tags, Logo Overrides, Tipografia Comfortaa y Estilos
add_action( 'wp_head', function() {
	$logo = furbuddy_navidad_logo_b64();
	$fav  = furbuddy_navidad_fav_b64();

	// Favicon links directos
	echo '<link rel="icon" type="image/png" href="data:image/png;base64,' . $fav . '" />' . "\n";
	echo '<link rel="shortcut icon" type="image/png" href="data:image/png;base64,' . $fav . '" />' . "\n";
	echo '<link rel="apple-touch-icon" href="data:image/png;base64,' . $fav . '" />' . "\n";

	// Script para forzar Logo y Favicon inmediatamente en el DOM
	echo '<script id="furbuddy-branding-script">
		(function(){
			var logoData = "data:image/jpeg;base64,' . $logo . '";
			var favData = "data:image/png;base64,' . $fav . '";
			function updateBranding() {
				// Actualizar logo en header
				var imgs = document.querySelectorAll(".site-logo-container img, [data-id=\"logo\"] img, .custom-logo-link img, .site-branding img, .site-header img[class*=\"logo\"]");
				imgs.forEach(function(img) {
					if (img.src !== logoData) {
						img.src = logoData;
						img.removeAttribute("srcset");
						img.removeAttribute("sizes");
					}
				});
				// Actualizar favicon en pestana del navegador
				var icons = document.querySelectorAll("link[rel*=\'icon\']");
				icons.forEach(function(l){ l.href = favData; });
				if (!icons.length) {
					var fl = document.createElement("link");
					fl.rel = "icon";
					fl.type = "image/png";
					fl.href = favData;
					document.head.appendChild(fl);
				}
			}
			if (document.readyState === "loading") {
				document.addEventListener("DOMContentLoaded", updateBranding);
			} else {
				updateBranding();
			}
			window.addEventListener("load", updateBranding);
		})();
	</script>' . "\n";

	// Estilos CSS
	echo '<style id="furbuddy-navidad-completo">
		@import url("https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600;700&display=swap");

		:root {
			--theme-palette-color-1: #E50914 !important;
			--theme-palette-color-2: #9E1B22 !important;
			--theme-palette-color-3: #14171A !important;
			--theme-palette-color-4: #2D3748 !important;
			--theme-palette-color-5: #E2E8F0 !important;
			--theme-palette-color-6: #F8FBFA !important;
			--theme-palette-color-7: #FFFFFF !important;
			--theme-palette-color-8: #FFFFFF !important;

			--theme-button-background-color: #E50914 !important;
			--theme-button-hover-background-color: #9E1B22 !important;
			--theme-link-initial-color: #E50914 !important;
			--theme-link-hover-color: #9E1B22 !important;
		}

		/* Tipografia Comfortaa */
		body, h1, h2, h3, h4, h5, h6,
		.ct-header, .menu-item a,
		.stk-button, a.stk-button, .button,
		.woocommerce-loop-product__title,
		.price, .amount, .entry-title, .shop_table,
		input, select, textarea, button {
			font-family: "Comfortaa", cursive, sans-serif !important;
		}

		/* Header y Logo */
		.site-logo-container img,
		[data-id="logo"] img,
		.custom-logo-link img,
		.site-branding img,
		.site-header img[class*="logo"] {
			content: url("data:image/jpeg;base64,' . $logo . '") !important;
			max-height: 70px !important;
			width: auto !important;
			object-fit: contain !important;
		}
		.ct-header-cart .ct-cart-item-count {
			background-color: #E50914 !important;
			color: #ffffff !important;
		}

		/* Migas de pan (Breadcrumbs) */
		.entry-breadcrumbs a, .ct-breadcrumbs a, .breadcrumb-trail a, [class*="breadcrumb"] a {
			color: #FFB800 !important;
			font-weight: 700 !important;
		}

		/* Seccion 01, 02, 03: Fondo Verde Pino Animado con Estrellas Doradas y Copos */
		@keyframes furbuddy-pine-shimmer {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}
		@keyframes furbuddy-sparkle-drift {
			0% { background-position: 0 0, 0 0, 0 0; }
			100% { background-position: 120px 120px, -60px 60px, 80px -80px; }
		}

		.stk-block-columns.stk--has-background-overlay,
		.stk-5cc302c,
		[data-block-id="5cc302c"] {
			background: linear-gradient(135deg, #051d11 0%, #0c4327 25%, #155734 50%, #0f4d2d 75%, #052113 100%) !important;
			background-size: 250% 250% !important;
			animation: furbuddy-pine-shimmer 12s ease infinite !important;
			border-radius: 24px !important;
			position: relative !important;
			overflow: hidden !important;
			box-shadow: 0 12px 36px rgba(6, 38, 23, 0.3) !important;
		}
		.stk-block-columns.stk--has-background-overlay::before,
		.stk-5cc302c::before,
		[data-block-id="5cc302c"]::before {
			content: "" !important;
			position: absolute !important;
			top: 0; left: 0; right: 0; bottom: 0 !important;
			background-image: 
				radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.25) 1.5px, transparent 2px),
				radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.16) 1.5px, transparent 2px),
				radial-gradient(circle at 80% 80%, rgba(255, 230, 150, 0.2) 2px, transparent 2.5px) !important;
			background-size: 40px 40px, 70px 70px, 100px 100px !important;
			animation: furbuddy-sparkle-drift 30s linear infinite !important;
			pointer-events: none !important;
			opacity: 0.9 !important;
		}
		.stk-block-columns h1,
		.stk-block-columns h2,
		.stk-block-columns [class*="heading"] {
			color: #FFB800 !important;
			text-shadow: 0 2px 10px rgba(0,0,0,0.45) !important;
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
			border-radius: 35px !important;
			padding: 12px 28px !important;
			box-shadow: 0 6px 18px rgba(229, 9, 20, 0.45) !important;
			transition: all 0.3s ease !important;
		}
		.stk-block-columns .stk-button:hover,
		.stk-block-columns a.stk-button:hover {
			background-color: #9E1B22 !important;
			border-color: #FFE082 !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 8px 22px rgba(255, 184, 0, 0.55) !important;
		}

		/* Color de Iconos de categorias del Home (Juguetes, Ropa, Camas, Portadoras) */
		.stk-img-wrapper svg path,
		.stk-img-wrapper svg circle,
		.stk-img-wrapper svg rect,
		.stk-img-wrapper svg polygon,
		.stk-img-wrapper svg polyline,
		.stk-block-image svg path,
		.stk-block-image svg circle,
		.stk-block-image svg rect,
		.stk-svg-wrapper svg path {
			stroke: #E50914 !important;
			transition: all 0.3s ease !important;
		}
		.stk-block-column:hover .stk-img-wrapper svg path,
		.stk-block-column:hover .stk-img-wrapper svg circle,
		.stk-block-column:hover .stk-img-wrapper svg rect,
		.stk-block-column:hover .stk-block-image svg path {
			stroke: #FFB800 !important;
			filter: drop-shadow(0 0 6px rgba(255, 184, 0, 0.6)) !important;
		}

		/* Botones Generales y Suavizado del Hover Rojo */
		.stk-block-button:first-child .stk-button,
		.stk-block-button a:not(.is-style-ghost),
		.stk-button:not(.is-style-ghost),
		.button:not(.is-style-ghost) {
			background-color: #E50914 !important;
			color: #ffffff !important;
			border: none !important;
			border-radius: 30px !important;
			box-shadow: 0 4px 12px rgba(229, 9, 20, 0.3) !important;
			transition: all 0.3s ease !important;
		}
		.stk-block-button:first-child .stk-button:hover,
		.stk-block-button a:not(.is-style-ghost):hover,
		.stk-button:not(.is-style-ghost):hover,
		.button:not(.is-style-ghost):hover {
			background-color: #9E1B22 !important;
			box-shadow: 0 6px 16px rgba(158, 27, 34, 0.45) !important;
		}
		.stk-block-button.is-style-ghost .stk-button,
		.stk-button.is-style-ghost,
		a.is-style-ghost {
			background-color: #ffffff !important;
			color: #0E8A42 !important;
			border: 2px solid #0E8A42 !important;
			border-radius: 30px !important;
			transition: all 0.3s ease !important;
		}
		.stk-block-button.is-style-ghost .stk-button:hover,
		.stk-button.is-style-ghost:hover,
		a.is-style-ghost:hover {
			background-color: #0E8A42 !important;
			color: #ffffff !important;
		}

		/* Botones WooCommerce (Carrito, Checkout, Anadir) */
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
			border-radius: 35px !important;
			box-shadow: 0 4px 14px rgba(229, 9, 20, 0.35) !important;
			transition: all 0.3s ease !important;
		}
		.wc-proceed-to-checkout .checkout-button:hover,
		#place_order:hover,
		.single_add_to_cart_button:hover,
		.add_to_cart_button:hover,
		.woocommerce-form-login__submit:hover,
		.ct-cart-content .checkout:hover {
			background-color: #9E1B22 !important;
			border-color: #9E1B22 !important;
			box-shadow: 0 6px 18px rgba(158, 27, 34, 0.45) !important;
		}

		/* Boton de cupon en Verde Pino */
		.woocommerce-cart-form .coupon button.button,
		.woocommerce-cart-form button[name="update_cart"] {
			background-color: #0E8A42 !important;
			color: #ffffff !important;
			border-color: #0E8A42 !important;
			border-radius: 30px !important;
			font-weight: 600 !important;
			transition: all 0.3s ease !important;
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
			border-radius: 12px !important;
		}

		/* Espaciado Generoso y Elegante en Tarjetas de Productos */
		ul.products li.product,
		.woocommerce ul.products li.product,
		.ct-products-grid li.product,
		.woocommerce-shop .product {
			padding: 26px 22px 28px 22px !important;
			border-radius: 22px !important;
			background: #ffffff !important;
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(229, 9, 20, 0.06) !important;
			transition: transform 0.35s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.35s ease, border-color 0.35s ease !important;
			margin-bottom: 34px !important;
			display: flex !important;
			flex-direction: column !important;
			justify-content: space-between !important;
			border: 1px solid rgba(0, 0, 0, 0.04) !important;
		}
		ul.products li.product:hover {
			transform: translateY(-6px) !important;
			box-shadow: 0 16px 36px rgba(12, 67, 39, 0.12), 0 0 0 1px rgba(255, 184, 0, 0.3) !important;
		}
		ul.products li.product .woocommerce-loop-product__link {
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			margin-bottom: 12px !important;
		}
		ul.products li.product .woocommerce-loop-product__link img {
			margin-bottom: 20px !important;
			border-radius: 16px !important;
			padding: 10px !important;
			max-height: 250px !important;
			object-fit: contain !important;
			transition: transform 0.35s ease !important;
		}
		ul.products li.product:hover .woocommerce-loop-product__link img {
			transform: scale(1.04) !important;
		}
		ul.products li.product .woocommerce-loop-product__title {
			font-size: 1.05rem !important;
			font-weight: 700 !important;
			line-height: 1.45 !important;
			margin-top: 12px !important;
			margin-bottom: 14px !important;
			min-height: 52px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			text-align: center !important;
			color: #14171A !important;
		}
		ul.products li.product .price {
			margin: 12px 0 14px 0 !important;
			font-size: 1.25rem !important;
			color: #0E8A42 !important;
			display: block !important;
			text-align: center !important;
			font-weight: 700 !important;
		}
		ul.products li.product .button {
			margin-top: 14px !important;
			width: 100% !important;
			padding: 13px 22px !important;
			border-radius: 35px !important;
			text-align: center !important;
			font-size: 0.95rem !important;
			font-weight: 700 !important;
			background-color: #E50914 !important;
			color: #ffffff !important;
			box-shadow: 0 4px 14px rgba(229, 9, 20, 0.3) !important;
		}
		ul.products li.product .button:hover {
			background-color: #9E1B22 !important;
			box-shadow: 0 6px 18px rgba(158, 27, 34, 0.45) !important;
		}

		/* Centrado del Carrusel Inferior */
		.stk-block-carousel,
		.stk-block-slider,
		.stk-carousel,
		.stk-block-carousel .stk-inner-blocks,
		[class*="carousel"],
		.stk-block-carousel .stk-block-content {
			text-align: center !important;
			align-items: center !important;
			justify-content: center !important;
		}
		.stk-block-carousel h1, .stk-block-carousel h2, .stk-block-carousel h3,
		.stk-block-carousel p, .stk-block-carousel .stk-block-text,
		.stk-block-carousel .stk-block-text__text,
		.stk-block-carousel .stk-button,
		.stk-block-carousel .stk-button-group,
		.stk-carousel [class*="heading"], .stk-carousel [class*="text"],
		.stk-carousel .stk-button-group {
			text-align: center !important;
			margin-left: auto !important;
			margin-right: auto !important;
			justify-content: center !important;
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
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

		/* Reproductor Flotante de Musica Navidena */
		.furbuddy-music-player {
			position: fixed;
			bottom: 24px;
			left: 24px;
			z-index: 9999999;
		}
		.furbuddy-music-player button {
			display: flex;
			align-items: center;
			gap: 10px;
			background: linear-gradient(135deg, #0C4327 0%, #062617 100%);
			color: #FFFFFF;
			border: 2px solid #FFB800;
			border-radius: 50px;
			padding: 11px 20px;
			font-family: "Comfortaa", cursive, sans-serif;
			font-size: 13px;
			font-weight: 700;
			cursor: pointer;
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3), 0 0 14px rgba(255, 184, 0, 0.45);
			transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
		}
		.furbuddy-music-player button:hover {
			transform: scale(1.06);
			border-color: #FFE082;
			box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4), 0 0 20px rgba(255, 184, 0, 0.7);
		}
		.furbuddy-music-player .music-icon {
			font-size: 16px;
			animation: furbuddy-bell-ring 2s ease infinite;
		}
		.furbuddy-music-player .music-state {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: #E50914;
			color: #FFFFFF;
			width: 24px;
			height: 24px;
			border-radius: 50%;
			font-size: 11px;
		}
		.furbuddy-music-player.is-playing .music-state {
			background: #FFB800;
			color: #062617;
		}
		@keyframes furbuddy-bell-ring {
			0%, 100% { transform: rotate(0deg); }
			20%, 60% { transform: rotate(12deg); }
			40%, 80% { transform: rotate(-12deg); }
		}
	</style>';
}, 99 );

// 4. Copos de Nieve, Reproductor de Musica y Script en el Footer
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

	echo '<div id="furbuddy-music-widget" class="furbuddy-music-player">
		<button id="furbuddy-music-toggle" onclick="toggleFurbuddyMusic()" aria-label="Reproducir musica navidena">
			<span class="music-icon">🔔</span>
			<span class="music-label">Música Navideña</span>
			<span class="music-state" id="furbuddy-music-status">▶</span>
		</button>
	</div>';

	echo '<script id="furbuddy-music-script">
		(function(){
			var audioCtx = null;
			var isPlaying = false;
			var timerId = null;
			var step = 0;

			var notes = [
				[659.25, 0.22, 0.08], [659.25, 0.22, 0.08], [659.25, 0.45, 0.15],
				[659.25, 0.22, 0.08], [659.25, 0.22, 0.08], [659.25, 0.45, 0.15],
				[659.25, 0.22, 0.08], [783.99, 0.22, 0.08], [523.25, 0.30, 0.08], [587.33, 0.18, 0.08],
				[659.25, 0.70, 0.20],
				[698.46, 0.22, 0.08], [698.46, 0.22, 0.08], [698.46, 0.35, 0.08], [698.46, 0.18, 0.08],
				[698.46, 0.22, 0.08], [659.25, 0.22, 0.08], [659.25, 0.22, 0.08], [659.25, 0.15, 0.08],
				[659.25, 0.20, 0.08], [587.33, 0.20, 0.08], [587.33, 0.20, 0.08], [659.25, 0.20, 0.08],
				[587.33, 0.45, 0.15], [783.99, 0.45, 0.25],
				[523.25, 0.28, 0.08],
				[698.46, 0.28, 0.08], [698.46, 0.15, 0.06], [783.99, 0.15, 0.06], [698.46, 0.15, 0.06], [659.25, 0.15, 0.06],
				[587.33, 0.28, 0.08], [587.33, 0.28, 0.08], [587.33, 0.28, 0.08],
				[783.99, 0.28, 0.08], [783.99, 0.15, 0.06], [880.00, 0.15, 0.06], [783.99, 0.15, 0.06], [698.46, 0.15, 0.06],
				[659.25, 0.28, 0.08], [523.25, 0.28, 0.08], [523.25, 0.28, 0.08],
				[880.00, 0.28, 0.08], [880.00, 0.15, 0.06], [987.77, 0.15, 0.06], [880.00, 0.15, 0.06], [783.99, 0.15, 0.06],
				[698.46, 0.28, 0.08], [587.33, 0.28, 0.08],
				[523.25, 0.18, 0.06], [523.25, 0.18, 0.06], [587.33, 0.28, 0.08], [783.99, 0.28, 0.08], [659.25, 0.28, 0.08],
				[698.46, 0.70, 0.50]
			];

			function playBell(freq, duration) {
				if (!audioCtx) {
					audioCtx = new (window.AudioContext || window.webkitAudioContext)();
				}
				if (audioCtx.state === 'suspended') {
					audioCtx.resume();
				}
				var now = audioCtx.currentTime;

				var osc1 = audioCtx.createOscillator();
				var gain1 = audioCtx.createGain();
				osc1.type = 'sine';
				osc1.frequency.setValueAtTime(freq, now);

				var osc2 = audioCtx.createOscillator();
				var gain2 = audioCtx.createGain();
				osc2.type = 'triangle';
				osc2.frequency.setValueAtTime(freq * 2.0, now);

				gain1.gain.setValueAtTime(0.001, now);
				gain1.gain.linearRampToValueAtTime(0.12, now + 0.02);
				gain1.gain.exponentialRampToValueAtTime(0.0001, now + duration + 0.2);

				gain2.gain.setValueAtTime(0.001, now);
				gain2.gain.linearRampToValueAtTime(0.03, now + 0.015);
				gain2.gain.exponentialRampToValueAtTime(0.0001, now + (duration * 0.6) + 0.1);

				osc1.connect(gain1);
				gain1.connect(audioCtx.destination);
				osc2.connect(gain2);
				gain2.connect(audioCtx.destination);

				osc1.start(now);
				osc2.start(now);
				osc1.stop(now + duration + 0.25);
				osc2.stop(now + duration + 0.25);
			}

			function playNextNote() {
				if (!isPlaying) return;
				var n = notes[step];
				playBell(n[0], n[1]);
				var waitMs = (n[1] + n[2]) * 1000;
				step = (step + 1) % notes.length;
				timerId = setTimeout(playNextNote, waitMs);
			}

			window.toggleFurbuddyMusic = function() {
				var status = document.getElementById('furbuddy-music-status');
				var container = document.getElementById('furbuddy-music-widget');
				if (isPlaying) {
					isPlaying = false;
					clearTimeout(timerId);
					if (status) status.textContent = '▶';
					if (container) container.classList.remove('is-playing');
				} else {
					isPlaying = true;
					if (status) status.textContent = '⏸';
					if (container) container.classList.add('is-playing');
					playNextNote();
				}
			};
		})();
	</script>';
} );
