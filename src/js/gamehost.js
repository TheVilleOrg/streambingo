'use strict';

$(function() {
  var gameVars = JSON.parse($('#game-vars').text());

  $('body').removeClass('nojs');

  var socket = io('//' + window.location.hostname + ':3000');

  var connected = false;

  var autoCallTimer;
  var autoRestartTimer;
  var autoRestartCountdown;
  var autoEndTimer;
  var autoEndCountdown;
  var settingsUpdateTimer;

  var uncalledNumbers = 75 - $('#board .marked').length;

  socket.on('connect', function() {
    socket.emit('getgame', gameVars.gameToken, function(gameName, ended, winner) {
      console.log('joined game ' + gameName);

      connected = true;

      $('#connection-status span').text('Connected');

      gameVars.ended = ended;
      gameVars.winner = winner;

      updateGameState();
      updateAutoCall();
      updateAutoRestart();
      updateAutoEnd();

      $('#create-game').prop('disabled', false);
    });
  });

  socket.on('disconnect', function() {
    console.warn('socket connection lost');

    connected = false;

    $('#connection-status span').text('Disconnected');
    $('#call-number').prop('disabled', true);
    $('#create-game').prop('disabled', true);

    clearTimers();
  });

  socket.on('numbercalled', function(letter, number) {
    console.log('called ' + letter + number);

    uncalledNumbers--;

    $('.latest').removeClass('latest');
    $('#board td[data-cell=' + number + ']').addClass('marked').addClass('latest');
    $('#last-number').text(letter + number);

    updateAutoEnd();
  });

  socket.on('addplayer', function () {
    gameVars.cardCount++;
    var count = gameVars.cardCount + ' ' + (gameVars.cardCount === 1 ? ' Player' : ' Players');
    $('#card-count').text(count);
  })

  socket.on('gameover', function(gameName, winner) {
    console.log('game ended');

    if (!gameVars.ended) {
      gameVars.ended = true;
      updateAutoRestart();
    }

    gameVars.winner = winner;
    updateGameState();
  });

  socket.on('resetgame', function() {
    console.log('reset game');

    $('#board td').removeClass('marked');
    $('#last-number').text('--');
    $('#game-winner').text('--');
    $('#card-count').text('0 Players');
    $('#call-number').prop('disabled', false);
    $('#create-game').prop('disabled', false);

    gameVars.ended = false;
    gameVars.winner = '';
    uncalledNumbers = 75;

    clearTimers();
    updateAutoCall();
  });

  $('#create-game').click(function() {
    if (window.confirm('Create a new game?')) {
      restartGame();
    }
  });

  $('#call-number').click(function() {
    callNumber();
  });

  $('#auto-call').change(function() {
    updateAutoCall();
  });

  $('#auto-restart').change(function () {
    updateAutoRestart();
  });

  $('#auto-end').change(function () {
    updateAutoEnd();
  });

  $('#auto-call-interval, #auto-restart-interval, #auto-end-interval').change(function() {
    updateAutoCall();
    updateAutoRestart();
    updateAutoEnd();

    if (settingsUpdateTimer) {
      clearTimeout(settingsUpdateTimer);
      settingsUpdateTimer = undefined;
    }

    settingsUpdateTimer = setTimeout(function () {
      updateGameSettings();
      settingsUpdateTimer = null;
    }, 3000);
  });

  $('#tts').change(function () {
    updateGameSettings();
  });

  $('#tts-voice').change(function () {
    updateGameSettings();
  });

  $('#source-url').click(function() {
    $(this).select();
  });

  $('#copy-source-url').click(function() {
    $('#source-url').select();
    document.execCommand('copy');
  });

  function callNumber() {
    if (!connected || gameVars.ended || !uncalledNumbers) {
      return;
    }

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
        updateGameState();
      }, 10000);
      updateAutoCall();
    }, 'json');
  }

  function restartGame() {
    if (!connected) {
      return;
    }

    $('#call-number').prop('disabled', true);
    $('#create-game').prop('disabled', true);

    var postData = {
      json: true,
      action: 'createGame'
    };
    $.post(window.location, postData);
  }

  function updateAutoCall() {
    if (autoCallTimer) {
      clearInterval(autoCallTimer);
      autoCallTimer = undefined;
    }

    if ($('#auto-call').prop('checked')) {
      autoCallTimer = setInterval(function() {
        callNumber();
      }, $('#auto-call-interval').val() * 1000);
    }
  }

  function updateAutoRestart() {
    if ($('#auto-restart').prop('checked')) {
      if (gameVars.ended && !autoRestartTimer) {
        autoRestartCountdown = $('#auto-restart-interval').val();
        autoRestartTimer = setInterval(function () {
          autoRestartCountdown--;
          if (!autoRestartCountdown) {
            restartGame();
            clearInterval(autoRestartTimer);
            autoRestartTimer = undefined;
          }
        }, 1000);
      }
    } else if (autoRestartTimer) {
      clearInterval(autoRestartTimer);
      autoRestartTimer = undefined;
    }
  }

  function updateAutoEnd() {
    if ($('#auto-end').prop('checked')) {
      if (!uncalledNumbers && !autoEndTimer) {
        autoEndCountdown = $('#auto-end-interval').val();
        autoEndTimer = setInterval(function () {
          autoEndCountdown--;
          if (!autoEndCountdown) {
            var postData = {
              json: true,
              action: 'endGame'
            };
            $.post(window.location, postData);
            clearInterval(autoEndTimer);
            autoEndTimer = undefined;
          }
        }, 1000);
      }
    } else if (autoEndTimer) {
      clearInterval(autoEndTimer);
      autoEndTimer = undefined;
    }
  }

  function updateGameSettings() {
    gameVars.tts = $('#tts').prop('checked');
    gameVars.ttsVoice = $('#tts-voice').val();

    var postData = {
      json: true,
      action: 'updateGameSettings',
      autoCall: $('#auto-call-interval').val(),
      autoRestart: $('#auto-restart-interval').val(),
      autoEnd: $('#auto-end-interval').val(),
      tts: gameVars.tts,
      ttsVoice: gameVars.ttsVoice
    };
    $.post(window.location, postData);
  }

  function updateGameState() {
    if (gameVars.ended) {
      if (gameVars.winner) {
        console.log('congrats ' + gameVars.winner + '!');
        $('.game-winner').text(gameVars.winner);
      }

      $('#call-number').prop('disabled', true);
    } else {
      $('#call-number').prop('disabled', !uncalledNumbers);
    }
  }

  function clearTimers() {
    if (autoCallTimer) {
      clearInterval(autoCallTimer);
      autoCallTimer = undefined;
    }

    if (autoRestartTimer) {
      clearInterval(autoRestartTimer);
      autoRestartTimer = undefined;
    }

    if (autoEndTimer) {
      clearInterval(autoEndTimer);
      autoEndTimer = undefined;
    }
  }
});
