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
ul.products li.product {
	background: #fff;
	border: 1px solid #efe8e1;
	border-radius: 20px;
	padding: 14px;
	box-shadow: 0 10px 28px rgba(55, 35, 20, 0.05);
	transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

ul.products li.product figure {
	margin: 0;
}

ul.products li.product a.ct-media-container {
	position: relative;
	display: block;
	width: 100%;
	aspect-ratio: 1 / 1;
	background: #fff;
	overflow: hidden;
	border-radius: 14px;
}

ul.products li.product a.ct-media-container img,
ul.products[data-hover="zoom-in"] > li.product:hover .has-hover-effect img,
ul.products[data-hover="zoom-out"] > li.product .has-hover-effect img {
	position: absolute !important;
	inset: 0;
	width: 100% !important;
	height: 100% !important;
	aspect-ratio: auto !important;
	object-fit: contain !important;
	object-position: center center !important;
	transform: none !important;
}

ul.products li.product .woocommerce-loop-product__title {
	margin-top: 14px;
	line-height: 1.35;
}

ul.products li.product .ct-woo-card-actions .button {
	border-radius: 12px;
	transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
}

@media (hover: hover) and (pointer: fine) {
	ul.products li.product:hover {
		transform: translateY(-8px);
		border-color: #e7d9cc;
		box-shadow: 0 18px 40px rgba(55, 35, 20, 0.12);
	}

	ul.products li.product:hover .ct-woo-card-actions .button {
		box-shadow: 0 8px 18px rgba(55, 35, 20, 0.12);
	}
}

@media (prefers-reduced-motion: reduce) {
	ul.products li.product,
	ul.products li.product .ct-woo-card-actions .button {
		transition: none;
	}

	ul.products li.product:hover {
		transform: none;
	}
}

.ct-posts-shortcode article.entry-card {
	display: flex;
	flex-direction: column;
	background: #fff;
	border: 1px solid #e4eef2;
	border-radius: 16px;
	overflow: hidden;
	box-shadow: 0 8px 22px rgba(24, 52, 68, 0.06);
	transition: box-shadow 0.35s ease, border-color 0.35s ease;
}

.ct-posts-shortcode article.entry-card .ct-media-container {
	position: relative;
	display: block;
	width: 100%;
	aspect-ratio: 16 / 10;
	overflow: hidden;
	border-radius: 0;
	background: #fff;
}

.ct-posts-shortcode article.entry-card .ct-media-container img,
.ct-posts-shortcode .entries[data-hover="zoom-in"] > article:hover .has-hover-effect img,
.ct-posts-shortcode .entries[data-hover="zoom-out"] > article .has-hover-effect img {
	position: absolute !important;
	inset: 0;
	width: 100% !important;
	height: 100% !important;
	aspect-ratio: auto !important;
	object-fit: cover !important;
	object-position: center center !important;
	transform: none !important;
}

.ct-posts-shortcode article.entry-card .entry-title {
	margin: 16px 16px 8px;
	line-height: 1.35;
}

.ct-posts-shortcode article.entry-card .entry-meta {
	margin: 0 16px 16px;
}

.ct-posts-shortcode article.entry-card .meta-date {
	color: #6b8490;
	font-size: 0.82rem;
}

@media (hover: hover) and (pointer: fine) {
	.ct-posts-shortcode article.entry-card:hover {
		border-color: #b7d0da;
		box-shadow: 0 14px 32px rgba(24, 52, 68, 0.12);
	}
}

@media (prefers-reduced-motion: reduce) {
	.ct-posts-shortcode article.entry-card {
		transition: none;
	}
}
CSS;

		wp_register_style( 'furbuddy-productos-imagen', false, array(), '4' );
		wp_enqueue_style( 'furbuddy-productos-imagen' );
		wp_add_inline_style( 'furbuddy-productos-imagen', $css );
	}
);
