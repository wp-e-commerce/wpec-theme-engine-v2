<?php

add_action( 'load-options.php', '_wpsc_action_load_settings_for_update' );

function _wpsc_te2_register_settings_tabs( $page_instance ) {
	$page_instance->register_tab( 'pages', _x( 'Pages', 'Pages settings tab in Settings->Store page', 'wpsc' ) );
}

function _wpsc_te2_load_settings_tab_class( $page_instance ) {
	$current_tab_id = $page_instance->get_current_tab_id();
	if ( in_array( $current_tab_id, array( 'pages' ) ) ) {
		require_once( WPSC_TE_V2_CLASSES_PATH . '/settings-tab.php' );
		require_once( WPSC_TE_V2_CLASSES_PATH . '/settings-pages.php' );
	}
}

function _wpsc_action_load_settings_for_update() {
	if ( isset( $_REQUEST['tab'] ) ) {
		require_once( WPSC_FILE_PATH . '/wpsc-admin/settings-page.php' );
		WPSC_Settings_Page::get_instance();
	}
}