$(function () {
    function unsplitTable(original) {
        original.closest('.table-wrapper').find('.pinned').remove();
        original.unwrap();
        original.unwrap();
    }

    function setCellHeights(original, copy) {
        const tr = original.find('tr');
        const tr_copy = copy.find('tr');
        const heights = [];

        tr.each(function (index) {
            const self = $(this);
            const tx = self.find('th, td');

            tx.each(function () {
                const height = $(this).outerHeight(true);
                heights[index] = heights[index] || 0;
                if (height > heights[index]) {
                    heights[index] = height;
                }
            });
        });
        tr_copy.each(function (index) {
            $(this).height(heights[index]);
        });
    }

    function splitTable(original) {
        original.wrap("<div class='table-wrapper' />");

        const copy = original.clone();
        copy.find('td:not(:first-child), th:not(:first-child)').css('display', 'none');
        copy.removeClass('responsive');

        original.closest('.table-wrapper').append(copy);
        copy.wrap("<div class='pinned' />");
        original.wrap("<div class='scrollable' />");

        setCellHeights(original, copy);
    }

    let switched = false;
    const updateTables = function () {
        if (($(window).width() < 767) && !switched) {
            switched = true;
            $('table.responsive').each(function (i, element) {
                splitTable($(element));
            });
            return true;
        }
        if (switched && ($(window).width() > 767)) {
            switched = false;
            $('table.responsive').each(function (i, element) {
                unsplitTable($(element));
            });
        }
        return false;
    };

    $(window).load(updateTables);
    $(window).on('redraw', function () {
        switched = false;
        updateTables();
    }); // An event to listen for
    $(window).on('resize', updateTables);
});
