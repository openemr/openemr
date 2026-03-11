/**
 * JavaScript functions for the Add/Edit Event calendar dialog.
 *
 * Extracted from add_edit_event.php to improve readability and
 * separate concerns (see GitHub Issue #8057).
 *
 * PHP-dependent values are passed via the global object
 * `window.addEditEventConfig`, which is set by a small inline
 * <script> block in add_edit_event.php before this file loads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* global DateToYYYYMMDD_js, dlgopen, dlgclose, restoreSession, submitme, $ */
/* global addEditEventConfig */

const IN_OFFICE_CAT_ID = '2';

// This is for callback by the find-patient popup.
function setpatient(pid, lname, fname, dob) {
    var f = document.forms[0];
    f.form_patient.value = lname + ', ' + fname;
    f.form_pid.value = pid;
    var dobstyle = (dob == '' || dob.substr(5, 10) == '00-00') ? '' : 'none';
    document.getElementById('dob_row').style.display = dobstyle;
    let event = new CustomEvent('openemr:appointment:patient:set', {
        bubbles: true,
        detail: {form: f, pid: pid, lname: lname, fname: fname, dob: dob}
    });
    f.dispatchEvent(event);
}

// This invokes the find-patient popup.
function sel_patient() {
    var title = addEditEventConfig.translations.patientSearch;
    dlgopen('find_patient_popup.php', 'findPatient', 650, 300, '', title);
}

// This is for callback by the find-group popup.
function setgroup(gid, name, end_date) {
    var f = document.forms[0];
    f.form_group.value = name;
    f.form_gid.value = gid;
    if (f.form_enddate.value == "") {
        f.form_enddate.value = end_date;
    }
}

// This invokes the find-group popup.
function sel_group() {
    top.restoreSession();
    var title = addEditEventConfig.translations.groupSearch;
    dlgopen('find_group_popup.php', '_blank', 650, 300, '', title);
}

// Do whatever is needed when a new event category is selected.
// For now this means changing the event title and duration.
function set_display() {
    var f = document.forms[0];
    var s = f.form_category;
    if (s.selectedIndex >= 0) {
        var catid = s.options[s.selectedIndex].value;
        var style_apptstatus = document.getElementById('title_apptstatus').style;
        var style_prefcat = document.getElementById('title_prefcat').style;
        if (catid == IN_OFFICE_CAT_ID) { // In Office
            style_apptstatus.display = 'none';
            style_prefcat.display = '';
            f.form_apptstatus.style.display = 'none';
            f.form_prefcat.style.display = '';
            f.form_duration.disabled = false;
            f.form_duration.value = 0;
            document.getElementById('tdallday4').style.color = 'var(--gray)';
        } else {
            style_prefcat.display = 'none';
            style_apptstatus.display = '';
            f.form_prefcat.style.display = 'none';
            f.form_apptstatus.style.display = '';
            f.form_duration.disabled = false;
            document.getElementById('tdallday4').style.color = '';
        }
    }
}

// Do whatever is needed when a new event category is selected.
// For now this means changing the event title and duration.
function set_category() {
    var f = document.forms[0];
    var s = f.form_category;
    if (s.selectedIndex >= 0) {
        var catid = s.options[s.selectedIndex].value;
        f.form_title.value = s.options[s.selectedIndex].text;
        f.form_duration.value = addEditEventConfig.durations[catid];
        set_display();
    }
}

// Modify some visual attributes when the all-day or timed-event
// radio buttons are clicked.
function set_allday() {
    const f = document.forms[0];
    const s = f.form_category;
    let color1 = 'var(--gray)';
    let color2 = 'var(--gray)';
    let timeDisabled = true;
    let durationDisabled = true;
    if (document.getElementById('rballday1').checked) {
        color1 = '';
    }
    if (document.getElementById('rballday2').checked) {
        color2 = '';
        timeDisabled = false;
        if (s.selectedIndex >= 0) {
            var catid = s.options[s.selectedIndex].value;
            if (catid != IN_OFFICE_CAT_ID) {
                durationDisabled = false;
            }
        } else {
            durationDisabled = false;
        }
    }
    document.getElementById('tdallday1').style.color = color1;
    document.getElementById('tdallday2').style.color = color2;
    document.getElementById('tdallday5').style.color = color2;
    f.form_hour.disabled = timeDisabled;
    f.form_minute.disabled = timeDisabled;
    if (addEditEventConfig.timeDisplayFormat == 1) {
        f.form_ampm.disabled = durationDisabled;
    }
    f.form_duration.disabled = durationDisabled;
}

// Modify some visual attributes when the Repeat checkbox is clicked.
function set_repeat() {
    var f = document.forms[0];
    var isdisabled = true;
    var mycolor = 'var(--gray)';
    var myvisibility = 'hidden';
    if (f.form_repeat.checked) {
        f.days_every_week.checked = false;
        document.getElementById("days_label").style.color = mycolor;
        var days = document.getElementById("days").getElementsByTagName('input');
        var labels = document.getElementById("days").getElementsByTagName('label');
        for (var i = 0; i < days.length; i++) {
            days[i].disabled = isdisabled;
            labels[i].style.color = mycolor;
        }
        isdisabled = false;
        mycolor = 'var(--black)';
        myvisibility = 'visible';
    }
    f.form_repeat_type.disabled = isdisabled;
    f.form_repeat_freq.disabled = isdisabled;
    f.form_enddate.disabled = isdisabled;
    document.getElementById('tdrepeat1').style.color = mycolor;
    document.getElementById('tdrepeat2').style.color = mycolor;
}

