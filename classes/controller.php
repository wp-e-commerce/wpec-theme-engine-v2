<?php

class WPSC_Controller {
	private $needs_authorization = false;
	public $title = '';
	protected $view = '';
	protected $message_collection;
	private $main_query;
	private $needs_compat = true;

	public function __get( $name ) {
		// read-only properties
		if ( in_array( $name, array(
			'message_collection', 'main_query', 'needs_compat', 'view' )
		) ) {
			return $this->$name;
		}

		return null;
	}

	public function __construct() {
		require_once( WPSC_TE_V2_CLASSES_PATH . '/message-collection.php' );

		add_filter( 'template_include', array( $this, '_filter_template_router' ) );
		$this->message_collection = WPSC_Message_Collection::get_instance();
	}

	protected function verify_nonce( $action ) {
		if ( ! wp_verify_nonce( $_POST['_wp_nonce'], $action ) ) {
			$this->message_collection->add(
				__( 'Your form submission could not be processed by our system because the page has been left idle for too long. Please try submitting it again.', 'wpsc' ),
				'error'
			);

			return false;
		}

		return true;
	}

	public function _filter_template_router() {
		$located = '';

		$located = $this->get_native_template();

		if ( ! $located ) {
			$current_controller = _wpsc_get_current_controller_name();
			$located = wpsc_locate_view_wrappers( $current_controller . '-wrapper.php' );
		}

		if ( $located )
			$this->needs_compat = false;

		if ( ! $located )
			$located = locate_template( 'page.php' );

		if ( $this->needs_compat )
			$this->prepare_compat();

		return $located;
	}

	protected function get_native_template() {
	}

	private function prepare_compat() {
		add_filter( 'the_content', array( $this, '_action_replace_content' ) );
		add_filter( 'comments_array', array( $this, '_filter_comments_array' ), 10, 2 );
		$this->reset_globals();
	}

	public function _filter_comments_array( $comments, $id ) {
		if ( is_main_query() && ! $id )
			return array();

		return $comments;
	}

	private function reset_globals() {
		global $wp_query, $wp_the_query;

		$reset_post = array(
			'ID'              => 0,
			'post_title'      => $this->title,
			'post_author'     => 0,
			'post_date'       => 0,
			'post_content'    => '',
			'post_type'       => 'page',
			'post_status'     => 'publish',
			'post_parent'     => 0,
			'post_name'       => '',
			'ping_status'     => 'closed',
			'comment_status'  => 'closed',
			'comment_count'   => 0,
		);

		$reset_wp_query = array(
			'post_count'      => 1,
			'is_404'          => false,
			'is_page'         => true,
			'is_single'       => false,
			'is_archive'      => false,
			'is_tax'          => false,
			'is_home'         => false,
			'is_front_page'   => false,
			'comment_count'   => 0,
		);

		// Default for current post
		if ( isset( $wp_query->post ) && is_singular() ) {
			$post_id = $wp_query->post->ID;
			$reset_post = array_merge( $reset_post, array(
				'post_author'     => get_post_field( 'post_author' , $post_id ) ,
				'post_date'       => get_post_field( 'post_date'   , $post_id ),
				'post_content'    => get_post_field( 'post_content', $post_id ),
				'post_type'       => get_post_field( 'post_type'   , $post_id ),
				'post_status'     => get_post_field( 'post_status' , $post_id ),
				'post_name'       => get_post_field( 'post_name'   , $post_id ),
				'comment_status'  => comments_open(),
				)
			);
		}

		$queried_object = $reset_post;
		if ( is_post_type_archive( 'wpsc-product' ) )
			$queried_object = get_post_type_object( 'wpsc-product' );

		$this->main_query = unserialize( serialize( $wp_query ) );

		// Clear out the post related globals
		$GLOBALS['post'] = $wp_query->post = (object) $reset_post;
		$wp_query->queried_object = (object) $queried_object;
		$wp_query->queried_object_id = 0;
		$wp_query->posts = array( $wp_query->post );

		// Prevent comments form from appearing
		foreach ( $reset_wp_query as $flag => $value ) {
			$wp_query->$flag = $value;
		}
	}

	public function _action_replace_content( $content ) {
		global $wp_query;

		if ( ! is_main_query() || get_the_ID() )
			return $content;

		$this->restore_main_query();

		$current_controller = _wpsc_get_current_controller_name();

		$before = apply_filters(
			'wpsc_replace_the_content_before',
			'<div class="%s">',
			$current_controller,
			$content
		);

		$after  = apply_filters(
			'wpsc_replace_the_content_after' ,
			'</div>',
			$current_controller,
			$content
		);

		$before = sprintf( $before, 'wpsc-page wpsc-page-' . $current_controller );
		ob_start();
		wpsc_get_template_part( $this->view );
		$content = ob_get_clean();

		wp_reset_query();

		return $before . $content . $after;
	}

	public function needs_authorization( $val = null ) {
		if ( is_null( $val ) )
			return $this->needs_authorization;

		$this->needs_authorization = $val;
	}

	private function restore_main_query() {
		$GLOBALS['wp_query'] = $this->main_query;
		wp_reset_postdata();
	}
}