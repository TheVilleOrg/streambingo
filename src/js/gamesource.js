'use strict';

$(function() {
  $('body').removeClass('nojs');

  var gameVars = JSON.parse($('#game-vars').text());

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
});
