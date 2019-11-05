jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var socket = io('//' + window.location.hostname + ':3000');

    socket.on('connect', function() {
      var match = document.cookie.match(/\baccess_token=(.*)[;\b]/);
      if (match.length === 2) {
        socket.emit('creategame', match[1], function(gameName) {
          console.log('joined game ' + gameName);
          $('#status').text('Connected');
        });
      }
    });

    socket.on('disconnect', function() {
      console.log('socket connection lost');
      $('#status').text('Disconnected');
    });

    socket.on('newnumber', function(letter, number) {
      $('#board td[data-cell=' + number + ']').addClass('marked');
      $('#number').text(letter + number);
    });

    socket.on('winner', function(winner) {
      console.log('congrats ' + winner + '!');
    });

    $('#call-number').click(function() {
      socket.emit('callnumber');
    });

    $('#create-game').click(function() {
      if (window.confirm('Create a new game?')) {
        var postData = {
          json: true,
          action: 'createGame'
        };
        $.post(window.location, postData, function() {
          $('#board td').removeClass('marked');
          $('#number').text('');
        }, 'json');
      }
    });
  });
})(jQuery);
