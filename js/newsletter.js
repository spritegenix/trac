$(function () {
  // Get the form.
  var form = $('.newsletter-form');

  // Get the messages div.
  var formMessages = $('.newsletter-response');

  // Set up an event listener for the contact form.
  $(form).submit(function (e) {
    // Stop the browser from submitting the form.
    e.preventDefault();

    // Serialize the form data.
    var formData = $(form).serialize();
    // Submit the form using AJAX.
    $('.newsletter-btn').text('Sending...');
    $.ajax({
      type: 'POST',
      url: $(form).attr('action'),
      data: formData,
    })
      .done(function (response) {
        // Make sure that the formMessages div has the 'success' class.
        $(formMessages).removeClass('error');
        $(formMessages).addClass('success');

        // Set the message text.
        $(formMessages).text(response);

        // Clear the form.
        $('.newsletter-form input,.newsletter-form textarea').val('');
        $('.newsletter-btn').text('Subscribed');
        setTimeout(() => {
          $('.newsletter-btn').text('Subscribe now');
        }, 3000);
      })
      .fail(function (data) {
        // Make sure that the formMessages div has the 'error' class.
        $(formMessages).removeClass('success');
        $(formMessages).addClass('error');

        // Set the message text.
        if (data.responseText !== '') {
          $(formMessages).text(data.responseText);
        } else {
          $(formMessages).text(
            'Oops! An error occured and your message could not be sent.'
          );
        }
        setTimeout(() => {
          $('.newsletter-btn').text('Subscribe now');
        }, 3000);
      });
  });
});
