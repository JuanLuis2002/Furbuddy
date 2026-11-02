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
<<<<<<< HEAD
 * FURBUDDY: AJUSTE DE DISENO COMPACTO - ABOUT US (/about-us/)
=======
 * MODULO NAVIDENO INTEGRAL: ESTILOS, TIPOGRAFIA Y MUSICA
>>>>>>> 1cfa5ad3c98bc740a3179c8d8086ae0899ed3083
 * =========================================================
 */
add_action( 'wp_head', function() {
	?>
<<<<<<< HEAD
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
=======
	<!-- Google Font: Comfortaa -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;600;700&display=swap">

	<style id="furbuddy-navidad-styles">
		/* 1. Tipografia Global Comfortaa */
		body, h1, h2, h3, h4, h5, h6, .site-title, nav, .menu, button, input, select, textarea, .woocommerce, .woocommerce-Price-amount, .entry-title, p, a, span {
			font-family: 'Comfortaa', cursive, sans-serif !important;
		}

		/* 2. Banner Verde Pino Festivo Animado con destellos y estrellas */
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

		#section-01::before, #section-02::before, #section-03::before {
			content: '' !important;
			position: absolute !important;
			top: 0; left: 0; right: 0; bottom: 0 !important;
			background-image: 
				radial-gradient(2px 2px at 20px 30px, #ffd700, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 40px 70px, #ffffff, rgba(0,0,0,0)),
				radial-gradient(1px 1px at 90px 40px, #ffe082, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 160px 120px, #ffffff, rgba(0,0,0,0)),
				radial-gradient(2px 2px at 230px 60px, #ffd700, rgba(0,0,0,0)),
				radial-gradient(1.5px 1.5px at 310px 150px, #ffffff, rgba(0,0,0,0));
			background-repeat: repeat !important;
			background-size: 350px 200px !important;
			opacity: 0.35 !important;
			pointer-events: none !important;
			z-index: 1 !important;
		}

		/* 3. Iconos de Categorias (Juguetes, Ropa, Camas, Portadoras) */
		.home .wp-block-columns .wp-block-column img,
		.ct-category-card img,
		.product-category img {
			filter: invert(18%) sepia(85%) saturate(5400%) hue-rotate(350deg) brightness(90%) contrast(105%) !important;
			transition: all 0.35s ease !important;
		}
		.home .wp-block-columns .wp-block-column:hover img,
		.ct-category-card:hover img,
		.product-category:hover img {
			filter: invert(75%) sepia(80%) saturate(1200%) hue-rotate(5deg) brightness(105%) contrast(102%) !important;
			transform: scale(1.1) rotate(-3deg) !important;
		}

		/* 4. Suavizado del tono Rojo en Botones Hover a Granate Elegante */
		button:hover,
		input[type="submit"]:hover,
		.button:hover,
		.button.primary:hover,
		.button.accent:hover,
		.btn:hover,
		.ct-button:hover,
		a.button:hover,
		.woocommerce a.button:hover,
		.woocommerce button.button:hover {
			background-color: #9E1B22 !important;
			border-color: #9E1B22 !important;
			color: #ffffff !important;
			box-shadow: 0 6px 18px rgba(158, 27, 34, 0.35) !important;
			transition: all 0.3s ease !important;
		}

		/* 5. Migas de pan (Breadcrumbs) en Preguntas Frecuentes y paginas secundarias */
		.entry-header .ct-breadcrumbs,
		.ct-breadcrumbs a,
		.ct-breadcrumbs span {
			color: #ffffff !important;
			opacity: 0.9 !important;
		}
		.ct-breadcrumbs a:hover {
			color: #ffd700 !important;
		}

		/* 6. Tarjetas de Productos en el Home */
		.products .product,
		ul.products li.product {
			padding: 26px 22px 28px 22px !important;
			border-radius: 22px !important;
			background: #ffffff !important;
			border: 1px solid #f0f0f0 !important;
			box-shadow: 0 4px 18px rgba(0,0,0,0.04) !important;
			transition: transform 0.3s ease, box-shadow 0.3s ease !important;
		}
		.products .product:hover,
		ul.products li.product:hover {
			transform: translateY(-6px) !important;
			box-shadow: 0 12px 28px rgba(21, 87, 52, 0.12) !important;
			border-color: rgba(21, 87, 52, 0.2) !important;
		}
		.products .product .woocommerce-loop-product__title {
			margin: 14px 0 8px 0 !important;
			font-size: 1.05rem !important;
			font-weight: 600 !important;
			color: #1e293b !important;
			text-align: center !important;
		}
		.products .product .price {
			color: #155734 !important;
			font-size: 1.15rem !important;
			font-weight: 700 !important;
			text-align: center !important;
			margin-bottom: 14px !important;
		}
		.products .product .button.add_to_cart_button {
			border-radius: 50px !important;
			padding: 10px 20px !important;
			font-weight: 600 !important;
			background: #155734 !important;
			color: #ffffff !important;
			text-transform: none !important;
			display: block !important;
			text-align: center !important;
			margin: 0 auto !important;
		}
		.products .product .button.add_to_cart_button:hover {
			background: #9E1B22 !important;
		}

		/* 7. Carrusel Final centrado */
		.ct-carousel .entry-content,
		.ct-carousel .content-container,
		[data-id*="carousel"] {
			text-align: center !important;
		}
		.ct-carousel h2, .ct-carousel p, .ct-carousel .ct-button {
			text-align: center !important;
			margin-left: auto !important;
			margin-right: auto !important;
		}

		/* 8. Nieve Navidena Multitono */
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
		}
		@keyframes furbuddyFall {
			0% { transform: translateY(-20px) rotate(0deg); opacity: 0; }
			10% { opacity: 0.9; }
			90% { opacity: 0.8; }
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
>>>>>>> 1cfa5ad3c98bc740a3179c8d8086ae0899ed3083
		}
	</style>
	<?php
}, 999 );

<<<<<<< HEAD
=======
add_action( 'wp_footer', function() {
	?>
	<!-- Capa de Nieve Multitono -->
	<div id="furbuddy-snow-layer"></div>

	<!-- Widget de Musica Navidena -->
	<div id="furbuddy-music-widget" onclick="toggleFurbuddyMusic()" title="Activar / Pausar Musica Navidena">
		<span>&#128276;</span>
		<span>M&uacute;sica Navide&ntilde;a</span>
		<span id="furbuddy-music-status">&#9654;</span>
	</div>

	<script id="furbuddy-navidad-scripts">
		// Generador de copos de nieve
		(function() {
			var container = document.getElementById('furbuddy-snow-layer');
			if (!container) return;
			var flakeChars = ['\u2744', '\u2745', '\u2746', '\u2022', '\u2726'];
			var flakeColors = ['#d0f0fd', '#ffffff', '#ffe082', '#e1f5fe'];
			var flakeCount = 38;

			for (var i = 0; i < flakeCount; i++) {
				var f = document.createElement('span');
				f.className = 'furbuddy-flake';
				f.textContent = flakeChars[Math.floor(Math.random() * flakeChars.length)];
				f.style.left = (Math.random() * 100) + 'vw';
				f.style.fontSize = (10 + Math.random() * 18) + 'px';
				f.style.color = flakeColors[Math.floor(Math.random() * flakeColors.length)];
				f.style.animationDuration = (5 + Math.random() * 7) + 's';
				f.style.animationDelay = (Math.random() * 8) + 's';
				container.appendChild(f);
			}
		})();

		// Sintetizador Web Audio de Campanas Navidenas
		(function() {
			var audioCtx = null;
			var isPlaying = false;
			var timerId = null;
			var step = 0;

			var notes = [
				[329.63, 0.28, 0.12], [329.63, 0.28, 0.12], [329.63, 0.58, 0.22],
				[329.63, 0.28, 0.12], [329.63, 0.28, 0.12], [329.63, 0.58, 0.22],
				[329.63, 0.28, 0.12], [392.00, 0.28, 0.12], [261.63, 0.42, 0.12], [293.66, 0.28, 0.12], [329.63, 0.85, 0.45],
				[349.23, 0.28, 0.12], [349.23, 0.28, 0.12], [349.23, 0.42, 0.12], [349.23, 0.28, 0.12],
				[349.23, 0.28, 0.12], [329.63, 0.28, 0.12], [329.63, 0.28, 0.12],
				[392.00, 0.28, 0.12], [349.23, 0.28, 0.12], [293.66, 0.28, 0.12], [261.63, 0.85, 0.80]
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

			function playBell(freq, duration) {
				var ctx = getAudioContext();
				if (!ctx) return;
				var now = ctx.currentTime;
				var osc = ctx.createOscillator();
				var gain = ctx.createGain();

				osc.type = 'sine';
				osc.frequency.setValueAtTime(freq, now);

				gain.gain.setValueAtTime(0.001, now);
				gain.gain.exponentialRampToValueAtTime(0.09, now + 0.02);
				gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);

				osc.connect(gain);
				gain.connect(ctx.destination);

				osc.start(now);
				osc.stop(now + duration + 0.05);
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
>>>>>>> 1cfa5ad3c98bc740a3179c8d8086ae0899ed3083
