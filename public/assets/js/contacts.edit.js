$(function () {

    setClicks();
});

function setClicks() {
    $('.addRow').unbind('click').on('click', addRow);
    $('.removeRow').unbind('click').on('click', removeRow);
}

function addRow(e) {
    var target = $(e.target);
    var rel = target.attr('rel');
    console.log('add ' + rel + (indexes[rel] + 1));

    var url = 'rpc/addrow/' + rel + '/' + (indexes[rel] + 1);
    $.get(url, function (data) {
        $('#' + rel).append(data);
        indexes[rel]++;
        setClicks();
    });
    return false;
}

function removeRow(e) {
    var target = $(e.target);
    var index = target.data('index');
    var type = target.data('type')
    $('table.' + type + '[data-index="' + index + '"]').remove();
    $('h4.' + type + '[data-index="' + index + '"]').remove();
    setClicks();
    return false;
}