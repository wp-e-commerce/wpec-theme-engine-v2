<?php

define( 'WPSC_TE_V2_PATH', dirname( __FILE__ ) );
define( 'WPSC_TE_V2_CLASSES_PATH', WPSC_TE_V2_PATH . '/classes' );
define( 'WPSC_TE_V2_HELPERS_PATH', WPSC_TE_V2_PATH . '/helpers' );

add_action( 'wpsc_includes', '_wpsc_te_v2_includes' );
add_filter(
	'wpsc_register_post_types_products_args',
	'_wpsc_te_v2_product_post_type_args'
);
add_filter(
	'wpsc_register_taxonomies_product_category_args',
	'_wpsc_te_v2_product_category_args'
);

add_action( 'after_switch_theme', '_wpsc_action_flush_rewrite_rules', 99 );

function _wpsc_te_v2_includes() {
	require_once( WPSC_TE_V2_CLASSES_PATH . '/settings.php' );
	require_once( WPSC_TE_V2_CLASSES_PATH . '/product.php' );

	require_once( WPSC_TE_V2_HELPERS_PATH . '/compat.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/mvc.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/settings.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/form.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/form-validation.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/template-tags/common.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/template-tags/url.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/css.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/js.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/widgets.php' );
	require_once( WPSC_TE_V2_HELPERS_PATH . '/customer.php' );

	if ( is_admin() )
		require_once( WPSC_TE_V2_PATH . '/admin.php' );

	if ( ! is_admin() ) {
		_wpsc_te2_mvc_init();
	}

	add_filter( 'rewrite_rules_array', '_wpsc_filter_rewrite_controller_slugs' );
}

function _wpsc_te_v2_product_post_type_args( $args ) {
	$store_slug = wpsc_get_option( 'store_slug' );

	$product_slug = $store_slug . '/' . wpsc_get_option( 'product_base_slug' );
	if ( wpsc_get_option( 'prefix_product_slug' ) )
		$product_slug .= '/%wpsc_product_category%';

	$args['has_archive'] = $store_slug;
	$args['rewrite']['slug'] = $product_slug;

	return $args;
}

function _wpsc_te_v2_product_category_args( $args ) {
	$store_slug = wpsc_get_option( 'store_slug' );
	$category_base_slug = wpsc_get_option( 'category_base_slug' );
	$hierarchical_product_category = wpsc_get_option( 'hierarchical_product_category_url' );

	$args['rewrite']['slug'] = $store_slug . '/' . $category_base_slug;
	$args['rewrite']['hierarchical'] = (bool) $hierarchical_product_category;

	return $args;
}

function _wpsc_action_flush_rewrite_rules() {
	flush_rewrite_rules( false );
}