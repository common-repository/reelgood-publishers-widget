jQuery(function($) {
  $(document).ready(function(){
    $('#reelgood_account_settings_manage_button').click(openManageAccountPopup);
    $('#reelgood_account_settings_require_admin_styling_changes').on('input', function (e) {
      setRequireAdminSettings()
    });

    $('.reelgood-appended-setting-checkbox[reelgood-settings-keys]').on('input', function (e) {
      setAppendedSetting($(this))
    });
    
    getSetRequireAdminSettings()
    getSetAppendedSetting()
  });

  function getSetRequireAdminSettings() {
    var data = {
      'action': 'reelgood_get_require_admin_edit_styling'
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success && response.data) {
        if (response.data === 'reelgood_bool_on') {
          $('#reelgood_account_settings_require_admin_styling_changes').attr('checked', true)
        } else {
          $('#reelgood_account_settings_require_admin_styling_changes').removeAttr('checked')
        }
      }
    });
  }

  function setRequireAdminSettings() {
    var data = {
      'action': 'reelgood_set_require_admin_edit_styling',
      'require_admin': $('#reelgood_account_settings_require_admin_styling_changes').prop('checked') ? 'reelgood_bool_on' : 'reelgood_bool_off'
    };

    jQuery.post(rgajax.url, data, function(response) { });
  }

  function getSetAppendedSetting() {
    var data = {
      'action': 'reelgood_get_user_global_settings_keypaths'
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success && response.data) {
        var currentSettings = sanitizeWPSettings(response.data)

        var allSettings = $('.reelgood-appended-setting-checkbox[reelgood-settings-keys]')
        
        for (var i = 0; i < allSettings.length; i++) {
          var checkbox = $(allSettings.get(i))
          var settingsKey = checkbox.attr('reelgood-settings-keys').split('&')[0]

          if (!(settingsKey in currentSettings)) { continue }

          if (currentSettings[settingsKey]) {
            checkbox.attr('checked', true)
          } else {
            checkbox.removeAttr('checked')
          }
        }
      }
    });
  }

  function setAppendedSetting(input) {
    var checked = input.prop('checked') ? 'reelgood_bool_on' : 'reelgood_bool_off'
    var settingKeys = input.attr('reelgood-settings-keys').split('&')

    var settings = {}

    for (var i = 0; i < settingKeys.length; i++) {
      settings[settingKeys[i]] = checked
    }

    var data = {
      'action': 'reelgood_set_user_global_settings_refresh',
      'settings': sanitizeSettingsForWP(settings)
    };

    jQuery.post(rgajax.url, data, function(response) { });
  }

  function getAPIKey() {
    var data = {
      'action': 'reelgood_get_api_key'
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success && response.data) {
        $('#reelgood_account_settings_input_api_key').attr('value', response.data)
      }
    });
  }

  function setAPIKey() {
    if ($(this).attr('disabled')) { return }

    if (!confirm('Are you sure you want to set a new API key?')) { return }

    var data = {
      'action': 'reelgood_set_api_key',
      'api_key': $('#reelgood_account_settings_input_api_key').val()
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success) {
        location.reload()
      } else {
        closeModal()
      }
    });
  }

  function validateTextFields() {
    var disabled = !$('#reelgood_account_settings_input_api_key').val().length
    if (disabled) {
      $('#reelgood_account_settings_set').attr('disabled', true);
    } else {
      $('#reelgood_account_settings_set').removeAttr('disabled');
    }
  }

  function openManageAccountPopup() {
    if (!modalIsOpen()) {
      var footerButtons = [
        $('<div>Cancel</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_account_settings_cancel'
        }),
        $('<div>Set</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_account_settings_set',
          'disabled': true
        })
      ]

      var supportModalContent = $('<span>')

      $('<div>')
        .text(rgbundle.api_key
          ? 'Change Your API Key'
          : 'Set Your API Key')
        .attr('class', 'reelgood_title')
        .appendTo(supportModalContent);

      $('<div>')
        .html(rgbundle.api_key 
          ? 'To change your API key, enter it below and click set. Note: this cannot be undone.'
          : 'If you don\'t have one please contact us at <a href=\"mailto:publishers@reelgood.com\">publishers@reelgood.com</a>')
        .attr('class', 'reelgood_desc')
        .appendTo(supportModalContent);

      var columnsRow = $('<div>').attr('class', 'reelgood_row').appendTo(supportModalContent)
      var leftColumn = $('<div>').attr('class', 'reelgood_column').appendTo(columnsRow)
      var rightColumn = $('<div>').attr('class', 'reelgood_column reelgood_fill_width').appendTo(columnsRow)

      $('<div>Your API Key</div>').attr('class', 'reelgood_subsubtitle reelgood_account_settings_input_title').appendTo(leftColumn)
      $('<input>')
        .attr({
          'class': 'reelgood_account_settings_input_text',
          'type': 'text'
        })
        .attr('id', 'reelgood_account_settings_input_api_key')
        .attr('value', rgbundle.api_key)
        .appendTo(
          $('<div>')
            .attr('class', 'reelgood_account_settings_input_text_wrapper')
            .appendTo(rightColumn)
        )

      presentModal(getModal(supportModalContent.html(), footerButtons, true));

      $('#reelgood_account_settings_cancel').click(closeModal);
      $('#reelgood_account_settings_set').click(setAPIKey);

      $('#reelgood_account_settings_input_api_key').on('input', validateTextFields);

      getAPIKey()
    }
  }
});
