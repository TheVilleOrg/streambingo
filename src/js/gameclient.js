$(function () {
  $('#card .marker').click(function () {
    const cell = $(this);
    const index = cell.data('cell');
    if (index === 12) {
      return;
    }

    let postData = {
      json: true,
      action: 'toggleCell',
      cell: index
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
