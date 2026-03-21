/**
 * Message center javascript.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var labels = [];
var postcards = [];

/**
 * Function to find a patient in the DB
 * This pop-up is the standard openEMR file find_patient_popup.php
 * It returns pid, lname, fname, dob to function "setpatient" below
 * which then populates the form with the select patient data
 */
function recall_name_click(field) {
    top.restoreSession();
    dlgopen('../../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
}

/**
 * Function to insert patient data into addRecall fields
 * pid is sent to server for the data to display
 */
function setpatient(pid, lname='', fname='', dob='') {
    top.restoreSession();
    $.ajax({
        type: "POST",
        url: "save.php",
        data: {
            'pid': pid,
            'action': 'new_recall',
            'csrf_token_form': csrfTokenForm
        }
    }).done(function (result) {
        var obj = JSON.parse(result);
        if (obj.DOLV > '') {
            //check to see if this is an already scheduled appt for the future
            //if so, do you really want a recall? Ask.
            //Maybe that appt needs to be removed...
            var now = moment(); //new Date()).format('YYYY-MM-DD'); //todays date
            var dolv = moment(obj.DOLV); // another date
            var duration = dolv.diff(now, 'days');
            if (duration > '0') { //it's a future appt dude!
                alert(xljs_NOTE + ': ' + xljs_PthsApSched + ' ' + obj.DOLV );
            }
        }
        $(".news").removeClass('nodisplay');
        $("#new_pid").val(obj.pid);
        $("#new_phone_home").val(obj.phone_home);
        $("#new_phone_cell").val(obj.phone_cell);
        if (obj.hipaa_allowsms === "NO") {
            $('#new_allowsms_no').prop('checked', true);
        } else {
            $('#new_allowsms_yes').prop('checked', true);
        }
        if (obj.hipaa_allowemail === 'NO') {
            $("#new_email_no").prop('checked', true);
        } else {
            $("#new_email_yes").prop('checked', true);
        }
        if (obj.hipaa_voice === 'NO') {
            $("#new_voice_no").prop('checked', true);
        } else {
            $("#new_voice_yes").prop('checked', true);
        }
        $("#new_address").val(obj.street);
        $("#new_city").val(obj.city);
        $("#new_state").val(obj.state);
        $("#new_postal_code").val(obj.postal_code);
        $("#new_DOB").html(obj.DOB);
        $("#new_email").val(obj.email);
        if (obj.DOLV > '') {
            $("#DOLV").val(obj.DOLV);
        } else {
            var today = moment().format('YYYY-MM-DD');
            $("#DOLV").val(today);
        }
        //there is an openemr global for age display under X years old (eg. under "2", so == 17 months old)
        //not sure where it is though... or if we can use it here.
        $("#new_age").html(obj.age + ' years old');
        $("#new_reason").val(obj.PLAN);
        $("#new_recall_name").val(obj.lname + ', ' + obj.fname);
        $("#form_recall_date").val(obj.recall_date);
        $("#new_provider").val(obj.provider).change();
        $("#new_facility").val(obj.facility).change();
    });
}

/**
 *  This function is called with pressing Submit on the Add a Recall page
 */
function add_this_recall(e) {
    let isValid = true;
    let errorMessage = '';

    if ($('#new_recall_name').val() === '' || $('#new_pid').val() === '') {
        errorMessage += '- ' + translations.patient_required + '\n';
        isValid = false;
    }

    if ($('#form_recall_date').val() === '') {
        errorMessage += '- ' + translations.date_required + '\n';
        isValid = false;
    }

    if ($('#new_provider').val() === '' || $('#new_provider').val() === null) {
        errorMessage += '- ' + translations.provider_required + '\n';
        isValid = false;
    }

    if ($('#new_facility').val() === '' || $('#new_facility').val() === null) {
        errorMessage += '- ' + translations.facility_required + '\n';
        isValid = false;
    }

    if (!isValid) {
        alert(errorMessage);
        if (e && e.preventDefault) {
            e.preventDefault();
        }
        return false;
    }

    var url = "save.php";
    var formData = $("form#addRecall").serialize();
    top.restoreSession();
    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        data: formData
    }).done(function (result) {
        goReminderRecall('Recalls');
    });
}

