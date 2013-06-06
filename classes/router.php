<?php

class WPSC_Router {
	private static $instance;

	public static function get_instance() {
		if ( empty( self::$instance ) )
			self::$instance = new WPSC_Router();

		return self::$instance;
	}

	private $controller;
	private $controller_path;
	private $controller_name;
	private $controller_method;
	private $controller_args;

	public function __get( $name ) {
		// read-only props
		if ( in_array( $name, array(
			'controller',
			'controller_name',
			'controller_method',
			'controller_args',
		) ) ) {
			return $this->$name;
		}

		return null;
	}

	private function __construct() {
		add_action( 'parse_request', array( $this, '_action_parse_request' ) );
		add_filter( 'query_vars', array( $this, '_filter_query_vars' ) );
		add_action( 'wp', array( $this, '_action_wp' ), 1 );
	}

	public function _action_parse_request( &$wp ) {
		if ( empty( $wp->query_vars['wpsc_controller'] ) )
			return;

		// Add / remove filters so that unnecessary SQL queries are not executed
		add_filter( 'posts_request', array( $this, '_filter_disable_main_query' ), 10, 2 );
		add_filter( 'split_the_query', array( $this, '_filter_disable_split_the_query' ), 10, 2 );

	}

	public function _filter_disable_main_query( $sql, $query ) {
		if ( ! $query->is_main_query() )
			return $sql;

		return '';
	}

	public function _filter_disable_split_the_query( $split, $query ) {
		if ( ! $query->is_main_query() )
			return $split;

		return false;
	}

	public function _action_wp() {
		$controller = get_query_var( 'wpsc_controller' );

		if ( is_post_type_archive( 'wpsc-product' ) )
			$controller = 'main-store';
		elseif ( is_singular( 'wpsc-product' ) )
			$controller = 'single';
		elseif ( is_tax( 'wpsc_product_category' ) )
			$controller = 'category';

		$this->init_query_flags( $controller );

		if ( ! empty( $controller ) ) {
			status_header( 200 );
			$this->init_controller( $controller );
		}
	}

	private function init_query_flags( $controller ) {
		global $wp_query;
		$props = array_keys( wpsc_get_page_slugs() );
		foreach ( $props as $name ) {
			$prop = 'wpsc_is_' . str_replace( '-', '_', $name );
			$wp_query->$prop = false;
		}
		$wp_query->wpsc_is_controller = false;

		if ( empty( $controller ) )
			return;

		$wp_query->is_home = false;
		$wp_query->is_404 = false;

		$wp_query->wpsc_is_controller = true;
		$prop = 'wpsc_is_' . str_replace( '-', '_', $controller );
		$wp_query->$prop = true;
	}

	private function init_controller( $controller ) {
		if ( empty( $controller ) )
			return;

		$controller_args = trim( get_query_var( 'wpsc_controller_args' ), '/' );
		$controller_args = explode( '/', $controller_args );

		if ( ! is_array( $controller_args ) )
			$controller_args = array();

		$slug = array_shift( $controller_args );
		$method = str_replace( array( ' ', '-' ), '_', $slug );
		if ( ! $method )
			$slug = $method = 'index';

		$this->controller_slug = $slug;
		$this->controller_method = $method;
		$this->controller_name = $controller;
		$this->controller = _wpsc_load_controller( $controller );

		if ( ! is_callable( array( $this->controller, $method ) ) )
			trigger_error( 'Invalid controller method: ' . get_class( $this->controller ) . '::' . $method . '()', E_USER_ERROR );

		do_action( 'wpsc_router_init' );

		$this->controller_args = $controller_args;

		if ( is_callable( array( $this->controller, '_pre_action' ) ) )
			call_user_func( array( $this->controller, '_pre_action' ), $method, $controller_args );

		call_user_func_array( array( $this->controller, $method ), $controller_args );
	}

	public function _filter_query_vars( $q ) {
		$q[] = 'wpsc_controller';
		$q[] = 'wpsc_controller_args';

		return $q;
	}
}