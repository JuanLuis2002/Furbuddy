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

		/* 5. Tarjetas de Categorias (Juguetes, Ropa, Camas, Portadoras) - Modernas, Limpias e Iconos Siempre Visibles */
		.stk-dcb23ef-container,
		.stk-7154f94-container,
		.stk-4652df5-container,
		.stk-cf8053e-container {
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

		.stk-dcb23ef-container:hover,
		.stk-7154f94-container:hover,
		.stk-4652df5-container:hover,
		.stk-cf8053e-container:hover {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-color: #FFB800 !important;
			box-shadow: 0 14px 32px rgba(255, 184, 0, 0.18) !important;
			transform: translateY(-5px) !important;
		}

		.stk-dcb23ef-container:before,
		.stk-7154f94-container:before,
		.stk-4652df5-container:before,
		.stk-cf8053e-container:before,
		.stk-dcb23ef-container:hover:before,
		.stk-7154f94-container:hover:before,
		.stk-4652df5-container:hover:before,
		.stk-cf8053e-container:hover:before {
			background: transparent !important;
			background-color: transparent !important;
		}

		/* Sin envoltorios circulares que deformen o reduzcan los iconos de categorias */
		.stk-dcb23ef .stk--svg-wrapper,
		.stk-7154f94 .stk--svg-wrapper,
		.stk-4652df5 .stk--svg-wrapper,
		.stk-cf8053e .stk--svg-wrapper {
			width: auto !important;
			height: auto !important;
			margin: 0 auto 12px auto !important;
			border-radius: 0 !important;
			background: transparent !important;
			border: none !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: none !important;
		}

		.stk-dcb23ef:hover .stk--svg-wrapper,
		.stk-7154f94:hover .stk--svg-wrapper,
		.stk-4652df5:hover .stk--svg-wrapper,
		.stk-cf8053e:hover .stk--svg-wrapper {
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
		}

		/* Asegurar que los iconos de categorias sean rojos y dorados en hover */
		.stk-dcb23ef svg,
		.stk-dcb23ef svg path,
		.stk-7154f94 svg,
		.stk-7154f94 svg path,
		.stk-4652df5 svg,
		.stk-4652df5 svg path,
		.stk-cf8053e svg,
		.stk-cf8053e svg path {
			fill: #E50914 !important;
			width: 52px !important;
			height: 52px !important;
			max-width: 52px !important;
			max-height: 52px !important;
			transition: all 0.3s ease !important;
		}

		.stk-dcb23ef:hover svg,
		.stk-dcb23ef:hover svg path,
		.stk-7154f94:hover svg,
		.stk-7154f94:hover svg path,
		.stk-4652df5:hover svg,
		.stk-4652df5:hover svg path,
		.stk-cf8053e:hover svg,
		.stk-cf8053e:hover svg path {
			fill: #FFB800 !important;
			transform: scale(1.08) !important;
		}

		/* Textos de Categorias (Juguetes, Ropa, Camas, Portadoras) */
		.stk-dcb23ef .stk-block-heading__text,
		.stk-7154f94 .stk-block-heading__text,
		.stk-4652df5 .stk-block-heading__text,
		.stk-cf8053e .stk-block-heading__text {
			color: #1e293b !important;
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			margin-top: 8px !important;
			transition: color 0.3s ease !important;
		}

		.stk-dcb23ef:hover .stk-block-heading__text,
		.stk-7154f94:hover .stk-block-heading__text,
		.stk-4652df5:hover .stk-block-heading__text,
		.stk-cf8053e:hover .stk-block-heading__text {
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

		/* 7. Carrusel Stackable Centrado */
		.wp-block-stackable-carousel .stk-block-card__content,
		.wp-block-stackable-carousel .stk-block-content,
		.wp-block-stackable-carousel .stk-block-heading,
		.wp-block-stackable-carousel .stk-block-text,
		.wp-block-stackable-carousel .stk-button-group,
		.ct-carousel .entry-content,
		.ct-carousel .content-container,
		[data-id*="carousel"] {
			text-align: center !important;
			justify-content: center !important;
			margin-left: auto !important;
			margin-right: auto !important;
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
	<div id="furbuddy-music-widget" onclick="toggleFurbuddyMusic()" title="Activar / Pausar Musica Navidena Suave">
		<span>&#128276;</span>
		<span>M&uacute;sica Navide&ntilde;a</span>
		<span id="furbuddy-music-status">&#9654;</span>
	</div>

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

		// Caja de Musica Navidena Suave (Sweet Celesta / Music Box)
		(function() {
			var audioCtx = null;
			var isPlaying = false;
			var timerId = null;
			var step = 0;

			// Melodia suave armonizada de Jingle Bells & Noche de Paz
			var notes = [
				[659.25, 0.22, 0.10], [659.25, 0.22, 0.10], [659.25, 0.44, 0.20],
				[659.25, 0.22, 0.10], [659.25, 0.22, 0.10], [659.25, 0.44, 0.20],
				[659.25, 0.22, 0.10], [783.99, 0.22, 0.10], [523.25, 0.32, 0.10], [587.33, 0.20, 0.10],
				[659.25, 0.70, 0.35],
				[698.46, 0.22, 0.10], [698.46, 0.22, 0.10], [698.46, 0.35, 0.10], [698.46, 0.20, 0.10],
				[698.46, 0.22, 0.10], [659.25, 0.22, 0.10], [659.25, 0.22, 0.10], [659.25, 0.18, 0.10],
				[659.25, 0.22, 0.10], [587.33, 0.22, 0.10], [587.33, 0.22, 0.10], [659.25, 0.22, 0.10],
				[587.33, 0.45, 0.20], [783.99, 0.45, 0.35]
			];

			function getAudioContext() {
				if (!audioCtx) {
					var AudioContextClass = window.AudioContext || window.webkitAudioContext;
					if (AudioContextClass) {
						audioCtx = new AudioContextClass();
					}
				}
				if (audioCtx && audioCtx.state === 'suspended') {
					audioCtx.resume();
				}
				return audioCtx;
			}

			function playCelestaNote(freq, duration) {
				var ctx = getAudioContext();
				if (!ctx) return;
				var now = ctx.currentTime;

				// Oscilador 1: Tono fundamental suave (sine)
				var osc1 = ctx.createOscillator();
				var gain1 = ctx.createGain();
				osc1.type = 'sine';
				osc1.frequency.setValueAtTime(freq, now);

				// Oscilador 2: Armonico superior de campanilla de cristal (sine afinado a la octava superior)
				var osc2 = ctx.createOscillator();
				var gain2 = ctx.createGain();
				osc2.type = 'sine';
				osc2.frequency.setValueAtTime(freq * 2.0, now);

				// Volumen muy sutil y suave (estilo caja de musica de salon)
				gain1.gain.setValueAtTime(0.0001, now);
				gain1.gain.linearRampToValueAtTime(0.045, now + 0.015);
				gain1.gain.exponentialRampToValueAtTime(0.0001, now + duration + 0.15);

				gain2.gain.setValueAtTime(0.0001, now);
				gain2.gain.linearRampToValueAtTime(0.012, now + 0.01);
				gain2.gain.exponentialRampToValueAtTime(0.0001, now + (duration * 0.7) + 0.05);

				osc1.connect(gain1);
				gain1.connect(ctx.destination);
				osc2.connect(gain2);
				gain2.connect(ctx.destination);

				osc1.start(now);
				osc2.start(now);
				osc1.stop(now + duration + 0.2);
				osc2.stop(now + duration + 0.2);
			}

			function playNextNote() {
				if (!isPlaying) return;
				var n = notes[step];
				playCelestaNote(n[0], n[1]);
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
					if (status) status.textContent = '\u25B6';
					if (container) container.classList.remove('is-playing');
				} else {
					isPlaying = true;
					if (status) status.textContent = '\u23F8';
					if (container) container.classList.add('is-playing');
					playNextNote();
				}
			};
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
