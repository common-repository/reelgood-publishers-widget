<?php
  include plugin_dir_path(__FILE__) . 'settings_management_helpers.php';
  include plugin_dir_path(__FILE__) . 'edit_default_styling_helpers.php';

  //Getting all the set settings, the server side defined HTML, etc. for the edit styling settings popup
  function reelgoodGetGlobalEditSettingsPopupHTML() {
    echo wp_send_json_success(reelgoodGetAllSettingsData());
  }
  add_action('wp_ajax_reelgood_get_global_edit_settings_popup_all','reelgoodGetGlobalEditSettingsPopupHTML');

  //Get HTML for edit settings popup with predefined setting values (used for editting a widget instance since their values might differ from the global default)
  function reelgoodGetEditSettingsPopupHTMLWithValues() {
    echo wp_send_json_success(reelgoodGetAllSettingsData($global = false, $withValuesKeyPaths = $_POST['values'] ?: array()));
  }
  add_action('wp_ajax_reelgood_get_edit_settings_popup_html_with_values','reelgoodGetEditSettingsPopupHTMLWithValues');

  //Get the current defined global defaults (used by the editor to initialize a new widget instance with said settings)
  function reelgoodGetUserGlobalDefaultSettingsAction() {
    $settings = reelgoodGetVisibleSettingsKeypaths();

    if ($settings != null) {
      echo wp_send_json_success(reelgoodGetUserGlobalDefaultSettingsDeep($settings));
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_get_user_global_settings','reelgoodGetUserGlobalDefaultSettingsAction');

  function reelgoodGetUserGlobalDefaultSettingsKeyPathsAction() {
    $settings = reelgoodGetVisibleSettingsKeypaths();

    if ($settings != null) {
      echo wp_send_json_success(reelgoodGetUserGlobalDefaultSettingsKeyPaths($settings));
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_get_user_global_settings_keypaths','reelgoodGetUserGlobalDefaultSettingsKeyPathsAction');

  function reelgoodSetUserGlobalDefaultSettingsAction() {
    if (array_key_exists('settings', $_POST)) {
      foreach ($_POST['settings'] as $key => $setting) {
        $sanitizedSetting = sanitize_text_field($setting);

        if (is_string($sanitizedSetting)) {
          $sanitizedSetting = str_replace("\"", "", json_encode($sanitizedSetting));
        }

        update_option( 'reelgood_pub_option_' . sanitize_text_field($key), $setting);
      }

      echo wp_send_json_success();
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_set_user_global_settings','reelgoodSetUserGlobalDefaultSettingsAction');

  function reelgoodSetUserGlobalDefaultSettingsWithRefreshAction() {
    if (array_key_exists('settings', $_POST)) {
      foreach ($_POST['settings'] as $key => $setting) {
        $sanitizedSetting = sanitize_text_field($setting);

        if (is_string($sanitizedSetting)) {
          $sanitizedSetting = str_replace("\"", "", json_encode($sanitizedSetting));
        }

        update_option( 'reelgood_pub_option_' . sanitize_text_field($key), $setting);
      }

      require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_regex.php';
	    Reelgood_Publishers_Widget_Regex::updateAllAppendedSettings();

      echo wp_send_json_success();
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_set_user_global_settings_refresh','reelgoodSetUserGlobalDefaultSettingsWithRefreshAction');

  //Resets all widget instances in every WP post to the globally defined settings
  function reelgoodResetAllWidgetsToGlobalDefault() {
    require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_regex.php';
	  Reelgood_Publishers_Widget_Regex::updateAllGlobalSettings();

    echo wp_send_json_success();
  }
  add_action('wp_ajax_reelgood_reset_all_widgets_to_global_default','reelgoodResetAllWidgetsToGlobalDefault');

  function reelgoodSetUserGlobalBehaviorSettingsAction() {
    if (array_key_exists('settings', $_POST)) {
      foreach ($_POST['settings'] as $key => $setting) {
        $sanitizedSetting = sanitize_text_field(setting);

        if (is_string($sanitizedSetting)) {
          $sanitizedSetting = str_replace("\"", "", json_encode($sanitizedSetting));
        }

        update_option( 'reelgood_pub_option_' . sanitize_text_field($key), $setting);
      }

      echo wp_send_json_success();
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_set_user_global_behavior_settings_action','reelgoodSetUserGlobalBehaviorSettingsAction');

  // Require Admin Styling
  function reelgoodGetRequireAdminEditStyling() {
    echo wp_send_json_success(get_option('reelgood_pub_wp_require_admin_edit_styling', 'reelgood_bool_false'));
  }
  add_action('wp_ajax_reelgood_get_require_admin_edit_styling','reelgoodGetRequireAdminEditStyling');

  function reelgoodSetRequireAdminEditStyling() {
    if (array_key_exists('require_admin', $_POST)) {
      update_option('reelgood_pub_wp_require_admin_edit_styling', sanitize_text_field($_POST['require_admin']));
      echo wp_send_json_success();
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_set_require_admin_edit_styling','reelgoodSetRequireAdminEditStyling');
?>
