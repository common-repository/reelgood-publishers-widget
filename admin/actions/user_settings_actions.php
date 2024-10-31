<?php
  function reelgoodGetAPIKey() {
    $apiKey = get_option('reelgood_pub_wp_api_key');

    if ($apiKey !== false) {
      echo wp_send_json_success($apiKey);
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_get_api_key','reelgoodGetAPIKey');

  function reelgoodSetAPIKey() {
    if (array_key_exists('api_key', $_POST)) {
      update_option('reelgood_pub_wp_api_key', sanitize_text_field($_POST['api_key']));
      define( 'REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY', sanitize_text_field($_POST['api_key']));

      echo wp_send_json_success();
    } else {
      echo wp_send_json_error();
    }
  }
  add_action('wp_ajax_reelgood_set_api_key','reelgoodSetAPIKey');
?>
