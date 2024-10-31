<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://reelgood.com
 * @since      0.0.1
 *
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/public
 * @author     Douwe Bos <douwe@reelgood.com>
 */
class Reelgood_Publishers_Widget_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
  }

	public function footer() {
		echo '<script id="reelgood-js-widget-bundle" src="' . REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL . '" data-api-key="' . REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY . '" async="true" ' . (REELGOOD_DEVELOPMENT ? 'data-env="development"' : '') . '></script>';
	}

	public function enqueue_styles() {
		wp_enqueue_style('reelgood-bundle-css', REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL);
	}
}
