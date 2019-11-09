jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var gameVars = JSON.parse($('#game-vars').text());

    var socket = io('//' + window.location.hostname + ':3000');

    var bingoBall = $('.bingo-ball');

    socket.on('connect', function() {
      socket.emit('getgame', gameVars.gameToken, function(gameName) {
        console.log('joined game ' + gameName);
        $('#connection-status span').text('Connected');
      });
    });

    socket.on('disconnect', function() {
      console.log('socket connection lost');
      $('#connection-status span').text('Disconnected');
    });

    socket.on('newnumber', function(letter, number) {
      $('.latest').removeClass('latest');
      $('#board td[data-cell=' + number + ']').addClass('marked').addClass('latest');
      $('#last-number').text(letter + number);

      var ball = bingoBall.clone();
      ball.addClass(letter.toLowerCase());
      ball.find('.letter').text(letter);
      ball.find('.number').text(number);
      bingoBall.before(ball);
      ball.css('animation-play-state', 'running').find('.inner-ball').css('animation-play-state', 'running');
      setTimeout(function() {
        ball.remove();
      }, 8000);
    });

    socket.on('winner', function(winner) {
      console.log('congrats ' + winner + '!');
    });

    socket.on('newgame', function() {
      $('#board td').removeClass('marked');
      $('#last-number').text('');
      $('.bingo-ball').finish();
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
