<?php
/**
 * Plugin Name: Furbuddy fotos WooCommerce
 * Description: Recibe las fotos del panel y las guarda en la biblioteca de WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'furbuddy/v1',
			'/media',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'furbuddy_guardar_foto',
			)
		);
	}
);

function furbuddy_guardar_foto( WP_REST_Request $request ) {
	$usuario = furbuddy_usuario_woocommerce( $request );
	if ( is_wp_error( $usuario ) ) {
		return $usuario;
	}

	$archivos = $request->get_file_params();
	$archivo  = $archivos['archivo'] ?? null;
	if ( ! is_array( $archivo ) || empty( $archivo['tmp_name'] ) ) {
		return new WP_Error( 'foto', 'Falta la foto', array( 'status' => 400 ) );
	}

	$permitidos = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );
	$tipo       = $archivo['type'] ?? '';
	if ( ! in_array( $tipo, $permitidos, true ) ) {
		return new WP_Error( 'foto', 'Usa JPG, PNG, WEBP o GIF', array( 'status' => 400 ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	wp_set_current_user( $usuario );
	$adjunto = media_handle_sideload( $archivo, 0 );
	if ( is_wp_error( $adjunto ) ) {
		return new WP_Error( 'foto', $adjunto->get_error_message(), array( 'status' => 400 ) );
	}

	return array(
		'id'  => $adjunto,
		'src' => (string) wp_get_attachment_url( $adjunto ),
	);
}

function furbuddy_usuario_woocommerce( WP_REST_Request $request ) {
	if ( ! function_exists( 'wc_api_hash' ) ) {
		return new WP_Error( 'woocommerce', 'WooCommerce no esta activo', array( 'status' => 503 ) );
	}

	$encabezado = (string) $request->get_header( 'authorization' );
	$clave      = '';
	$secreto    = '';
	if ( stripos( $encabezado, 'basic ' ) === 0 ) {
		$decodificado = base64_decode( substr( $encabezado, 6 ) );
		$partes       = explode( ':', (string) $decodificado, 2 );
		$clave        = $partes[0] ?? '';
		$secreto      = $partes[1] ?? '';
	}

	if ( '' === $clave || '' === $secreto ) {
		return new WP_Error( 'credenciales', 'Falta la clave de WooCommerce', array( 'status' => 401 ) );
	}

	global $wpdb;
	$fila = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT user_id, permissions, consumer_secret FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_key = %s",
			wc_api_hash( $clave )
		)
	);

	if ( ! $fila || ! in_array( $fila->permissions, array( 'read_write', 'write' ), true ) ) {
		return new WP_Error( 'credenciales', 'La clave de WooCommerce no puede subir fotos', array( 'status' => 401 ) );
	}

	$guardado = (string) $fila->consumer_secret;
	$valido   = strlen( $guardado ) === strlen( $secreto ) && hash_equals( $guardado, $secreto );
	$hash     = wc_api_hash( $secreto );
	if ( ! $valido && strlen( $guardado ) === strlen( $hash ) ) {
		$valido = hash_equals( $guardado, $hash );
	}
	if ( ! $valido ) {
		return new WP_Error( 'credenciales', 'La clave de WooCommerce no puede subir fotos', array( 'status' => 401 ) );
	}

	return (int) $fila->user_id;
}
