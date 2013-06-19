<?php

class WPSC_Settings_Tab_Pages extends _WPSC_Settings_Tab_Form
{
	private $slug_settings = array(
		'store_slug',
		'cart_page_slug',
		'customer_account_page_slug',
		'product_base_slug',
	);

	public function __construct() {
		flush_rewrite_rules( false );
		$this->populate_form_array();
		parent::__construct();
		$this->hide_submit_button();
	}

	private function check_slug_conflicts() {
		foreach ( $this->slug_settings as $setting ) {
			settings_errors( 'wpsc_' . $setting, true, true );
		}
	}

	public function display() {
		$this->check_slug_conflicts();
		parent::display();
	}

	private function populate_form_array() {
		$this->sections = array(
			'locations' => array(
				'title' => _x(
					'Page Slugs',
					'page locations section title',
					'wpsc'
				),
				'fields' => array(
					'store_slug',
					'category_base_slug',
					'product_base_slug',
					'cart_page_slug',
					'checkout_page_slug',
					'customer_account_page_slug',
					'login_page_slug',
					'password_reminder_page_slug',
					'register_page_slug',
					'prefix_product_slug',
					'hierarchical_product_category_url',
				),
			),
			'titles' => array(
				'title' => _x(
					'Page Titles',
					'page titles section title',
					'wpsc'
				),
				'fields' => array(
					'store_title',
					'cart_page_title',
					'checkout_page_title',
					'customer_account_page_title',
					'login_page_title',
					'password_reminder_page_title',
					'register_page_title',
				),
			),
		);

		$view_button = '<a class="button button-secondary button-view-page" href="%1$s">%2$s</a>';
		$view_message = _x( 'View', 'view page', 'wpsc' );
		$view_category_message = _x( 'Sample Category', 'view page', 'wpsc' );
		$view_product_message = _x( 'Sample Product', 'view page', 'wpsc' );

		$base_shop_url      = '<small>' . esc_url( wpsc_get_store_url( '/' ) ) . '</small>';
		$sample_category    = get_terms( 'wpsc_product_category', array( 'number' => 1 ) );
		$sample_product     = get_posts( array(	'post_type' => 'wpsc-product', 'numberposts' => 1 ) );

		$this->form_array = array(
			'store_slug' => array(
				'type'    => 'textfield',
				'prepend' => '<small>' . esc_url( home_url( '/' ) ) . '</small>',
				'title'   => _x(
					'Main store',
					'page slug setting',
					'wpsc'
				),
				'append' => sprintf(
					$view_button,
					wpsc_get_store_url(),
					$view_message
				),
				'validation' => 'required',
				'class' => 'regular-text code',
			),
			'store_title' => array(
				'type'  => 'textfield',
				'title' => _x( 'Main store title', 'page slug title', 'wpsc' ),
				'validation' => 'required',
			),
			'category_base_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'append'      =>   empty( $sample_category )
				                 ? ''
				                 : sprintf(
				                 	$view_button,
				                 	get_term_link( $sample_category[0] ),
				                 	$view_category_message
				                 ),
				'title'       => _x(
					'Product category base slug',
					'permalinks setting',
					'wpsc'
				),
				'validation'  => 'required',
				'class' => 'regular-text code',
			),
			'product_base_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'append'      =>   empty( $sample_product )
				                 ? ''
				                 : sprintf(
				                 	$view_button,
				                 	get_permalink( $sample_product[0] ),
				                 	$view_product_message
				                 ),
				'title'       => _x(
					'Single product base plug',
					'permalinks setting',
					'wpsc'
				),
				'validation'  => 'required',
				'class' => 'regular-text code',
			),
			'prefix_product_slug' => array(
				'type'    => 'checkboxes',
				'title'   => _x( 'Product prefix', 'permalinks setting', 'wpsc' ),
				'options' => array(
					1 => __(
						'Include category slug in product URL.',
						'wpsc'
					)
				),
			),
			'hierarchical_product_category_url' => array(
				'type'    => 'radios',
				'title'   => _x(
					'Hierarchical product category URL',
					'permalinks setting',
					'wpsc'
				),
				'options' => array(
					1 => _x(
						'Yes',
						'permalinks setting / hierarchical product category URL',
						'wpsc'
					),
					0 => _x(
						'No',
						'permalinks setting / hierarchical product category URL',
						'wpsc'
					),
				),
				'description' => __(
					'When hierarchical product category URL is enabled, parent product categories are also included in the product URL.',
					'wpsc'
				),
			),
			'cart_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'append'      => sprintf(
					$view_button,
					wpsc_get_cart_url(),
					$view_message
				),
				'title'       => _x( 'Cart page', 'page settings', 'wpsc' ),
				'validation'  => 'required',
				'class' => 'regular-text code',
			),
			'cart_page_title' => array(
				'type'        => 'textfield',
				'title'       => _x( 'Cart page', 'page settings', 'wpsc' ),
				'validation'  => 'required',
			),
			'checkout_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'title'       => _x( 'Checkout page', 'page setting', 'wpsc' ),
				'validation'  => 'required',
				'class' => 'regular-text code',
			),
			'checkout_page_title' => array(
				'type' => 'textfield',
				'title' => _x( 'Checkout page', 'page settings', 'wpsc' ),
				'validation' => 'required',
			),
			'customer_account_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'append'      => sprintf(
					$view_button,
					wpsc_get_customer_account_url(),
					$view_message
				),
				'title'       => _x( 'Customer account page', 'permalinks setting', 'wpsc' ),
				'validation'  => 'required|slug_not_conflicted',
				'class' => 'regular-text code',
			),
			'customer_account_page_title' => array(
				'type' => 'textfield',
				'title' => _x( 'Customer account page', 'page settings', 'wpsc' ),
				'validation' => 'required',
			),
			'login_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'title'       => _x( 'Login page', 'permalinks setting', 'wpsc' ),
				'description' => __( "Leaving this field blank will disable the page.", 'wpsc' ),
				'validation'  => 'slug_not_conflicted',
				'class' => 'regular-text code',
			),
			'login_page_title' => array(
				'type' => 'textfield',
				'title' => _x( 'Login page', 'page settings', 'wpsc' ),
				'validation' => 'required',
			),
			'password_reminder_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'title'       => _x( 'Password reminder page', 'permalinks setting', 'wpsc' ),
				'description' => __( "Leaving this field blank will disable the page.", 'wpsc' ),
				'validation'  => 'slug_not_conflicted',
				'class' => 'regular-text code',
			),
			'password_reminder_page_title' => array(
				'type' => 'textfield',
				'title' => _x( 'Password reminder page', 'page settings', 'wpsc' ),
				'validation' => 'required',
			),
			'register_page_slug' => array(
				'type'        => 'textfield',
				'prepend'     => $base_shop_url,
				'title'       => _x( 'Register page', 'permalinks setting', 'wpsc' ),
				'description' => __( "Leaving this field blank will disable the page.", 'wpsc' ),
				'validation'  => 'slug_not_conflicted',
				'class' => 'regular-text code',
			),
			'register_page_title' => array(
				'type' => 'textfield',
				'title' => _x( 'Register page', 'page settings', 'wpsc' ),
				'validation' => 'required',
			),
		);

		if ( ! get_option( 'users_can_register' ) ) {
			$additional_description = '<br /> ' . __( '<strong>Note:</strong> Enable "Anyone can register" in <a href="%s">Settings -> General</a> first if you want to use this page.', 'wpsc' );
			$additional_description = sprintf( $additional_description, admin_url( 'options-general.php' ) );
			$this->form_array['login_page_slug']['description']         .= $additional_description;
			$this->form_array['password_reminder_page_slug']['description'] .= $additional_description;
			$this->form_array['register_page_slug']['description']      .= $additional_description;
		}
	}
}