/**
 * This function is called when a preference is changed
 */
function show_patient(newpid) {
    if (newpid.length === 0) {
        return;
    }
    top.restoreSession();
    top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
}

/**
 *  This function is called when the user clicks a header "ALL" checkbox in Postcards or Labels.
 *  The goal is to select visible checkboxes in the selected column,
 *  which can then be printed locally (labels or postcards at present 10/31/2016).
 */
function checkAll(chk, set) {
    if ($("#chk_" + chk).hasClass('fa-square')) {
        $("[name=" + chk + "]").each(function () {
            this.checked = !$(this).parents('.nodisplay').length;
        });
    } else {
        $("[name=" + chk + "]").each(function () {
            this.checked = false;
        });
    }
    $("#chk_" + chk).toggleClass('fa-square-check').toggleClass('fa-square');
}

/**
 * This function sends a list of checked items to the server for processing.
 */
function process_this(material, id, eid='') {
    var make_this = [];
    var make_that = [];
    var make_all = [];
    var notes = '';
    if ((material === "phone") || (material === "notes")) {  //we just checked a phone box or left/blurred away from a notes field
        make_this.push(id);
        make_that.push(eid);
        make_all.push(id + '_' + eid);
        notes = $("#msg_notes_" + id).val() || '';
    } else {
        $('input:checkbox[name=' + material + ']:checked').each(function () {
            make_this.push(this.value);
        });
    }

    // Open window synchronously (in click context) so popup blocker doesn't block it
    var printWin = null;
    if (material === 'labels' || material === 'postcards') {
        if (make_this.length === 0) {
            return; // nothing checked
        }
        printWin = window.open("about:blank", material === 'labels' ? "_blank" : "rbot");
    }

    var url = "save.php";
    var formData = JSON.stringify(make_this);
    var pc_Data = JSON.stringify(make_that);
    var all_Data = JSON.stringify(make_all);
    top.restoreSession();
    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        data: {
            'parameter': formData,
            'pc_eid': pc_Data,
            'uid_pc_eid': all_Data,
            'msg_notes': notes,
            'action': 'process',
            'item': material,
            'csrf_token_form': csrfTokenForm
        }
    }).done(function (result) {
        if (printWin) {
            // Build absolute URL from current page location (works from any context including about:blank)
            var basePath = window.location.href.replace(/[?#].*$/, '').replace(/\/[^/]*$/, '/');
            if (material === 'labels') printWin.location.href = basePath + "../../patient_file/addr_appt_label.php";
            if (material === 'postcards') printWin.location.href = basePath + "print_postcards.php";
        }

        // Handle notes/phone save feedback
        if (material === 'notes' || material === 'phone') {
            var dateval = $.datepicker.formatDate('mm/dd/yy', new Date());
            if (material === 'notes') {
                $("#msg_notes_" + id).val('');
                var statusEl = $("#status_" + id);
                if (statusEl.length) {
                    statusEl.prepend('<small><b>Note:</b> ' + dateval + '</small><br />');
                }
            } else {
                $("#msg_phone_" + id).parent().append(' ' + dateval);
            }
            return;
        }

        //now change the checkmark to a date, turn it red and leave a comment
        $('input:checkbox[name=' + material + ']:checked').each(function () {
            var r_uid = this.value;
            var dateval = $.datepicker.formatDate('mm/dd/yy', new Date());
            $(this).parents('.' + material).append(' ' + dateval);
         });
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (printWin) printWin.close();
        console.error('process_this AJAX failed:', textStatus, errorThrown, jqXHR.responseText);
    });
    //

}


// Open the add-event dialog.
function newEvt(pid, pc_eid) {
    const params = new URLSearchParams({
        patientid: pid,
        eid: pc_eid
    });
    const url = '../../main/calendar/add_edit_event.php?' + params.toString();
    top.restoreSession();
    dlgopen(url, '_blank', 800, 480);
    return false;
}
// AI-generated code end

function delete_Recall(pid, r_ID) {
    if (confirm((typeof translations !== 'undefined' && translations.confirm_delete) ? translations.confirm_delete : 'Are you sure you want to delete this Recall?')) {
        var url = 'save.php';
        top.restoreSession();
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                'action': 'delete_Recall',
                'pid': pid,
                'r_ID': r_ID,
                'csrf_token_form': csrfTokenForm
            }

        }).done(function (result) {
            refresh_me();
        });
    }

}

