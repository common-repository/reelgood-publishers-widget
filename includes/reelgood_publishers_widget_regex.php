<?php
/**
 * Fired during plugin life cycle and updating all settings.
 *
 * This class defines all code necessary to run change all widget instances in previous posts.
 *
 * @since      0.0.1
 * @package    Reelgood_Publishers_Widget
 * @subpackage Reelgood_Publishers_Widget/includes
 * @author     Douwe Bos <douwe@reelgood.com>
 */
class Reelgood_Publishers_Widget_Regex {
	static $entireDiv = '/(<div [^<>]*?data-widget-host="reelgood-pub(?:-deactivated)?"[^<>]*?>[^<>]*?(?:(?:<a href="https:\/\/www.reelgood.com\/(?:movie|show){1}\/[^<>]*?"[^<>]*?>[^<>]*?<\/a>)?|(?:<script type="text\/props">[^<>]*?<\/script>)?)[^<>]*?<\/div>)/';
  static $inlineProps = '/data-prop-props="({[^<>]*?})"/';
  static $scriptProps = '/<script type="text\/props">[\n	 ]*?({[^<>]*?})[\n	 ]*?<\/script>/';

  public static function sanitizeSettingsString($input) {
    $res = $input;
    $res = preg_replace('/"reelgood_bool_off"/', 'false', $res);
    $res = preg_replace('/"reelgood_bool_on"/', 'true', $res);
    return $res;
  }

  private static function getPostContentFromPost($id) {
    return apply_filters('the_content', get_post($id)->post_content);
  }

  private static function getWidgetInstancesFromPost($id) {
    $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($id);
    $matches = array();

    if (strpos($postContent, 'data-widget-host="reelgood-pub') !== false) {
      preg_match_all(
        Reelgood_Publishers_Widget_Regex::$entireDiv,
        $postContent,
        $matches
      );

      error_log('Found '.count($matches[1]).' widget instances in post index '.$id);
    }

    return $matches[1];
  }

