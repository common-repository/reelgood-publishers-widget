<?php
  // Array helpers

  function reelgoodSetValue(array &$data, $path, $value) {
    $temp = &$data;
    foreach(explode(".", $path) as $key) {
        $temp = &$temp[$key];
    }
    $temp = $value;
  }

  function getValue($data, $path) {
      $temp = $data;
      foreach(explode(".", $path) as $ndx) {
          $temp = isset($temp[$ndx]) ? $temp[$ndx] : null;
      }
      return $temp;
  }

  function reelgoodGetAllKeys($setting) {
    $res = array();

    if (array_key_exists('keys', $setting)) {
      $res = $setting['keys'];
    }
    
    if (array_key_exists('items', $setting)) {
      foreach ($setting['items'] as $key => $item) {
        $res = array_merge($res, reelgoodGetAllKeys($item));
      }
    }

    return $res;
  }

  function reelgoodSetValueForKeypath($object, $pathArray, $value) {
    if (count($pathArray) == 1) {
      $pathArray = $pathArray[0];
    }

    if (is_string($pathArray)) {
      $object[$pathArray] = $value;

      return $object;
    }

    if (count($pathArray) == 0) {
      return $object;
    } else {
      $thisLevelKey = array_shift($pathArray);

      if (!array_key_exists($thisLevelKey, $object)) {
        $object[$thisLevelKey] = array();
      }
      
      $object[$thisLevelKey] = array_merge($object[$thisLevelKey], reelgoodSetValueForKeypath($object[$thisLevelKey], $pathArray, $value));

      return $object;
    }
  }


  // Fetching settings
  
  function reelgoodGetJSONDefinedSettings() {
    if (!defined('REELGOOD_PUBLISHERS_SETTINGS')) {
      error_log('Fetch Widget Settings Settings Helper');
			define('REELGOOD_PUBLISHERS_SETTINGS', json_decode(get_option('reelgood_pub_settings'), true));
		}

    return REELGOOD_PUBLISHERS_SETTINGS;
  }

  function reelgoodGetAppendedSettings() {
    return reelgoodGetJSONDefinedSettings()['settings_editor']['append_settings'];
  }

  function reelgoodGetBehaviorSettings() {
    return reelgoodGetJSONDefinedSettings()['settings_editor']['behavior_settings'];
  }

  function reelgoodGetVisibleSettings() {
    return reelgoodGetJSONDefinedSettings()['settings_editor']['visible_settings'];
  }

  function reelgoodGetVisibleSettingsKeypaths() {
    $res = array();

    foreach (reelgoodGetVisibleSettings() as $key => $item) {
      $res = array_merge($res, reelgoodGetAllKeys($item));
    }

    return $res;
  }

  function reelgoodGetRGDefaultSettingsDeep() {
    return reelgoodGetJSONDefinedSettings()['js_widget_settings']['default_settings'];
  }

  function reelgoodGetRGDefaultSettingsKeyPaths($visibleSettingsKeypaths) {
    $rgDefaultsDeep = reelgoodGetRGDefaultSettingsDeep();
    
    $res = array();

    foreach ($visibleSettingsKeypaths as $key => $value) {
      $defaultValue = getValue($rgDefaultsDeep, $value);
      $res[$value] = is_bool($defaultValue) ? ($defaultValue ? 'reelgood_bool_on' : 'reelgood_bool_off') : $defaultValue;
    }

    return $res;
  }

  function reelgoodGetRGSettingsOverwritesForThemes() {
    return reelgoodGetJSONDefinedSettings()['js_widget_settings']['settings_for_themes'] ?: array();
  }

  function reelgoodGetRGSettingsOverwritesForStyles() {
    return reelgoodGetJSONDefinedSettings()['js_widget_settings']['settings_for_styles'] ?: array();
  }

  function reelgoodGetUserGlobalDefaultSettingsKeyPaths($visibleSettingsKeypaths, $filterDefaults = true) {
    $rgDefaults = reelgoodGetRGDefaultSettingsKeyPaths($visibleSettingsKeypaths);

    $res = array();

    $themeOverwrite = reelgoodGetRGSettingsOverwritesForThemes()[get_option('reelgood_pub_option_' . 'appearance.general.theme', $rgDefaults['appearance.general.theme'])] ?: array();
    $rgDefaults = array_merge($rgDefaults, $themeOverwrite);

    foreach ($visibleSettingsKeypaths as $key => $value) {
      $optionValue = get_option('reelgood_pub_option_' . $value, NULL);

      if ($optionValue != NULL && (!$filterDefaults || $optionValue != $rgDefaults[$value])) {
        $res[$value] = $optionValue;
      }
    }

    return reelgoodForceAddSettings($res);
  }

  function reelgoodGetUserGlobalDefaultSettingsDeep($visibleSettingsKeypaths) {
    $allUserGlobalDefaultValuesKeys = reelgoodGetUserGlobalDefaultSettingsKeyPaths($visibleSettingsKeypaths);

    $res = array();

    foreach ($allUserGlobalDefaultValuesKeys as $key => $value) {
      if ($value != NULL) {
        $res = array_merge($res, reelgoodSetValueForKeypath($res, explode(".", $key), $value));
      }
    }

    return $res;
  }

  function reelgoodGetEditSettingsPopup($visibleSettingsKeypaths, $withValuesKeyPaths) {
    $visibleSettings = reelgoodGetVisibleSettings();
    $rgDefaults = reelgoodGetRGDefaultSettingsKeyPaths($visibleSettingsKeypaths);
    $themeOverwrite = reelgoodGetRGSettingsOverwritesForThemes()[array_key_exists('appearance.general.theme', $withValuesKeyPaths) ? $withValuesKeyPaths['appearance.general.theme'] : $rgDefaults['appearance.general.theme']] ?: array();
    $rgDefaults = array_merge($rgDefaults, $themeOverwrite);

    $mergedValues = reelgoodForceAddSettings(array_merge($rgDefaults, $withValuesKeyPaths));

    $res = '';

    foreach ($visibleSettings as $key => $value) {
      $res = $res . reelgoodGetElementFrom($value, $mergedValues);
    }

    return $res;
  }

  function reelgoodGetEditUserGlobalSettingsPopup($visibleSettingsKeypaths) {
    return reelgoodGetEditSettingsPopup($visibleSettingsKeypaths, reelgoodGetUserGlobalDefaultSettingsKeyPaths($visibleSettingsKeypaths));
  }

  function reelgoodGetUserGlobalBehaviorSettings() {
    $settings = reelgoodGetBehaviorSettings();

    $res = array();
    $defaultSettings = reelgoodGetRGDefaultSettingsKeyPaths($settings);

    foreach ($settings as $key => $setting) {
      $settingValue = get_option('reelgood_pub_option_' . $setting, $defaultSettings[$setting]);
      $settingValue = is_bool($settingValue) ? ($settingValue ? 'reelgood_bool_on' : 'reelgood_bool_off') : $settingValue;

      $res[$setting] = $settingValue;
    }

    return $res;
  }

  function reelgoodGetUserGlobalAppendedSettings() {
    $settings = reelgoodGetAppendedSettings();

    $res = array();
    $defaultSettings = reelgoodGetRGDefaultSettingsKeyPaths($settings);

    foreach ($settings as $key => $setting) {
      $settingValue = get_option('reelgood_pub_option_' . $setting, $defaultSettings[$setting]);
      $settingValue = is_string($settingValue) ? (json_decode($settingValue) ?: $settingValue) : $settingValue;
      $settingValue = is_bool($settingValue) ? ($settingValue ? 'reelgood_bool_on' : 'reelgood_bool_off') : $settingValue;

      $res[$setting] = $settingValue;
    }

    return $res;
  }

  function reelgoodGetUserGlobalAppendedSettingsDeep() {
    $allAppendedSettingsKeys = reelgoodGetUserGlobalAppendedSettings();

    $res = array();

    foreach ($allAppendedSettingsKeys as $key => $value) {
      if ($value != NULL) {
        $res = array_merge($res, reelgoodSetValueForKeypath($res, explode(".", $key), $value));
      }
    }

    return $res;
  }

  function reelgoodForceAddSettings($settings, $isKeyPaths = true) {
    $forceAddedSettings =  reelgoodGetUserGlobalAppendedSettings();

    if ($isKeyPaths) {
      return array_merge($settings, $forceAddedSettings);
    } else {
      $res = $settings;

      foreach ($forceAddedSettings as $key => $value) {
        $res = array_merge($res, reelgoodSetValueForKeypath($res, explode(".", $key), $value));
      }

      return $res;
    }
  }

  function reelgoodGetAllSettingsData($global = true, $withValuesKeyPaths = array()) {
    $visibleSettingsKeypaths = reelgoodGetVisibleSettingsKeypaths();

    return array(
      'current' => $global ? reelgoodGetUserGlobalDefaultSettingsKeyPaths($visibleSettingsKeypaths) : reelgoodForceAddSettings($withValuesKeyPaths),
      'default' => reelgoodGetRGDefaultSettingsKeyPaths($visibleSettingsKeypaths),
      'global_default' => reelgoodGetUserGlobalDefaultSettingsKeyPaths($visibleSettingsKeypaths),
      'html' => $global ? reelgoodGetEditUserGlobalSettingsPopup($visibleSettingsKeypaths) : reelgoodGetEditSettingsPopup($visibleSettingsKeypaths, $withValuesKeyPaths),
      'settings_for_themes' => reelgoodGetRGSettingsOverwritesForThemes(),
      'settings_for_styles' => reelgoodGetRGSettingsOverwritesForStyles()
    );
  }
?>
