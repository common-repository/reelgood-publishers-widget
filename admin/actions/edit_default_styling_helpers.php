<?php
  // Create UI elements for given settings with given values

  function reelgoodDescFromSetting($setting) {
    $res = '';

    if (array_key_exists('desc', $setting)) {
      $desc = nl2br($setting['desc']);
      $res = $res . '<div class="reelgood_edit_styling_setting_desc">' . $desc . '</div>';
    }

    return $res;
  }

  function reelgoodAfterFromSetting($setting) {
    $res = '';

    if (array_key_exists('after', $setting)) {
      $after = nl2br($setting['after']);
      $res = $res . '<div class="reelgood_edit_styling_setting_after">' . $after . '</div>';
    }

    return $res;
  }

  function reelgoodRadioButtonsFromSetting($setting, $withValuesKeyPaths, $prefix) {
    if ($setting['type'] !== 'radio') {
      return '';
    }
    
    $radioValue = $withValuesKeyPaths[$setting['keys'][0]];

    // Why the extra quotation marks? Who knows. But it works. So don't touch it
    $radioValue = in_array($radioValue, array('"reelgood_bool_on"', 'reelgood_bool_on', 'true')) 
      ? true 
      : (in_array($radioValue, array('"reelgood_bool_off"', 'reelgood_bool_off', "false")) 
        ? false
        : $radioValue);

    $res = '<div class="reelgood_edit_styling_radio" reelgood-settings-keys="' . join("&", $setting['keys']) . '" ' . ((array_key_exists('refresh_all', $setting) && $setting['refresh_all']) ? 'reelgood-settings-refresh-all-inputs="true"' : '') . '>';

    if (array_key_exists('options', $setting)) {
      foreach ($setting['options'] as $key => $value) {
        $optionValue = $value['value'];

        $res = $res . '
          <label class="reelgood_edit_styling_radio_option">
            <input class="reelgood_edit_styling_radio_option_selected" type="radio" name="' . ($prefix !== null ? ($prefix . '-') : '' ) . join("&", $setting['keys']) . '" value="' . json_encode($optionValue) . '" ' . (($radioValue == $optionValue) ? 'checked' : '') . '>
            <div class="reelgood_edit_styling_radio_option_checkmark"></div>
            ' . $value['title'] . '
          </label>
        ';
      }
    }

    $res = $res . '</div>';

    return $res;
  }

  function reelgoodColorFromSetting($setting, $withValuesKeyPaths) {
    if ($setting['type'] !== 'color') {
      return '';
    }

    $colorValue = $withValuesKeyPaths[$setting['keys'][0]];
    
    $res = '<div class="reelgood_edit_styling_color" reelgood-settings-keys="' . join("&", $setting['keys']) . '">
      <input type="color" class="reelgood_edit_styling_color_selected" value="' . $colorValue . '" style="background-color:' . $colorValue . '" ' . ((array_key_exists('refresh_all', $setting) && $setting['refresh_all']) ? 'reelgood-settings-refresh-all-inputs="true"' : '') . '>
      <span>#</span>
      <input type="text" spellcheck="false" class="reelgood_edit_styling_color_title" value="' . substr($colorValue, 1) . '">
    </div>';
    
    return $res;
  }

  function reelgoodTextFromSetting($setting, $withValuesKeyPaths) {
    if ($setting['type'] !== 'text') {
      return '';
    }

    $textValue = $withValuesKeyPaths[$setting['keys'][0]];
    
    $res = '<div class="reelgood_edit_styling_text" reelgood-settings-keys="' . join("&", $setting['keys']) . '" ' . ((array_key_exists('refresh_all', $setting) && $setting['refresh_all']) ? 'reelgood-settings-refresh-all-inputs="true"' : '') . '>
      <input type="text" spellcheck="false" class="reelgood_edit_styling_text_title" value="' . $textValue . '">
    </div>';
    
    return $res;
  }

  function reelgoodDropdownFromSetting($setting, $withValuesKeyPaths) {
    if ($setting['type'] !== 'dropdown') {
      return '';
    }

    $options_res = '';
    $selected_option_value = '';
    $selected_option_title = '';

    if (array_key_exists('is_multi_value_setting', $setting) && $setting['is_multi_value_setting']) {
      $selected_option_value = join("&", array_map(function ($setting_key) use( &$withValuesKeyPaths) { return $withValuesKeyPaths[$setting_key]; }, $setting['keys']));
    } else {
      $selected_option_value = $withValuesKeyPaths[$setting['keys'][0]];
    }
    
    if (array_key_exists('options', $setting)) {
      $options_res = $options_res . '<div class="reelgood_edit_styling_dropdown_options">';

      foreach ($setting['options'] as $key => $value) {
        $sanitized_option_value = '';
        $value_string = '';

        if (array_key_exists('values', $value)) {
          $sanitized_option_value = str_replace("\"", "", json_encode(join("&", $value['values'])));
          $value_string = 'reelgood-settings-values="' . $sanitized_option_value . '"';
        } else {
          $sanitized_option_value = str_replace("\"", "", json_encode($value['value']));
          $value_string = 'reelgood-settings-value="' . $sanitized_option_value . '"';
        }

        $options_res = $options_res . '<div class="reelgood_edit_styling_dropdown_options_option" ' . $value_string . '>' . $value['title'] . '</div>';

        if ($sanitized_option_value == $selected_option_value) {
          $selected_option_title = $value['title'];
        }
      }

      $options_res = $options_res . '</div>';
    }

    return '<div class="reelgood_edit_styling_dropdown" reelgood-settings-keys="' . join("&", $setting['keys']) . '" ' . ((array_key_exists('refresh_all', $setting) && $setting['refresh_all']) ? 'reelgood-settings-refresh-all-inputs="true"' : '') . '>
      <div class="reelgood_row reelgood_fill_width">
        <div class="reelgood_edit_styling_dropdown_title reelgood_fill_width">' . $selected_option_title . '</div>
        <div class="reelgood_dropdown_arrow"></div>
      </div>
      ' . $options_res . '
    </div>';
  }

  function reelgoodPreviewFromSettings($setting, $withValuesKeyPaths) {
    if ($setting['type'] !== 'preview') {
      return '';
    }

    $settingsObject = array();

    foreach ($withValuesKeyPaths as $key => $value) {
      if ($value != NULL) {
        $settingsObject = array_merge($settingsObject, reelgoodSetValueForKeypath($settingsObject, explode(".", $key), $value));
      }
    }

    $scriptTagSettings = str_replace('"reelgood_bool_on"', "true", str_replace('"reelgood_bool_off"', "false", json_encode($settingsObject)));

    $res = '<div 
      data-widget-host="reelgood-pub"
      class="reelgood_edit_styling_preview reelgood_publishers_widget"
      data-reelgood-preview-content-type="'. $setting['content_type'] . '"
      data-reelgood-preview-id="'. $setting['id'] . '"
    >
      <script type="text/props">
        {
          "content_type": "' . $setting['content_type'] . '",
          "id": "' . $setting['id'] . '",
          "id_type": "rg",
          "settings": ' . $scriptTagSettings . '
        }
      </script>
    </div>';
    
    return $res;
  }

  function reelgoodGetElementFrom($setting, $withValuesKeyPaths, $prefix = null) {
    $res = '';

    if (!array_key_exists('type', $setting)) {
      error_log('No Type in Setting: '.json_encode($setting));
      return '';
    }

    switch ($setting['type']) {
      case 'section':
        $visibleWhenString = (array_key_exists('visible_when', $setting) ? ('reelgood-visible-when=' . json_encode($setting['visible_when']) . ' ') : '');
        $pageString = (array_key_exists('page', $setting) ? ('data-reelgood-page=' . $setting['page'] . ' ') : '');
        $prefixString = (array_key_exists('prefix', $setting) ? $setting['prefix'] : null);

        $res = '<div class="reelgood_edit_styling_section" ' . $pageString . $visibleWhenString . '>
          <div class="reelgood_edit_styling_title">' . $setting['title'] . '</div>';

        if (array_key_exists('desc', $setting)) {
          $res = $res . '<div class="reelgood_edit_styling_desc">' . nl2br($setting['desc']) . '</div>';
        }

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . reelgoodGetElementFrom($value, $withValuesKeyPaths, $prefixString);
          }
        }

        $res = $res . '</div>';
        break;
      case 'subsection':
        $res = '<div class="reelgood_edit_styling_subsection">
          <div class="reelgood_edit_styling_subtitle">' . $setting['title'] . '</div>';

        if (array_key_exists('desc', $setting)) {
          $res = $res . '<div class="reelgood_edit_styling_desc">' . nl2br($setting['desc']) . '</div>';
        }

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . reelgoodGetElementFrom($value, $withValuesKeyPaths, $prefix);
          }
        }

        $res = $res . '</div>';
        break;
      case 'row':
        if (array_key_exists('center', $setting) && $setting['center'] === true) {
          $res = $res . '<div class="reelgood_edit_styling_row reelgood_edit_styling_row_center">';
        } else {
          $res = $res . '<div class="reelgood_edit_styling_row">';
        }
        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . reelgoodGetElementFrom($value, $withValuesKeyPaths, $prefix);
          }
        }

        $res = $res . '</div>';
        break;
      case 'preview':
        $res = $res . reelgoodPreviewFromSettings($setting, $withValuesKeyPaths);
        break;
      case 'dropdown':
        $visibleWhenString = array_key_exists('visible_when', $setting) ? ' reelgood-visible-when=' . json_encode($setting['visible_when']) : '';

        $res = $res . '<div class="reelgood_edit_styling_setting"' . $visibleWhenString . '>
          <div class="reelgood_edit_styling_setting_title">' . $setting['title'] . '</div>
          <div class="reelgood_edit_styling_setting_input">
            ' . reelgoodDropdownFromSetting($setting, $withValuesKeyPaths) . '
            ' . reelgoodAfterFromSetting($setting) . '
            ' . reelgoodDescFromSetting($setting) . '
          </div>
        </div>';
        break;
      case 'color':
        $visibleWhenString = array_key_exists('visible_when', $setting) ? ' reelgood-visible-when=' . json_encode($setting['visible_when']) : '';

        $res = $res . '<div class="reelgood_edit_styling_setting"' . $visibleWhenString . '>
          <div class="reelgood_edit_styling_setting_title">' . $setting['title'] . '</div>
          <div class="reelgood_edit_styling_setting_input">
            ' . reelgoodColorFromSetting($setting, $withValuesKeyPaths) . '
            ' . reelgoodAfterFromSetting($setting) . '
            ' . reelgoodDescFromSetting($setting) . '
          </div>
        </div>';
        break;
      case 'radio':
        $visibleWhenString = array_key_exists('visible_when', $setting) ? ' reelgood-visible-when=' . json_encode($setting['visible_when']) : '';

        $res = $res . '<div class="reelgood_edit_styling_setting"' . $visibleWhenString . '>
          <div class="reelgood_edit_styling_setting_title">' . $setting['title'] . '</div>
          <div class="reelgood_edit_styling_setting_input">
            ' . reelgoodRadioButtonsFromSetting($setting, $withValuesKeyPaths, $prefix) . '
            ' . reelgoodAfterFromSetting($setting) . '
            ' . reelgoodDescFromSetting($setting) . '
          </div>
        </div>';
        break;
      case 'text':
        $visibleWhenString = array_key_exists('visible_when', $setting) ? ' reelgood-visible-when=' . json_encode($setting['visible_when']) : '';

        $res = $res . '<div class="reelgood_edit_styling_setting"' . $visibleWhenString . '>
          <div class="reelgood_edit_styling_setting_title">' . $setting['title'] . '</div>
          <div class="reelgood_edit_styling_setting_input">
            ' . reelgoodTextFromSetting($setting, $withValuesKeyPaths) . '
            ' . reelgoodAfterFromSetting($setting) . '
            ' . reelgoodDescFromSetting($setting) . '
          </div>
        </div>';
        break;
      case 'break':
        $res = '<div class="reelgood_edit_styling_section_break" ' . (array_key_exists('page', $setting) ? ('data-reelgood-page=' . $setting['page']) : '') . ' ></div>';
      default:
          break;
    }

    return $res;
  }
?>
