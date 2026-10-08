<?php
/**
 * Plugin Name: Furbuddy acceso WooCommerce
 * Description: Comprueba el usuario de WordPress para el panel de Vue. WooCommerce usa la misma tabla de usuarios.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'furbuddy/v1',
			'/login',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => static function ( WP_REST_Request $request ) {
					$usuario  = (string) $request->get_param( 'usuario' );
					$password = (string) $request->get_param( 'password' );
					$user     = wp_authenticate( $usuario, $password );

					if ( is_wp_error( $user ) ) {
						return new WP_Error(
							'credenciales',
							'Usuario o contrasena incorrectos',
							array( 'status' => 401 )
						);
					}

					$permitidos = array( 'administrator', 'shop_manager' );
					if ( ! array_intersect( $user->roles, $permitidos ) ) {
						return new WP_Error(
							'permiso',
							'Esta cuenta no puede entrar al panel',
							array( 'status' => 403 )
						);
					}

					return array(
						'id'      => $user->ID,
						'usuario' => $user->user_login,
						'nombre'  => $user->display_name,
						'email'   => $user->user_email,
					);
				},
			)
		);
	}
);
