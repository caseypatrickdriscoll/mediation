<?php
/**
 * Utility Pro.
 *
 * @package      Utility_Pro
 * @link         http://www.carriedils.com/utility-pro
 * @author       Carrie Dils
 * @copyright    Copyright (c) 2015, Carrie Dils
 * @license      GPL-2.0+
 */

// Load internationalization components.
// English users do not need to load the text domain and can comment out or remove.
load_child_theme_textdomain( 'utility-pro', get_stylesheet_directory() . '/languages' );

// This file loads the Google fonts used in this theme.
require get_stylesheet_directory() . '/includes/google-fonts.php';

// This file contains search form improvements.
require get_stylesheet_directory() . '/includes/class-search-form.php';

add_action( 'genesis_setup', 'utility_pro_setup', 15 );
/**
 * Theme setup.
 *
 * Attach all of the site-wide functions to the correct hooks and filters. All
 * the functions themselves are defined below this setup function.
 *
 * @since 1.0.0
 */
function utility_pro_setup() {

	define( 'CHILD_THEME_NAME', 'utility-pro' );
	define( 'CHILD_THEME_URL', 'https://store.carriedils.com/utility-pro' );
	define( 'CHILD_THEME_VERSION', '1.0.0' );

	// Add HTML5 markup structure.
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );

	// Add viewport meta tag for mobile browsers.
	add_theme_support( 'genesis-responsive-viewport' );

	// Add support for custom background.
	add_theme_support( 'custom-background', array( 'wp-head-callback' => '__return_false' ) );

	// Add support for three footer widget areas.
	add_theme_support( 'genesis-footer-widgets', 3 );


	// Add support for structural wraps (all default Genesis wraps unless noted).
	add_theme_support(
		'genesis-structural-wraps',
		array(
			'footer',
			'footer-widgets',
			'footernav',    // Custom.
			'menu-footer',  // Custom.
			'header',
			'home-gallery', // Custom.
			'nav',
			'site-inner',
			'site-tagline',
		)
	);

	// Add support for two navigation areas (theme doesn't use secondary navigation).
	add_theme_support(
		'genesis-menus',
		array(
			'primary' => __( 'Primary Navigation Menu', 'utility-pro' ),
			'footer'  => __( 'Footer Navigation Menu', 'utility-pro' ),
		)
	);

	// Add custom image sizes.
	add_image_size( 'feature-large', 960, 330, true );

	// Unregister secondary sidebar.
	unregister_sidebar( 'sidebar-alt' );

	// Unregister layouts that use secondary sidebar.
	genesis_unregister_layout( 'content-sidebar-sidebar' );
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );

	// Register the default widget areas.
	utility_pro_register_widget_areas();

	// Add Utility Bar above header.
	add_action( 'genesis_before_header', 'utility_pro_add_bar' );

	// Add featured image above posts.
	add_filter( 'the_content', 'utility_pro_featured_image' );

	// Add a navigation area above the site footer.
	add_action( 'genesis_before_footer', 'utility_pro_do_footer_nav' );

	// Remove Genesis archive pagination (Genesis pagination settings still apply).
	remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );

	// Add WordPress archive pagination (accessibility).
	add_action( 'genesis_after_endwhile', 'utility_pro_post_pagination' );

	// Load accesibility components if the Genesis Accessible plugin is not active.
	if ( ! utility_pro_genesis_accessible_is_active() ) {

		// Load skip links (accessibility).
		include get_stylesheet_directory() . '/includes/skip-links.php';
	}

	// Apply search form enhancements (accessibility).
	add_filter( 'get_search_form', 'utility_pro_get_search_form', 25 );

	// Load files in admin.
	if ( is_admin() ) {

		// Add suggested plugins nag.
		include get_stylesheet_directory() . '/includes/suggested-plugins.php';

		// Add theme license (don't remove, unless you don't want theme support).
		include get_stylesheet_directory() . '/includes/theme-license.php';
	}
}

/**
 * Add Utility Bar above header.
 *
 * @since 1.0.0
 */
function utility_pro_add_bar() {

	genesis_widget_area( 'utility-bar', array(
		'before' => '<div class="utility-bar"><div class="wrap">',
		'after'  => '</div></div>',
	) );
}

/**
 * Add featured image above single posts.
 *
 * Outputs image as part of the post content, so it's included in the RSS feed.
 * H/t to Robin Cornett for the suggestion of making image available to RSS.
 *
 * @since 1.0.0
 *
 * @param string $content Post content.
 *
 * @return null|string Return early if not a single post or there is no thumbnail.
 *                     Image and content markup otherwise.
 */
function utility_pro_featured_image( $content ) {

	if ( ! is_singular( 'post' ) || ! has_post_thumbnail() ) {
		return $content;
	}

	$image = '<div class="featured-image">';
	$image .= get_the_post_thumbnail( get_the_ID(), 'feature-large' );
	$image .= '</div>';

	return $image . $content;
}

add_filter( 'genesis_footer_creds_text', 'utility_pro_footer_creds' );
/**
 * Change the footer text.
 *
 * @since  1.0.0
 *
 * @param string $creds Existing credentials.
 *
 * @return string Footer credentials, as shortcodes.
 */
function utility_pro_footer_creds( $creds ) {

	return '[footer_copyright first="2015"] Charlie Rowan, Mediation from the Heart <br/><img src="wp-content/themes/mediation/images/IACP-Logo.png" height="100px" width="125px"><br/>Website Design by <a href="designtlc.com">Design TLC</a>.<div class="disclaimer"><p>DISCLAMER: Although I am a licensed attorney, the information at this website and Mediation From The Heart® services are neither legal advice nor legal services. No attorney-client relationship with me is created in connection with the provision of these services.</p><p>The content of this website is neither legal advice nor legal information. Use of this website, including contacting Charlie Rowan or using the Mediation From The Heart® process, does not create an attorney-client relationship. If you contact me through links or features of this website or send me electronic communications, your communication will not create an attorney-client relationship, will not be treated as privileged, and will not be assured of confidentiality. You should not send sensitive or confidential information via this website or email. As the Internet is not necessarily a secure environment, it is possible that your message will be intercepted and read by persons without your knowledge or consent.</p></div>';
}

add_filter( 'genesis_author_box_gravatar_size', 'utility_pro_author_box_gravatar_size' );
/**
 * Customize the Gravatar size in the author box.
 *
 * @since 1.0.0
 *
 * @param int $size Existing pixel size of gravatar.
 *
 * @return int Pixel size of gravatar.
 */
function utility_pro_author_box_gravatar_size( $size ) {
	return 96;
}

// Add theme widget areas.
include get_stylesheet_directory() . '/includes/widget-areas.php';

// Add footer navigation components.
include get_stylesheet_directory() . '/includes/footer-nav.php';

// Add scripts to enqueue.
include get_stylesheet_directory() . '/includes/enqueue-assets.php';

// Miscellaenous functions used in theme configuration.
include get_stylesheet_directory() . '/includes/theme-config.php';


add_action( 'pre_get_posts', 'be_exclude_category_from_blog' );
/**
 * Exclude Category from Blog
 * 
 * @author Bill Erickson
 * @link http://www.billerickson.net/customize-the-wordpress-query/
 * @param object $query data
 *
 */
function be_exclude_category_from_blog( $query ) {
	
	if( $query->is_main_query() && $query->is_home() ) {
		$query->set( 'cat', '-2 -3' );
	}

}