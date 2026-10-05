<?php
/**
 * Plugin Name: Furbuddy gato del inicio
 * Description: Sustituye la foto del gato de la portada por el gato animado. No cambia el texto ni el botón.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_head',
	static function () {
		if ( ! is_front_page() ) {
			return;
		}

		echo '<style id="furbuddy-hero-gato-critical">.wp-block-stackable-columns:has(#el-lugar-favorito-de-tus-mascotas) .wp-block-stackable-image img:not(.furbuddy-hero-cat):not(.furbuddy-hero-ball){visibility:hidden}</style>' . "\n";
	},
	1
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_front_page() ) {
			return;
		}

		$dir = __DIR__ . '/furbuddy-hero-gato';
		$url = plugin_dir_url( __FILE__ ) . 'furbuddy-hero-gato/';

		wp_enqueue_style(
			'furbuddy-hero-gato',
			$url . 'hero.css',
			array(),
			(string) filemtime( $dir . '/hero.css' )
		);

		wp_enqueue_script(
			'furbuddy-hero-gato-eyes',
			$url . 'eye-rig.js',
			array(),
			(string) filemtime( $dir . '/eye-rig.js' ),
			true
		);

		wp_enqueue_script(
			'furbuddy-hero-gato',
			$url . 'hero.js',
			array( 'furbuddy-hero-gato-eyes' ),
			(string) filemtime( $dir . '/hero.js' ),
			true
		);

		wp_localize_script(
			'furbuddy-hero-gato',
			'furbuddyHeroGato',
			array(
				'media' => $url . 'media/',
			)
		);
	}
);
