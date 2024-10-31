<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/includes
 * @author     Douwe Bos <douwe@reelgood.com>
 */
class Reelgood_Publishers_Widget_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
    delete_option('reelgood_pub_settings');
    delete_option('reelgood_pub_settings_fetch_time');

    require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_regex.php';
    Reelgood_Publishers_Widget_Regex::activateAllPosts();
	}
}
?>
