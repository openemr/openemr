function validate_search() {
    if ($('#downloadccda').val()) {
        $('#downloadccda').val('');
    }
    if ($('#downloadccr').val()) {
        $('#downloadccr').val('');
    }
    if ($('#downloadccd').val()) {
        $('#downloadccd').val('');
    }
    let from_date = document.getElementsByName('form_date_from')[0].value;
    let to_date = document.getElementsByName('form_date_to')[0].value;
    const from_date_arr = from_date.split('/');
    const to_date_arr = to_date.split('/');
    let flag = true;

    const from_year = from_date_arr[2];
    const from_month = from_date_arr[0];
    from_date = from_date_arr[1];

    const to_year = to_date_arr[2];
    const to_month = to_date_arr[0];
    to_date = to_date_arr[1];

    if (to_year < from_year) {
        flag = false;
    } else if (to_year === from_year) {
        if (to_month >= from_month) {
            if (to_date < from_date) {
                flag = false;
            }
        } else {
            flag = false;
        }
    }
    if (!flag) {
        const resultTranslated = js_xl('Invalid date range');
        alert(resultTranslated.msg);
        return false;
    }
    return true;
}

function isNumber(evt) {
    if (!evt) {
        evt = window.event;
    }
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 13) {
        return false;
    }
    return true;
}

// Hide Menu if clicked outside
$(document).mouseup(function (e) {
    const container = $('.se_in_15');
    const calendar = $('.ui-datepicker');
    const buttons = $('.search_button');
    if (!container.is(e.target) && container.has(e.target).length === 0
        && !buttons.is(e.target) && calendar.has(e.target).length === 0) {
        $('.se_in_15').css('display', 'none');
    }
});

$(function () {
    $('.header_wrap_left').on('click', '.search_button', function () {
        const pos = $(this).position();
        $('.se_in_15').fadeToggle().css({
            left: `${(pos.left + 5)}px`,
            top: `${(pos.top + 35)}px`,
        });
    });

    // date picker
    $('.dateClass').datepicker({
        changeMonth: true,
        changeYear: true,
    });
    $('.dateClass').datepicker('option', 'dayNamesMin', ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']);
    $('.rangeClass').datepicker();

    $('.expand').click(function () {
        $(`#patient_${this.id}`).toggle('slow');
        $(this).toggleClass('se_in_21');

        const exp_count = $('.expand').length;
        const expanded_count = $('.se_in_21').length;

        if (exp_count === expanded_count) {
            $('.exp_all').addClass('se_in_24');
            $('.exp_all').removeClass('se_in_23');
            $('#form_expand_all').val('1');
        } else {
            $('.exp_all').addClass('se_in_23');
            $('.exp_all').removeClass('se_in_24');
            $('#form_expand_all').val('');
        }
    });

    $('.check_pid').click(function () {
        const pid = $(this).val();
        if ($(this).is(':checked')) {
            $(`.check_${pid}`).attr('checked', true);
        } else {
            $(`.check_${pid}`).attr('checked', false);
        }

        const pid_check_len = $('.check_pid').length;
        const pid_check_checked_len = $('.check_pid:checked').length;
        if (pid_check_checked_len === pid_check_len) {
            $('#form_select_all').attr('checked', true);
        } else {
            $('#form_select_all').attr('checked', false);
        }
    });

    $('.exp_all').click(function () {
        if ($(this).hasClass('se_in_23')) {
            $(this).addClass('se_in_24');
            $(this).removeClass('se_in_23');
            $('.expand').addClass('se_in_21');
            $('.encounetr_data').css('display', '');
            $('#form_expand_all').val('1');
        } else if ($(this).hasClass('se_in_24')) {
            $(this).addClass('se_in_23');
            $(this).removeClass('se_in_24');
            $('.expand').removeClass('se_in_21');
            $('.encounetr_data').css('display', 'none');
            $('#form_expand_all').val('');
        }
    });

    $('#form_select_all').click(function () {
        if ($(this).is(':checked')) {
            $('.check_pid').attr('checked', true);
            $('.check_encounter').attr('checked', true);
        } else {
            $('.check_pid').attr('checked', false);
            $('.check_encounter').attr('checked', false);
        }
    });

    $('.check_encounter').click(function () {
        const class_name = $(this).attr('class');
        const class_names = class_name.split(' ');
        const pid = class_names[1].replace('check_', '');

        const enc_len = $(`.${class_names[1]}`).length;
        const enc_checked_len = $(`.${class_names[1]}:checked`).length;

        if (enc_len === enc_checked_len) {
            $(`.check_pid_${pid}`).attr('checked', true);
        } else {
            $(`.check_pid_${pid}`).attr('checked', false);
        }

        const pid_check_len = $('.check_pid').length;
        const pid_check_checked_len = $('.check_pid:checked').length;
        if (pid_check_checked_len === pid_check_len) {
            $('#form_select_all').attr('checked', true);
        } else {
            $('#form_select_all').attr('checked', false);
        }
    });
});

function clearCount() {
    document.getElementById('form_current_page').value = 1;
    document.getElementById('form_new_search').value = 1;
}

function pagination(action) {
    if (action === 'first') {
        document.getElementById('form_current_page').value = 1;
    } else if (action === 'last') {
        document.getElementById('form_current_page').value = document.getElementById('form_total_pages').value;
    } else if (action === 'next') {
        current_page = document.getElementById('form_current_page').value;
        document.getElementById('form_current_page').value = Number(current_page) + 1;
    } else if (action === 'previous') {
        current_page = document.getElementById('form_current_page').value;
        document.getElementById('form_current_page').value = Number(current_page) - 1;
    }
    document.getElementById('theform').submit();
}
