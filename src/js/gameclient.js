'use strict';

$(function() {
  var gameVars = JSON.parse($('#game-vars').text());

  $('body').removeClass('nojs');

  var blankCard = $('.card.template');
  blankCard.removeClass('template').remove();

  var gameOverModal = $('.game-over-wrapper.template');
  gameOverModal.removeClass('template').remove();
  gameOverModal.find('p').hide();

  var socket = io('//' + window.location.hostname + ':3000');

  socket.on('connect', function() {
    var gameNames = [];
    $('.card').each(function() {
      gameNames.push($(this).data('game-name'));
    });
    socket.emit('playgame', gameVars.twitchId, gameNames);
    $('#connection-status span').text('Connected');
  });

  socket.on('disconnect', function() {
    console.warn('socket connection lost');
    $('#connection-status span').text('Disconnected');
  });

  socket.on('gameover', function(gameId, winner) {
    console.log('game #' + gameId + ' ended');
    var card = $('.card[data-game-id=' + gameId + ']');
    var gameOver = gameOverModal.clone();

    if (winner) {
      console.log('congrats ' + winner + '!');
      gameOver.find('p').show().find('.game-winner').text(winner);
    }

    card.append(gameOver);
  });

  socket.on('newcard', function(gameId) {
    console.log('received new card for game #' + gameId);
    var postData = {
      json: true,
      action: 'fetchCard',
      gameId: gameId
    };
    $.post(window.location, postData, function(data) {
      var card = blankCard.clone();
      card.attr('data-game-id', gameId);
      card.attr('data-game-name', data.gameName);
      card.find('.game-name').text(data.gameName);

      for (var i = 0; i < data.grid.length; i++) {
        card.find('.marker[data-cell=' + i + ']').text(data.grid[i]);
      }

      $('#empty-list').addClass('hidden');
      $('#cards').prepend(card);

      socket.emit('joingame', data.gameName);
    }, 'json');
  });

  $(document).on('click', '.card .marker', function() {
    var cell = $(this);
    var index = cell.data('cell');
    if (index === 12) {
      return;
    }

    var postData = {
      json: true,
      action: 'toggleCell',
      gameId: cell.parents('.card').data('game-id'),
      cell: index
    };
    $.post(window.location, postData, function(data) {
      if (data.marked) {
        cell.addClass('marked');
      } else {
        cell.removeClass('marked');
      }
    }, 'json');
  });

  $(document).on('click', '.game-over-buttons .cancel', function() {
    $(this).parents('.card').remove();
    if (!$('.card').length) {
      $('#empty-list').removeClass('hidden');
    }
  });
});
