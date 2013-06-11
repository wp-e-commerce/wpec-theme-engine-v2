<?php

if ( ! defined( 'WPSC_THEME_ENGINE_TEMPLATE_PART_FOLDER' ) )
	define( 'WPSC_THEME_ENGINE_TEMPLATE_PART_FOLDER', 'wp-e-commerce' );

if ( ! defined( 'WPSC_THEME_ENGINE_LESS_JS' ) )
	define( 'WPSC_THEME_ENGINE_LESS_JS', false );

/**
 * Locate the path to a certain WPEC theme file.
 *
 * In 4.0, we allow themes and child themes to override a default template, stylesheet, script or
 * image files by providing the same file structure inside the theme (or child theme) folder.
 *
 * This function searches for the file in multiple paths. It willlook for the template in the
 * following order:
 * - wp-content/themes/wpsc-theme-engine/{$current_theme_name}
 * - wp-content/themes/wpsc-theme-engine/{$parent_theme_name}
 * - wp-content/themes/wpsc-theme-engine
 * - current theme's path
 * - parent theme's path
 * - wp-content/plugins/wp-e-commerce/wpsc-theme-engine/{$current_theme_name}
 * - wp-content/plugins/wp-e-commerce/wpsc-theme-engine/{$parent_theme_name}
 * - wp-content/plugins/wp-e-commerce
 *
 * The purpose of the "wp-content/themes/wpsc-theme-engine" path is to provide a way for the users
 * to preserve their custom templates when the current theme is updated. This makes it much more
 * flexible for users who is already using a child theme or a third-party WP e-Commerce theme.
 *
 * Inside wp-content/plugins/wp-e-commerce/wpsc-theme-engine, we provide template files for
 * TwentyTen and TwentyEleven in two separate folders. All the relevant template parts are inside
 * wpsc-theme-engine/wp-e-commerce.
 *
 * For example, on a WordPress installation that uses TwentyTen as the main theme, the main catalog
 * template by default will be located in wp-content/plugins/wp-e-commerce/twentyeleven/archive-wpsc-product.php.
 * If you want to override this template in your TwentyTen theme, simply create an archive-wpsc-product.php
 * file in wp-content/themes/twentyeleven/ and that file will be used.
 *
 * You can essentially override any kind of files inside wp-content/plugins/wp-e-commerce/wpsc-theme-engine/{$theme_name}
 * by creating the same file structure in wp-content/themes/{$theme_name}.
 *
 * @since 4.0
 * @uses  get_stylesheet()
 * @uses  get_template()
 * @uses  get_theme_root()
 *
 * @param  array  $files The file names you want to look for
 * @return string        The path to the matched template file
 */
function wpsc_locate_theme_file( $files ) {
	$located = '';
	$theme_root = get_theme_root();
	$current_theme = get_stylesheet();
	$parent_theme = get_template();

	if ( $current_theme == $parent_theme ) {
		$paths = array(
			STYLESHEETPATH,
		);
	} else {
		$paths = array(
			STYLESHEETPATH,
			TEMPLATEPATH,
		);
	}

	foreach ( (array) $files as $file ) {
		if ( ! $file )
			continue;

		foreach ( $paths as $path ) {
			if ( file_exists( $path . '/' . $file ) ) {
				$located = $path . '/' . $file;
				break 2;
			}
		}
	}

	return $located;
}

/**
 * Return the URI of a certain WPEC file inside our theme engine folder structure.
 *
 * See {@link wpsc_locate_theme_file()} for more information about how this works.
 *
 * @since 4.0
 * @uses  content_url()
 * @uses  get_site_url()
 * @uses  plugins_url()
 * @uses  wpsc_locate_theme_file()
 *
 * @param  array  $file Files to look for.
 * @return string       The URL of the matched file
 */
