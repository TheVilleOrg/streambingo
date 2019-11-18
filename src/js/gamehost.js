jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var gameVars = JSON.parse($('#game-vars').text());

    $('body').removeClass('nojs');

    var socket = io('//' + window.location.hostname + ':3000');

    var bingoBall = $('.bingo-ball.template');
    bingoBall.removeClass('template').remove();

    var autoCallTimer;

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
      console.log('called ' + letter + number);
      $('.latest').removeClass('latest');
      $('#board td[data-cell=' + number + ']').addClass('marked').addClass('latest');
      $('#last-number').text(letter + number);

      var ball = bingoBall.clone();
      ball.addClass(letter.toLowerCase());
      ball.find('.letter').text(letter);
      ball.find('.number').text(number);
      $('#inner-container').append(ball);
      setTimeout(function() {
        ball.remove();
      }, 8000);
    });

    socket.on('gameover', function(gameName, winner) {
      console.log('game ended');
      if (winner) {
        console.log('congrats ' + winner + '!');
        $('.game-winner').text(winner);
        $('#winner-display').show().find('strong').text(winner);
      }

      $('#call-number').prop('disabled', true);
    });

    socket.on('resetgame', function() {
      console.log('reset game');
      $('#board td').removeClass('marked');
      $('#last-number').text('--');
      $('#game-winner').text('--');
      $('#card-count').text('0 Players');
      $('#call-number').prop('disabled', false);
      $('#create-game').prop('disabled', false);
      $('#winner-display').hide();
    });

    $('#create-game').click(function() {
      if (window.confirm('Create a new game?')) {
        $('#call-number').prop('disabled', true);
        $('#create-game').prop('disabled', true);

        var postData = {
          json: true,
          action: 'createGame',
          autoCall: $('#auto-call').prop('checked') ? $('#auto-call-interval').val() : 0
        };
        $.post(window.location, postData);
      }
    });

    $('#call-number').click(function() {
      callNumber();
    });

    $('#auto-call').change(function() {
      setAutoCall();
    });

    $('#auto-call-interval').change(function() {
      setAutoCall();
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

    function callNumber() {
      if (autoCallTimer) {
        clearInterval(autoCallTimer);
        autoCallTimer = undefined;
      }

      $('#call-number').prop('disabled', true);
      $('#create-game').prop('disabled', true);

      var postData = {
        json: true,
        action: 'callNumber'
      };
      $.post(window.location, postData, function() {
        $('#create-game').prop('disabled', false);
        setTimeout(function() {
          $('#call-number').prop('disabled', false);
        }, 10000);
        setAutoCall();
      }, 'json');
    }

    function setAutoCall() {
      if (autoCallTimer) {
        clearInterval(autoCallTimer);
        autoCallTimer = undefined;
      }

      var enabled = $('#auto-call').prop('checked');
      var interval = $('#auto-call-interval').val();

      if (enabled) {
        autoCallTimer = setInterval(function() {
          callNumber();
        }, interval * 1000);
      }

      var postData = {
        json: true,
        action: 'updateAutoCall',
        interval: interval
      };
      $.post(window.location, postData);
    }
  });
})(jQuery);
