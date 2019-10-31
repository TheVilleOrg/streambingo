$(function () {
  $('#card td').click(function () {
    const cell = $(this);
    let postData = {
      json: true,
      action: 'toggleCell',
      cell: cell.data('cell')
    };
    $.post(window.location, postData, function (data) {
      if (data.marked) {
        cell.addClass('marked');
      } else {
        cell.removeClass('marked');
      }
    }, 'json');
  });
});
