var currentSettings = {};
var defaultSettings = {};
var globalDefaultSettings = {};
var settingsForThemes = {};
var settingsForStyles = {};
var widgetInstanceReference = null;

jQuery(function($) {
  $(document).ready(function(){
    addClickEditDefaultStyling()
  });

  function addClickEditDefaultStyling() {
    var checkTinyMCEExist = setInterval(function() {
      if (typeof content_ifr === 'undefined') { return }
      var tinyMCE = $(content_ifr)
      if (typeof tinyMCE !== 'undefined' && tinyMCE.contents()) {
        $($(tinyMCE.contents().get(0)).find('body').first())

        if (rgcontext_editor && JSON.parse(rgcontext_editor.can_edit_styling)) {
          $($(tinyMCE.contents().get(0)).find('body').first()).on('click', '.reelgood_pub_edit_widget_button_edit', editWidgetInstanceStyling);
        } else {
          $($(tinyMCE.contents().get(0)).find('body').first()).on('click', '.reelgood_pub_edit_widget_button_edit', showEditWidgetInstanceStylingError);
        }

        $($(tinyMCE.contents().get(0)).find('body').first()).on('click', '.reelgood_pub_edit_widget_button_remove', removeWidgetInstance);

        clearInterval(checkTinyMCEExist);
        clearInterval(checkEditDefaultExist);
      }
    }, 100); // check every 100ms

    var checkEditDefaultExist = setInterval(function() {
      if ($('#reelgood_default_styling_edit_styling_button').length) {
        $('#reelgood_default_styling_edit_styling_button').click(function() { openEditStylingPopup() });
        
        clearInterval(checkTinyMCEExist);
        clearInterval(checkEditDefaultExist);
      }
    }, 100); // check every 100ms
  }

  function showEditWidgetInstanceStylingError() {
    var footerButtons = [
      $('<div>Close</div>')
        .attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_editor_edit_widget_cancel'
        })
    ]

    var content = $('<span>')

    $('<div>Edit Styling</div>')
      .attr('class', 'reelgood_title')
      .appendTo(content);
    $('<div>The settings you make below will adjust the appearance of this particular widget instance. To adjust the default, please visit the widget settings.</div>')
      .attr('class', 'reelgood_desc')
      .appendTo(content);

    presentIconModal(content.html(), footerButtons, true, 'warning', 'Only admins can edit styling.');

    $('#reelgood_editor_edit_widget_cancel').click(closeModal);
  }

  function editWidgetInstanceStyling() {
    var widgetID = $(this).parent()
      .attr('data-reelgood-pub-widget-instance')
    var widgetElement = $(this.ownerDocument).find(`#${widgetID}`)

    if (widgetElement) {
      openEditStylingPopup(widgetElement)
    }
  }

  function removeWidgetInstance() {
    if(confirm("Are you sure you want to delete this widget?")) {
      var widgetID = $(this).parent()
        .attr('data-reelgood-pub-widget-instance')
      $(this.ownerDocument).find(`#${widgetID}`).remove()
    }
  }

  function setOnPagePreviewContent(contentType, rgID) {
    $('.reelgood_edit_styling_preview')
      .attr({
        'data-reelgood-preview-content-type': contentType,
        'data-reelgood-preview-id': rgID
      })

    refreshOnPageWidgetPreview()
  }

  function refreshOnPageWidgetPreview() {
    var onPageWidgets = $('.reelgood_edit_styling_preview')

    for (var i = 0; i < onPageWidgets.length; i++) {
      var widget = $(onPageWidgets.get(i))
      var newInstance = widget.clone();
      var deepSettings = {
        'content_type': newInstance.attr('data-reelgood-preview-content-type'),
        'id_type': 'rg',
        'id': newInstance.attr('data-reelgood-preview-id'),
        'settings': makeKeyPathsDeep(currentSettings)
      }
    
      newInstance.empty()

      $('<script>').attr('type', 'text/props').text(JSON.stringify(deepSettings)).appendTo(newInstance);

      widget.replaceWith(newInstance);
    }

    refreshOnPageBundle();
  }

  function refreshOnPageBundle() {
    if (!rgbundle.api_key) { return }

    var newBundle = $('<script>')
      .attr({
        'src': rgbundle.js,
        'data-api-key': rgbundle.api_key,
        'id': 'reelgood-js-widget-bundle',
        'async': true
      })

    if (rgbundle.dev_env ===  'reelgood_bool_on') {
      newBundle.attr('data-env', 'development')
    }

    $('#reelgood-js-widget-bundle').remove();
    
    newBundle.appendTo($(document.head));
    newBundle.appendTo($(document.body));
  }

  function refreshTinyMCEPreview(widgetReference) {
    var widgetInstanceProps = JSON.parse(widgetReference.attr('data-prop-props').replace(/'/g, '"'));
    var inlineSettingsProp = JSON.stringify(makeKeyPathsDeep(currentSettings)).replace(/"/g, '\'')

    $(widgetReference)
      .attr('data-prop-props', `{'content_type':'${widgetInstanceProps['content_type']}','id':'${widgetInstanceProps['id']}','id_type':'rg','settings':${inlineSettingsProp}}`)
    var newInstance = $(widgetReference).clone()

    $(widgetReference).replaceWith(newInstance)

    refreshTinyMCEBundle();
  }

  function refreshTinyMCEBundle() {
    if (!rgbundle.api_key) { return }

    if ($('#content_ifr').length) {
      var tinymce = $('#content_ifr').contents();

      var jsBundle = tinymce[0].getElementById('reelgood-js-widget-bundle')

      if (jsBundle) {
        jsBundle.remove();
      }

      var rgBundleJS = document.createElement('script');
      rgBundleJS.id = 'reelgood-js-widget-bundle';
      rgBundleJS.src = rgbundle.js;
      rgBundleJS.setAttribute('data-api-key', rgbundle.api_key);
      rgBundleJS.setAttribute('async', true);

      if (rgbundle.dev_env ===  'reelgood_bool_on') {
        rgBundleJS.setAttribute('data-env', 'development')
      }

      tinymce[0].head.appendChild(rgBundleJS);
    }
  }

  function refreshSettingsInputs() {
    var allSettingsInputs = $('[reelgood-settings-keys]')

    for (var i = 0; i < allSettingsInputs.length; i++) {
      var parent = $(allSettingsInputs.get(i));
      var settingsKeys = parent.attr('reelgood-settings-keys').split('&');
      var setValue = currentSettings[settingsKeys[0]];

      if (setValue === undefined) { continue }

      if (parent.hasClass('reelgood_edit_styling_dropdown')) {
        var setOptionTitle = parent.find(`.reelgood_edit_styling_dropdown_options_option[reelgood-settings-value="${setValue}"]`).first().html();

        if (!!parent.find(`.reelgood_edit_styling_dropdown_options_option[reelgood-settings-values]`).length) {
          var setValue = settingsKeys.map(function(settingKey) { return currentSettings[settingKey]; }).join('&')
          var setOptionTitle = parent.find(`.reelgood_edit_styling_dropdown_options_option[reelgood-settings-values="${setValue}"]`).first().html();
        }

        parent.find('.reelgood_edit_styling_dropdown_title').first().html(setOptionTitle);
      } else if (parent.hasClass('reelgood_edit_styling_text')) {
        parent.find('.reelgood_edit_styling_text_title').first().attr('value', setValue);
      } else if (parent.hasClass('reelgood_edit_styling_color')) {
        parent.find('.reelgood_edit_styling_color_selected').first().attr('value', setValue);
        parent.find('.reelgood_edit_styling_color_selected').first().css('backgroundColor', setValue);
        parent.find('.reelgood_edit_styling_color_title').first().attr('value', setValue.slice(1));
      } else if (parent.hasClass('reelgood_edit_styling_radio')) {
        parent.find('.reelgood_edit_styling_radio_option_selected').removeAttr('checked');
        parent.find(`.reelgood_edit_styling_radio_option_selected[value="${setValue}"]`).attr('checked', true);
      }
      // else if (parent.hasClass('reelgood_service_settings_dropdown')) {
      //   var setOptionTitle = parent.find(`.reelgood_service_settings_dropdown_options_option[reelgood-settings-value="${setValue}"]`).first().html();

      //   if (!!parent.find(`.reelgood_service_settings_dropdown_options_option[reelgood-settings-values]`).length) {
      //     var setValue = settingsKeys.map(function(settingKey) { return currentSettings[settingKey]; }).join('&')
      //     var setOptionTitle = parent.find(`.reelgood_service_settings_dropdown_options_option[reelgood-settings-values="${setValue}"]`).first().html();
      //   }

      //   parent.find('.reelgood_service_settings_dropdown_title').first().html(setOptionTitle);
      // } else if (parent.hasClass('reelgood_service_settings_radio')) {
      //   parent.find('.reelgood_radio_button reelgood_service_settings_radio_option_selected').removeAttr('checked');
      //   parent.find(`.reelgood_radio_button reelgood_service_settings_radio_option_selected[value="${setValue}"]`).attr('checked', true);
      // }
    }
  }

  function valueChangedWithinElement(element, value, values) {
    var settingsKeys = element.attr('reelgood-settings-keys').split('&');

    if (settingsKeys.includes('appearance.general.theme') && value !== currentSettings['appearance.general.theme']) {
      currentSettings['appearance.general.theme'] = value;
      setSettingsToTheme(value);
      return;
    }

    if (settingsKeys.includes('appearance.general.style') && value !== currentSettings['appearance.general.style']) {
      currentSettings['appearance.general.style'] = value;
      setSettingsToStyle(value);
      return;
    }

    for (var i = 0; i < settingsKeys.length; i++) {
      var settingsKey = settingsKeys[i];

      currentSettings[settingsKey] = value !== undefined ? value : values[i];
    }

    if (element.attr('reelgood-settings-refresh-all-inputs') === 'true') {
      // refreshSettingsInputs()
    }

    refreshSettingVisiblity()
  }

  function setSettingsToTheme(theme) {
    if (theme in settingsForThemes) {
      var overrideSettings = settingsForThemes[theme];

      for (settingsKey in overrideSettings) {
        var value = overrideSettings[settingsKey];

        currentSettings[settingsKey] = value;
      }

      refreshSettingsInputs();
    }
  }

  function setSettingsToStyle(style) {
    if (style in settingsForStyles) {
      var overrideSettings = settingsForStyles[style];

      for (settingsKey in overrideSettings) {
        var value = overrideSettings[settingsKey];

        currentSettings[settingsKey] = value;
      }

      refreshSettingsInputs();
    }
  }

  function resetSettingsToDefault() {
    if (widgetInstanceReference != null) {
      currentSettings = globalDefaultSettings
    } else {
      var defaultSettingsKeys = Object.keys(defaultSettings);

      for (var i = 0; i < defaultSettingsKeys.length; i++) {
        var settingsKey = defaultSettingsKeys[i];
        var value = defaultSettings[settingsKey];

        currentSettings[settingsKey] = value;
      } 
    }

    refreshOnPageWidgetPreview();
    refreshSettingsInputs();
  }
  
  function settingsClickListeners() {
    $('.reelgood_edit_styling_dropdown').click(function (e) {
      var allDropdowns = $('.reelgood_dropdown');

      for (var i = 0; i < allDropdowns.length; i++) {
        var dropdownKey = $(allDropdowns.get(i)).attr('reelgood-settings-keys');

        if (dropdownKey === $(this).attr('reelgood-settings-keys')) { continue }

        var dropdownArrow = $($(allDropdowns.get(i)).find('.reelgood_dropdown_arrow').first());
        dropdownArrow.empty();
        var dropdownOptions = $(allDropdowns.get(i)).find('.reelgood_edit_styling_dropdown_options').first();
        dropdownOptions.css('display', '');
        setDropdownArrow(dropdownArrow, false)
      }

      var toggledOptions = $(this).find('.reelgood_edit_styling_dropdown_options').first()
      var alreadyToggled = toggledOptions.css('display') === 'block';
      toggledOptions.css('display', alreadyToggled ? '' : 'block');
      var dropdownArrow = $($(this).find('.reelgood_dropdown_arrow').first());
      dropdownArrow.empty();
      setDropdownArrow(dropdownArrow, !alreadyToggled)

      stopEvent(e);
    });

    $('.reelgood_edit_styling_dropdown_options_option').click(function (e) {
      var dropdown = $(this).parent().parent();
      
      var value = $(this).attr('reelgood-settings-value');
      var values = $(this).attr('reelgood-settings-values');
      values = values && values.split('&');

      dropdown.find('.reelgood_edit_styling_dropdown_title').first().html($(this).html())

      valueChangedWithinElement(dropdown, value, values);

      refreshOnPageWidgetPreview();
    });

    $('.reelgood_edit_styling_color_selected').on('input', function() {
      var color = $(this).parent();
    
      var value = $(this).attr('value');
      $(this).css('backgroundColor', value);
      color.find('.reelgood_edit_styling_color_title').first().attr('value', value.slice(1));

      valueChangedWithinElement(color, value);
    });

    $('.reelgood_edit_styling_color_title').on('input', function() {
      var value = $(this).attr('value');

      if (value.length !== 3 && value.length !== 6) { return; }
      
      if (value.length === 3) {
        value = `${value.substr(0, 1)}${value.substr(0, 1)}${value.substr(1, 1)}${value.substr(1, 1)}${value.substr(2, 1)}${value.substr(2, 1)}`;
      }

      value = `#${value}`

      var color = $(this).parent();
      color.find('.reelgood_edit_styling_color_selected').first().css('backgroundColor', value);
      color.find('.reelgood_edit_styling_color_selected').first().attr('value', value);
      color.find('.reelgood_edit_styling_color_selected_disabled').first().css('backgroundColor', value);
      color.find('.reelgood_edit_styling_color_selected_disabled').first().attr('value', value);

      valueChangedWithinElement(color, value);
    });

    $('.reelgood_edit_styling_color_title').focusout(function() {
      var value = $(this).attr('value');

      if (value.length !== 3 && value.length !== 6) { 
        $(this).attr('value', $(this).parent().find('.reelgood_edit_styling_color_selected').first().attr('value').slice(1));
        $(this).attr('value', $(this).parent().find('.reelgood_edit_styling_color_selected_disabled').first().attr('value').slice(1));
      }

      if (value.length === 3) {
        value = `${value.substr(0, 1)}${value.substr(0, 1)}${value.substr(1, 1)}${value.substr(1, 1)}${value.substr(2, 1)}${value.substr(2, 1)}`;
        $(this).attr('value', value);
      }
    })

    $('.reelgood_edit_styling_color_selected').on('input', $.debounce(function() {
      refreshOnPageWidgetPreview();
    }, 1000));

    $('.reelgood_edit_styling_color_title').on('input', $.debounce(function() {
      refreshOnPageWidgetPreview();
    }, 1000));

    $('.reelgood_edit_styling_text_title').on('input', function() {
      var text = $(this).parent();
      var value = $(this).attr('value');

      if (text.length == 0) { return; }
      valueChangedWithinElement(text, value);
    });

    $('.reelgood_edit_styling_text_title').on('input', $.debounce(function() {
      refreshOnPageWidgetPreview();
    }, 1000));

    $('.reelgood_edit_styling_text_title').focusout(function() {
      var value = $(this).attr('value');

    });

    $('.reelgood_edit_styling_radio_option_selected').on('input', function() {
      if (!this.checked) { return; }
      var radio = $($(this).parent()).parent();
      valueChangedWithinElement(radio, JSON.parse($(this).attr('value')));
      refreshOnPageWidgetPreview();
    });
  }

  function saveEdittedStyling() {
    if (widgetInstanceReference) {
      refreshTinyMCEPreview(widgetInstanceReference)
      
      closeModal()
    } else {
      var data = {
        'action': 'reelgood_set_user_global_settings',
        'settings': sanitizeSettingsForWP(currentSettings)
      };
    
      jQuery.post(rgajax.url, data, function(response) {
        refreshWidgets()
        closeModal()
      });
    }
  }

  function setPageVisibility(page) {
    $('div[data-reelgood-page]').css('display', 'none')
    $(`div[data-reelgood-page=${page}]`).css('display', '')
  }

  function refreshSettingVisiblity() {
    var allConditionalSettings = $('[reelgood-visible-when]:not(.reelgood_edit_styling_section)')
    allConditionalSettings.css('display', 'none')

    for (var i = 0; i < allConditionalSettings.length; i++) {
      var setting = $(allConditionalSettings.get(i))
      var conditionals = JSON.parse(setting.attr('reelgood-visible-when'))

      var keys = Object.keys(conditionals)
      var allTrue = !(keys.map(
        function(key) { 
          return ((key in currentSettings) ? currentSettings[key] : defaultSettings[key]) === conditionals[key]
        }
      ).includes(false))

      if (allTrue) {
        setting.css('display', '')
      }
    }
  }

  function removeFinalPreviewSections() {
    var allSections = $('.reelgood_edit_styling_section[reelgood-visible-when]')

    for (var i = 0; i < allSections.length; i++) {
      var section = $(allSections.get(i))
      var conditionals = JSON.parse(section.attr('reelgood-visible-when'))

      if (conditionals['widget_reference'] === (widgetInstanceReference == null)) {
        section.remove()
      }
    }
  }

  function settingsFallBack() {
    if (!hasColorInputSupport(document)) {
      $('input[type="color"]').attr({
        'disabled': true,
        'class': 'reelgood_edit_styling_color_selected_disabled'
      })
    }
  }

  function reviewEditStyling() {
    setPageVisibility(1)

    var footerButtons = [
      $('<div>Cancel Changes</div>').attr({
        'id': 'reelgood-pub-widget-edit-styling-footer-inline-cancel',
        'class': 'reelgood_button_inline'
      }),
      $('<div>Back</div>').attr({
        'id': 'reelgood-pub-widget-edit-styling-footer-back',
        'class': 'reelgood_button_big'
      }),
      $('<div>Save</div>').attr({
        'id': 'reelgood-pub-widget-edit-styling-footer-save',
        'class': 'reelgood_button_big'
      })
    ]

    setNewModalFooters(footerButtons)

    $('#reelgood-pub-widget-edit-styling-footer-inline-cancel').click(closeModal)
    $('#reelgood-pub-widget-edit-styling-footer-save').click(saveEdittedStyling)
    $('#reelgood-pub-widget-edit-styling-footer-back').click(function() { 
      var footerButtons = [
        $('<div>Reset Above Settings to Reelgood Default</div>').attr({
          'id': 'reelgood-pub-widget-edit-styling-footer-default',
          'class': 'reelgood_button_inline'
        }),
        $('<div>Cancel</div>').attr({
          'id': 'reelgood-pub-widget-edit-styling-footer-cancel',
          'class': 'reelgood_button_big'
        }),
        $('<div>Review</div>').attr({
          'id': 'reelgood-pub-widget-edit-styling-footer-review',
          'class': 'reelgood_button_big'
        })
      ]

      setNewModalFooters(footerButtons)

      $('#reelgood-pub-widget-edit-styling-footer-cancel').click(closeModal)
      $('#reelgood-pub-widget-edit-styling-footer-review').click(reviewEditStyling)
      $('#reelgood-pub-widget-edit-styling-footer-default').click(resetSettingsToDefault)

      setPageVisibility(0)
    })
  }

  function openEditStylingPopup(widgetReference) {    
    if (!modalIsOpen()) {
      var data = {
        'action': 'reelgood_get_global_edit_settings_popup_all'
      };

      widgetInstanceReference = widgetReference

      if (widgetReference != null) {
        var widgetInstanceProps = JSON.parse(widgetReference.attr('data-prop-props').replace(/'/g, '"'));
        data['action'] = 'reelgood_get_edit_settings_popup_html_with_values'

        if ('settings' in widgetInstanceProps) {
          data['values'] = sanitizeSettingsForWP(getAllKeypathsValues(widgetInstanceProps['settings']))
        } else {
          data['values'] = []
        }
      }
    
      jQuery.post(rgajax.url, data, function(response) {
        if (!response.success || modalIsOpen()) {
          return;
        }

        currentSettings = sanitizeWPSettings(response.data.current)
        defaultSettings = sanitizeWPSettings(response.data.default)
        globalDefaultSettings = sanitizeWPSettings(response.data.global_default)
        settingsForThemes = response.data.settings_for_themes
        settingsForStyles = response.data.settings_for_styles
        visiblePage = 0

        var footerButtons = [
          $('<div>')
            .text((widgetReference != null) ? 'Reset styling to your default settings' : 'Reset Above Settings to Reelgood Default')
            .attr({
              'id': 'reelgood-pub-widget-edit-styling-footer-default',
              'class': 'reelgood_button_inline'
            }),
          $('<div>Cancel</div>').attr({
            'id': 'reelgood-pub-widget-edit-styling-footer-cancel',
            'class': 'reelgood_button_big'
          }),
          $('<div>Review</div>').attr({
            'id': 'reelgood-pub-widget-edit-styling-footer-review',
            'class': 'reelgood_button_big'
          })
        ]

        presentModal(getModal(response.data.html, footerButtons))
        setPageVisibility(0)
        refreshSettingVisiblity()
        removeFinalPreviewSections()

        if (widgetReference) {
          var widgetInstanceProps = JSON.parse(widgetReference.attr('data-prop-props').replace(/'/g, '"'));
          setOnPagePreviewContent(widgetInstanceProps['content_type'], widgetInstanceProps['id'])
        }

        $('#reelgood-pub-widget-edit-styling-container').click(closeModal)
        $('#reelgood-pub-widget-edit-styling-footer-cancel').click(closeModal)
        $('#reelgood-pub-widget-edit-styling-footer-review').click(reviewEditStyling)
        $('#reelgood-pub-widget-edit-styling-footer-default').click(resetSettingsToDefault)

        addDropdownArrows()
        settingsClickListeners()
        settingsFallBack()
        refreshOnPageBundle()
      });
    }
  }
});
