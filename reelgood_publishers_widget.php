<?php
/**
 * Core plugin class. Registers all the separate parts (public, editor, admin) etc.
 *
 *
 * @link       https://reelgood.com
 * @since      0.0.1
 *
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/includes
 */

/**
 * Core plugin class. Registers all the separate parts (public, editor, admin) etc.
 *
 *
 * @since      0.0.1
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/includes
 * @author     Douwe Bos <douwe@reelgood.com>
 */
class Reelgood_Publishers_Widget {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Reelgood_Publishers_Widget_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'REELGOOD_PUBLISHERS_WIDGET_VERSION' ) ) {
			$this->version = REELGOOD_PUBLISHERS_WIDGET_VERSION;
		} else {
			$this->version = '0.0.1';
		}

		if ( ! defined( 'REELGOOD_PUBLISHERS_WIDGET_DIR_PATH' ) ) {
			define( 'REELGOOD_PUBLISHERS_WIDGET_DIR_PATH', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'REELGOOD_PUBLISHERS_WIDGET_PLUGIN_PATH' ) ) {
			define( 'REELGOOD_PUBLISHERS_WIDGET_PLUGIN_PATH', plugin_basename( __FILE__ ) );
		}

		if ( ! defined( 'REELGOOD_PUBLISHERS_WIDGET_DIR_URL' ) ) {
			define( 'REELGOOD_PUBLISHERS_WIDGET_DIR_URL', plugin_dir_url( __FILE__ ) );
		}

		if ((get_option('reelgood_pub_settings') === false || 
			(
				get_option('reelgood_pub_settings_fetch_time') === false || 
				(
					//TODO: Set cache time back to a sensible time
					get_option('reelgood_pub_settings_fetch_time') !== false && ((time() - get_option('reelgood_pub_settings_fetch_time')) > 60 * 60 * 6) // 60 secs * 60 mins * 6 hours
				)
			)
		)) {
      		error_log('Fetch Widget Settings Admin Construct');
			$request = wp_remote_get('https://assets.reelgood.com/publishers-widget/' . REELGOOD_JS_BUNDLE_VERSION . '/settings/wordpress_settings.json?rand='.rand(0,10000), array(
				'headers' => array(
					'cache-control' => 'no-cache',
					'x-api-key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
					'origin' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"
				),
			));
		
			$response = wp_remote_retrieve_body( $request );
			$response_code = wp_remote_retrieve_response_code( $request );

			if ($response_code >=200 && $response_code < 300 && $response != NULL) {
				update_option('reelgood_pub_settings', $response);
				update_option('reelgood_pub_settings_fetch_time', time());
			}
		}

		$this->plugin_name = 'reelgood-publishers-widget';

		$this->load_dependencies();
  		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Reelgood_Publishers_Widget_Loader. Orchestrates the hooks of the plugin.
	 * - Reelgood_Publishers_Widget_i18n. Defines internationalization functionality.
	 * - Reelgood_Publishers_Widget_Admin. Defines all hooks for the admin area.
	 * - Reelgood_Publishers_Widget_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH. 'admin/reelgood_publishers_widget_admin.php';

		require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH. 'admin/reelgood_publishers_widget_editor.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH. 'public/reelgood_publishers_widget_public.php';



		$this->loader = new Reelgood_Publishers_Widget_Loader();

	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Reelgood_Publishers_Widget_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'elementor/editor/before_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'elementor/editor/before_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Reelgood_Publishers_Widget_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
    	$this->loader->add_action('wp_footer', $plugin_public, 'footer');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Reelgood_Publishers_Widget_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
