<?php

add_action(
	'update_option_wpsc_store_slug',
	'_wpsc_action_update_transact_url_option'
);

add_action( 'wpsc_te2_activate', '_wpsc_te2_action_setup_settings');

function wpsc_get_option( $option_name ) {
	$wpsc_settings = WPSC_Settings::get_instance();
	return $wpsc_settings->get( $option_name );
}

function wpsc_update_option( $option_name, $value ) {
	$wpsc_settings = WPSC_Settings::get_instance();
	return $wpsc_settings->set( $option_name, $value );
}

function _wpsc_action_update_transact_url_option( $option ) {
	update_option( 'transact_url', wpsc_get_checkout_url( 'results' ) );
}

function _wpsc_te2_action_setup_settings() {
	$wpsc_settings = WPSC_Settings::get_instance();
	$wpsc_settings->_action_setup();
}