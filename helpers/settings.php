<?php

add_action(
	'update_option_wpsc_store_slug',
	'_wpsc_action_update_transact_url_option'
);

add_action(
	'add_option_rewrite_rules',
	'_wpsc_action_update_transact_url_option'
);

add_action( 'wpsc_te2_activate', '_wpsc_te2_action_setup_settings');

add_action(
	'sanitize_option_show_on_front',
	'_wpsc_te2_action_sanitize_show_on_front'
);

add_filter(
	'pre_option_wpsc_store_slug',
	'_wpsc_te2_filter_store_slug'
);

/**
 * Retrieve WP e-Commerce option value based on name of the option.
 *
 * Works just like get_option(), except that it automatically prefixes
 * the option name with 'wpsc_' and assign a default value as defined in
 * WPSC_Settings class in case the option has no value.
 *
 * @since  0.1
 *
 * @uses   WPSC_Settings::get()
 * @param  string $option_name Name of the option, not escaped.
 * @return mixed               Value of the option
 */
function wpsc_get_option( $option_name ) {
	$wpsc_settings = WPSC_Settings::get_instance();
	return $wpsc_settings->get( $option_name );
}

/**
 * Update the value of an option that was already added.
 *
 * Works just like update_option(), except that it automatically prefixes the
 * option name with 'wpsc_'.
 *
 * @since  0.1
 *
 * @param  string $option_name Option name
 * @param  mixed  $value       New value
 * @return bool                True if updated successfully
 */
function wpsc_update_option( $option_name, $value ) {
	$wpsc_settings = WPSC_Settings::get_instance();
	return $wpsc_settings->set( $option_name, $value );
}

/**
 * The 'transact_url' option is still used by other components outside of theme
 * engine (such as payment gateways). To ensure compatibility, we need to keep
 * this option updated and point to the last step of the checkout process.
 *
 * Action hook: 'update_option_wpsc_store_slug'.
 *
 * @access private
 *
 * @since  0.1
 * @param  string $option Value of the store page slug
 */
function _wpsc_action_update_transact_url_option( $option ) {
	update_option( 'transact_url', wpsc_get_checkout_url( 'results' ) );
}

/**
 * When the theme engine is activated, setup the options.
 *
 * Action hooks: 'wpsc_te2_activate', 'add_option_rewrite_rules'
 *
 * @since  0.1
 * @uses   WPSC_Settings::_action_setup()
 */
function _wpsc_te2_action_setup_settings() {
	$wpsc_settings = WPSC_Settings::get_instance();
	$wpsc_settings->_action_setup();
}

/**
 * Provide compatibility between 'show_on_front' and 'store_as_front_page' options.
 *
 * WordPress currently doesn't allow any values for 'show_on_front' other than
 * 'page' and 'posts'.
 *
 * {@link _wpsc_te2_action_admin_enqueue_script()} is used to dynamically inject
 * a radio box in Settings->Reading so that it's more user friendly to select
 * 'Main store as front page' as an option.
 *
 * Behind the scene, it doesn't really matter what the value of 'show_on_front'
 * is. What really matters is the 'wpsc_store_as_front_page' option.
 *
 * Filter hook: sanitize_option_show_on_front
 *
 * @access private
 *
 * @since  0.1
 * @param  mixed $value
 * @return mixed
 */
function _wpsc_te2_action_sanitize_show_on_front( $value ) {
	// if the value is 'wpsc_main_store', just reset it back to 'posts' or
	// 'page'.
	if ( $value == 'wpsc_main_store' ) {
		if ( ! get_option( 'page_on_front' ) && ! get_option( 'page_for_posts' ) )
			$value = 'posts';
		else
			$value = 'page';
		wpsc_update_option( 'store_as_front_page', true );
	} else {
		// if the user selected something other than main store as front page,
		// reset 'wpsc_store_as_front_page' to false
		wpsc_update_option( 'store_as_front_page', false );
	}

	// regenerate rewrite rules again because wpsc-product post type archive
	// slug has possibly changed
	wpsc_register_post_types();
	flush_rewrite_rules();

	return $value;
}

/**
 * In case store is set to display on front page, force the 'store_slug' option
 * to always return empty value.
 *
 * @param  string $value Current value of 'store_slug' option
 * @return string        New value
 */
function _wpsc_te2_filter_store_slug( $value ) {
	if ( wpsc_get_option( 'store_as_front_page' ) )
		return '';
	return false;
}