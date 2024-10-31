<html>
<head>
  <script>
    <?php
      echo 'var REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL = "'. REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL . '?rand='.rand(0,10000) . '";
      var REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY = "'. REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY . '";
      ';
    ?>

    jQuery(function($) {
      var selectedIndex = "0";

      function selectMenuItem() {
        selectedIndex = this.getAttribute('data-index');
        refreshMenuContentVisibility();
      }

      $(document).ready(function(){
        refreshMenuContentVisibility();
        addClickMenuItem();
        addClickEditDefaultStyling();

        addPreviewsToPage();
        addServicePriorityRows();
      });

      function refreshMenuContentVisibility() {
        var itemContents = $('.reelgood_item_content');

        for (var i = 0; i < itemContents.length; i++) {
          var item = itemContents[i];
          if (item.getAttribute('data-index') === selectedIndex) {
            item.style.display = 'block';
          } else {
            item.style.display = 'none';
          }
        }

        var items = $('.reelgood_item');
        for (var i = 0; i < items.length; i++) {
          if (items[i].getAttribute('data-index') === selectedIndex) {
            items[i].setAttribute('reelgood-selected', true);
          } else {
            items[i].removeAttribute('reelgood-selected');
          }
        }
      }

      function addClickMenuItem() {
        var checkExist = setInterval(function() {
          if ($('.reelgood_item').length) {
            $('.reelgood_item').click(selectMenuItem);
            clearInterval(checkExist);
          }
        }, 100); // check every 100ms
      }

      function addClickEditDefaultStyling() {
        var checkExist = setInterval(function() {
          if ($('#reelgood_default_styling').length) {
            $('#reelgood_default_styling').click(open_edit_styling_popup);
            clearInterval(checkExist);
          }
        }, 100); // check every 100ms
      }
    });
  </script>
</head>

