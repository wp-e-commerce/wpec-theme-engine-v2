<?php

define( 'WPSC_TE_V2_CSS_URL', WPSC_TE_V2_URL . '/admin/css' );
define( 'WPSC_TE_V2_JS_URL', WPSC_TE_V2_URL . '/admin/js' );

add_action( 'wpsc_register_settings_tabs', '_wpsc_te2_register_settings_tabs', 10, 1 );
add_action( 'wpsc_load_settings_tab_class', '_wpsc_te2_load_settings_tab_class', 10, 1 );
add_action( 'admin_enqueue_scripts', '_wpsc_te2_action_admin_enqueue_styles' );
add_action( 'admin_enqueue_scripts', '_wpsc_te2_action_admin_enqueue_scripts' );

require_once( WPSC_TE_V2_HELPERS_PATH . '/settings-page.php' );

function _wpsc_te2_action_admin_enqueue_styles() {
	wp_register_style( 'wpsc-te2-admin', WPSC_TE_V2_CSS_URL . '/admin.css' );
	wp_enqueue_style( 'wpsc-te2-admin' );
}

function _wpsc_te2_action_admin_enqueue_scripts() {
	wp_register_script(
		'wpsc-auto-resize-field', WPSC_TE_V2_JS_URL . '/auto-resize-field.js'
	);

	wp_enqueue_script( 'wpsc-auto-resize-field' );
}