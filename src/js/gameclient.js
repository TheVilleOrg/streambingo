jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var socket = io('//' + window.location.hostname + ':3000');

    socket.on('connect', function() {
      $('#status').text('Connected');
    });

    socket.on('disconnect', function() {
      console.log('socket connection lost');
      $('#status').text('Disconnected');
    });

    $('#card .marker').click(function() {
      var cell = $(this);
      var index = cell.data('cell');
      if (index === 12) {
        return;
      }

      var postData = {
        json: true,
        action: 'toggleCell',
        cell: index
      };
      $.post(window.location, postData, function(data) {
        if (data.marked) {
          cell.addClass('marked');
        } else {
          cell.removeClass('marked');
        }
      }, 'json');
    });
  });
})(jQuery);
