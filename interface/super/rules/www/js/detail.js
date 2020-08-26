// eslint-disable-next-line no-var
var rule_detail = function (args) {
    const editable = args.editable;

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
        if (!editable) {
            $('.action_link').hide();
            $('.left_col').hide();
        }
    };

    const fn_wire_events = function () {
        // TODO:
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
