// eslint-disable-next-line no-var
var custom = function (args) {
    const selectedColumn = args.selectedColumn;

    const fn_fill_columns = function (table, callback) {
        const colSelect = $('#fld_column');
        colSelect.find('.populated').remove();
        window.top.restoreSession();
        $.getJSON(`index.php?action=edit!columns&table=${table}`,
            function (data) {
                $.each(data, function (i, item) {
                    colSelect.append(`<option class='populated' value='${item}'>${item}</option>`);
                });
                callback();
            });
    };

    const fn_work = function () {
        const selected = $('#fld_table').val();

        if (selected) {
            const colSelect = $('#fld_column');
            fn_fill_columns(selected, function () {
                colSelect.val(selectedColumn);
            });
        }
    };

    const fn_wire_events = function () {
        $('#fld_table').on('change', function () {
            fn_work();
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
