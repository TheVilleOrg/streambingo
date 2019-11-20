'use strict';

$(function() {
  var gameVars = JSON.parse($('#game-vars').text());

  $('body').removeClass('nojs');

  var socket = io('//' + window.location.hostname + ':3000');

  var bingoBall = $('.bingo-ball.template');
  bingoBall.removeClass('template').remove();

  var ttsVoices = {};
  window.speechSynthesis.onvoiceschanged = function () {
    var voices = window.speechSynthesis.getVoices();
    for (var i = 0; i < voices.length; i++) {
      ttsVoices[voices[i].name] = voices[i];
    }
  };

  socket.on('connect', function() {
    socket.emit('getgame', gameVars.gameToken, function(gameName) {
      console.log('joined game ' + gameName);
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
      var tts = new SpeechSynthesisUtterance(letter + ', ' + number);
      tts.voice = ttsVoices[gameVars.ttsVoice];
      window.speechSynthesis.speak(tts);
    }
  });

  socket.on('gamesettings', function (settings) {
    gameVars.tts = settings.tts;
    gameVars.ttsVoice = settings.ttsVoice;
  });

  socket.on('gameover', function(gameName, winner) {
    console.log('game ended');
    if (winner) {
      console.log('congrats ' + winner + '!');
      $('#winner-display').show().find('strong').text(winner);
    }
  });

  socket.on('resetgame', function() {
    console.log('reset game');
    $('#board td').removeClass('marked');
    $('#card-count').text('0 Players');
    $('#winner-display').hide();
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
