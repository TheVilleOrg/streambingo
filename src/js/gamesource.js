'use strict';

$(function() {
  $('body').removeClass('nojs');

  var gameVars = JSON.parse($('#game-vars').text());

  var autoRestartTimer;
  var autoRestartCountdown;
  var autoEndTimer;
  var autoEndCountdown;

  var socket = io('//' + window.location.hostname + ':3000');

  var bingoBall = $('.bingo-ball.template');
  bingoBall.removeClass('template').remove();

  socket.on('connect', function() {
    socket.emit('getgame', gameVars.gameToken, function(gameName, ended, winner) {
      console.log('joined game ' + gameName);

      gameVars.ended = ended;
      gameVars.winner = winner;

      updateEndgamePanel();
    });
  });

  socket.on('disconnect', function() {
    console.warn('socket connection lost');

    clearTimers();
  });

  socket.on('numbercalled', function(letter, number) {
    console.log('called ' + letter + number);

    $('.latest').removeClass('latest');
    $('#board td[data-cell=' + number + ']').addClass('marked').addClass('latest');

    var ball = bingoBall.clone();
    ball.addClass(letter.toLowerCase());
    ball.find('.letter').text(letter);
    ball.find('.number').text(number);
    $('#inner-container').append(ball);
    setTimeout(function() {
      ball.remove();
    }, 8000);

    if (gameVars.tts) {
      new Audio('../../audio/' + gameVars.ttsVoice + '/' + letter.toLowerCase() + number + '.ogg').play();
    }
  });

  socket.on('gamesettings', function (settings) {
    gameVars.tts = settings.tts;
    gameVars.ttsVoice = settings.ttsVoice;
  });

  socket.on('addplayer', function () {
    gameVars.cardCount++;
    var count = gameVars.cardCount + ' ' + (gameVars.cardCount === 1 ? ' Player' : ' Players');
    $('#card-count').text(count);
  })

  socket.on('gameover', function(gameName, winner) {
    gameVars.ended = true;
    gameVars.winner = winner;
    updateEndgamePanel();
  });

  socket.on('resetgame', function() {
    console.log('reset game');
    $('#board td').removeClass('marked');
    $('#card-count').text('0 Players');

    gameVars.ended = false;
    gameVars.winner = '';
    updateEndgamePanel();
  });

  socket.on('timer', function (name, running, value) {
    if (name === 'end') {
      if (autoEndTimer) {
        clearInterval(autoEndTimer);
        autoEndTimer = undefined;
      }

      if (running) {
        autoEndCountdown = value;
        $('#end-countdown').removeClass('hidden').find('strong').text(formatTime(autoEndCountdown));
        autoEndTimer = setInterval(function () {
          autoEndCountdown--;
          $('#end-countdown strong').text(formatTime(autoEndCountdown));
          if (!autoEndCountdown) {
            clearInterval(autoEndTimer);
            autoEndTimer = undefined;
            $('#end-countdown').addClass('hidden');
          }
        }, 1000);
      }
    } else if (name === 'restart') {
      if (autoRestartTimer) {
        clearInterval(autoRestartTimer);
        autoRestartTimer = undefined;
      }

      if (running) {
        autoRestartCountdown = value;
        autoRestartTimer = setInterval(function () {
          autoRestartCountdown--;
          if (!autoRestartCountdown) {
            clearInterval(autoRestartTimer);
            autoRestartTimer = undefined;
          }
        }, 1000);
      }
    }
  });

  function updateEndgamePanel() {
    if (gameVars.ended) {
      console.log('game ended');

      if (gameVars.tts) {
        new Audio('../../audio/' + gameVars.ttsVoice + '/gameover.ogg').play();
      }

      if (gameVars.winner) {
        console.log('congrats ' + gameVars.winner + '!');
        $('#winner-display').show().find('strong').text(gameVars.winner);
      }
    } else {
      $('#winner-display').hide();
    }
  }

  function clearTimers() {
    if (autoRestartTimer) {
      clearInterval(autoRestartTimer);
      autoRestartTimer = undefined;
    }

    if (autoEndTimer) {
      clearInterval(autoEndTimer);
      autoEndTimer = undefined;
    }
  }

  function formatTime(seconds) {
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;

    return m + ':' + (s < 10 ? '0' + s : s);
  }
});
