jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var gameVars = JSON.parse($('#game-vars').text());

    var socket = io('//' + window.location.hostname + ':3000');

    var bingoBall = $('.bingo-ball');

    socket.on('connect', function() {
      socket.emit('getgame', gameVars.gameToken, function(gameName, ended) {
        console.log('joined game ' + gameName);
        $('#connection-status span').text('Connected');

        if (!ended) {
          $('#call-number').prop('disabled', false);
        }

        $('#create-game').prop('disabled', false);
      });
    });

    socket.on('disconnect', function() {
      console.warn('socket connection lost');
      $('#connection-status span').text('Disconnected');
      $('#call-number').prop('disabled', true);
      $('#create-game').prop('disabled', true);
  });

    socket.on('numbercalled', function(letter, number) {
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

    socket.on('gameover', function(gameName, winner) {
      if (winner) {
        console.log('congrats ' + winner + '!');
        $('.game-winner').text(winner);
      }

      $('#call-number').prop('disabled', true);
    });

    socket.on('resetgame', function() {
      $('#board td').removeClass('marked');
      $('#last-number').text('');
      $('#card-count').text('0 Players');
      $('#call-number').prop('disabled', false);
    });

    $('#create-game').click(function() {
      if (window.confirm('Create a new game?')) {
        var postData = {
          json: true,
          action: 'createGame'
        };
        $.post(window.location, postData, function() {
          socket.emit('resetgame');
        }, 'json');
      }
    });

    $('#call-number').click(function() {
      var postData = {
        json: true,
        action: 'callNumber'
      };
      $.post(window.location, postData, function(data) {
        socket.emit('callnumber', data.letter, data.number);
      }, 'json');
    });

    $('#source-url').click(function() {
      $(this).select();
    });

    $('#copy-source-url').click(function() {
      $('#source-url').select();
      document.execCommand('copy');
    });

    setInterval(function() {
      var postData = {
        json: true,
        action: 'getStats'
      };
      $.post(window.location, postData, function(data) {
        $('#card-count').text(data.cardCount);
      }, 'json');
    }, 10000);
  });
})(jQuery);
