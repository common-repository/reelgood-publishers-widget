<?php
  function reelgoodGetServicePriorities() {
    $servicePriorities = array_map(
      function($prio) {
        return array(
          'name' => $prio['name'],
          'id' => (int)$prio['id'],
          'group_id' => (int)$prio['group_id']
        );
      },
      json_decode(get_option('reelgood_pub_option_service_priorities', '[]'), true)
    );

    echo wp_send_json_success(
      array(
        'set_platforms'=> $servicePriorities,
        'available_platforms' => reelgoodGetJSONDefinedSettings()['service_prioritization_editor']['available_platforms']
      )
    );
  }
  add_action('wp_ajax_reelgood_get_service_priorities','reelgoodGetServicePriorities');

  function reelgoodSetServicePriorities() {
    if (array_key_exists('service_priorities', $_POST)) {
      $servicePriorities = array_map(
        function($prio) {
          return array(
            'name' => sanitize_text_field($prio['name']),
            'id' => (int)sanitize_text_field($prio['id']),
            'group_id' => (int)sanitize_text_field($prio['group_id'])
          );
        },
        $_POST['service_priorities']
      );

      $serviceIds = array_map(function($prio) { return (int)$prio['group_id']; }, $servicePriorities);
      $platformIds = array_map(function($prio) { return (int)$prio['id']; }, $servicePriorities);

      update_option('reelgood_pub_option_service_priorities', json_encode($servicePriorities));
      update_option('reelgood_pub_option_' . 'behavior.services.service_groups_prioritization', json_encode($serviceIds));
      update_option('reelgood_pub_option_' . 'behavior.services.service_platforms_prioritization', json_encode($platformIds));

      require_once REELGOOD_PUBLISHERS_WIDGET_DIR_PATH . 'includes/reelgood_publishers_widget_regex.php';
	    Reelgood_Publishers_Widget_Regex::updateAllAppendedSettings();

      echo wp_send_json_success();
    } else {
      delete_option('reelgood_pub_option_service_priorities');
      delete_option('reelgood_pub_option_' . 'behavior.services.service_groups_prioritization');
      delete_option('reelgood_pub_option_' . 'behavior.services.service_platforms_prioritization');

      echo wp_send_json_success();
    }
  }
  add_action('wp_ajax_reelgood_set_service_priorities','reelgoodSetServicePriorities');
?>
