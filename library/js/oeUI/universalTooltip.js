$(function () {
    $(document).tooltip({
        show: {
            delay: 400
        },
        hide: {
            delay: 0
        }
    });
    $(this).click(function () {
        $(this).tooltip({
            hide: {
                delay: 0
            }
        });
    });
});
