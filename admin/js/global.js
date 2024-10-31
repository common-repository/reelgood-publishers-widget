var $ = jQuery;

function stopEvent(e) {
  e.stopPropagation();
}

function uuidv4() {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  )
}

function sanitizeWPSettings(settings) {
  var res = {}
  var optionsKeys = Object.keys(settings)

  for (var i = 0; i < optionsKeys.length; i++) {
    var key = optionsKeys[i];
    var value = settings[key];

    res[optionsKeys[i]] = ['reelgood_bool_on', 'reelgood_bool_off'].includes(value) ? value === 'reelgood_bool_on' : value
  }

  return res
}

function sanitizeSettingsForWP(settings) {
  var res = {}
  var optionsKeys = Object.keys(settings)

  for (var i = 0; i < optionsKeys.length; i++) {
    var key = optionsKeys[i];
    var value = settings[key];

    res[optionsKeys[i]] = (typeof value === 'boolean') ? (value ? 'reelgood_bool_on' : 'reelgood_bool_off') : value
  }

  return res
}

function makeKeyPathsDeep(keyPathsValues) {
  var currentSettingsKeyPaths = Object.keys(keyPathsValues);

  var currentSettingsDeep = {};

  for (var i = 0; i < currentSettingsKeyPaths.length; i++) {
    currentSettingsDeep = reelgoodSetValueForKeypath(currentSettingsDeep, currentSettingsKeyPaths[i].split('.'), keyPathsValues[currentSettingsKeyPaths[i]]);
  }

  return currentSettingsDeep;
}

function reelgoodSetValueForKeypath(object, pathArray, value) {
  if (pathArray.length == 1) {
    object[pathArray[0]] = value;

    return object;
  }
  if (pathArray.length == 0) {
    return object;
  } else {
    var thisLevelKey = pathArray[0];

    if (!(thisLevelKey in object)) {
      object[thisLevelKey] = {};
    }

    object[thisLevelKey] = Object.assign(object[thisLevelKey], reelgoodSetValueForKeypath(object[thisLevelKey], pathArray.slice(1), value));

    return object;
  }
}

function getValueForKeypath(object, pathArray) {
  if (pathArray.length == 1) {
    return object[pathArray[0]];
  }
  if (pathArray.length == 0) {
    return undefined;
  } else {
    var thisLevelKey = pathArray[0];

    if (!(thisLevelKey in object)) {
      return undefined
    }

    return getValueForKeypath(object[thisLevelKey], pathArray.slice(1));
  }
}

function getAllKeypaths(object, path = '') {
  path = path.length ? (path + '.') : ''

  var res = Object.keys(object).flatMap(function(key) { 
    return (typeof object[key] === 'object')
      ? getAllKeypaths(object[key], `${path}${key}`)
      : `${path}${key}`
    }
  );

  return res
}

function getAllKeypathsValues(object, path = '') {
  var res = {}

  if (!object) {
    return res
  }

  path = path.length ? (path + '.') : ''

  var keys = Object.keys(object)

  for (var i = 0; i < keys.length; i++) {
    var key = keys[i]
    var fullPath = `${path}${key}`

    if (typeof object[key] === 'object') {
      res = Object.assign(res, getAllKeypathsValues(object[key], fullPath))
    } else {
      res[fullPath] = object[key]
    }
  }

  return res
}

function modalIsOpen() {
  return !! $('body').find('.reelgood-pub-widget-popup-container').length
}

function blockModalClose(blocked) {
  $('body').find('.reelgood-pub-widget-popup-container').attr('reelgood-blocked', blocked);
}

function closeModal() {
  var modal = $('body').find('.reelgood-pub-widget-popup-container')
  if (modal.attr('reelgood-blocked') === "true") { return false; }

  modal.remove()
  $('body').css('overflow', '');
}

function presentModal(modal) {
  if (modalIsOpen()) {
    return false;
  }

  $(modal).appendTo($('body'));
  $('body').css('overflow', 'hidden');
}

function setNewModalFooters(footerButtons) {
  var editStylingFooter = $('.reelgood-pub-widget-popup-footer')
  editStylingFooter.empty()

  for (var i = 0; i < footerButtons.length; i++) {
    $(footerButtons[i]).appendTo(editStylingFooter)
  }
}