function refresh_me() {
    top.restoreSession();
    location.reload();
}

/****  FUNCTIONS RELATED TO NAVIGATION *****/

function goReminderRecall(choice, pid) {
    var url = 'messages.php?go=' + encodeURIComponent(choice);
    if (pid) {
        url += '&recall_pid=' + encodeURIComponent(pid);
    }
    top.restoreSession();
    location.href = url;
}

/****  END FUNCTIONS RELATED TO NAVIGATION *****/

/**
 * Convert a datepicker form value to ISO YYYY-MM-DD using the global date format.
 *
 * @param {string} val - Date string from a form field
 * @return {string|null} ISO date string or null if empty/invalid
 */
function toISODate(val) {
    if (!val) {
        return null;
    }
    var fmt = window.top.jsGlobals.date_display_format || '0';
    var parts;
    switch (fmt) {
        case '1': // MM/DD/YYYY
            parts = val.split('/');
            return parts[2] + '-' + parts[0] + '-' + parts[1];
        case '2': // DD/MM/YYYY
            parts = val.split('/');
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        default: // 0 = YYYY-MM-DD (already ISO)
            return val;
    }
}

function show_this() {
    var facV = $("#form_facility").val();
    var provV = $("#form_provider").val();
    var pidV = $("#form_patient_id").val();
    var pidRE = new RegExp(pidV.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
    var pnameV = $("#form_patient_name").val();
    var pnameRE = new RegExp(pnameV.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
    var fromISO = toISODate($("#form_from_date").val());
    var toISO = toISODate($("#form_to_date").val());

    $('.ALL').hide();

    var visibleRows = $('.ALL').filter(function () {
        var d = $(this).data();
        var meets_fac = (facV === '') || (facV == d.facility);
        var meets_prov = (provV === '') || (provV == d.provider);
        var meets_pid = (pidV === '') || pidRE.test(d.pid);
        var meets_pname = (pnameV === '') || pnameRE.test(d.pname);
        var meets_date = true;

        if (fromISO || toISO) {
            var rowDate = d.date;
            if (rowDate) {
                if (fromISO && rowDate < fromISO) {
                    meets_date = false;
                }
                if (toISO && rowDate > toISO) {
                    meets_date = false;
                }
            }
        }

        return meets_fac && meets_prov && meets_pid && meets_pname && meets_date;
    });

    visibleRows.show();

    if (visibleRows.length === 0) {
        if ($("#no_recalls_message").length > 0) {
            $("#no_recalls_message").show();
        } else {
            $("#show_recalls").prepend(
                '<div id="no_recalls_message" class="alert alert-info text-center">' +
                ((typeof translations !== 'undefined' && translations.no_recalls_found) || 'No recalls found for the selected filters.') +
                '</div>'
            );
        }
    } else {
        $("#no_recalls_message").hide();
    }
}

$(function () {
    //bootstrap menu functions
    $('.dropdown').hover(function () {
        $(".dropdown").removeClass('open');
        $(this).addClass('open');
        $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
    }, function () {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideUp();
        $('.dropdown').removeClass('open');
        $(this).parent().removeClass('open');
    });
    $("[class='dropdown-toggle']").hover(function () {
        $(".dropdown").removeClass('open');
        $(this).parent().addClass('open');
        $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
    });
    $(".divTableRow").mouseover(function () {
        if ((!$(this).hasClass('divTableHeading')) &&
            (!$(this).hasClass('greenish')) &&
            (!$(this).parents().hasClass('newRecall')) &&
            (!$(this).parents().hasClass('prefs'))
        ) $(this).addClass("yellow").css('cursor', 'pointer');
    });
    $(".divTableRow").mouseout(function () {
        $(this).removeClass('yellow');
    });
    $("[name='new_recall_when']").change(function () {
        var dolv = moment($("#DOLV").val());
        now = dolv.add($(this).val(), 'days').format(format_date_moment_js);
        $("#form_recall_date").val(now);
    });
    $("#form_from_date, #form_to_date").on('change', function() {
        show_this();
    });
});
