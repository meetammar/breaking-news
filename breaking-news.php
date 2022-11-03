<?php
/**
 * Plugin Name: Breaking News
 * Plugin URI: https://www.toptal.com/
 * Description: The plugin helps you mark any Post as Breaking News easily.
 * Version: 1.0
 * Author: Muhammad Ammar Ilyas
 * Author URI: https://www.toptal.com/
 * Text Domain: breakingnews
 *
 * @package Breaking_News
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BNS_BASENAME' ) ) {
	define( 'BNS_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'BNS_ABSPATH' ) ) {
	define( 'BNS_ABSPATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BNS_BASEPATH' ) ) {
	define( 'BNS_BASEPATH', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'BNS_VERSION' ) ) {
	define( 'BNS_VERSION', 1.0 );
}

// Load core packages and the autoloader.
require BNS_ABSPATH . '/inc/bns-loader.php';
