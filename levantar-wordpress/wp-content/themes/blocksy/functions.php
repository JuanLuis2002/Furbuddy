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
		/* 1. Tipografia Global Comfortaa */
		body, h1, h2, h3, h4, h5, h6, .site-title, nav, .menu, button, input, select, textarea, .woocommerce, .woocommerce-Price-amount, .entry-title, p, a, span {
			font-family: 'Comfortaa', cursive, sans-serif !important;
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

		/* 4. Banner "Sorprende a tu mascota" (Adios Naranja -> Verde Pino Elegante) */
		.stk-4754272,
		.stk-4754272.stk-block-background {
			background: linear-gradient(135deg, #0d3820 0%, #155734 100%) !important;
			background-color: #0d3820 !important;
			border-radius: 24px !important;
			box-shadow: 0 12px 32px rgba(13, 56, 32, 0.18) !important;
		}
		.stk-4754272:before {
			background: transparent !important;
			background-color: transparent !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button {
			background: #ffffff !important;
			border-radius: 50px !important;
			transition: all 0.3s ease !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button .stk-button__inner-text {
			color: #0d3820 !important;
			font-weight: 700 !important;
		}
		.stk-4754272 .stk-2b904ee .stk-button:hover {
			background: #ffd700 !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 6px 18px rgba(0,0,0,0.15) !important;
		}

		/* 5. Tarjetas de Categorias (Juguetes, Ropa, Camas, Portadoras) - Modernas, Blancas y Sin Animaciones Bruscas */
		.stk-dcb23ef-container,
		.stk-7154f94-container,
		.stk-4652df5-container,
		.stk-cf8053e-container,
		.wp-block-stackable-column.stk-block-column .stk-hover-parent {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-radius: 20px !important;
			border: 1.5px solid #edf2f7 !important;
			box-shadow: 0 6px 20px rgba(0,0,0,0.04) !important;
			padding: 42px 20px 38px 20px !important;
			transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
			position: relative !important;
		}

		/* Anular el fondo naranja por completo en hover */
		.stk-dcb23ef-container:hover,
		.stk-7154f94-container:hover,
		.stk-4652df5-container:hover,
		.stk-cf8053e-container:hover,
		.wp-block-stackable-column.stk-block-column .stk-hover-parent:hover {
			background: #ffffff !important;
			background-color: #ffffff !important;
			border-color: #155734 !important;
			box-shadow: 0 14px 32px rgba(13, 56, 32, 0.12) !important;
			transform: translateY(-4px) !important;
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

		/* Iconos de Categorias - Limpios, alineados y sin rotaciones bruscas */
		.wp-block-stackable-icon svg,
		.wp-block-stackable-icon svg path,
		.stk-block-icon svg,
		.stk-block-icon svg path,
		.home .wp-block-columns .wp-block-column svg,
		.home .wp-block-columns .wp-block-column svg path {
			fill: #E50914 !important;
			transform: none !important;
			transition: fill 0.3s ease, transform 0.3s ease !important;
		}

		.stk-block-column:hover .wp-block-stackable-icon svg,
		.stk-block-column:hover .wp-block-stackable-icon svg path,
		.stk-hover-parent:hover .wp-block-stackable-icon svg,
		.stk-hover-parent:hover .wp-block-stackable-icon svg path {
			fill: #155734 !important; /* Verde pino elegante en hover */
			transform: translateY(-3px) scale(1.05) !important; /* Movimiento sutil y profesional sin rotar */
		}

		/* Textos de Categorias (Juguetes, Ropa, Camas, Portadoras) */
		.stk-block-column .stk-block-heading__text {
			color: #1e293b !important; /* Carbon oscuro legible y moderno */
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			margin-top: 14px !important;
			transition: color 0.3s ease !important;
		}
		.stk-block-column:hover .stk-block-heading__text,
		.stk-hover-parent:hover .stk-block-heading__text {
			color: #155734 !important;
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

		/* 8. Nieve Navidena Multitono de Alto Contraste */
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
			text-shadow: 0 0 5px rgba(0, 180, 216, 0.8), 0 0 10px rgba(255, 215, 0, 0.4);
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
		// Generador de copos de nieve con destello de alto contraste
		(function() {
			var container = document.getElementById('furbuddy-snow-layer');
			if (!container) return;
			var flakeChars = ['\u2744', '\u2745', '\u2746', '\u2022', '\u2726', '\u2727'];
			var flakeColors = ['#00b4d8', '#ffd700', '#ffffff', '#90e0ef', '#ffea79'];
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
 * FURBUDDY: ESTILO MODERNO PARA PREGUNTAS FRECUENTES (FAQ)
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
	<style id="furbuddy-faq-accordions">
		/* =========================================================
		   1. TITULO PREGUNTAS FRECUENTES ESTILO PILDORA (PORTADA)
		   ========================================================= */
		.stk-93afa3a,
		[data-block-id="93afa3a"] {
			text-align: center !important;
			margin-bottom: 30px !important;
		}

		.stk-93afa3a .stk-block-heading__text,
		[data-block-id="93afa3a"] .stk-block-heading__text {
			display: inline-block !important;
			color: #ffffff !important;
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			letter-spacing: 2px !important;
			text-transform: uppercase !important;
			background: rgba(0, 0, 0, 0.22) !important;
			border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
			border-radius: 50px !important;
			padding: 10px 32px !important;
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25), inset 0 0 12px rgba(255, 255, 255, 0.06) !important;
			text-shadow: 0 0 12px rgba(100, 181, 246, 0.45), 0 2px 6px rgba(0, 0, 0, 0.5) !important;
			backdrop-filter: blur(8px) !important;
			-webkit-backdrop-filter: blur(8px) !important;
			transition: all 0.3s ease !important;
		}

		.stk-93afa3a .stk-block-heading__text:hover,
		[data-block-id="93afa3a"] .stk-block-heading__text:hover {
			border-color: #ffd700 !important;
			color: #ffd700 !important;
			box-shadow: 0 6px 20px rgba(255, 215, 0, 0.3) !important;
			transform: translateY(-2px) !important;
		}

		/* =========================================================
		   2. TARJETA UNICA Y ELIMINACION DE TARJETA INTERNA ANIDADA
		   ========================================================= */
		/* Contenedor exterior: La UNICA tarjeta visible */
		.stk-e839a25 .stk-block-accordion,
		.stk-block-accordion {
			background: #ffffff !important;
			border-radius: 14px !important;
			margin-bottom: 14px !important;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
			border-left: 5px solid #0E8A42 !important;
			border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
			border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
			border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
			transition: transform 0.25s ease, box-shadow 0.25s ease, border-left-color 0.25s ease !important;
			overflow: hidden !important;
			padding: 0 !important;
		}

		/* Hover en la tarjeta */
		.stk-e839a25 .stk-block-accordion:hover,
		.stk-block-accordion:hover {
			transform: translateY(-3px) !important;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.14) !important;
			border-left-color: #0b7336 !important;
		}

		/* Tarjeta abierta */
		.stk-block-accordion[open] {
			border-left-color: #0E8A42 !important;
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12) !important;
		}

		/* ELIMINAR LA SEGUNDA TARJETA INTERNA: reset completo de contenedores hijos */
		.stk-block-accordion .stk-column-wrapper,
		.stk-block-accordion .stk-container,
		.stk-block-accordion .stk-block-column,
		.stk-block-accordion .stk-block-column__content,
		.stk-block-accordion .stk-hover-parent,
		.stk-block-accordion .stk-block-accordion__heading {
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
			border-radius: 0 !important;
			padding: 0 !important;
			margin: 0 !important;
		}

		/* 3. Cabecera (Summary): Altura uniforme y alineacion vertical limpia */
		.stk-block-accordion > summary,
		.stk-e839a25 .stk-block-accordion > summary {
			min-height: 68px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			padding: 14px 20px !important;
			cursor: pointer !important;
			background: transparent !important;
			list-style: none !important;
			border: none !important;
			box-shadow: none !important;
			outline: none !important;
		}

		.stk-block-accordion > summary::-webkit-details-marker {
			display: none !important;
		}

		.stk-block-accordion .stk-block-icon-label .stk-row {
			align-items: center !important;
			justify-content: space-between !important;
			width: 100% !important;
		}

		/* Tipografia de la pregunta */
		.stk-block-accordion .stk-block-heading__text,
		.stk-block-accordion .stk-highlight,
		.stk-block-accordion h3 {
			font-size: 0.98rem !important;
			font-weight: 700 !important;
			line-height: 1.35 !important;
			color: #14171A !important;
			margin: 0 !important;
		}

		/* 4. Icono / Flecha: Capsula circular con rotacion fluida y color neutro permanente */
		.stk-block-accordion .stk-block-icon {
			width: 32px !important;
			height: 32px !important;
			background: #f0f4f6 !important;
			border-radius: 50% !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			flex-shrink: 0 !important;
			margin-left: 12px !important;
			transition: transform 0.3s ease !important;
		}

		.stk-block-accordion[open] .stk-block-icon {
			background: #f0f4f6 !important;
			transform: rotate(180deg) !important;
		}

		.stk-block-accordion .stk-block-icon svg,
		.stk-block-accordion[open] .stk-block-icon svg {
			width: 12px !important;
			height: 12px !important;
			fill: #365b6c !important;
		}

		/* 5. Cuerpo de la respuesta al desplegar */
		.stk-block-accordion .stk-block-accordion__content {
			padding: 10px 22px 20px 22px !important;
			color: #4a5568 !important;
			font-size: 0.92rem !important;
			line-height: 1.55 !important;
			border-top: 1px solid #f0f0f0 !important;
			background: #ffffff !important;
		}</style>
	<?php
}, 999 );

