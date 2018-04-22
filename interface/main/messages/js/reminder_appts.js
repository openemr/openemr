/**
 * Message center javascript.
 *
 * @package MedEx
 * @link    http://www.MedExbank.com
 * @author  MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

var labels = [];
var postcards = [];
var show_just;

/**
 * Function to find a patient in the DB
 * This pop-up is the standard openEMR file find_patient_popup.php
 * It returns pid, lname, fname, dob to function "setpatient" below
 * which then populates the form with the select patient data
 */
function recall_name_click(field) {
    dlgopen('../../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
}

/**
 * Function to insert patient data into addRecall fields
 * pid is sent to server for the data to display
 */
function setpatient(pid, lname, fname, dob) {
    var f = document.forms['addRecall'];
    f.new_recall_name.value = lname + ', ' + fname;//+ '&nbsp; ('+dob+')'+''+pid;
                                                   //go get the rest of the data
    top.restoreSession();
    $.ajax({
        type: "POST",
        url: "save.php",
        data: {
            'pid': pid,
            'action': 'new_recall'
        }
    }).done(function (result) {
        obj = JSON.parse(result);
        if (obj.DOLV > '') {
            //check to see if this is an already scheduled appt for the future
            //if so, do you really want a recall? Ask.
            //Maybe that appt needs to be removed...
            var now = moment(); //new Date()).format('YYYY-MM-DD'); //todays date
            var dolv = moment(obj.DOLV); // another date
            var duration = dolv.diff(now, 'days');
            if (duration > '0') { //it's a future appt dude!
                alert(xljs_NOTE + ': ' + xljs_PthsApSched + ' ' + obj.DOLV + '...');
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

    });
}

/**
 *  This function is called with pressing Submit on the Add a Recall page
 */
function add_this_recall(e) {
    if ($('#form_recall_date').val() === '') {
        alert(xljs_PlsDecRecDate);
        $("#form_recall_date").focus();
        //e.defaultPrevented();
        e.preventDefault();
        return false;
    } else {
        var url = "save.php";
        formData = JSON.stringify($("form#addRecall").serialize());
        top.restoreSession();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            action: 'add_recall',
            data: formData
        }).done(function (result) {
            goReminderRecall('Recalls');
        });
    }
}

/**
 * This function is called when a preference is changed
 */
function save_preferences(event) {
    event.preventDefault;
    var url = "save.php";
    formData = JSON.stringify($("form#addRecall").serialize());
    top.restoreSession();
    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        action: 'add_recall',
        data: formData
    }).done(function (result) {
        if (result.msg > '') {
            $("#message").html = result.msg
        }
    });
}

function show_patient(newpid) {
    if (newpid.length === 0) {
        return;
    }
    top.restoreSession();
    top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + newpid;
}

/**
 *  This function is called when the user clicks a header "ALL" checkbox in Postcards or Labels.
 *  The goal is to select visible checkboxes in the selected column,
 *  which can then be printed locally (labels or postcards at present 10/31/2016).
 */
function checkAll(chk, set) {
    if ($("#chk_" + chk).hasClass('fa-square-o')) {
        $("[name=" + chk + "]").each(function () {
            this.checked = !$(this).parents('.nodisplay').length;
        });
    } else {
        $("[name=" + chk + "]").each(function () {
            this.checked = false;
        });
    }
    $("#chk_" + chk).toggleClass('fa-check-square-o').toggleClass('fa-square-o');
}

/**
 * This function sends a list of checked items to the server for processing.
 */
function process_this(material, id, eid='') {
    var make_this = [];
    var make_that = [];
    var make_all = [];
    if ((material === "phone") || (material === "notes")) {  //we just checked a phone box or left/blurred away from a notes field
        make_this.push(id);
        make_that.push(eid);
        make_all.push(id + '_' + eid);
        var notes = $("#msg_notes_" + id).val();
    } else {
        $('input:checkbox[name=' + material + ']:checked').each(function () {
            make_this.push(this.value);
        });
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
            'item': material
        }
    }).done(function (result) {
        if (material === 'labels') window.open("../../patient_file/addr_appt_label.php", "_blank");
        if (material === 'postcards') window.open("print_postcards.php", "rbot");
        //now change the checkmark to a date, turn it red and leave a comment
        $('input:checkbox[name=' + material + ']:checked').each(function () {
            r_uid = this.value;
            var dateval = $.datepicker.formatDate('mm/dd/yy', new Date());
            if (material !== 'phone') {
                $(this).parents('.' + material).append(' ' + dateval);
                $("#remind_" + r_uid).removeClass('whitish')
                    .removeClass('reddish')
                    .removeClass('greenish')
                    .removeClass('yellowish')
                    .addClass('yellowish');
            } else {
                $("#msg_phone_" + r_uid).append('<br />' + dateval);
            }
         });
    });
    //

}


