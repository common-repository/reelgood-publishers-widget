<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/includes
 * @author     Douwe Bos <douwe@reelgood.com>
 */
class Reelgood_Publishers_Widget_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function deactivate() {
    require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_regex.php';
    Reelgood_Publishers_Widget_Regex::deactivateAllPosts();
	}
}
?>
