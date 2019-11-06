jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var socket = io('//' + window.location.hostname + ':3000');

    socket.on('connect', function() {
      socket.emit('joingame', $('#game-name').text());
      $('#status').text('Connected');
    });

    socket.on('disconnect', function() {
      console.log('socket connection lost');
      $('#status').text('Disconnected');
    });

    socket.on('newgame', function() {
      var postData = {
        json: true,
        action: 'newCard'
      };
      $.post(window.location, postData, function(data) {
        for (var i = 0; i < data.grid.length; i++) {
          if (i === 12) {
            continue;
          }

          $('#card .marker[data-cell=' + i + ']').removeClass('marked').text(data.grid[i]);
        }
      }, 'json');
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
