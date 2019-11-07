jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var gameVars = JSON.parse($('#game-vars').text());

    var socket = io('//' + window.location.hostname + ':3000');

    socket.on('connect', function() {
      socket.emit('creategame', gameVars.gameToken, function(gameName) {
        console.log('joined game ' + gameName);
        $('#status').text('Connected');
      });
    });

    socket.on('disconnect', function() {
      console.log('socket connection lost');
      $('#status').text('Disconnected');
    });

    socket.on('newnumber', function(letter, number) {
      $('.latest').removeClass('latest');
      $('#board td[data-cell=' + number + ']').addClass('marked').addClass('latest');
      $('#number').text(letter + number);
    });

    socket.on('winner', function(winner) {
      console.log('congrats ' + winner + '!');
    });

    socket.on('newgame', function() {
      $('#board td').removeClass('marked');
      $('#number').text('');
    });

    $('#create-game').click(function() {
      if (window.confirm('Create a new game?')) {
        var postData = {
          json: true,
          action: 'createGame'
        };
        $.post(window.location, postData, function() {
          socket.emit('newgame');
        }, 'json');
      }
    });

    $('#call-number').click(function() {
      var postData = {
        json: true,
        action: 'callNumber'
      };
      $.post(window.location, postData, function(data) {
        socket.emit('newnumber', data.letter, data.number);
      }, 'json');
    });

    $('#source-url').click(function() {
      $(this).select();
    });

    $('#copy-source-url').click(function() {
      $('#source-url').select();
      document.execCommand('copy');
    });
  });
})(jQuery);
