var searchQuery = "";
var searchResults = [];
var selectedSearchResults = [];

jQuery(function($) {
  $(document).ready(function(){
    addClickAddWidget()
  });

  function addClickAddWidget() {
    var checkExist = setInterval(function() {
      if ($('#insert-reelgood-widget-button').length) {
        $('#insert-reelgood-widget-button').click(openMediaSearchPopup);
        clearInterval(checkExist);
      }
    }, 100); // check every 100ms
  }

  function addClickListeners() {
    $('#reelgood-pub-widget-search-results').on('click', '.reelgood-pub-widget-search-item', function(e) { stopEvent(e); didSelectListing($(this)) });
    $('#reelgood-pub-widget-search-selected-results').on('click', '.reelgood-pub-widget-search-selected-item-remove', function(e) { stopEvent(e); didSelectRemoveSelectedListing($(this)) });
  }

  function confirmMediaSearchPopup() {
    if ($(this).attr('disabled')) { return }

    var data = {
      'action': 'reelgood_get_user_global_settings',
      'ids': selectedSearchResults
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success && response.data) {
        for (var i = 0; i < selectedSearchResults.length; i++) {
          var item = selectedSearchResults[i];

          var inlineSettingsProp = JSON.stringify(response.data)
            .replace(/"reelgood_bool_on"/g, 'true')
            .replace(/"reelgood_bool_off"/g, 'false')
            .replace(/"/g, '\'')

          var listingDiv = $('<div>')
            .attr({
              'class': 'reelgood_publishers_widget',
              'id': uuidv4(),
              'data-widget-host': 'reelgood-pub',
              'data-reelgood-preview-content-type': item['content_type'].toLowerCase(),
              'data-reelgood-preview-slug': item['slug'],
              'data-reelgood-preview-title': item['title'],
              'data-prop-props': `{'content_type': '${item['content_type'].toLowerCase()}','id': '${item['id']}','id_type': 'rg','settings':${inlineSettingsProp}}`,
              'contenteditable': 'false'
            })
            .css('display', 'flex')

          $('<a>')
            .attr({
              'href': `https://www.reelgood.com/${item['content_type'].toLowerCase()}/${item['slug']}`,
              'target': '_blank'
            })
            .appendTo(listingDiv)

          wp.media.editor.insert("\n&nbsp;\n" + listingDiv.prop('outerHTML')  + "\n&nbsp;");
        }

        if (selectedSearchResults.length) {
          var checkExist = setInterval(function() {
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

              clearInterval(checkExist);
            }
          }, 100); // check every 100ms
        }

        searchResults = []
        selectedSearchResults = []

        closeModal();
      } else {
        closeModal();
      }
    });    
  }

  function fetchSearchResults(query) {
    if (!query.trim()) { setSearchResults([]); return }

    var data = {
      'action': 'reelgood_search',
      'query': query
    };

    jQuery.post(rgajax.url, data, function(response) {
      if (response.success && response.data.results.length && response.data.query === $('#reelgood-pub-widget-search-input').attr('value')) {
        setSearchResults(JSON.parse(response.data.results));
      } else if ((!response.success || !response.data.results) && response.data.query === $('#reelgood-pub-widget-search-input').attr('value')) {
        setSearchResults([]);
      }
    });
  }

  function listingItemFor(itemData, index) {
    var searchItem = $('<div>')
      .attr({
        'class': 'reelgood-pub-widget-search-item',
        'role': 'button',
        'data-reelgood-search-index': index,
        'tabIndex': 1
      })

    var imageWrapper = $('<div>')
      .attr('class', 'reelgood-pub-widget-search-item-image-wrapper')
      .appendTo(searchItem);

    $('<img>')
      .attr({
        'class': 'reelgood-pub-widget-search-item-image',
        'data-async-image': true,
        'src': 'https://img.reelgood.com/content/'+ itemData.content_type.toLowerCase() + '/' + itemData.id + '/poster-342.jpg'
      })
      .appendTo(imageWrapper);

    $('<span>' + itemData.title + " (" + new Date(itemData.released_on).getFullYear() + ")" + '</span>')
      .appendTo(searchItem);

    if (itemData.content_type === 'Show') {
      $('<div>TV</div>')
        .attr('class', 'reelgood-pub-widget-tv-badge')
        .appendTo(searchItem);
    }

    return searchItem
  }

  function selectedListingFor(itemData, index) {
    var searchItem = $('<div>')
    .attr({
      'class': 'reelgood-pub-widget-search-selected-item',
      'data-reelgood-search-selected-index': index,
      'tabIndex': 1
    });

    var imageWrapper = $('<div>')
      .attr('class', 'reelgood-pub-widget-search-selected-item-image-wrapper')
      .appendTo(searchItem);

    $('<img>')
      .attr({
        'class': 'reelgood-pub-widget-search-selected-item-image',
        'data-async-image': true,
        'src': 'https://img.reelgood.com/content/'+ itemData.content_type.toLowerCase() + '/' + itemData.id + '/poster-342.jpg'
      })
      .appendTo(imageWrapper);

    $('<div>' + itemData.title + " (" + new Date(itemData.released_on).getFullYear() + ")" + '</div>')
      .attr('class', 'reelgood-pub-widget-search-selected-item-title')
      .appendTo(searchItem);

    if (itemData.content_type === 'Show') {
      $('<div>TV</div>')
        .attr('class', 'reelgood-pub-widget-tv-badge')
        .appendTo(searchItem);
    }   

    var remove = $('<div>')
      .attr({
        'role': 'button',
        'tabIndex': 1,
        'class': 'reelgood-pub-widget-search-selected-item-remove'
      })
      .appendTo(searchItem);

    $('<img>')
      .attr('src', `${rgcontext.location}images/remove.svg`)
      .appendTo(remove)

    $('<span>Remove</span>').appendTo(remove)

    return searchItem
  }

  function setSearchResults(results) {
    searchResults = results;

    $('#reelgood-pub-widget-search-results').empty();
    $('#reelgood-pub-widget-search-results').css('display', 'none');

    for (var i = 0; i < results.length; i++) {
      var itemNode = listingItemFor(results[i], i);
      itemNode.appendTo($('#reelgood-pub-widget-search-results'));
    }

    if (results.length) {
      $('#reelgood-pub-widget-search-results').css('display', 'block');
      $('#reelgood-pub-widget-search-results').scrollTop(0);
    }
  }

  function setSelectedSearchresults() {
    $('#reelgood-pub-widget-search-selected-results').empty();

    for (var i = 0; i < selectedSearchResults.length; i++) {
      var itemNode = selectedListingFor(selectedSearchResults[i], i);
      itemNode.appendTo($('#reelgood-pub-widget-search-selected-results'));
    }

    if (selectedSearchResults.length) {
      $('<div>Search again to add another show or movie. Each will be added as their own independent widget blocks.</div>')
        .attr('class', 'reelgood_desc_small')
        .appendTo($('#reelgood-pub-widget-search-selected-results'));
    } 

    if (selectedSearchResults.length) {
      $('#reelgood_editor_add_widget_add').removeAttr('disabled')
    } else {
      $('#reelgood_editor_add_widget_add').attr('disabled', true);
    }
  }

  function didSelectListing(item) {
    $('#reelgood-pub-widget-search-input').attr('value', '');
    var index = parseInt(item.attr('data-reelgood-search-index'), 10);
    var itemData = searchResults[index];

    setSearchResults([]);
    selectedSearchResults.push(itemData);
    setSelectedSearchresults()
  }

  function didSelectRemoveSelectedListing(item) {
    var index = parseInt($(item.parent()).attr('data-reelgood-search-selected-index'), 10);
    selectedSearchResults.splice(index, 1); 

    setSelectedSearchresults()
  }

  function openMediaSearchPopup() {
    if (!modalIsOpen()) {
      var footerButtons = [
        $('<div>Cancel</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_editor_add_widget_cancel'
        }),
        $('<div>Add</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_editor_add_widget_add'
        }).attr('disabled', true)
      ]

      var searchModalContent = $('<span>')

      $('<div>Add Where to Watch</div>').attr('class', 'reelgood_title').appendTo(searchModalContent);
      $('<div>Search for a movie or show to add</div>').attr('class', 'reelgood_subtitle').appendTo(searchModalContent);
      $('<input>').attr({
        'id': 'reelgood-pub-widget-search-input',
        'autocomplete': 'off'
      }).appendTo(searchModalContent);
      $('<div>Type the title of a show or movie to see results</div>').attr('class', 'reelgood_desc_small').appendTo(searchModalContent);
      $('<div>').attr('id', 'reelgood-pub-widget-search-selected-results').appendTo(searchModalContent);
      $('<div>').attr('id', 'reelgood-pub-widget-search-results').appendTo(searchModalContent);

      presentModal(getModal(searchModalContent.html(), footerButtons, true, false));

      $('#reelgood_editor_add_widget_cancel').click(closeModal);
      $('#reelgood_editor_add_widget_add').click(confirmMediaSearchPopup);

      var debounced = $.debounce(function(e) {
        fetchSearchResults($('#reelgood-pub-widget-search-input')[0].value);
      }, 100);

      $('#reelgood-pub-widget-search-input').on('input', debounced);

      addClickListeners()
    }
  }
});
