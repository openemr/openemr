// eslint-disable-next-line no-var
var rule_edit = function (args) {
    const fn_work = function () {
        // setup required
        $('.req').each(function () {
            let txt = $(this).text();
            txt = `<span class='required'>*</span>${txt}`;
            $(this).html(txt);
        });

        if ($('.req').length > 0) {
            $('#required_msg').show();
        }
    };

    const fn_validate = function () {
        String.prototype.trim = function () {
            return this.replace(/^\s+|\s+$/g, '');
        };

        // clear previous validation markings
        $('.field_err_marker').removeClass('field_err_marker');
        $('.field_err_lbl_marker').removeClass('field_err_lbl_marker');

        let success = true;
        $('.req').each(function () {
            const label = $(this);

            // test field
            const fldName = label.attr('data-fld');
            const fld = $(`[name='${fldName}']`);
            if (fld.length > 0) {
                if (fld.length === 1) {
                    // likely dealing with some kind of textbox
                    const val = fld.prop('value');
                    if (!val || val.trim() === 'value') {
                        fld.addClass('field_err_marker');
                        label.addClass('field_err_lbl_marker');
                        success = false;
                    }
                } else {
                    // likely dealing with a set
                    const fieldSet = fld.serializeArray();
                    if (fieldSet.length === 0) {
                        label.addClass('field_err_lbl_marker');
                        success = false;
                    }
                }
            }

            // test group
            const dataGroup = label.attr('data-grp');
            const grp = $(`[data-grp-tgt='${dataGroup}']`);
            let ct = 0;
            for (let i = 0; i < grp.length; i += 1) {
                const el = grp[i];
                if (el.selectedIndex !== undefined) {
                    // its a selectbox
                    if (el.selectedIndex >= 0) {
                        ct += 1;
                    }
                } else if (el.value && el.value.trim() !== '') {
                    ct += 1;
                }
            }
            if (ct !== grp.length) {
                label.addClass('field_err_lbl_marker');
                grp.addClass('field_err_marker');
                success = false;
            }
        });
        return success;
    };

    const fn_wire_events = function () {
        $('#btn_save').on('click', function () {
            if (fn_validate()) {
                window.top.restoreSession();
                $('#frm_submit').trigger('submit');
            }
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
