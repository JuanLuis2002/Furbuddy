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

@keyframes furbuddy-entrar {
	from {
		opacity: 0;
		transform: translateY(22px);
	}
	to {
		opacity: 1;
		transform: none;
	}
}

article.entry-card {
	display: flex;
	flex-direction: column;
	background: #fff;
	border: 1px solid #e4eef2;
	border-radius: 16px;
	overflow: hidden;
	box-shadow: 0 8px 22px rgba(24, 52, 68, 0.06);
	transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
	animation: furbuddy-entrar 0.7s ease both;
}

article.entry-card:nth-child(2) {
	animation-delay: 0.08s;
}

article.entry-card:nth-child(3) {
	animation-delay: 0.16s;
}

article.entry-card:nth-child(4) {
	animation-delay: 0.24s;
}

article.entry-card .ct-media-container {
	position: relative;
	display: block;
	width: 100%;
	aspect-ratio: 16 / 10;
	overflow: hidden;
	border-radius: 0;
	background: #fff;
}

article.entry-card .ct-media-container img,
.entries[data-hover="zoom-in"] > article:hover .has-hover-effect img,
.entries[data-hover="zoom-out"] > article .has-hover-effect img {
	position: absolute !important;
	inset: 0;
	width: 100% !important;
	height: 100% !important;
	aspect-ratio: auto !important;
	object-fit: contain !important;
	object-position: center center !important;
	transform: none !important;
}

article.entry-card .entry-title {
	margin: 16px 16px 8px;
	line-height: 1.35;
}

article.entry-card .entry-meta {
	margin: 0 16px 16px;
}

article.entry-card .meta-date {
	color: #6b8490;
	font-size: 0.82rem;
}

.stk-71d0377 {
	padding-block: 48px 56px;
}

.stk-71d0377 .stk-948b4bd {
	text-align: center;
}

.stk-71d0377 .stk-948b4bd .stk-block-text__text {
	display: inline-block;
	margin: 0;
	padding: 6px 14px;
	border: 1px solid rgba(255, 255, 255, 0.35);
	border-radius: 999px;
	background: rgba(255, 255, 255, 0.14);
	letter-spacing: 0.08em;
	text-transform: uppercase;
	font-size: 12px;
}

.stk-71d0377 .stk-block-heading__text {
	letter-spacing: -0.03em;
	text-wrap: balance;
}

.stk-71d0377 .stk-img-wrapper:not(.stk-block-card__image) {
	width: 96px !important;
	height: 96px !important;
	max-width: 96px;
	margin: 10px auto 20px !important;
	padding: 16px;
	border-radius: 28px;
	background: #fff;
	box-shadow: 0 14px 32px rgba(8, 28, 38, 0.22);
	overflow: hidden;
}

.stk-71d0377 .stk-img-wrapper:not(.stk-block-card__image) .stk-img {
	object-fit: contain !important;
	width: 100% !important;
	height: auto !important;
	animation: none !important;
}

@keyframes furbuddy-mover {
	0%,
	100% {
		transform: translateX(-18px);
	}
	50% {
		transform: translateX(18px);
	}
}

.stk-71d0377 .stk-block-countdown {
	width: min(100%, 560px);
	background: transparent !important;
	box-shadow: none !important;
	margin-inline: auto;
}

.stk-71d0377 .stk-block-countdown__container {
	display: flex;
	width: 100%;
	gap: 10px;
	justify-content: center;
}

.stk-71d0377 .stk-block-countdown__content-container {
	flex: 1 1 0;
	min-width: 0;
	padding: 16px 8px 12px;
	border-radius: 18px;
	background: #fff;
	box-shadow: 0 12px 28px rgba(8, 28, 38, 0.16);
}

.stk-71d0377 .stk-block-countdown__digit {
	font-size: 30px;
	font-weight: 700;
	line-height: 1;
	color: #2c4a56;
}

.stk-71d0377 .stk-block-countdown__label {
	margin: 8px 0 0;
	font-size: 11px;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	white-space: nowrap;
	color: #7d939d;
}

.stk-71d0377 .stk-block-card,
.stk-71d0377 .stk-block-card > .stk-container {
	background: transparent !important;
	box-shadow: none !important;
	border-radius: 0;
}

.stk-71d0377 .stk-block-card > .stk-container {
	padding: 0 !important;
}

.stk-71d0377 .stk-block-card__image,
.stk-71d0377 .stk-block-card__image.stk-image--shape-stretch {
	height: auto !important;
	min-height: 0 !important;
	max-height: none !important;
	aspect-ratio: auto !important;
	display: flex !important;
	justify-content: center !important;
	align-items: center !important;
	width: 100% !important;
	overflow: visible !important;
	background: transparent !important;
	border-radius: 0;
	margin: 0 auto 16px !important;
}

.stk-71d0377 .stk-block-card__image img {
	position: static !important;
	inset: auto !important;
	display: block;
	width: min(100%, 420px) !important;
	height: auto !important;
	max-width: 100%;
	margin-left: auto !important;
	margin-right: auto !important;
	overflow: hidden;
	border-radius: 22px;
	object-fit: contain !important;
	animation: furbuddy-mover 3.6s ease-in-out infinite;
}

.stk-71d0377 .stk-block-card .stk-block-heading__text,
.stk-71d0377 .stk-block-card .stk-block-text__text {
	color: #fff !important;
}

.stk-71d0377 .stk-block-card .stk-button,
.stk-71d0377 .stk-block-card .stk-block-button .stk-button {
	border-radius: 999px;
}

.stk-71d0377 .stk-block-carousel__button {
	width: 46px;
	height: 46px;
	background: #fff;
	box-shadow: 0 8px 20px rgba(8, 28, 38, 0.18);
	transition: transform 0.25s ease;
}

.stk-71d0377 .stk-block-carousel__dot {
	width: 8px;
	height: 8px;
	border-radius: 99px;
	background: rgba(255, 255, 255, 0.45);
}

.stk-71d0377 .stk-block-carousel__dot--active {
	width: 22px;
	background: #fff;
}

@media (hover: hover) and (pointer: fine) {
	article.entry-card:hover {
		transform: translateY(-8px);
		border-color: #b7d0da;
		box-shadow: 0 16px 34px rgba(24, 52, 68, 0.14);
	}

	.stk-71d0377 .stk-block-carousel__button:hover {
		transform: scale(1.12);
	}
}

@media (prefers-reduced-motion: reduce) {
	article.entry-card,
	.stk-71d0377 .stk-block-card__image img,
	.stk-71d0377 .stk-block-carousel__button {
		animation: none;
		transition: none;
	}

	article.entry-card:hover,
	.stk-71d0377 .stk-block-carousel__button:hover {
		transform: none;
	}
}
CSS;

		wp_register_style( 'furbuddy-productos-imagen', false, array(), '13' );
		wp_enqueue_style( 'furbuddy-productos-imagen' );
		wp_add_inline_style( 'furbuddy-productos-imagen', $css );
	}
);
