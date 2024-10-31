jQuery(function($) {
  $(document).ready(function(){
    $('#reelgood_default_styling_reset_all_widgets_button').click(openResetSettingsPopup);
  });

  function openResetSettingsPopup() {
    if (!modalIsOpen()) {
      var footerButtons = [
        $('<div>Cancel</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_admin_default_styling_reset_widgets_cancel'
        }),
        $('<div>Update All Widgets to Default</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_admin_default_styling_reset_widgets_reset'
        })
      ]

      var resetWidgetsModalContent = $('<span>')

      $('<div></div>').attr('class', 'reelgood_title').appendTo(resetWidgetsModalContent);
      $('<div></div>').attr('class', 'reelgood_desc').appendTo(resetWidgetsModalContent);

      presentTopCenterIconModal(
        'Warning: Update All Widgets Cannot Be Undone',
        'This will change all the widgets you have posted before to match the default styling. It can only be changed by updating the default styling and then updating all to default again.',
        footerButtons,
        true,
        'warning'
      )

      $('#reelgood_admin_default_styling_reset_widgets_cancel').click(closeModal);
      $('#reelgood_admin_default_styling_reset_widgets_reset').click(resetAllWidgetsToGLobalDefault);
    }
  }

  function resetAllWidgetsToGLobalDefault() {
    if (!confirm('This will change all the widgets you have currently posted to match the default styling. This cannot be undone.\n Are you sure?')) { return; }

    blockModalClose(true);

    var footerButtons = [
      $('<div>Close</div>').attr({
        'class': 'reelgood_button_big',
        'id': 'reelgood_admin_default_styling_reset_widgets_close'
      })
        .attr('disabled', true)
    ]

    var resetWidgetsModalContent = $('<span>')
    $('<div>Updating...</div>').attr('class', 'reelgood_title').appendTo(resetWidgetsModalContent);

    setNewModalContent(resetWidgetsModalContent.html(), footerButtons, true);
    $('#reelgood_admin_default_styling_reset_widgets_close').click(closeModal);

    var data = {
      'action': 'reelgood_reset_all_widgets_to_global_default'
    };

    jQuery.post(rgajax.url, data, function(response) {
      var resetWidgetsModalContent = $('<span>')
      if (response.success) {
        $('<div>Success!</div>').attr('class', 'reelgood_title').appendTo(resetWidgetsModalContent);
      } else {
        $('<div>Oops... Something went wrong!</div>').attr('class', 'reelgood_title').appendTo(resetWidgetsModalContent);
      }
      setNewModalContent(resetWidgetsModalContent.html(), undefined, true)

      $('#reelgood_admin_default_styling_reset_widgets_close').removeAttr('disabled');
      blockModalClose(false);
    });
  }
});
