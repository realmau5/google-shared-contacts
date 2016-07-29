$(function () {

    $('.select-all').on('click', toggleAll);
});

function toggleAll() {
    $.each($('.do-check'), function (i, v) {
        var box = $(v);

        if (box.prop('checked')) {
            box.prop('checked', false);
        } else {
            box.prop('checked', true);

        }
    });

}