function setNewModalContent(popupContent, withFooterButtons, wrapInColumn = false, icon = undefined, icon_title = undefined) {
  $('.reelgood-pub-widget-popup-content').empty()

  if (wrapInColumn === true) {
    var column = $('<div>')
      .attr('class', 'reelgood_column reelgood_margin_left reelgood_margin_right')
      .appendTo($('.reelgood-pub-widget-popup-content'))


      if (icon) {
        var content = $('<span>')
        $(popupContent).appendTo(content)

        $('<img>').attr('class', 'reelgood_popup_icon_small')
          .attr('src', `${rgcontext.location}images/${icon}.svg`)
          .appendTo(content)

        if (icon_title) {
          $('<div>').text(icon_title).attr('class', 'reelgood_popup_icon_small_title reelgood_center').appendTo(content);
        }

        $(content.html()).appendTo(column)
      } else {
        $(popupContent).appendTo(column)
      }
  } else {
    if (icon) {
      var content = $('<span>')
      $(popupContent).appendTo(content)

      $('<img>').attr('class', 'reelgood_popup_icon_small')
        .attr('src', `${rgcontext.location}images/${icon}.svg`)
        .appendTo(content)

      if (icon_title) {
        $('<div>').text(icon_title).attr('class', 'reelgood_popup_icon_small_title').appendTo(content);
      }

      $(content.html()).appendTo($('.reelgood-pub-widget-popup-content'))
    } else {
      $(popupContent).appendTo($('.reelgood-pub-widget-popup-content'))
    }
  }

  if (withFooterButtons) {
    setNewModalFooters(withFooterButtons)
  }
}

function presentIconModal(popupContent, withFooterButtons, wrapInColumn, icon, icon_title) {
  var content = $('<span>')
  $(popupContent).appendTo(content)

  $('<img>').attr('class', 'reelgood_popup_icon_small').attr('src', `${rgcontext.location}images/${icon}.svg`).appendTo(content)

  if (icon_title) {
    $('<div>').text(icon_title).attr('class', 'reelgood_center reelgood_popup_icon_small_title').appendTo(content);
  }

  presentModal(getModal(content, withFooterButtons, wrapInColumn));
}

function presentTopCenterIconModal(title, message, withFooterButtons, wrapInColumn, icon) {
  var contentSpan = $('<span>')

  $('<img>').attr('class', 'reelgood_popup_icon_big').attr('src', `${rgcontext.location}images/${icon}.svg`).appendTo(contentSpan)
  $('<div>').html(title).attr('class', 'reelgood_title reelgood_center').appendTo(contentSpan);
  $('<div>').html(message).attr('class', 'reelgood_desc reelgood_center').appendTo(contentSpan);

  presentModal(getModal(contentSpan.html(), withFooterButtons, wrapInColumn));
}

function getModal(popupContent, withFooterButtons, wrapInColumn = false, wrapContent = true) {
  var modalContainer = $('<div>')
    .attr('role', 'button')
    .attr('tabIndex', 1)
    .attr('class', 'reelgood-pub-widget-popup-container')
    .click(closeModal)

  var modalWrapper = $('<div>')
    .attr('class', `reelgood-pub-widget-popup-wrapper ${wrapContent && 'reelgood_wrap_content'}`)
    .appendTo(modalContainer)
    .click(stopEvent);

  var modalContent = $('<div>')
    .attr('class', 'reelgood-pub-widget-popup-content')
    .appendTo(modalWrapper)
  
  if (wrapInColumn === true) {
    var column = $('<div>')
      .attr('class', 'reelgood_column reelgood_margin_left reelgood_margin_right')
      .appendTo(modalContent)

      $(popupContent).appendTo(column)
  } else {
    $(popupContent).appendTo(modalContent)
  }

  if (withFooterButtons) {
    var modalFooter = $('<div>')
      .attr('class', 'reelgood-pub-widget-popup-footer')
      .appendTo(modalWrapper)

    for (var i = 0; i < withFooterButtons.length; i++) {
      $(withFooterButtons[i]).appendTo(modalFooter)
    }
  }

  return modalContainer
}

function addDropdownArrows() {
  var dropdownArrows = $('.reelgood_dropdown_arrow')
  for (var i = 0; i < dropdownArrows.length; i++) {
    setDropdownArrow($(dropdownArrows.get(i), false))
  }
}

function setDropdownArrow(element, isOpen, color='#d8d8d8') {
  element.attr('id', element.attr('id') || uuidv4())
  element.empty()

  var drawing = SVG(element.attr('id'))
    .size(10, 10)
    .attr({
      'viewBox': '0 0 11 11',
      'fill': '#000000'
    })

  drawing.path

  if (isOpen) {
    drawing.path('M11.314 4.657l-5.657 5.657L0 4.657z')
      .transform({
        x: 0,
        y: -3
      })
      .transform({rotation: 180})
  } else {
    drawing.path('M11.314 4.657l-5.657 5.657L0 4.657z')
      .transform({
        x: 0,
        y: -3
      })
  }
}

function refreshWidgets() {
  refreshWidgetsSettings(refreshBundle)
}

function refreshBundle() {
  if (!rgbundle.api_key) { return }

  var newBundle = $('<script>')
  .attr('src', rgbundle.js)
  .attr('data-api-key', rgbundle.api_key)
  .attr('id', 'reelgood-js-widget-bundle')
  .attr('async', true)

  $('#reelgood-js-widget-bundle').replaceWith(newBundle);
}

