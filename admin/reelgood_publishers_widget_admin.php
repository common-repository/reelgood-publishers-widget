<?php
/* Copyright (C) Reelgood, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Douwe Bos <douwe@reelgood.com>, October 2019
 */

/**
 * Functionality related to the admin settings panel.
 *
 * @package    Reelgood_Publishers_Widget
 * @author     Douwe Bos <douwe@reelgood.com>
 * @since      0.0.1
 * @license    CC BY-NC-ND 4.0
 * @copyright  Copyright (c) 2019, Reelgood Inc
 */
class Reelgood_Publishers_Widget_Admin {
  /**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version     The version of this plugin.
	 */
  public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_action( 'init', array( $this, 'reelgood_init' ) );
		add_action( 'admin_menu', array( $this, 'reelgood_add_menu_page' ) );
  }
  
  function reelgood_init() {
		include plugin_dir_path(__FILE__) . 'actions/user_settings_actions.php';
		include plugin_dir_path(__FILE__) . 'actions/styling_settings_actions.php';
		include plugin_dir_path(__FILE__) . 'actions/service_settings_actions.php';
		include plugin_dir_path(__FILE__) . 'actions/support_settings_actions.php';
  }

  function reelgood_add_menu_page() {
		$page_title = 'Reelgood Publishers Widget';
		$menu_title = 'Reelgood';
		$capability = 'manage_options';
		$slug       = 'reelgood-publishers-widget';
		$callback   = array( $this, 'settings_page_content' );
		$icon       = REELGOOD_PUBLISHERS_WIDGET_DIR_URL . 'images/reelgood_logo@1x.png';
		$position   = 100;

		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
  }

  public function settings_page_content() {
		include plugin_dir_path(__FILE__) . 'pages/settings_page.php';	
  }

	/**
	 * Register the CSS for the admin editor side of the site.
	 *
	 * @since    0.0.1
	 */
  public function enqueue_styles() {
		$all_styles = array(
			'admin_account_settings_styles',
			'admin_main_styles',
			'admin_main_popup_styles',
			'admin_service_settings_styles',
			'admin_styling_settings_edit_styles',
			'admin_styling_settings_reset_styles',
			'admin_styling_settings_styles',
			'admin_support_settings_styles'
		);

		foreach ($all_styles as $key => $style) {
			wp_enqueue_style('reelgood_pub_' . $style, plugins_url('/styles/' . $style . '.css?rand='.rand(0,10000), __FILE__));
		}

		wp_enqueue_style('reelgood-bundle-css', REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL . '?rand='.rand(0,10000));
  }

	/**
	 * Register the JavaScript for the admin editor side of the site.
	 *
	 * @since    0.0.1
	 */
  public function enqueue_scripts() {
		wp_enqueue_script(
			'reelgood_pub_js_admin_jquery.debounce-1.0.5',
			plugins_url('/js/jquery.debounce-1.0.5.js', __FILE__),
			array('jquery'),
			'1.0'
		);

		wp_enqueue_script(
			'reelgood_pub_js_admin_sortablejs',
			plugins_url('/js/sortablejs.js', __FILE__),
			array('jquery', 'jquery-ui-sortable'),
			'1.0'
		);

		wp_enqueue_script(
			'reelgood_pub_js_admin_svg_min',
			plugins_url('/js/svg.min.js', __FILE__),
			array('jquery'),
			'1.0'
		);

		wp_enqueue_script(
			'reelgood_pub_js_global',
			plugins_url('/js/global.js?rand='.rand(0,10000), __FILE__),
			array('jquery'),
			'1.0'
		);

		wp_localize_script(
			'reelgood_pub_js_global',
			'rgajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_localize_script(
			'reelgood_pub_js_global',
			'rgcontext',
			array(
				'location' => REELGOOD_PUBLISHERS_WIDGET_DIR_URL
			)
		);

		wp_localize_script(
			'reelgood_pub_js_global',
			'rgbundle',
			array(
				'api_key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
				'js' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL.'?rand='.rand(0,10000),
				'css' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL.'?rand='.rand(0,10000),
				'dev_env' => REELGOOD_DEVELOPMENT ? 'reelgood_bool_on' : 'reelgood_bool_off'
			)
		);

		$all_localized_scripts = array(
			'settings_page_account_listeners',
			'edit_settings_popup',
			'settings_page_service_listeners',
			'settings_page_support_listeners',
			'settings_page_styling_listeners',
			'settings_page_account_listeners'
		);

		foreach ($all_localized_scripts as $key => $script) {
			wp_enqueue_script(
				'reelgood_pub_js_' . $script,
				plugins_url('/js/' . $script . '.js?rand='.rand(0,10000), __FILE__),
				array('jquery', 'jquery-ui-sortable'),
				'1.0'
			);
			
			wp_localize_script(
				'reelgood_pub_js_' . $script,
				'rgajax',
				array(
					'url'   => admin_url( 'admin-ajax.php' ),
				)
			);
	
			wp_localize_script(
				'reelgood_pub_js_' . $script,
				'rgbundle',
				array(
					'api_key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
					'js' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL.'?rand='.rand(0,10000),
					'css' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL.'?rand='.rand(0,10000),
					'gateway' => REELGOOD_PUBLISHERS_GATEWAY_API_URL,
					'dev_env' => REELGOOD_DEVELOPMENT ? 'reelgood_bool_on' : 'reelgood_bool_off'
				)
			);

			wp_localize_script(
				'reelgood_pub_js_' . $script,
				'rgcontext_admin',
				array(
					'location' => plugin_dir_url( __FILE__ )
				)
			);
		}
  }
}
?>
