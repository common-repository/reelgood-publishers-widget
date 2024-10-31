const defaultServicePlatform = { name: 'Default', id: 0, group_id: 0 }

var setServicePlatforms = []
var availableServicePlatforms = []

var minVisibleServices = 6

jQuery(function($) {
  $(document).ready(function(){
    $('#reelgood_service_settings_edit_priority').click(openEditServicePriorityPopup);
  });

  function saveServicePriority() {
    if ($(this).attr('disabled')) { return }

    var data = {
      'action': 'reelgood_set_service_priorities',
      'service_priorities': getAllSetValues().filter((value) => value.id !== defaultServicePlatform.id)
    };
  
    jQuery.post(rgajax.url, data, function(response) {
      closeModal()
      refreshWidgets()
      addServicePriorityRows()
    });
  }

  function addRow() {
    setServicePlatforms.push(defaultServicePlatform)
    setServicePrioritiesRows()
  }

  function addDropdownListeners() {
    $('.reelgood_dropdown').click(function (e) {
      var allDropdowns = $('.reelgood_dropdown');

      for (var i = 0; i < allDropdowns.length; i++) {
        var dropdownKey = $(allDropdowns.get(i)).attr('reelgood-settings-keys');

        if (dropdownKey === $(this).attr('reelgood-settings-keys')) { continue }

        var dropdownArrow = $($(allDropdowns.get(i)).find('.reelgood_dropdown_arrow').first());
        dropdownArrow.empty();
        var dropdownOptions = $(allDropdowns.get(i)).find('.reelgood_dropdown_options').first();
        dropdownOptions.css('display', '');
        setDropdownArrow(dropdownArrow, false)
      }

      var toggledOptions = $(this).find('.reelgood_dropdown_options').first()
      var alreadyToggled = toggledOptions.css('display') === 'block';
      toggledOptions.css('display', alreadyToggled ? '' : 'block');
      var dropdownArrow = $($(this).find('.reelgood_dropdown_arrow').first());
      dropdownArrow.empty();
      setDropdownArrow(dropdownArrow, !alreadyToggled)

      stopEvent(e);
    });

    $('.reelgood_dropdown_options_option').click(function (e) {
      var dropdown = $(this).parent().parent();
      
      dropdown.find('.reelgood_dropdown_title').first().html($(this).html())
      dropdown.attr('reelgood-settings-id', $(this).attr('reelgood-settings-id'));
      dropdown.attr('reelgood-settings-group-id', $(this).attr('reelgood-settings-group-id'));
      servicePrioritiesDidChange()    
    });
  }

  function addDragDrop() {
    $('#reelgood_service_priorities').sortable({
      handle: '.reelgood_service_priority_row_drag',
      animation: 150,
      ghostClass: 'reelgood_service_priority_row_dragging',
      onMove: function (event) {
        if (!event.dragged) { return }
        $('.reelgood_service_priority_row').not($('.reelgood_service_priority_row[draggable="true"]')).find('.reelgood_service_priority_row_drag_backdrop').css('background-color', 'unset');
      },
      onUpdate: function (event) {
        servicePrioritiesDidChange()
        $('.reelgood_service_priority_row').find('.reelgood_service_priority_row_drag_backdrop').removeAttr('style');
      }
    })
  }

  function setServicePrioritiesRows() {
    $('#reelgood_service_priorities').empty()
    $('#reelgood_priority_indices').empty()

    for (var i = 0; i < setServicePlatforms.length; i++) {
      priorityRowForService(setServicePlatforms[i], i)
    }

    $('<div>Add a new service</div>')
      .attr({
        'class': 'reelgood_button_inline',
        'id': 'reelgood_service_priority_add_row'
      })
      .appendTo($('#reelgood_service_priorities'))
      .click(addRow)
    
    setAddRowVisiblity()
    addDropdownListeners()
    addDropdownArrows()
    addDragDrop()
  }

  function setAddRowVisiblity() {
    var noDefaults = (setServicePlatforms.filter((value) => value.id === defaultServicePlatform.id).length === 0)
    var optionsLeft = availableServicePlatforms.length > setServicePlatforms.length

    $('#reelgood_service_priority_add_row')
      .css('display', (noDefaults && optionsLeft) ? 'block' : 'none')
  }

  function getAllSetValues() {
    var allRows = $('.reelgood_service_priority_row')
    var newSetValues = allRows.map(
      (index) => {
        var row = allRows.get(index)
        var dropdown = $($(row).find('.reelgood_dropdown').first())
        return {
          'name': $(dropdown.find('.reelgood_dropdown_title').first()).text(),
          'id': parseInt(dropdown.attr('reelgood-settings-id')),
          'group_id': parseInt(dropdown.attr('reelgood-settings-group-id'))
        }
      }
    )

    return [...Array(newSetValues.length).keys()].map((index) => newSetValues[index])
  }

  function servicePrioritiesDidChange() {
    setServicePlatforms = getAllSetValues()
    setServicePrioritiesRows()
  }

  function priorityRowForService(service, index) {
    var row = $('<li>')
      .attr('class', 'reelgood_row reelgood_row_center reelgood_service_priority_row')
      .css('z-index', 99999 - index)
      .appendTo($('#reelgood_service_priorities'))

    $('<span>')
      .attr('class', 'reelgood_subsubtitle reelgood_service_priority_row_title')
      .text(`Service Priority ${index + 1}`)
      .appendTo($('#reelgood_priority_indices'))

    $('<img>')
      .attr({
        'class': 'reelgood_service_priority_row_drag',
        'src': `${rgcontext.location}images/service_drag.svg`
      })
      .appendTo(row)
    
    $('<div>')
      .attr('class', 'reelgood_service_priority_row_drag_backdrop')
      .appendTo(row)

    var dropdown = $('<div>')
      .attr({
        'class': 'reelgood_dropdown reelgood_service_priority_dropdown',
        'reelgood-settings-keys': index,
        'reelgood-settings-id': service.id,
        'reelgood-settings-group-id': service.group_id
      })
      .appendTo(row)

    var dropdownRow = $('<div>')
      .attr('class', 'reelgood_row reelgood_fill_width')
      .appendTo(dropdown)

    $('<div>')
      .attr('class', 'reelgood_dropdown_title reelgood_fill_width')
      .text(service.name)
      .appendTo(dropdownRow)

    $('<div>')
      .attr('class', 'reelgood_dropdown_arrow')
      .appendTo(dropdownRow)

    var optionsDiv = $('<div>')
      .attr('class', 'reelgood_dropdown_options')
      .appendTo(dropdown)

    $('<div>')
      .attr({
        'class': 'reelgood_dropdown_options_option', 
        'reelgood-settings-id': defaultServicePlatform.id,
        'reelgood-settings-group-id': defaultServicePlatform.group_id
      })
      .text(defaultServicePlatform.name)
      .appendTo(optionsDiv)

    var selectedValues = setServicePlatforms.map((value) => value.id)
    var filteredPossibleServices = availableServicePlatforms.filter((value) => !selectedValues.includes(value.id))

    for (var j = 0; j < filteredPossibleServices.length; j++) {
      var option = filteredPossibleServices[j]

      $('<div>')
        .attr({
          'class': 'reelgood_dropdown_options_option', 
          'reelgood-settings-id': option.id,
          'reelgood-settings-group-id': option.group_id
        })
        .text(option.name)
        .appendTo(optionsDiv)
    }

    return row
  }

  function openEditServicePriorityPopup() {
    if (!modalIsOpen()) {
      var data = {
        'action': 'reelgood_get_service_priorities'
      };
    
      jQuery.post(rgajax.url, data, function(response) {
        if (!response.success || modalIsOpen()) {
          return;
        }

        setServicePlatforms = response.data.set_platforms
        availableServicePlatforms = response.data.available_platforms
        
        if (setServicePlatforms.length < minVisibleServices) {
          var defaultServiceRows = Math.min(
            minVisibleServices - setServicePlatforms.length,
            availableServicePlatforms.length - setServicePlatforms.length
          )
          padArray(setServicePlatforms, defaultServiceRows, defaultServicePlatform)
        }

        var footerButtons = [
          $('<div>Cancel</div>').attr({
            'class': 'reelgood_button_big',
            'id': 'reelgood_service_settings_cancel'
          }),
          $('<div>Save</div>').attr({
            'class': 'reelgood_button_big',
            'id': 'reelgood_service_settings_save'
          })
        ]

        var servicePriorityModalContent = $('<span>')

        $('<div>Edit Service Priority</div>').attr('class', 'reelgood_title').appendTo(servicePriorityModalContent);
        $('<div>Enter the services you would like to appear in the desired order below. Saving the settings will update all previously published widgets on this domain. You do not need to fill in all, all other services will follow in the default order set by Reelgood based on popularity and streaming quality.</div>').attr('class', 'reelgood_desc').appendTo(servicePriorityModalContent);

        var columnsRow = $('<div>')
          .attr({
            'class': 'reelgood_row',
            'id': 'reelgood_priority_column'
          })
          .appendTo(servicePriorityModalContent)
        $('<div>').attr({
          'class': 'reelgood_column',
          'id': 'reelgood_priority_indices'
        })
          .appendTo(columnsRow)
        $('<ul>').attr({
          'class': 'reelgood_column noselect',
          'id': 'reelgood_service_priorities'
        }).appendTo(columnsRow)

        presentModal(getModal(servicePriorityModalContent.html(), footerButtons, true));

        setServicePrioritiesRows()

        $('#reelgood_service_settings_cancel').click(closeModal)
        $('#reelgood_service_settings_save').click(saveServicePriority)
      })
    }
  }
});
