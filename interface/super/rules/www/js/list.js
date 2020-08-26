// eslint-disable-next-line no-var
var list_rules = function (args) {
    const fn_create_row = function (rowData) {
        const clone = $('.rule_row.template').clone().removeClass('template');
        let anchor = clone.find('.rule_title a');
        anchor.text(rowData.title);
        anchor.attr('href', `${anchor.attr('href')}&id=${rowData.id}`);

        anchor = clone.find('.rule_type a');
        anchor.text(rowData.type);
        anchor.attr('href', `${anchor.attr('href')}&id=${rowData.id}`);
        $('.rule_container').append(clone);
        clone.show();
    };

    const fn_work = function (sort) {
        if (!sort) {
            sort = 'title';
        }

        window.top.restoreSession();
        $.getJSON('index.php?action=browse!getrows',
            function (data) {
                data.sort(function (a, b) {
                    if (sort === 'title') {
                        return (a.title < b.title) ? -1 : (a.title > b.title) ? 1 : 0;
                    }
                    return (a.type < b.type) ? -1 : (a.type > b.type) ? 1 : 0;
                });

                for (const i in data) {
                    if (Object.prototype.hasOwnProperty.call(data, i)) {
                        fn_create_row(data[i]);
                    }
                }
            });
    };

    const fn_sort = function (field) {
        $('.rule_row.data').sortElements(function (a, b) {
            const x = $(a).find(`.${field}`).text();
            const y = $(b).find(`.${field}`).text();
            return (x < y) ? -1 : (x > y) ? 1 : 0;
        });
    };

    const fn_wire_events = function () {
        $('.header_title').on('click', function () {
            fn_sort('rule_title');
        });

        $('.header_type').on('click', function () {
            fn_sort('rule_type');
        });
    };

    return {
        init() {
            $(function () {
                fn_wire_events();
                fn_work();
            });
        },
    };
};
