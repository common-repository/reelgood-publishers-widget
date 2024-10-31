<?php
/* Copyright (C) Reelgood, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Douwe Bos <douwe@reelgood.com>, October 2019
 */

/**
 * Functionality related to the admin TinyMCE editor.
 *
 * @package    Reelgood_Publishers_Widget
 * @author     Douwe Bos <douwe@reelgood.com>
 * @since      0.0.1
 * @license    CC BY-NC-ND 4.0
 * @copyright  Copyright (c) 2019, Reelgood Inc
 */
class Reelgood_Publishers_Widget_Admin_Editor {

	/**
	 * Primary class constructor.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'reelgood_init' ) );
        add_action( 'media_buttons', array( $this, 'media_button' ), 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ));
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ));
        
        add_action('elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_styles' ));
        add_action('elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_scripts' ));
	}
	
	function reelgood_init() {
    include plugin_dir_path(__FILE__) . 'actions/search_actions.php';
  }

	/**
	 * Register the CSS for the adming editor side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style('reelgood_pub_editor_add_widget_styles', plugins_url('/styles/editor_add_widget_styles.css?rand='.rand(0,10000), __FILE__));
		wp_enqueue_style('reelgood_pub_editor_edit_widget_instance', plugins_url('/styles/editor_edit_widget_instance.css?rand='.rand(0,10000), __FILE__));
		wp_enqueue_style('reelgood_pub_admin_main_popup_styles', plugins_url('/styles/admin_main_popup_styles.css?rand='.rand(0,10000), __FILE__));
  }

  /**
	 * Register the JavaScript for the admin editor side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'reelgood_pub_js_global',
			plugin_dir_url( __FILE__ ) . 'js/global.js?rand='.rand(0,10000),
			array( 'jquery' ),
			1.0
        );
        
        wp_enqueue_script(
			'reelgood_pub_js_admin_jquery.debounce-1.0.5',
			plugins_url('/js/jquery.debounce-1.0.5.js', __FILE__),
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
    
        wp_enqueue_script(
			'reelgood_pub_js_editor_render_widget',
			plugin_dir_url( __FILE__ ) . 'js/editor_render_widget.js?rand='.rand(0,10000),
			array( 'jquery' ),
			1.0
		);

		wp_localize_script(
			'reelgood_pub_js_editor_render_widget',
			'rgajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_localize_script(
			'reelgood_pub_js_editor_render_widget',
			'rgbundle',
			array(
				'api_key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
				'js' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL.'?rand='.rand(0,10000),
				'css' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL.'?rand='.rand(0,10000),
				'dev_env' => REELGOOD_DEVELOPMENT ? 'reelgood_bool_on' : 'reelgood_bool_off'
			)
		);

		wp_localize_script(
			'reelgood_pub_js_editor_render_widget',
			'rgcontext_editor',
			array(
				'location' => plugin_dir_url( __FILE__ ),
				'can_edit_styling' => json_encode(
					user_can( get_current_user_id(), 'administrator' ) || !(get_option('reelgood_pub_wp_require_admin_edit_styling', 'reelgood_bool_false') === 'reelgood_bool_on')
				)
			)
		);

		wp_enqueue_script(
			'reelgood_pub_js_edit_settings_popup',
			plugins_url('/js/edit_settings_popup.js?rand='.rand(0,10000), __FILE__),
			array('jquery'),
			'1.0'
		);
    
    wp_localize_script(
			'reelgood_pub_js_edit_settings_popup',
			'rgajax',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_localize_script(
			'reelgood_pub_js_edit_settings_popup',
			'rgbundle',
			array(
				'api_key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
				'js' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL.'?rand='.rand(0,10000),
				'css' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL.'?rand='.rand(0,10000),
				'dev_env' => REELGOOD_DEVELOPMENT ? 'reelgood_bool_on' : 'reelgood_bool_off'
			)
		);

		wp_localize_script(
			'reelgood_pub_js_edit_settings_popup',
			'rgcontext_editor',
			array(
				'location' => plugin_dir_url( __FILE__ ),
				'can_edit_styling' => json_encode(
					user_can( get_current_user_id(), 'administrator' ) || !(get_option('reelgood_pub_wp_require_admin_edit_styling', 'reelgood_bool_false') === 'reelgood_bool_on')
				)
			)
		);
	}

	/**
	 * Allow easy shortcode insertion via a custom media button.
	 *
	 * @since 0.0.1
	 *
	 * @param string $editor_id
	 */
	public function media_button( $editor_id ) {
		if ( ! apply_filters( is_admin(), $editor_id ) ) {
			return;
		}

		if (! REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY) {
			return;
		}

		$icon = '<span class="wp-media-buttons-icon" style="font-size:16px;margin-top:-2px;"><svg width="18px" height="18px" viewBox="0 0 60 60">
		<defs>
			<radialGradient cx="42.6890365%" cy="54.3550515%" fx="42.6890365%" fy="54.3550515%" r="117.629891%" gradientTransform="translate(0.426890,0.543551),scale(1.000000,0.865385),rotate(44.100468),translate(-0.426890,-0.543551)" id="radialGradient-5">
				<stop stop-color="#66FFC5" offset="0%" />
				<stop stop-color="#00E08C" offset="100%" />
			</radialGradient>
			<radialGradient cx="42.6890365%" cy="54.3550515%" fx="42.6890365%" fy="54.3550515%" r="117.571086%" gradientTransform="translate(0.426890,0.543551),scale(1.000000,0.866279),rotate(44.070888),translate(-0.426890,-0.543551)" id="radialGradient-6">
				<stop stop-color="#66FFC5" offset="0%" />
				<stop stop-color="#00E08C" offset="100%" />
			</radialGradient>
		</defs>
		<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			<mask id="mask-2" fill="white">
				<path d="M17.0895772,-1.59727351e-15 L42.9104228,6.90439374e-16 C48.8528411,-4.0116516e-16 51.0077056,0.618729126 53.1801649,1.78057308 C55.3526242,2.94241704 57.057583,4.64737582 58.2194269,6.81983513 C59.3812709,8.99229444 60,11.1471589 60,17.0895772 L60,42.9104228 C60,48.8528411 59.3812709,51.0077056 58.2194269,53.1801649 C57.057583,55.3526242 55.3526242,57.057583 53.1801649,58.2194269 C51.0077056,59.3812709 48.8528411,60 42.9104228,60 L17.0895772,60 C11.1471589,60 8.99229444,59.3812709 6.81983513,58.2194269 C4.64737582,57.057583 2.94241704,55.3526242 1.78057308,53.1801649 C0.618729126,51.0077056 7.9434019e-16,48.8528411 -1.36712706e-15,42.9104228 L4.60292916e-16,17.0895772 C-2.6744344e-16,11.1471589 0.618729126,8.99229444 1.78057308,6.81983513 C2.94241704,4.64737582 4.64737582,2.94241704 6.81983513,1.78057308 C8.99229444,0.618729126 11.1471589,9.28061909e-16 17.0895772,-1.59727351e-15 Z" id="path-1" />
			</mask>
			<g id="Mask" />
			<g id="Icon" mask="url(#mask-2)">
				<rect fill="#081118" id="path-4" x="0" y="0" width="60" height="60" />
				<linearGradient fill-opacity="0.16" x1="0%" y1="6.24500451e-15%" x2="101.999998%" y2="100.999999%" id="linearGradient-3">
					<stop stop-color="#79CFF9" offset="0%" />
					<stop stop-color="#71FF97" offset="100%" />
				</linearGradient>
				<g id="Group" stroke-width="1" transform="translate(11.718750, 12.539062)" stroke="#00DC89">
					<path d="M28.3256399,22.8632481 C30.6380893,21.9459449 33.0159854,19.5855904 33.0159854,15.5708141 C33.0159854,10.6737005 29.5684945,7.3828125 24.4380438,7.3828125 L21.669997,7.3828125 L21.669997,13.0327951 L23.5018717,13.0327951 C24.8753035,13.0327951 26.265176,13.8801664 26.265176,15.4989923 C26.265176,17.1178181 24.8753035,17.9651895 23.5018717,17.9651895 L18.8304963,17.9651895 L18.8304963,14.4500158 L12.1875,18.0370113 L12.1875,31.7578125 L18.8304963,31.7578125 L18.8304963,23.651083 L21.6339539,23.651083 L25.6663547,31.7578125 L33.28125,31.7578125 L28.3256399,22.8632481 Z" id="Fill-10" stroke-width="0.5" fill="url(#radialGradient-5)" />
					<path d="M0,0 L17.4609375,10.078125 L0,20.15625 L0,0 Z M2.37175581,9.28776412 L3.97025684,9.28776412 L3.97025684,13.2874501 L11.0253402,9.10127801 L2.37175581,4.10647446 L2.37175581,9.28776412 Z" id="Fill-12" stroke-width="0.5" fill="url(#radialGradient-6)" />
				</g>
			</g>
		</g>
	</svg></span>';

		printf(
			'<button type="button" id="insert-reelgood-widget-button" class="button" data-editor="%s" title="%s">%s %s</button>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Where to Watch'),
			$icon,
			__( 'Add Where to Watch')
    );
    
		wp_enqueue_script(
			'reelgood_pub_js_editor_add_widget',
			plugin_dir_url( __FILE__ ) . 'js/editor_add_widget.js?rand='.rand(0,10000),
			array( 'jquery' ),
			1.0
    );

    wp_localize_script(
			'reelgood_pub_js_editor_add_widget',
			'rgajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_localize_script(
			'reelgood_pub_js_editor_add_widget',
			'rgcontext',
			array(
				'location' => REELGOOD_PUBLISHERS_WIDGET_DIR_URL
			)
		);

		wp_localize_script(
			'reelgood_pub_js_editor_add_widget',
			'rgbundle',
			array(
				'api_key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
				'js' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL.'?rand='.rand(0,10000),
				'css' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_CSS_URL.'?rand='.rand(0,10000),
				'dev_env' => REELGOOD_DEVELOPMENT ? 'reelgood_bool_on' : 'reelgood_bool_off'
			)
		);
	}
}

new Reelgood_Publishers_Widget_Admin_Editor;