$.date = function (dateObject) {
    var d = new Date(dateObject);
    var day = d.getDate();
    var month = d.getMonth() + 1;
    var year = d.getFullYear();
    if (day < 10) {
        day = "0" + day;
    }
    if (month < 10) {
        month = "0" + month;
    }
    var date = day + "/" + month + "/" + year;

    return date;
};

$(function () {
    /*
     * this swallows backspace keys.
     * stops backspace -> back a page in the browser, a very annoying thing indeed.
     */
    var rx = /INPUT|SELECT|TEXTAREA|SPAN|DIV/i;

    $(document).bind("keydown keypress", function (e) {
        if (e.which === 8) { // 8 == backspace
            if (!rx.test(e.target.tagName) || e.target.disabled || e.target.readOnly) {
                e.preventDefault();
            }
        }
    });
});

// Open the add-event dialog.
function newEvt(pid, pc_eid) {
    var f = document.forms[0];
    var url = '../../main/calendar/add_edit_event.php?patientid=' + pid + '&eid=' + pc_eid;
    dlgopen(url, '_blank', 800, 480);
    return false;
}

function delete_Recall(pid, r_ID) {
    if (confirm('Are you sure you want to delete this Recall?')) {
        //top.restoreSession();
        var url = 'save.php';
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                'action': 'delete_Recall',
                'pid': pid,
                'r_ID': r_ID
            }

        }).done(function (result) {
            refresh_me();
        });
    }

}

function refresh_me() {
    location.reload();
}

/****  FUNCTIONS RELATED TO NAVIGATION *****/
// Process click to pop up the edit window.
function doRecallclick_edit(goHere) {
    top.restoreSession();
    dlgopen('messages.php?nomenu=1&go=' + goHere, '_blank', 900, 400);
}

function goReminderRecall(choice) {
    tabYourIt('recall', 'main/messages/messages.php?go=' + choice);
}

function goMessages() {
    R = 'messages.php?showall=no&sortby=users.lname&sortorder=asc&begin=0&task=addnew&form_active=1';
    top.restoreSession();
    location.href = R;
}

function goMedEx() {
    location.href = 'https://medexbank.com/cart/upload/index.php?route=information/campaigns';
}

/****  END FUNCTIONS RELATED TO NAVIGATION *****/

function show_this(colorish='') {
    var facV = $("#form_facility").val();
    var provV = $("#form_provider").val();
    var pidV = $("#form_patient_id").val();
    var pidRE = new RegExp(pidV, 'i');
    var pnameV = $("#form_patient_name").val();
    var pnameRE = new RegExp(pnameV, 'i');

    $('.ALL').hide().filter(function () {
        var d = $(this).data();
        meets_fac = (facV === '') || (facV == d.facility);
        meets_prov = (provV === '') || (provV == d.provider);
        meets_pid = pidV === '';
        if ((pidV > '') && pidRE.test(d.pid)) {
            meets_pid = true;
        }
        meets_pname = pnameV === '';
        if ((pnameV > '') && pnameRE.test(d.pname)) {
            meets_pname = true;
        }
        meets_color = (colorish === '') || (colorish == d.status );
        return meets_fac && meets_prov && meets_pid && meets_pname && meets_color;
    }).show('4000', 'linear');
}

//in bootstrap_menu.js
function tabYourIt(tabNAME, url) {
    if (!top.tab_mode) {
        tabNAME = window.name;
    }
    parent.left_nav.loadFrame('1', tabNAME, url);
}

$(document).ready(function () {
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
    $(".update").on('change', function (e) {
        var formData = $("form#save_prefs").serialize();
        var url = "save.php";
        top.restoreSession();
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            action: 'save_prefs'
        }).done(function (result) {
            $("#div_response").html('<span style="color:red;">' + xljs1 + '.</span>');
            setTimeout(function () {
                $("#div_response").html('<br />');
            }, 2000);
        });
    });
});

