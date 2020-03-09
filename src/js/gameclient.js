/**
 * This file is part of StreamBingo.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 3 (GPL-3.0)
 *
 * For full license information, see the LICENSE file included with the source.
 */

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
    socket.emit('playgame', gameVars.gameToken, gameNames, function () {
      $('#connection-status span').text('Connected');
    });
  });

  socket.on('disconnect', function() {
    console.warn('socket connection lost');
    $('#connection-status span').text('Disconnected');
  });

  socket.on('gameover', function(gameId, winner) {
    var card = $('.card[data-game-id=' + gameId + ']');

    if (!card.find('.game-over-wrapper').length) {
      console.log('game #' + gameId + ' ended');

      var gameOver = gameOverModal.clone();

      if (winner) {
        console.log('congrats ' + winner + '!');
        gameOver.find('p').show().find('.game-winner').text(winner);
      }

      card.append(gameOver);
    }
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

      if (data.gameType < 2) {
        card.find('.connect').removeClass('hidden');
      } else {
        card.find('.fill').removeClass('hidden');
      }

      for (var i = 0; i < data.grid.length; i++) {
        var cell = card.find('.marker[data-cell=' + i + ']');
        cell.text(data.grid[i]);
        if (data.freeSpace && i === 12) {
          cell.addClass('free');
        }
      }

      $('#empty-list').addClass('hidden');
      $('#cards').prepend(card);

      socket.emit('joingame', data.gameName);
    }, 'json');
  });

  $(document).on('click', '.card .marker', function() {
    markCell($(this), true);
  });

  $(document).on('dblclick', '.card .marker', function() {
    markCell($(this), false);
  });

  function markCell(cell, marked) {
    var index = cell.data('cell');
    var postData = {
      json: true,
      action: 'markCell',
      gameId: cell.parents('.card').data('game-id'),
      cell: index,
      marked: marked
    };
    $.post(window.location, postData, function(data) {
      if (data.marked) {
        cell.addClass('marked');
      } else {
        cell.removeClass('marked');
      }
    }, 'json');
  }

  $(document).on('click', '.game-over-buttons .cancel', function() {
    $(this).parents('.card').remove();
    if (!$('.card').length) {
      $('#empty-list').removeClass('hidden');
    }
  });
});
