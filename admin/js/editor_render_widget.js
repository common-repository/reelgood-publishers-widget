jQuery(function($) {
  $(document).ready(function(){
    $('#content-tmce').click(resetBundle);
    $('#content-html').on('mouseup', resetRenderedWidget);
    $('#publish').on('mouseup', publishWidgets);
    
    injectBundle();

    var checkExist = setInterval(function() {
      if (typeof content_ifr === 'undefined') { return }
      var tinyMCE = $(content_ifr)
      if (typeof tinyMCE !== 'undefined' && tinyMCE.contents()) {
        $($(tinyMCE.contents().get(0)).find('body').first()).on('mouseover', '.reelgood_publishers_widget > span', addEditWidgetButton);

        clearInterval(checkExist);
      }
    }, 100); // check every 100ms
  });

  function resetRenderedWidget() {
    forceAddLinksToWidgets()
  }

  function publishWidgets() {
    forceAddLinksToWidgets()
  }

  function forceAddLinksToWidgets() {
    if (typeof content_ifr === 'undefined') { return }
    var tinyMCE = $(content_ifr)
    var renderedWidgets = $(tinyMCE.contents().get(0)).find('.reelgood_publishers_widget')
    var replaceWith = []

    for (var i = 0; i < renderedWidgets.length; i++) {
      var oldWidget = $(renderedWidgets.get(i))
      oldWidget.empty()

      var currentHTML = oldWidget.prop('outerHTML')
      var widget = oldWidget.clone()

      widget.removeAttr('data-mce-style', '')
      var contentType = widget.attr('data-reelgood-preview-content-type')
      var slug = widget.attr('data-reelgood-preview-slug')
      var title = widget.attr('data-reelgood-preview-title')

      widget.html(
        $(`<a>${title}</a>`)
          .attr('href', `https://www.reelgood.com/${contentType.toLowerCase()}/${slug}`)
          .attr('target', '_blank')
          .prop('outerHTML')
      )
      
      replaceWith.push({old: currentHTML, new: widget.prop('outerHTML')})
      oldWidget.replaceWith(widget)
    }

    var editor = $($('#wp-content-editor-container').find('.wp-editor-area'))
    var oldEditorValue = editor.val()
    for (var i = 0; i < replaceWith.length; i++) {
      oldEditorValue = oldEditorValue.replace(replaceWith[i].old, replaceWith[i].new)
    }
    editor.val(oldEditorValue)
    editor.text(oldEditorValue)
  }

  function addEditWidgetButton() {
    if (!$(this).find('.reelgood_pub_edit_widget_button_wrapper').length) {
      $($(this).children().first()).css('pointer-events', 'none');

      var widgetEditButtonWrapper = $('<div>')
        .attr('class', 'reelgood_pub_edit_widget_button_wrapper')
        .attr('data-reelgood-pub-widget-instance', $(this).parent().attr('id'))
        .appendTo($(this));

      $('<div>')
        .attr('class', 'reelgood_pub_edit_widget_button_edit')
        .attr('contenteditable', false)
        .html('Edit')
        .appendTo(widgetEditButtonWrapper);

      $('<div>')
      .attr('class', 'reelgood_pub_edit_widget_button_remove')
      .attr('contenteditable', false)
      .html('Remove')
      .appendTo(widgetEditButtonWrapper);
    }
  }

  function resetBundle() {
    if (!rgbundle.api_key) { return }

    var checkExist = setInterval(function() {
      if (typeof content_ifr === 'undefined') { return }
      var tinyMCE = $(content_ifr)
      if (typeof tinyMCE !== 'undefined' && tinyMCE.contents()) {
        var tinymce = tinyMCE.contents();

        // $(tinymce.get(0)).find('.reelgood_publishers_widget').attr('contenteditable', false)

        var jsBundle = tinymce.get(0).getElementById('reelgood-js-widget-bundle')

        if (jsBundle) {
          jsBundle.remove();
        }

        var rgBundleJS = document.createElement('script');
        rgBundleJS.id = 'reelgood-js-widget-bundle';
        rgBundleJS.src = rgbundle.js;
        rgBundleJS.setAttribute('data-api-key', rgbundle.api_key);
        rgBundleJS.setAttribute('async', true);
        tinymce.get(0).head.appendChild(rgBundleJS);

        clearInterval(checkExist);
      }
    }, 100); // check every 100ms
  }

  function injectBundle() {
    if (!rgbundle.api_key) { return }
    
    var checkExist = setInterval(function() {
      if (typeof content_ifr === 'undefined') { return }
      var tinyMCE = $(content_ifr)
      if (typeof tinyMCE !== 'undefined' && tinyMCE.contents()) {
        var tinymce = tinyMCE.contents();

        var cssGlobalBundle = document.getElementById('reelgood-css-widget-bundle')
        if (!cssGlobalBundle) {
          var rgBundleStyling = document.createElement('link');
          rgBundleStyling.href = rgbundle.css;
          rgBundleStyling.rel = 'stylesheet';
          rgBundleStyling.id = 'reelgood-css-widget-bundle';
          document.head.appendChild(rgBundleStyling);
        };

        var cssBundle = tinymce.get(0).getElementById('reelgood-css-widget-bundle')
        if (!cssBundle) {
          var rgBundleStyling = document.createElement('link');
          rgBundleStyling.href = rgbundle.css;
          rgBundleStyling.rel = 'stylesheet';
          rgBundleStyling.id = 'reelgood-css-widget-bundle';
          tinymce.get(0).head.appendChild(rgBundleStyling);
        };

        var jsBundle = tinymce.get(0).getElementById('reelgood-js-widget-bundle')
        if (!jsBundle) {
          var rgBundleJS = document.createElement('script');
          rgBundleJS.id = 'reelgood-js-widget-bundle';
          rgBundleJS.src = rgbundle.js;
          rgBundleJS.setAttribute('data-api-key', rgbundle.api_key);
          rgBundleJS.setAttribute('async', true);
          tinymce.get(0).head.appendChild(rgBundleJS);
        };

        var cssBundleEditWidget = tinymce.get(0).getElementById('reelgood-css-widget-edit-widget-bundle')
        if (!cssBundleEditWidget) {
          var rgBundleStyling = document.createElement('link');
          rgBundleStyling.href = `${rgcontext_editor.location}styles/editor_edit_widget_instance.css?ver=${Math.floor(Math.random() * 10000)}`;
          rgBundleStyling.rel = 'stylesheet';
          rgBundleStyling.id = 'reelgood-css-widget-edit-widget-bundle';
          tinymce.get(0).head.appendChild(rgBundleStyling);
        };

        clearInterval(checkExist);
      }
    }, 100); // check every 100ms
  }
});
