<?php

add_action( 'wpsc_register_settings_tabs', '_wpsc_te2_register_settings_tabs', 10, 1 );
add_action( 'wpsc_load_settings_tab_class', '_wpsc_te2_load_settings_tab_class', 10, 1 );
add_action( 'load-options.php', '_wpsc_action_load_settings_for_update' );

require_once( WPSC_TE_V2_HELPERS_PATH . '/settings-page.php' );