function refreshWidgetsSettings(callback = undefined) {
  var data = {
    'action': 'reelgood_get_user_global_settings'
  };

  jQuery.post(rgajax.url, data, function(response) {
    if (response.success && response.data) {
      var widgets = $('.reelgood_publishers_widget')

      for (var i = 0; i < widgets.length; i++) {
        var widget = $(widgets.get(i));
        var contentType = widget.attr('data-reelgood-preview-content-type')
        var contentId = widget.attr('data-reelgood-preview-id')

        var deepSettings = {
          'content_type': contentType,
          'id_type': 'rg',
          'id': contentId,
          'settings': JSON.parse(
            JSON.stringify(response.data)
              .replace(/"reelgood_bool_on"/g, 'true')
              .replace(/"reelgood_bool_off"/g, 'false')
          )
        }

        var listingDiv = $('<div>')
          .attr('class', 'reelgood_publishers_widget')
          .attr('id', uuidv4())
          .attr('data-reelgood-preview-content-type', contentType)
          .attr('data-reelgood-preview-id', contentId)
          .attr('data-widget-host', 'reelgood-pub')
          .css('display', 'flex')

          $('<script>').attr('type', 'text/props').text(JSON.stringify(deepSettings)).appendTo(listingDiv);

        widget.replaceWith(listingDiv)
      }

      if (callback) {
        callback()
      }
    }
  });
}

function hasColorInputSupport(document) {
  try {
      const input = document.createElement('input');
      input.type = 'color';
      input.value = '!';
      return input.type === 'color' && input.value !== '!';
  } catch (e) {
      return false;
  };
}

function padArray(arr,len,fill) {
  arr.push(...Array(len).fill(fill));
}

function addPreviewsToPage() {
  var data = {
    'action': 'reelgood_get_user_global_settings'
  };

  jQuery.post(rgajax.url, data, function(response) {
    if (response.success && response.data) {
      var widgets = $('.reelgood_publishers_widget')

      for (var i = 0; i < widgets.length; i++) {
        var widget = $(widgets.get(i));
        var contentType = widget.attr('data-reelgood-preview-content-type')
        var contentId = widget.attr('data-reelgood-preview-id')

        var deepSettings = {
          'content_type': contentType,
          'id_type': 'rg',
          'id': contentId,
          'settings': JSON.parse(
            JSON.stringify(response.data)
              .replace(/"reelgood_bool_on"/g, 'true')
              .replace(/"reelgood_bool_off"/g, 'false')
          )
        }

        var listingDiv = $('<div>')
          .attr('class', 'reelgood_publishers_widget')
          .attr('id', uuidv4())
          .attr('data-reelgood-preview-content-type', contentType)
          .attr('data-reelgood-preview-id', contentId)
          .attr('data-widget-host', 'reelgood-pub')
          .css('display', 'flex')

          $('<script>').attr('type', 'text/props').text(JSON.stringify(deepSettings)).appendTo(listingDiv);

        widget.replaceWith(listingDiv)
      }

      var rgBundleJS = $('<script>')
        .attr('id', 'reelgood-js-widget-bundle')
        .attr('src', REELGOOD_PUBLISHERS_WIDGET_BUNDLE_JS_URL)
        .attr('data-api-key', REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY)
        .attr('async', true)
        .appendTo($(document.head));

      if (rgbundle.dev_env ===  'reelgood_bool_on') {
        rgBundleJS.attr('data-env', 'development')
      }
    }
  });        
}

function addServicePriorityRows() {
  var data = {
    'action': 'reelgood_get_service_priorities'
  };

  jQuery.post(rgajax.url, data, function(response) {
    if (response.success && response.data) {
      if (!response.data.set_platforms.length) {
        $('#reelgood_service_settings_current_service_settings').empty()

        var row = $('<div>')
            .attr('class', 'reelgood_row reelgood_row_center')
            .appendTo($('#reelgood_service_settings_current_service_settings'))

        $('<div>')
          .attr('class', 'reelgood_subsubtitle')
          .text('You have no services set to priority and are using the default display order.')
          .appendTo(row)
      } else {
        $('#reelgood_service_settings_current_service_settings').empty()

        for (var i = 0; i < response.data.set_platforms.length; i++) {
          var platform = response.data.set_platforms[i]
          var row = $('<div>')
            .attr('class', 'reelgood_row reelgood_row_center')
            .appendTo($('#reelgood_service_settings_current_service_settings'))

          $('<div>')
            .attr('class', 'reelgood_subsubtitle')
            .text(`Service Priority ${i + 1}`)
            .appendTo(row)

          $('<div>')
            .attr('class', 'reelgood_desc')
            .text(platform.name)
            .appendTo(row)
        }
      }
    }
  });
}
