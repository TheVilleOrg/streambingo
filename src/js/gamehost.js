'use strict';

$(function() {
  var gameVars = JSON.parse($('#game-vars').text());

  $('body').removeClass('nojs');

  var socket = io('//' + window.location.hostname + ':3000');

  var autoCallTimer;
  var autoCallUpdateTimer;

  socket.on('connect', function() {
    socket.emit('getgame', gameVars.gameToken, function(gameName, ended, winner) {
      console.log('joined game ' + gameName);
      $('#connection-status span').text('Connected');

      gameVars.ended = ended;
      gameVars.winner = winner;
      updateGameState();

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
  });

  socket.on('addplayer', function () {
    gameVars.cardCount++;
    var count = gameVars.cardCount + ' ' + (gameVars.cardCount === 1 ? ' Player' : ' Players');
    $('#card-count').text(count);
  })

  socket.on('gameover', function(gameName, winner) {
    console.log('game ended');

    gameVars.ended = true;
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
  });

  $('#create-game').click(function() {
    if (window.confirm('Create a new game?')) {
      $('#call-number').prop('disabled', true);
      $('#create-game').prop('disabled', true);

      var postData = {
        json: true,
        action: 'createGame'
      };
      $.post(window.location, postData);
    }
  });

  $('#call-number').click(function() {
    callNumber();
  });

  $('#auto-call').change(function() {
    updateAutoCall();
  });

  $('#auto-call-interval').change(function() {
    if (autoCallUpdateTimer) {
      clearInterval(autoCallUpdateTimer);
      autoCallUpdateTimer = undefined;
    }

    autoCallUpdateTimer = setTimeout(function () {
      updateGameSettings();
      updateAutoCall();
      autoCallUpdateTimer = null;
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
    if ($('#board .marked').length >= 75) {
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

  function updateGameSettings() {
    gameVars.tts = $('#tts').prop('checked');
    gameVars.ttsVoice = $('#tts-voice').val();

    var postData = {
      json: true,
      action: 'updateGameSettings',
      autoCallInterval: $('#auto-call-interval').val(),
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
      $('#call-number').prop('disabled', $('#board .marked').length >= 75);
    }
  }
});