function wpsc_locate_theme_file_uri( $file ) {
	$path = wpsc_locate_theme_file( $file );
	if ( strpos( $path, WP_CONTENT_DIR ) !== false )
		return content_url( substr( $path, strlen( WP_CONTENT_DIR ) ) );
	elseif ( strpos( $path, WP_PLUGIN_DIR ) !== false )
		return plugins_url( substr( $path, strlen( WP_PLUGIN_DIR ) ) );
	elseif ( strpos( $path, WPMU_PLUGIN_DIR ) !== false )
		return plugins_url( substr( $path, strlen( WP_PLUGIN_DIR ) ) );
	elseif ( strpos( $path, ABSPATH ) !== false )
		return get_site_url( null, substr( $path, strlen( ABSPATH ) ) );

	return '';
}

function wpsc_locate_asset( $file ) {
	$paths = array(
		STYLESHEETPATH . '/wp-e-commerce/assets',
	);

	if ( is_child_theme() )
		$paths[] = TEMPLATEPATH . '/wp-e-commerce/assets';

	$paths[] = WPSC_TE_V2_ASSETS_PATH;

	return _wpsc_locate_stuff( $paths, $file, false, false );
}

function wpsc_locate_asset_uri( $file ) {
	$path = wpsc_locate_asset( $file );

	if ( strpos( $path, WP_CONTENT_DIR ) !== false )
		return content_url( substr( $path, strlen( WP_CONTENT_DIR ) ) );
	elseif ( strpos( $path, WP_PLUGIN_DIR ) !== false )
		return plugins_url( substr( $path, strlen( WP_PLUGIN_DIR ) ) );
	elseif ( strpos( $path, WPMU_PLUGIN_DIR ) !== false )
		return plugins_url( substr( $path, strlen( WP_PLUGIN_DIR ) ) );
	elseif ( strpos( $path, ABSPATH ) !== false )
		return get_site_url( null, substr( $path, strlen( ABSPATH ) ) );

	return '';
}

