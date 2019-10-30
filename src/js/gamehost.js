(function ($) {
  $(function () {
    $('#call-number').click(function () {
      let postData = {
        json: true,
        action: 'callNumber'
      };
      $.post(window.location, postData, function (data) {
        $('#board td[data-cell=' + data.number + ']').addClass('marked');
        $('#number').text(data.letter + data.number);
      }, 'json');
    });

    $('#create-game').click(function () {
      let postData = {
        json: true,
        action: 'createGame'
      };
      $.post(window.location, postData, function (data) {
        $('#board td').removeClass('marked');
        $('#number').text('');
      }, 'json');
    })
  });
}(jQuery));
