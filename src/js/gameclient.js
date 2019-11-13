jQuery.noConflict();
(function($) {
  'use strict';

  $(function() {
    var gameVars = JSON.parse($('#game-vars').text());

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

    socket.on('gameover', function(gameName, winner) {
      var card = $('.card[data-game-name=' + gameName + ']');
      var gameOver = gameOverModal.clone();

      if (winner) {
        console.log('congrats ' + winner + '!');
        gameOver.find('p').show().find('.game-winner').text(winner);
      }

      card.append(gameOver);
    });

    socket.on('newcard', function(gameName) {
      var postData = {
        json: true,
        action: 'fetchCard',
        gameName: gameName
      };
      $.post(window.location, postData, function(data) {
        var card = blankCard.clone();
        card.attr('data-game-name', gameName);
        card.find('.game-name').text(gameName);

        for (var i = 0; i < data.grid.length; i++) {
          card.find('.marker[data-cell=' + i + ']').text(data.grid[i]);
        }

        $('#cards').prepend(card);
      }, 'json');

      socket.emit('joingame', gameName);
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
        gameName: cell.parents('.card').data('game-name'),
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
    });
  });
})(jQuery);