// Event when days_every_week is checked.
function set_days_every_week() {
    var f = document.forms[0];
    if (f.days_every_week.checked) {
        //disable regular repeat
        f.form_repeat.checked = false;
        f.form_repeat_type.disabled = true;
        f.form_repeat_freq.disabled = true;
        document.getElementById('tdrepeat1').style.color = 'var(--gray)';

        //enable end_date setting
        document.getElementById('tdrepeat2').style.color = 'var(--black)';
        f.form_enddate.disabled = false;

        var isdisabled = false;
        var mycolor = 'var(--black)';
        var myvisibility = 'visible';
    } else {
        var isdisabled = true;
        var mycolor = 'var(--gray)';
        var myvisibility = 'hidden';
    }
    document.getElementById("days_label").style.color = mycolor;
    var days = document.getElementById("days").getElementsByTagName('input');
    var labels = document.getElementById("days").getElementsByTagName('label');
    for (var i = 0; i < days.length; i++) {
        days[i].disabled = isdisabled;
        labels[i].style.color = mycolor;
    }

    //If no repetition is checked, disable end_date setting.
    if (!f.days_every_week.checked && !f.form_repeat.checked) {
        //disable end_date setting
        document.getElementById('tdrepeat2').style.color = mycolor;
        f.form_enddate.disabled = isdisabled;
    }
}

// Monitor start date changes to adjust repeat type options.
function dateChanged() {
    var f = document.forms[0];
    if (!f.form_date.value) return;
    var d = new Date(DateToYYYYMMDD_js(f.form_date.value));
    var downame = addEditEventConfig.translations.weekDays[d.getUTCDay()];
    var nthtext = '';
    var occur = Math.floor((d.getUTCDate() - 1) / 7);
    if (occur < 4) { // 5th is not allowed
        nthtext = addEditEventConfig.translations.occurNames[occur] + ' ' + downame;
    }
    var lasttext = '';
    var tmp = new Date(d.getUTCFullYear(), d.getUTCMonth() + 1, 0);
    if (tmp.getDate() - d.getUTCDate() < 7) { // Modified by epsdky 2016 (details in commit)
        // This is a last occurrence of the specified weekday in the month,
        // so permit that as an option.
        lasttext = addEditEventConfig.translations.last + ' ' + downame;
    }
    var si = f.form_repeat_type.selectedIndex;
    var opts = f.form_repeat_type.options;
    opts.length = 5; // remove any nth and Last entries
    if (nthtext) {
        opts[opts.length] = new Option(nthtext, '5');
    }
    if (lasttext) {
        opts[opts.length] = new Option(lasttext, '6');
    }
    if (si < opts.length) {
        f.form_repeat_type.selectedIndex = si;
    } else {
        f.form_repeat_type.selectedIndex = 5;
    } // Added by epsdky 2016 (details in commit)
}

// This is for callback by the find-available popup.
function setappt(year, mon, mday, hours, minutes) {
    //Infeg Save button should become active once an appointment is selected.
    $('#form_save').attr('disabled', false);
    var f = document.forms[0];
    var dateFormat = addEditEventConfig.dateDisplayFormat;
    if (dateFormat == 0) {
        f.form_date.value = '' + year + '-' +
            ('' + (mon + 100)).substring(1) + '-' +
            ('' + (mday + 100)).substring(1);
    } else if (dateFormat == 1) {
        f.form_date.value = ('' + (mon + 100)).substring(1) + '/' +
            ('' + (mday + 100)).substring(1) + '/' +
            '' + year;
    } else if (dateFormat == 2) {
        f.form_date.value = ('' + (mday + 100)).substring(1) + '/' +
            ('' + (mon + 100)).substring(1) + '/' +
            '' + year;
    }
    f.form_hour.value = hours;
    if (addEditEventConfig.timeDisplayFormat == 1) {
        f.form_hour.value = (hours > 12) ? hours - 12 : hours;
        f.form_ampm.selectedIndex = (hours >= 12) ? 1 : 0;
    }
    f.form_minute.value = ('' + (minutes + 100)).substring(1);
}

// Invoke the find-available popup.
function find_available(extra) {
    //Infeg Save button should become active once an appointment is selected.
    $('#form_save').attr('disabled', false);
    top.restoreSession();
    // (CHEMED) Conditional value selection, because there is no <select> element
    // when making an appointment for a specific provider
    var s = document.forms[0].form_provider;
    var f = document.forms[0].facility;
    if (addEditEventConfig.userId != 0) {
        s = document.forms[0].form_provider.value;
        f = document.forms[0].facility.value;
    } else {
        s = document.forms[0].form_provider.options[s.selectedIndex].value;
        f = document.forms[0].facility.options[f.selectedIndex].value;
    }
    var c = document.forms[0].form_category;
    var formDate = document.forms[0].form_date;
    var title = addEditEventConfig.translations.availableAppointments;
    dlgopen(addEditEventConfig.webRoot + '/interface/main/calendar/find_appt_popup.php' +
        '?providerid=' + s +
        '&catid=' + c.options[c.selectedIndex].value +
        '&facility=' + f +
        '&startdate=' + formDate.value +
        '&evdur=' + document.forms[0].form_duration.value +
        '&eid=' + addEditEventConfig.eid + extra,
        '', 725, 200, '', title);
}
