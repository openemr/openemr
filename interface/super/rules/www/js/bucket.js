// eslint-disable-next-line no-var
var bucket = function (args) {
    const fn_work = function () {

    };

    const fn_handle_change = function () {
        const that = $(this);
        const selected = that.val();
        const txt = this[this.selectedIndex].text;
        const textBox = that.parent().find("input[type='text']");
        textBox.val(txt);
        textBox.show();
        const hidden = $(`#${that.attr('data-hidden')}`);
        hidden.val(selected);
        that.remove();
    };

    const fn_prep_options = function (select, listType, hidden, showMe) {
        select.append("<option value=''></option>");
        window.top.restoreSession();
        $.getJSON(`index.php?action=edit!${listType}`,
            function (data) {
                $.each(data, function (i, item) {
                    select.append(`<option value='${item.code}'>${item.lbl}</option>`);
                });
                select.val('');
            });
        select.attr('data-hidden', hidden);
        select.on('change', fn_handle_change);
        select.on('change', function () {
            showMe.show();
        });
    };

    const fn_wire_events = function () {
        $('#change_category').on('click', function () {
            $('#fld_category_lbl').hide();
            const select = $('<select></select>');
            $('#fld_category_lbl').parent().append(select);
            fn_prep_options(select, 'categories', 'fld_category', $(this));
            $(this).hide();
        });

        $('#change_item').on('click', function () {
            $('#fld_item_lbl').hide();
            const select = $('<select></select>');
            $('#fld_item_lbl').parent().append(select);
            fn_prep_options(select, 'items', 'fld_item', $(this));
            $(this).hide();
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