<?php
  function getSettingsPageElementFrom($setting) {
    $res = '';

    if (!array_key_exists('type', $setting)) {
      return '';
    }

    switch ($setting['type']) {
      case 'settings_page':
        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }
        break;
      case 'section':
        $res = '<div class="reelgood_column reelgood_margin_left reelgood_margin_right">
        <div class="reelgood_title">' . $setting['title'] . '</div>';

        if (array_key_exists('desc', $setting)) {
          $res = $res . '<div class="reelgood_desc">' . nl2br($setting['desc']) . '</div>';
        }

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }

        $res = $res . '</div>';
        break;
      case 'subsection':
        $res = '<div class="reelgood_subtitle">' . $setting['title'] . '</div>';

        if (array_key_exists('desc', $setting)) {
          $res = $res . '<div class="reelgood_desc">' . nl2br($setting['desc']) . '</div>';
        }

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }
        break;
      case 'row':
        $domId = array_key_exists('id', $setting) ? ('id="' . $setting['id'] . '" ') : '';
        $class = 'class="reelgood_row reelgood_row_center ' .
          (
            array_key_exists('options', $setting) 
              ? (
                join(
                  ' ',
                  array_map(
                    function($option) {
                      return 'reelgood_row_' . $option;
                    },
                    $setting['options']
                  )
                )
              )
              : ''
          )
        . '" ';
        $res = $res . '<div ' . $class . $domId . '>';

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }

        $res = $res . '</div>';
        break;
      case 'subtitle':
        $res = $res . '<div class="reelgood_subtitle">' . $setting['title'] . '</div>';
        break;
      case 'desc':
        $res = $res . '<div class="reelgood_desc">' . nl2br($setting['title']) . '</div>';
        break;
      case 'button':
        $domId = array_key_exists('id', $setting) ? ('id="' . $setting['id'] . '" ') : '';
        $res = $res . '<div class="reelgood_button_big" ' . $domId . 'role="button" tabindex="1">' . $setting['title'] . '</div>';
        break;
      case 'preview':
        $res = $res . '<div data-widget-host="reelgood-pub" class="reelgood_publishers_widget" data-reelgood-preview-content-type="' . $setting['content_type'] . '" data-reelgood-preview-id="' . $setting['id'] . '"></div>';
        break;
      case 'footer':
        $res = $res . '<div class="reelgood_footer">';

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }

        $res = $res . '</div>';
        break;
      case 'button_banner':
        $domId = array_key_exists('id', $setting) ? ('id="' . $setting['id'] . '" ') : '';
        $res = $res . '<div class="reelgood_button_banner_big" ' . $domId . '>' . $setting['title'] . '</div>';
        break;
      case 'button_banner_small':
        $domId = array_key_exists('id', $setting) ? ('id="' . $setting['id'] . '" ') : '';
        $res = $res . '<div class="reelgood_button_banner_small" ' . $domId . '>' . $setting['title'] . '</div>';
        break;
      case 'require_admin_styling_changes':
        $res = $res . '<div class="reelgood_row">
          <label class="reelgood_account_settings_input_checkbox">
            <input type="checkbox" name="require_admin_styling_changes" id="reelgood_account_settings_require_admin_styling_changes">
            <span class="reelgood_account_settings_input_checkbox_checkmark"></span>
          </label>
          <div class="reelgood_column">
            <div class="reelgood_subsubtitle">' . $setting['title'] . '</div>
            <div class="reelgood_desc">' . nl2br($setting['desc']) . '</div>
          </div>
        </div>';
        break;
      case 'full_width_checkbox':
        $res = $res . '<div class="reelgood_row">
          <label class="reelgood_account_settings_input_checkbox">
            <input type="checkbox" name="require_admin_styling_changes" class="reelgood-appended-setting-checkbox" reelgood-settings-keys="' . join('&', $setting['keys']) . '">
            <span class="reelgood_account_settings_input_checkbox_checkmark"></span>
          </label>
          <div class="reelgood_column">
            <div class="reelgood_subsubtitle">' . $setting['title'] . '</div>
            <div class="reelgood_desc">' . nl2br($setting['desc']) . '</div>
          </div>
        </div>';
        break;
      case 'current_service_priorities':
        $res = $res . '<div id="reelgood_service_settings_current_service_settings"></div>';
        break;
      case 'current_default_styling':
        $res = $res . '<div id="reelgood_service_settings_current_default_styling">';

        if (array_key_exists('items', $setting)) {
          foreach ($setting['items'] as $key => $value) {
            $res = $res . getSettingsPageElementFrom($value);
          }
        }

        $res = $res . '</div>';
        break;
      default:
        break;
    }

    return $res;
  }

  define('REELGOOD_PUBLISHERS_SETTINGS', json_decode(get_option('reelgood_pub_settings'), true));

  $itemSettings = array_key_exists('settings_pages', REELGOOD_PUBLISHERS_SETTINGS) ? REELGOOD_PUBLISHERS_SETTINGS['settings_pages'] : array();

  $settingsPages = array();

  foreach ($itemSettings as $index => $value) {
    if (($value['type'] !== 'settings_page') || (!in_array((REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY !== false), $value['api_key_set']))) {
      continue;
    }

    array_push(
      $settingsPages,
      array(
        'menu_title' => $value['title'],
        'page' => getSettingsPageElementFrom($value)
      )
    );
  }

  echo '<body>
  <div class="reelgood_container">
    <div class="reelgood_menu">';

  foreach ($settingsPages as $index => $value) {
    echo '<div class="reelgood_item" reelgood-selected="' . ($index == 0 ? 'true' : 'false') . '" data-index="' . $index . '"><div class="reelgood_item_title">' . $value['menu_title'] . '</div></div>';
  }
  echo '</div>';

  echo '<div class="reelgood_content">
  <div class="reelgood_wrapper">';

  foreach ($settingsPages as $index => $value) {
    echo '<div class="reelgood_item_content" data-index="' . $index . '">';
    echo $value['page'];
    echo '</div>';
  }

  echo '</div>
    </div>
  </div>
</body>';
?>
</html>