  /**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
  public static function getAllWidgetPostIDs() {
    $allPostIDs = get_posts(array(
      'numberposts' => -1,
      'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'trash'),
      'post_type' => get_post_types('', 'names'),
      'fields'          => 'ids' // Only get post IDs
    ));

    $widgetPostIDs = array();

    foreach ($allPostIDs as $postKey => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);

      if (strpos($postContent, 'data-widget-host="reelgood-pub') !== false) {
        array_push($widgetPostIDs, $postID);
      }
    }

    return $widgetPostIDs;
  }

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.2
	 */
	public static function activateAllPosts() {
    $widgetPostIDs = Reelgood_Publishers_Widget_Regex::getAllWidgetPostIDs();
    
    foreach ($widgetPostIDs as $index => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);
      $postContent = str_replace('data-widget-host="reelgood-pub-deactivated"', 'data-widget-host="reelgood-pub"', $postContent);
      $postContent = str_replace('style="display: none;"', 'style="display: flex;"', $postContent);

      wp_update_post(
        array(
          'ID' => $postID,
          'post_content' => $postContent
        )
      );
    }
	}

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.2
	 */
	public static function deactivateAllPosts() {
    $widgetPostIDs = Reelgood_Publishers_Widget_Regex::getAllWidgetPostIDs();
    
    foreach ($widgetPostIDs as $index => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);
      $postContent = str_replace('data-widget-host="reelgood-pub"', 'data-widget-host="reelgood-pub-deactivated"', $postContent);
      $postContent = str_replace('style="display: flex;"', 'style="display: none;"', $postContent);

      wp_update_post(
        array(
          'ID' => $postID,
          'post_content' => $postContent
        )
      );
    }
	}

  /**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
  public static function updateAllAppendedSettings() {
    $widgetPostIDs = Reelgood_Publishers_Widget_Regex::getAllWidgetPostIDs();
    $newSettings = reelgoodGetUserGlobalAppendedSettingsDeep();

    error_log('Updating All Global Settings For '.count($widgetPostIDs).' Widget Posts');

    foreach ($widgetPostIDs as $index => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);
      $instances = Reelgood_Publishers_Widget_Regex::getWidgetInstancesFromPost($postID);

      error_log('Found '.count($instances).' widget instances in post index '.$index);

      foreach ($instances as $index => $widgetInstance) {
        $inlineSettings = array();
        preg_match_all( 
          Reelgood_Publishers_Widget_Regex::$inlineProps,
          $widgetInstance,
          $inlineSettings
        );

        foreach ( $inlineSettings[1] as $key => $foundInlineSettings) {
          $parsed = json_decode(str_replace('\'', '"', $foundInlineSettings), true);
          $oldParsedSettings = array_key_exists('settings', $parsed) ? $parsed['settings'] : array();
          $newParsedSettings = array_merge($oldParsedSettings, $newSettings);
          $newParsedSettings = str_replace("\n", "", str_replace('"', '\'', Reelgood_Publishers_Widget_Regex::sanitizeSettingsString(json_encode($newParsedSettings))));

          error_log('Found inline settings: '.json_encode($parsed));
          $replacingInlineSettings = '{\'content_type\': \'' . $parsed['content_type'] . '\',\'id\': \'' . $parsed['id'] . '\',\'id_type\': \'' . $parsed['id_type'] . '\', \'settings\': ' . $newParsedSettings . '}';
          $postContent = str_replace($foundInlineSettings, $replacingInlineSettings, $postContent);
        }

        $scriptSettings = array();
        preg_match_all(
          Reelgood_Publishers_Widget_Regex::$scriptProps,
          $widgetInstance,
          $scriptSettings
        );

        foreach ( $scriptSettings[1] as $key => $foundscriptSettings) {
          $parsed = json_decode($foundscriptSettings, true);
          $oldParsedSettings = array_key_exists('settings', $parsed) ? $parsed['settings'] : array();
          $newParsedSettings = array_merge($oldParsedSettings, $newSettings);
          $newParsedSettings = Reelgood_Publishers_Widget_Regex::sanitizeSettingsString(json_encode($newParsedSettings));

          error_log('Found script settings: '.json_encode($parsed));
          $replacingScriptSettings = '{"content_type": "' . $parsed['content_type'] . '","id": "' . $parsed['id'] . '","id_type": "'. $parsed['id_type'] .'", "settings": ' . trim(json_encode($newParsedSettings), '"') . '}';
          $postContent = str_replace($foundscriptSettings, $replacingScriptSettings, $postContent);
        }
      }

      wp_update_post(
        array(
          'ID' => $postID,
          'post_content' => $postContent
        )
      );
    }
  }

  /**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
  public static function updateAllGlobalSettings() {
    $widgetPostIDs = Reelgood_Publishers_Widget_Regex::getAllWidgetPostIDs();
    $newSettings = Reelgood_Publishers_Widget_Regex::sanitizeSettingsString(json_encode(reelgoodGetUserGlobalDefaultSettingsDeep(reelgoodGetVisibleSettingsKeypaths())));
    $newInlineSettings = str_replace("\n", "", str_replace('"', '\'', $newSettings));

    error_log('Updating All Global Settings For '.count($widgetPostIDs).' Widget Posts');
    error_log('New Inline Settings: '.$newInlineSettings);

    foreach ($widgetPostIDs as $index => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);
      $instances = Reelgood_Publishers_Widget_Regex::getWidgetInstancesFromPost($postID);

      error_log('Found '.count($instances).' widget instances in post index '.$index);

      foreach ($instances as $index => $widgetInstance) {
        $inlineSettings = array();
        preg_match_all(
          Reelgood_Publishers_Widget_Regex::$inlineProps,
          $widgetInstance,
          $inlineSettings
        );

        foreach ( $inlineSettings[1] as $key => $foundInlineSettings) {
          $parsed = json_decode(str_replace('\'', '"', $foundInlineSettings), true);
          error_log('Found inline settings: '.json_encode($parsed));
          $replacingInlineSettings = '{\'content_type\': \'' . $parsed['content_type'] . '\',\'id\': \'' . $parsed['id'] . '\',\'id_type\': \'' . $parsed['id_type'] . '\', \'settings\': ' . $newInlineSettings . '}';
          $postContent = str_replace($foundInlineSettings, $replacingInlineSettings, $postContent);
        }

        $scriptSettings = array();
        preg_match_all(
          Reelgood_Publishers_Widget_Regex::$scriptProps,
          $widgetInstance,
          $scriptSettings
        );

        foreach ( $scriptSettings[1] as $key => $foundscriptSettings) {
          $parsed = json_decode($foundscriptSettings, true);
          error_log('Found script settings: '.json_encode($parsed));
          $replacingScriptSettings = '{"content_type": "' . $parsed['content_type'] . '","id": "' . $parsed['id'] . '","id_type": "'. $parsed['id_type'] .'", "settings": ' . trim(json_encode($newSettings), '"') . '}';
          $postContent = str_replace($foundscriptSettings, $replacingScriptSettings, $postContent);
        }
      }

      wp_update_post(
        array(
          'ID' => $postID,
          'post_content' => $postContent
        )
      );
    }
  }

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function removeAllInstances() {
    $widgetPostIDs = Reelgood_Publishers_Widget_Regex::getAllWidgetPostIDs();

    foreach ($widgetPostIDs as $index => $postID) {
      $postContent = Reelgood_Publishers_Widget_Regex::getPostContentFromPost($postID);
      $instances = Reelgood_Publishers_Widget_Regex::getWidgetInstancesFromPost($postID);

      foreach ( $instances as $index => $widgetInstance) {
        $postContent = str_replace($widgetInstance, '', $postContent);
      }

      wp_update_post(
        array(
          'ID' => $postID,
          'post_content' => $postContent
        )
      );
    }
  }
}
?>
