<?php
/**
 * Breaking News Manager
 *
 * @package    Breaking_News
 * @subpackage Breaking_News/inc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * This hook `breaking_news_loaded` triggers when WP has loaded all the plugins.
 * bns_on_plugins_loaded
 *
 * @return void
 */
function bns_on_plugins_loaded() {
	do_action( 'breaking_news_loaded' );
}
add_action( 'plugins_loaded', 'bns_on_plugins_loaded', -1 );


/**
 * WP init hook.
 *
 * @return void
 */
function bns_init() {
	bns_includes();
}
add_action( 'init', 'bns_init' );


/**
 * Include the required files used in backend and the frontend.
 *
 * @return void
 */
function bns_includes() {
	// Backend Files.
	require_once BNS_ABSPATH . '/inc/admin/class-bns-admin-options.php';
	require_once BNS_ABSPATH . '/inc/admin/class-bns-admin-metabox.php';
}


/**
 * Enqueue scripts for admin panel
 *
 * @return void
 */
function bns_admin_enqueue_scripts() {
	$screen = get_current_screen();

	if ( is_admin() && $screen && ( 'settings_page_bns-settings' === $screen->id || 'post' === $screen->id ) ) {
		// Add the color picker css file.
		wp_enqueue_style( 'wp-color-picker' );

		wp_register_script(
			'breakingnews',
			BNS_BASEPATH . 'assets/js/breaking-news.js',
			array( 'jquery', 'wp-color-picker' ),
			BNS_VERSION,
			true
		);
		wp_enqueue_script( 'breakingnews' );
	}
}
add_action( 'admin_enqueue_scripts', 'bns_admin_enqueue_scripts' );


/**
 * Get the breaking news if available
 *
 * @return WP_Post|null|false
 */
function bns_get_breaking_news() {
	$bns_options = (array) get_option( 'bns_breaking_news' );

	if ( isset( $bns_options['bns_is_active'] ) && 'yes' === $bns_options['bns_is_active'] && isset( $bns_options['post_id'] ) ) {
		if (
			isset( $bns_options['bns_is_expirable'] ) && 'yes' === $bns_options['bns_is_expirable'] && // Breaking news is expireable.
			isset( $bns_options['bns_expiry'] ) && '' !== $bns_options['bns_expiry'] // Check if expiry is available.
		) {

			$expiry_time  = strtotime( $bns_options['bns_expiry'] );
			$current_time = current_time( 'timestamp' );

			if ( $current_time > $expiry_time ) { // Breaking news expired.
				update_option( 'bns_breaking_news', array() );
				return false;
			}
		}
		return get_post( $bns_options['post_id'] );
	}

	return false;

}


/**
 * Breaking News HTML Print.
 *
 * @return void
 */
function bns_the_breaking_news() {
	$bns_options       = get_option( 'bns_options' );
	$mb_options        = get_option( 'bns_breaking_news' );
	$breaking_news     = bns_get_breaking_news();
	$bns_options_title = isset( $bns_options['title'] ) ? $bns_options['title'] : '';
	$background        = isset( $bns_options['background'] ) ? 'background:' . $bns_options['background'] . ';' : 'background:#1e73be;';
	$color             = isset( $bns_options['color'] ) ? 'color:' . $bns_options['color'] . ';' : 'color:#ffffff;';

	if ( $breaking_news && 'publish' === $breaking_news->post_status ) {
		$title = ( isset( $mb_options['bns_custom_title'] ) && '' !== trim( $mb_options['bns_custom_title'] ) ) ? $mb_options['bns_custom_title'] : $breaking_news->post_title;
		$link  = ( false === is_admin() ) ? get_the_permalink( $breaking_news->ID ) : get_edit_post_link( $breaking_news->ID );
		$style = ( false === is_admin() ) ? 'width:100%;position:fixed;' : 'width:95%;font-size:16px;';
		echo '
		<center>
			<div style="padding:10px;' . esc_attr( $style ) . esc_attr( $background ) . esc_attr( $color ) . '">' .
				esc_html( $bns_options_title ) . ': <a href="' . esc_url( $link ) . '" style="' . esc_attr( $color ) . '">' . esc_html( $title ) . '</a>
			</div>
		</center>';
	}
}
add_action( 'wp_body_open', 'bns_the_breaking_news' );
