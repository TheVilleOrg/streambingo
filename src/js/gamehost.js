$(function () {
  $('#call-number').click(function () {
    let postData = {
      json: true,
      action: 'callNumber'
    };
    $.post(window.location, postData, function (data) {
      $('#board td[data-cell=' + data.number + ']').addClass('marked');
      $('#number').text(data.letter + data.number);
    }, 'json');
  });

  $('#create-game').click(function () {
    let postData = {
      json: true,
      action: 'createGame'
    };
    $.post(window.location, postData, function (data) {
      $('#board td').removeClass('marked');
      $('#number').text('');
    }, 'json');
  })
});

const socket = io('//' + window.location.hostname + ':3000');
socket.on('connect', function () {
  let match = document.cookie.match(/\baccess_token=(.*)[;\b]/);
  if (match.length === 2) {
    socket.emit('creategame', match[1], function (gameName) {
      console.log('joined game ' + gameName);
    });
  }
});

socket.on('winner', function (winner) {
  console.log('congrats ' + winner + '!');
});