function _wpsc_locate_stuff( $paths, $files, $load = false, $require_once = true ) {
	$located = '';

	foreach ( (array) $files as $file ) {
		if ( ! $file )
			continue;

		foreach ( $paths as $path ) {
			if ( file_exists( $path . '/' . $file ) ) {
				$located = $path . '/' . $file;
				break 2;
			}
		}
	}

	if ( $load && '' != $located )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * See {@link wpsc_locate_theme_file()} for more information about how this works.
 *
 * @see   wpsc_locate_theme_file()
 * @since 4.0
 * @uses  load_template()
 * @uses  wpsc_locate_theme_file()
 *
 * @param  string|array $template_names Template files to search for, in order
 * @param  bool         $load           If true the template will be loaded if found
 * @param  bool         $require_once   Whether to use require_once or require. Default true. No effect if $load is false
 * @return string                       The template file name is located
 */
function wpsc_locate_template( $template_names, $load = false, $require_once = true ) {
	if ( $load && '' != $located )
		load_template( $located, $require_once );

	return $located;
}

function wpsc_locate_template_part( $files, $load = false, $require_once = true ) {
	$paths = array(
		STYLESHEETPATH . '/wp-e-commerce/template-part',
	);

	if ( is_child_theme() )
		$paths[] = TEMPLATEPATH . '/wp-e-commerce/template-parts';

	$paths[] = WPSC_TE_V2_VIEWS_PATH;

	return _wpsc_locate_stuff( $paths, $files, $load, $require_once );
}

function wpsc_locate_view_wrappers( $files, $load = false, $require_once = true ) {
	$paths = array(
		STYLESHEETPATH . '/wp-e-commerce',
	);

	if ( is_child_theme() )
		$paths[] = TEMPLATEPATH . '/wp-e-commerce';

	return _wpsc_locate_stuff( $paths, $files, $load, $require_once );
}

/**
 * This works just like get_template_part(), except that it uses wpsc_locate_template()
 * to search for the template part in 2 extra WP e-Commerce specific paths.
 *
 * @since 4.0
 * @see   get_template()
 * @see   wpsc_locate_theme_file()
 * @uses  apply_filters() Applies 'wpsc_get_template_part_paths_for_{$slug}' filter.
 * @uses  do_action()     Calls   'wpsc_get_template_part_{$slug}'           action.
 * @uses  do_action()     Calls   'wpsc_template_before_{$slug}-{$name}'     action.
 * @uses  do_action()     Calls   'wpsc_template_after_{$slug}-{$name}'      action.
 * @uses  wpsc_locate_template()
 *
 * @param  string $slug The slug name for the generic template.
 * @param  string $name The name of the specialised template. Optional. Default null.
 */
function wpsc_get_template_part( $slug = false, $name = null ) {
	if ( ! $slug ) {
		$controller = _wpsc_get_current_controller();
		$slug = $controller->view;
	}
	do_action( "wpsc_get_template_part_{$slug}", $slug, $name );

	$templates = array();
	if ( isset( $name ) ) {
		$templates[] =  "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	$templates = apply_filters( "wpsc_get_template_part_paths_for_{$slug}", $templates, $slug, $name );

	do_action( "wpsc_template_before_{$slug}-{$name}" );
	wpsc_locate_template_part( $templates, true, false );
	do_action( "wpsc_template_after_{$slug}-{$name}" );
}

/**
 * WPEC provides a way to separate all WPEC-related theme functions into a file called 'wpsc-functions.php'.
 * By providing a file named 'wpsc-functions.php', you can override the same function file of the parent
 * theme or that of the default theme engine that comes with WPEC.
 *
 * @since 4.0
 * @uses  get_stylesheet()
 * @uses  get_template()
 * @uses  get_theme_root()
 */
function _wpsc_action_after_setup_theme() {
	$theme_root = get_theme_root();
	$current_theme = get_stylesheet();
	$parent_theme = get_template();

	$paths = array(
		STYLESHEETPATH . '/wp-e-commerce',
	);

	if ( $current_theme != $parent_theme )
		$paths[] = TEMPLATEPATH . '/wp-e-commerce';

	foreach ( $paths as $path ) {
		$filename = $path . '/functions.php';
		if ( file_exists( $filename ) )
			require_once( $filename );
	}
}
add_action( 'after_setup_theme', '_wpsc_action_after_setup_theme' );

/**
 * Determine whether pagination is enabled for a certain position of the page.
 *
 * @since 4.0
 * @uses get_option() Gets 'use_pagination' option.
 * @uses wpsc_get_option() Gets WPEC 'page_number_postion' option.
 *
 * @param  string $position 'bottom', 'top', or 'both'
 * @return bool
 */
function wpsc_is_pagination_enabled( $position = 'bottom' ) {
	$pagination_enabled = wpsc_get_option( 'display_pagination' );
	if ( ! $pagination_enabled )
		return false;

	$pagination_position = wpsc_get_option( 'page_number_position' );
	if ( $pagination_position == WPSC_PAGE_NUMBER_POSITION_BOTH )
		return true;

	$id = WPSC_PAGE_NUMBER_POSITION_BOTTOM;
	if ( $position == 'top' )
		$id = WPSC_PAGE_NUMBER_POSITION_TOP;

	return ( $pagination_position == $id );
}

/**
 * Override the per page parameter to use WPEC own "products per page" option.
 *
 * @since 4.0
 * @uses  WP_Query::is_main_query()
 * @uses  wpsc_get_option()            Gets WPEC 'products_per_page' option.
 * @uses  wpsc_is_pagination_enabled()
 * @uses  wpsc_is_store()
 * @uses  wpsc_is_product_category()
 * @uses  wpsc_is_product_tag()
 *
 * @param  object $query
 */
function wpsc_action_set_product_per_page_query_var( $query ) {
	if ( is_single() )
		return;

	if ( wpsc_is_pagination_enabled() && $query->is_main_query() && ( wpsc_is_store() || wpsc_is_product_category() || wpsc_is_product_tag() ) )
		$query->query_vars['posts_per_archive_page'] = wpsc_get_option( 'products_per_page' );
}
add_action( 'pre_get_posts', 'wpsc_action_set_product_per_page_query_var', 10, 1 );

/**
 * Hook into 'post_class' filter to add custom classes to the current product in the loop.
 *
 * @since 4.0
 * @uses apply_filters() Applies 'wpsc_product_class' filter
 * @uses get_post() Gets the current post object
 * @uses wpsc_is_product_on_sale() Checks to see whether the current product is on sale
 * @uses $wpsc_query Global WPEC query object
 *
 * @param  array  $classes
 * @param  string $class
 * @param  int    $post_id
 * @return array  The filtered class array
 */
function wpsc_filter_product_class( $classes, $class, $post_id ) {
	if ( is_main_query() && ! $post_id )
		return $classes;

	$post = get_post( $post_id );
	if ( $post->post_type == 'wpsc-product' ) {
		$count     = isset( $wp_query->current_post ) ? (int) $wp_query->current_post : 1;
		$classes[] = $count % 2 ? 'even' : 'odd';
		if ( wpsc_is_product_on_sale( $post_id ) )
			$classes[] = 'wpsc-product-on-sale';

		return apply_filters( 'wpsc_product_class', $classes, $class, $post_id );
	}

	return $classes;
}
add_filter( 'post_class', 'wpsc_filter_product_class', 10, 3 );

/**
 * Properly replace permalink tags with product's name and product category.
 *
 * This function also takes into account two settings if $canonical is false: whether to prefix
 * product permalink with product category, and whether hierarchical product category URL is enabled.
 *
 * @access private
 * @since  4.0
 * @uses   apply_filters()        Applies 'wpsc_product_permalink_canonical' filter if $canonical is true.
 * @uses   apply_filters()        Applies 'wpsc_product_permalink' filter if $canonical is false.
 * @uses   get_option()           Gets 'permalink_structure' option.
 * @uses   get_query_var()        Gets the current "wpsc_product_category" context of the product.
 * @uses   get_term()             Gets the ancestor terms.
 * @uses   get_term_by()          Gets parent term so that we can recursively get the ancestors.
 * @uses   is_wp_error()
 * @uses   user_trailingslashit()
 * @uses   wp_get_object_terms()  Gets the product categories associated with the product.
 * @uses   wp_list_pluck()        Plucks only the "slug" of the categories array.
 * @uses   wpsc_get_option()      Gets 'hierarchical_product_category_url' option.
 *
 * @param  string $permalink
 * @param  object $post
 * @param  bool   $leavename
 * @param  bool   $sample
 * @param  bool   $canonical Whether to return a canonical URL or not
 * @return string
 */
function _wpsc_filter_product_permalink( $permalink, $post, $leavename, $sample, $canonical = false ) {
	// Define what to replace in the permalink
	$rewritecode = array(
		'%wpsc_product_category%',
		$leavename ? '' : '%wpsc-product%',
	);

	$category_slug = '';

	// only need to do this if a permalink structure is used
	$permalink_structure = get_option( 'permalink_structure' );

	if ( empty( $permalink_structure ) || $post->post_type != 'wpsc-product' || in_array( $post->post_status, array( 'draft', 'pending' ) ) )
		return $permalink;

	if ( strpos( $permalink, '%wpsc_product_category%' ) !== false ) {
		$category_slug = 'uncategorized';
		$categories    = wp_list_pluck( wp_get_object_terms( $post->ID, 'wpsc_product_category' ), 'slug' );

		// if there are multiple product categories, choose an appropriate one based on the current
		// product category being viewed
		if ( ! empty( $categories ) ) {
			$category_slug = $categories[0];
			$context       = get_query_var( 'wpsc_product_category' );
			if ( ! $canonical && $context && in_array( $context, $categories ) )
				$category_slug = $context;
		}

		// if hierarchical product category URL is enabled, we need to get the ancestors
		if ( ! $canonical && wpsc_get_option( 'hierarchical_product_category_url' ) ) {
			$term = get_term_by( 'slug', $category_slug, 'wpsc_product_category' );
			if ( is_object( $term ) ) {
				$ancestors = array( $category_slug );
				while ( $term->parent ) {
					$term = get_term( $term->parent, 'wpsc_product_category' );
					if ( in_array( $term->slug, $ancestors ) || is_wp_error( $term ) )
						break;
					$ancestors[] = $term->slug;
				}

				$category_slug = implode( '/', array_reverse( $ancestors ) );
			}
		}
	}

	$rewritereplace = array(
		$category_slug,
		$post->post_name,
	);

	$permalink = str_replace( $rewritecode, $rewritereplace, $permalink );
	$permalink = user_trailingslashit( $permalink, 'single' );

	if ( $canonical )
		return apply_filters( 'wpsc_product_permalink_canonical', $permalink, $post->ID );
	else
		return apply_filters( 'wpsc_product_permalink', $permalink, $post->ID );
}

/**
 * Return the canonical permalink of a product.
 *
 * This function is usually used inside a hook action.
 *
 * @since 4.0
 * @uses  _wpsc_filter_product_permalink()
 *
 * @param  string $permalink
 * @param  object $post
 * @param  bool   $leavename
 * @param  bool   $sample
 * @return string
 */
function wpsc_filter_product_permalink_canonical( $permalink, $post, $leavename, $sample ) {
	return _wpsc_filter_product_permalink( $permalink, $post, $leavename, $sample, true );
}

/**
 * Return the permalink of a product.
 *
 * This function is usually used inside a hook action.
 *
 * @since 4.0
 * @uses  _wpsc_filter_product_permalink()
 *
 * @param  string $permalink
 * @param  object $post
 * @param  bool   $leavename
 * @param  bool   $sample
 * @return string
 */
function wpsc_filter_product_permalink( $permalink, $post, $leavename, $sample ) {
	return _wpsc_filter_product_permalink( $permalink, $post, $leavename, $sample, false );
}
add_filter( 'post_type_link', 'wpsc_filter_product_permalink', 10, 4 );

/**
 * When hierarchical category url is enabled and wpsc_filter_product_permalink is attached to
 * 'post_type_link' filter hook, this function will make sure the resulting permalink scheme won't
 * return 404 errors.
 *
 * @since 4.0
 *
 * @param  array $q Query variable array
 * @return array
 */
function wpsc_filter_hierarchical_category_request( $q ) {
	if ( empty( $q['wpsc-product'] ) )
		return $q;

	// break down the 'wpsc-product' query var to get the current and parent node
	$components = explode( '/', $q['wpsc-product'] );
	if ( count( $components ) == 1 )
		return $q;
	$end_node    = array_pop( $components );
	$parent_node = array_pop( $components );

	// check to see if a post with the slug exists
	// if it doesn't then we're viewing a product category
	$posts = get_posts( array(
		'post_type' => 'wpsc-product',
		'name'      => $end_node,
	) );

	if ( ! empty( $posts ) ) {
		$q['wpsc-product'] = $q['name'] = $end_node;
		$q['wpsc_product_category'] = $parent_node;
	} else {
		$q['wpsc_product_category'] = $end_node;
		unset( $q['name'        ] );
		unset( $q['wpsc-product'] );
		unset( $q['post_type'   ] );
	}
	return $q;
}
if ( wpsc_get_option( 'hierarchical_product_category_url' ) )
	add_filter( 'request', 'wpsc_filter_hierarchical_category_request' );

/**
 * Make sure the canonical URL of a single product page is correct.
 *
 * When wpsc_filter_product_permalink() is attached to 'post_type_link', the side effect is that
 * canonical URL is not canonical any more because 'wpsc_product_category' query var is taken into
 * account.
 *
 * This function temporarily removes the original wpsc_filter_product_permalink() function from 'post_type_link'
 * hook, and replaces it with wpsc_filter_product_permalink_canonical().
 *
 * @since 4.0
 * @uses  add_filter() Restores wpsc_filter_product_permalink() to 'post_type_link' filter.
 * @uses  add_filter() Temporarily attaches wpsc_filter_product_permalink_canonical() to 'post_type_link' filter.
 * @uses  remove_filter() Removes wpsc_filter_product_permalink_canonical() from 'post_type_link' filter.
 * @uses  remove_filter() Temporarily removes wpsc_filter_product_permalink() from 'post_type_link' filter.
 */
function wpsc_action_rel_canonical() {
	remove_filter( 'post_type_link' , 'wpsc_filter_product_permalink'          , 10, 4 );
	add_filter   ( 'post_type_link' , 'wpsc_filter_product_permalink_canonical', 10, 4 );
	rel_canonical();
	remove_filter( 'post_type_link' , 'wpsc_filter_product_permalink_canonical', 10, 4 );
	add_filter   ( 'post_type_link' , 'wpsc_filter_product_permalink'          , 10, 4 );
}

/**
 * Make sure we fix the canonical URL of the single product. The canonical URL is broken when
 * single product permalink is prefixed by product category.
 *
 * @since 4.0
 * @uses  add_action()    Adds wpsc_action_rel_canonical() to 'wp_head' action hook.
 * @uses  is_singular()
 * @uses  remove_action() Removes rel_canonical() from 'wp_head' action hook.
 */
function _wpsc_action_canonical_url() {
	if ( is_singular( 'wpsc-product' ) ) {
		remove_action( 'wp_head', 'rel_canonical'             );
		add_action   ( 'wp_head', 'wpsc_action_rel_canonical' );
	}
}
add_action( 'wp', '_wpsc_action_canonical_url' );

/**
 * In case the display mode is set to "Show list of product categories", this function is hooked into
 * the filter inside wpsc_get_template_part() and returns paths to category list template instead of
 * the usual one.
 *
 * @since 4.0
 *
 * @param  array  $templates
 * @param  string $slug
 * @param  string $name
 * @return array
 */
function wpsc_get_category_list_template_paths( $templates, $slug, $name ) {
	$templates = array(
		'wp-e-commerce/archive-category-list.php',
		'wp-e-commerce/archive.php',
	);
	return $templates;
}

function _wpsc_filter_body_class( $classes ) {
	if ( ! wpsc_is_controller() )
		return $classes;

	$classes[] = 'wpsc-controller';
	$classes[] = 'wpsc-' . _wpsc_get_current_controller_name();

	return $classes;
}
add_filter( 'body_class', '_wpsc_filter_body_class' );

function _wpsc_filter_title( $title ) {
	if ( wpsc_is_controller() ) {
		$controller = _wpsc_get_current_controller();
		if (    is_post_type_archive( 'wpsc-product' )
			 || get_post_type() == 'page'
		)
			return $controller->title;
	}

	return $title;
}
add_filter( 'post_type_archive_title', '_wpsc_filter_title', 1 );
add_filter( 'single_post_title', '_wpsc_filter_title', 1 );

add_action( 'update_option_users_can_register', '_wpsc_action_flush_rewrite_rules' );

function _wpsc_action_remove_post_type_thumbnail_support() {
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'page', 'thumbnail' );
}

function _wpsc_action_check_thumbnail_support() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
		add_action( 'init', '_wpsc_action_remove_post_type_thumbnail_support' );
	}

	$crop = wpsc_get_option( 'crop_thumbnails' );

	add_image_size(
		'wpsc_product_single_thumbnail',
		get_option( 'single_view_image_width' ),
		get_option( 'single_view_image_height' ),
		$crop
	);

	add_image_size(
		'wpsc_product_archive_thumbnail',
		get_option( 'product_image_width' ),
		get_option( 'product_image_height' ),
		$crop
	);

	add_image_size(
		'wpsc_product_taxonomy_thumbnail',
		get_option( 'category_image_width' ),
		get_option( 'category_image_height' ),
		$crop
	);

	add_image_size( 'wpsc_product_cart_thumbnail', 64, 64, $crop );
}

add_action( 'after_setup_theme', '_wpsc_action_check_thumbnail_support', 99 );