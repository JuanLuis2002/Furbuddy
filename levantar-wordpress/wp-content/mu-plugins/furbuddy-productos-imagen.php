<?php
/**
 * Plugin Name: Furbuddy fotos del catalogo
 * Description: Muestra las fotos de los productos completas y del mismo tamano.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'wp_get_attachment_image_src',
	static function ( $image, $attachment_id, $size ) {
		if ( 'woocommerce_archive_thumbnail' !== $size ) {
			return $image;
		}

		$completa = wp_get_attachment_image_src( $attachment_id, 'large' );
		if ( ! $completa ) {
			$completa = wp_get_attachment_image_src( $attachment_id, 'full' );
		}

		return $completa ? $completa : $image;
	},
	20,
	3
);

add_filter(
	'wp_get_attachment_image_attributes',
	static function ( $attr, $attachment, $size ) {
		if ( 'woocommerce_archive_thumbnail' === $size ) {
			unset( $attr['srcset'], $attr['sizes'] );
		}

		return $attr;
	},
	20,
	3
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$css = <<<'CSS'
ul.products li.product a.ct-media-container {
	position: relative;
	display: block;
	width: 100%;
	aspect-ratio: 1 / 1;
	background: #fff;
	overflow: hidden;
}

ul.products li.product a.ct-media-container img {
	position: absolute !important;
	inset: 0;
	width: 100% !important;
	height: 100% !important;
	aspect-ratio: auto !important;
	object-fit: contain !important;
	object-position: center center !important;
}
CSS;

		wp_register_style( 'furbuddy-productos-imagen', false, array(), '1' );
		wp_enqueue_style( 'furbuddy-productos-imagen' );
		wp_add_inline_style( 'furbuddy-productos-imagen', $css );
	}
);
