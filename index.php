<?php
/* Copyright (C) Reelgood, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Douwe Bos <douwe@reelgood.com>, October 2019
 */

//TODO: camel case or snake case. pick one.

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://reelgood.com
 * @since             0.0.1
 * @package           Reelgood_Publishers_Widget
 *
 * @wordpress-plugin
 * Plugin Name:       Reelgood Publishers Widget
 * Description:       Add where to watch information and links for streaming for movies and TV shows to your posts. Includes support for affiliate codes.
 * Version:           0.0.8
 * Author:            Reelgood <douwe@reelgood.com>
 * Author URI:        https://www.reelgood.com
 * License:           CC BY-NC-ND 4.0
 * License URI:       https://creativecommons.org/licenses/by-nc-nd/4.0/
 * Text Domain:       reelgood_publishers_widget
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */


define( 'REELGOOD_DEVELOPMENT', false );
define( 'REELGOOD_PUBLISHERS_WIDGET_VERSION', '0.0.8' );
define( 'REELGOOD_JS_BUNDLE_VERSION', (REELGOOD_DEVELOPMENT ? 'development' : '0.0.1') );

define( 'REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL', 'https://assets.reelgood.com/publishers-widget/' . REELGOOD_JS_BUNDLE_VERSION . '/bundle.js');
define( 'REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL', 'https://assets.reelgood.com/publishers-widget/' . REELGOOD_JS_BUNDLE_VERSION . '/bundle.css');
define( 'REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY', get_option('reelgood_pub_wp_api_key'));

define('REELGOOD_PUBLISHERS_GATEWAY_API_URL', (REELGOOD_DEVELOPMENT ? 'https://api.staging.reelgood.com' : 'https://api.reelgood.com'));
define('REELGOOD_PUBLISHERS_WIDGET_API_URL_STAGING', 'https://staging-partner-api.reelgood.com/v1.0/widget/');
define('REELGOOD_PUBLISHERS_WIDGET_API_URL', 'https://partner-api.reelgood.com/v1.0/widget/');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/reelgood_publishers_widget_activator.php
 */
function activate_reelgood_publishers_widget() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/reelgood_publishers_widget_activator.php';
	Reelgood_Publishers_Widget_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/reelgood_publishers_widget_deactivator.php
 */
function deactivate_reelgood_publishers_widget() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/reelgood_publishers_widget_deactivator.php';
	Reelgood_Publishers_Widget_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/reelgood_publishers_widget_uninstall.php
 */
function uninstall_reelgood_publishers_widget() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/reelgood_publishers_widget_uninstall.php';
	Reelgood_Publishers_Widget_Uninstall::uninstall();
}

register_activation_hook( __FILE__, 'activate_reelgood_publishers_widget' );
register_deactivation_hook( __FILE__, 'deactivate_reelgood_publishers_widget' );
register_uninstall_hook( __FILE__, 'uninstall_reelgood_publishers_widget' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'reelgood_publishers_widget.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_reelgood_publishers_widget() {

	$plugin = new Reelgood_Publishers_Widget();
	$plugin->run();

}

run_reelgood_publishers_widget();
?>
