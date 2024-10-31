jQuery(function($) {
  $(document).ready(function(){
    $('#reelgood_support_help_contact_button').click(openSupportPopup);
  });

  function validateTextFields() {
    var disabled = !$('#reelgood_support_help_input_name').val().length || ($('#reelgood_support_help_input_message').val().length < 8) || !validateEmail($('#reelgood_support_help_input_email').val())
    $('#reelgood_support_help_send').attr('disabled', disabled);
  }

  function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function sendSupportMessage() {
    if ($(this).attr('disabled')) { return }

    var url = `${rgbundle.gateway}/v1/feedback`
    var feedback = {
      'name': $('#reelgood_support_help_input_name').val(),
      'email': $('#reelgood_support_help_input_email').val(),
      'feedback': $('#reelgood_support_help_input_message').val(),
      'referer': window.location.href
    }

    var data = {
      'action': 'reelgood_feedback',
      'feedback': feedback
    };
  
    jQuery.post(rgajax.url, data, function(response) {
      if (response.success) {
        var footerButtons = [
          $('<div>Close</div>').attr({
            'class': 'reelgood_button_big',
            'id': 'reelgood_support_help_close'
          }),
        ]
  
        var supportModalContent = $('<span>')
  
        $('<div>Contact Us</div>').attr('class', 'reelgood_title').appendTo(supportModalContent);
        $('<div>Questions, bug reports, missing or wrong data, praise, or feature requests? We\'re all ears. Just fill out the form below and someone will get back you to soon!</div>')
          .attr('class', 'reelgood_desc')
          .appendTo(supportModalContent);

        setNewModalContent(supportModalContent.html(), footerButtons, true, 'success', 'Message Sent!');

        $('#reelgood_support_help_close').click(closeModal);
      } else {
        closeModal()
      }
    });
  }

  function openSupportPopup() {
    if (!modalIsOpen()) {
      var footerButtons = [
        $('<div>Cancel</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_support_help_cancel'
        }),
        $('<div>Send</div>').attr({
          'class': 'reelgood_button_big',
          'id': 'reelgood_support_help_send'
        })
          .attr('disabled', true)
      ]

      var supportModalContent = $('<span>')

      $('<div>Contact Us</div>').attr('class', 'reelgood_title').appendTo(supportModalContent);
      $('<div>Questions, bug reports, missing or wrong data, praise, or feature requests? We\'re all ears. Just fill out the form below and someone will get back you to soon!</div>').attr('class', 'reelgood_desc').appendTo(supportModalContent);

      var columnsRow = $('<div>').attr('class', 'reelgood_row').appendTo(supportModalContent)
      var leftColumn = $('<div>').attr('class', 'reelgood_column').appendTo(columnsRow)
      var rightColumn = $('<div>').attr('class', 'reelgood_column reelgood_fill_width').appendTo(columnsRow)

      $('<div>Your Email</div>').attr('class', 'reelgood_subsubtitle reelgood_support_help_input_title').appendTo(leftColumn)
      $('<input>')
        .attr({
          'class': 'reelgood_support_help_input_text',
          'type': 'text'
        })
        .attr('id', 'reelgood_support_help_input_email')
        .appendTo(
          $('<div>')
            .attr('class', 'reelgood_support_help_input_text_wrapper')
            .appendTo(rightColumn)
        )

      $('<div>Your Name</div>').attr('class', 'reelgood_subsubtitle reelgood_support_help_input_title').appendTo(leftColumn)
      $('<input>')
        .attr({
          'class': 'reelgood_support_help_input_text',
          'type': 'text'
        })
        .attr('id', 'reelgood_support_help_input_name')
        .appendTo(
          $('<div>')
            .attr('class', 'reelgood_support_help_input_text_wrapper')
            .appendTo(rightColumn)
        )

      $('<div>Your Message</div>').attr('class', 'reelgood_subsubtitle reelgood_support_help_input_title').appendTo(leftColumn)
      $('<textarea>')
        .attr({
          'class': 'reelgood_support_help_input_textarea',
          'id': 'reelgood_support_help_input_message'
        })
        .appendTo(
          $('<div>')
            .attr('class', 'reelgood_support_help_input_textarea_wrapper')
            .appendTo(rightColumn)
        )
      

      presentModal(getModal(supportModalContent.html(), footerButtons, true));

      $('#reelgood_support_help_cancel').click(closeModal);
      $('#reelgood_support_help_send').click(sendSupportMessage);

      $('#reelgood_support_help_input_name').on('input', validateTextFields);
      $('#reelgood_support_help_input_email').on('input', validateTextFields);
      $('#reelgood_support_help_input_message').on('input', validateTextFields);
    }
  }
});
