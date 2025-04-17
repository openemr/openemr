<?php

/**
 * Common javascript functions are stored in this page.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<script>
    //Calendar Functions to set From and To dates
    function calendar_function(val, from, to) {

        var date = new Date();
        fromdate = document.getElementById(from);
        todate = document.getElementById(to);
        if (val == 'this_month_to_date') {
            var dt = date.getDate() - 1;
            fromdate.value = disp_date(-dt, 0, 0);
            todate.value = disp_date(0, 0, 0);
        } else if (val == 'today') {
            fromdate.value = disp_date(0, 0, 0);
            todate.value = disp_date(0, 0, 0);
        } else if (val == 'last_month') {
            var m = date.getMonth();
            var yy = date.getYear();
            var mt = daysInMonth(m, yy);
            fromdate.value = last_month() + "-01";
            todate.value = last_month() + "-" + mt;
        } else if (val == 'this_calendar_year') {
            var dt = date.getDate() - 1;
            var m = date.getMonth();
            fromdate.value = disp_date(-dt, -m, 0);
            dt = 30 - dt;
            m = 11 - m;
            todate.value = disp_date(dt, m, 0);
        } else if (val == 'last_calendar_year') {
            var dt = date.getDate() - 1;
            var m = date.getMonth();
            fromdate.value = disp_date(-dt, -m, -1);
            dt = 30 - dt;
            m = 11 - m;
            todate.value = disp_date(dt, m, -1);
        } else if (val == 'this_week_to_date') {
            fromdate.value = week_date();
            todate.value = disp_date(0, 0, 0);
        } else {
            fromdate.value = '';
            todate.value = '';
        }

    }

    //Below functions are called in the above function
    function disp_date(dt, mt, yr) {
        var date = new Date();
        var d = date.getDate() + dt;
        var day = (d < 10) ? '0' + d : d;
        var m = date.getMonth() + 1 + mt;
        var month = (m < 10) ? '0' + m : m;
        var yy = date.getYear() + yr;
        var year = (yy < 1000) ? yy + 1900 : yy;
        current = (year + "-" + month + "-" + day);
        return current;
    }

    function daysInMonth(month, year) {
        var dd = new Date(year, month, 0);
        return dd.getDate();
    }

    function last_month() {
        var date = new Date();
        var yy = date.getYear();
        var m = date.getMonth();
        if (date.getMonth() == 0) {
            yy--;
            m = 12;
        }
        var month = (m < 10) ? '0' + m : m;
        var year = (yy < 1000) ? yy + 1900 : yy;
        current = (year + "-" + month);
        return current;
    }

    function week_date() {
        var today = new Date();
        var day = today.getDate();
        var month = today.getMonth() + 1;
        var year = today.getYear();
        if (year < 2000)
            year = year + 1900;
        var offset = today.getDay();
        var week;

        if (offset != 0) {
            day = day - offset;
            if (day < 1) {
                if (month == 1) day = 31 + day;
                if (month == 2) day = 31 + day;
                if (month == 3) {
                    if ((year == 00) || (year == 04)) {
                        day = 29 + day;
                    } else {
                        day = 28 + day;
                    }
                }
                if (month == 4) day = 31 + day;
                if (month == 5) day = 30 + day;
                if (month == 6) day = 31 + day;
                if (month == 7) day = 30 + day;
                if (month == 8) day = 31 + day;
                if (month == 9) day = 31 + day;
                if (month == 10) day = 30 + day;
                if (month == 11) day = 31 + day;
                if (month == 12) day = 30 + day;
                if (month == 1) {
                    month = 12;
                    year = year - 1;
                } else {
                    month = month - 1;
                }
            }
        }
        month = (month < 10) ? '0' + month : month;
        day = (day < 10) ? '0' + day : day;
        week = year + "-" + month + "-" + day;
        return week;
    }

    //================================================================================================================================
    //Search Functionality
    function CriteriaVisible()//This function is called when, on change event happens, in the select box containing all the search criteria of the page.
    {//This shows different values based on the type of the search criteria.For 'date' type it shows 'From' 'To' etc.
        for (OptionIndex = 0; OptionIndex < document.getElementById('choose_this_page_criteria').options.length; OptionIndex++) {
            choose_this_page_criteria_value = document.getElementById('choose_this_page_criteria').options[OptionIndex].value;
            if (document.getElementById('table_' + choose_this_page_criteria_value))
                document.getElementById('table_' + choose_this_page_criteria_value).style.display = 'none';
        }
        choose_this_page_criteria_value = document.getElementById('choose_this_page_criteria').options[document.getElementById('choose_this_page_criteria').selectedIndex].value;
        document.getElementById('table_' + choose_this_page_criteria_value).style.display = '';
    }

    function checkOptionExist(value0, seperator)//It returns the position of the option, if the value is found, in the final criteria select box submitted.
    {//If doesn't exist new insertion is done.If exist it is updated.
        var elSel = document.getElementById('final_this_page_criteria');
        var i;
        for (i = elSel.length - 1; i >= 0; i--) {
            OptionValue = elSel.options[i].value;
            OptionValue = OptionValue.split('|' + seperator.trim());
            if (OptionValue[0] == value0) {
                return i;
            }
        }
        return -1;
    }

    function checkOptionExistDateCriteria(value0)//For Date Criteria.It returns the position of the option, if the value is found,
    {//in the final criteria select box submitted.//If doesn't exist new insertion is done.If exist it is updated.
        var elSel = document.getElementById('final_this_page_criteria');
        var i;
        for (i = elSel.length - 1; i >= 0; i--) {
            OptionValue = elSel.options[i].value;
            OptionValue = OptionValue.split('|between|');
            if (OptionValue[0] == value0) {
                return i;
            }
        }
        return -1;
    }

    function appendOptionRadioCriteria(text0, value0, text1, value1, seperator, Type) {//If option doesn't exist new insertion is done.If exist it is updated in the select drop down.//Remove the item if the option is All.
        var elOptNew = document.createElement('option');
        if (Type == 'radio' || Type == 'query_drop_down') {
            elOptNew.text = text0 + seperator + text1;
            elOptNew.value = value0 + "|" + seperator.trim() + "|" + value1;
        } else if (Type == 'radio_like') {
            elOptNew.text = text0 + ' = ' + text1;
            elOptNew.value = value0 + "|" + seperator.trim() + "|" + value1;
        }
        var elSel = document.getElementById('final_this_page_criteria');
        TheOptionIndex = checkOptionExist(value0, seperator);
        if (TheOptionIndex == -1) {
            if (value1 != 'all') {
                try {
                    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
                } catch (ex) {
                    elSel.add(elOptNew); // IE only
                }
            }
        } else if (value1 == 'all') {
            elSel.remove(TheOptionIndex);
        } else {
            elSel.options[TheOptionIndex].value = elOptNew.value;
            elSel.options[TheOptionIndex].text = elOptNew.text;
        }
    }

    function appendOptionTextCriteria(text0, value0, text1, value1, seperator, Type) {//If option doesn't exist new insertion is done.If exist it is updated in the select drop down.//Remove the item if the value is blank.
        var elOptNew = document.createElement('option');
        elOptNew.text = text0 + seperator + text1;
        if (Type == 'text') {
            elOptNew.value = value0 + "|" + seperator.trim() + "|" + value1;
        } else if (Type == 'text_like') {
            elOptNew.value = value0 + "|" + seperator.trim() + "|" + value1 + "%";
        }
        var elSel = document.getElementById('final_this_page_criteria');
        TheOptionIndex = checkOptionExist(value0, seperator);
        if (TheOptionIndex == -1) {
            if (!(value1 == '' || value1 == '&nbsp;'))//'&nbsp;' is for ajax case
            {
                try {
                    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
                } catch (ex) {
                    elSel.add(elOptNew); // IE only
                }
            }
        } else if (value1 == '' || value1 == '&nbsp;') {
            elSel.remove(TheOptionIndex);
        } else {
            elSel.options[TheOptionIndex].value = elOptNew.value;
            elSel.options[TheOptionIndex].text = elOptNew.text;
        }
    }

    function appendOptionDateCriteria(text0, value0, text1, value1, seperator, FromDate, ToDate, Type)//For Date drop down
    {//If option doesn't exist new insertion is done.If exist it is updated in the select drop down.//Remove the item if the drop down is All.
        var elOptNew = document.createElement('option');
        elOptNew.text = text0 + seperator + text1;
        FromDateValue = document.getElementById(FromDate).value;
        ToDateValue = document.getElementById(ToDate).value;
        // only require biller to enter from date
        ToDateValue = (ToDateValue == '') ? FromDateValue : ToDateValue;
        if (Type == 'date') {
            elOptNew.value = value0 + "|between|" + FromDateValue + "|" + ToDateValue;
        }
        if (Type == 'datetime') {
            elOptNew.value = value0 + "|between|" + FromDateValue + " 00:00:00|" + ToDateValue + " 23:59:59";
        }
        var elSel = document.getElementById('final_this_page_criteria');
        TheOptionIndex = checkOptionExistDateCriteria(value0);
        if (TheOptionIndex == -1) {
            if (value1 != 'all') {
                try {
                    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
                } catch (ex) {
                    elSel.add(elOptNew); // IE only
                }
            }
        } else if (value1 == 'all') {
            elSel.remove(TheOptionIndex);
        } else {
            elSel.options[TheOptionIndex].value = elOptNew.value;
            elSel.options[TheOptionIndex].text = elOptNew.text;
        }
    }

    function CleanUpAjax(text0, value0, seperator) {//Cleans the values in the ajax (Insurance Company criteria)
        document.getElementById('type_code').value = '';
        document.getElementById('hidden_ajax_close_value').value = '';
        document.getElementById('hidden_type_code').value = '';
        document.getElementById('div_insurance_or_patient').innerHTML = '';
        var elSel = document.getElementById('final_this_page_criteria');
        TheOptionIndex = checkOptionExist(value0, seperator);
        if (TheOptionIndex == -1) {
        } else {
            elSel.remove(TheOptionIndex);
        }
    }

    function removeOptionSelected() {//Remove the selected options from the drop down.
        OptionRemoved = 'no';
        var elSel = document.getElementById('final_this_page_criteria');
        var i;
        for (i = elSel.length - 1; i >= 0; i--) {
            if (elSel.options[i].selected) {
                elSel.remove(i);
                OptionRemoved = 'yes';
            }
        }
        if (OptionRemoved == 'no') {
            alert(<?php echo xlj('Select Criteria to Remove'); ?>)
        }
    }

    function removeOptionsAll() {//Remove all options from the drop down.
        OptionRemoved = 'no';
        var elSel = document.getElementById('final_this_page_criteria');
        var i;
        for (i = elSel.length - 1; i >= 0; i--) {
            if (elSel.options[i]) {
                elSel.remove(i);
                OptionRemoved = 'yes';
            }
        }
        if (OptionRemoved == 'no') {
            alert(<?php echo xlj('Select Criteria to Remove'); ?>)
        }
    }

    function ProcessBeforeSubmitting()//Validations and necessary actions are taken here.
    {//Text of the drop down 'final_this_page_criteria' is copied to the value of the drop down 'final_this_page_criteria_text'
     //So that both value and text of the final criteria can be restored back.
        if (!ValidateDateCriteria('final_this_page_criteria')) {
            return false;
        }
        var selObj = document.getElementById('final_this_page_criteria');
        var elSel = document.getElementById('final_this_page_criteria_text');
        var i;
        for (i = elSel.length - 1; i >= 0; i--) {
            elSel.remove(i);
        }
        for (i = 0; i < selObj.options.length; i++) {
            var elOptNew = document.createElement('option');
            elOptNew.text = 'Sample';//This value has no relevance can be any thing.
            elOptNew.value = selObj.options[i].text;
            try {
                elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
            } catch (ex) {
                elSel.add(elOptNew); // IE only
            }
        }
        selectAllOptions('final_this_page_criteria');
        selectAllOptions('final_this_page_criteria_text');
        return true;
    }

    function selectAllOptions(selStr) {//Before submitting the multiselct drop downs are selected,then only they will be got in php.
        var selObj = document.getElementById(selStr);
        for (var i = 0; i < selObj.options.length; i++) {
            selObj.options[i].selected = true;
        }
    }

    function SetDateCriteriaCustom(ObjectPassed) {//Setting the value of drop down to 'custom' when user changes the date manually using the date picker.
        var selObj = document.getElementById(ObjectPassed);
        selObj.value = 'custom';
    }

    function ValidateDateCriteria(selStr) {//From date should not be greater than To date.
        var selObj = document.getElementById(selStr);
        for (var i = 0; i < selObj.options.length; i++) {
            if (selObj.options[i].value.indexOf('between') != -1) {
                DateArray = selObj.options[i].value.split("'");
                if (DateArray[1] > DateArray[3]) {
                    alert(<?php echo xlj('From Date Cannot be Greater than To Date.'); ?>);
                    return false;
                }
                if (DateArray[1] == '' || DateArray[3] == '') {
                    alert(<?php echo xlj('Date values Cannot be Blank.'); ?>);
                    return false;
                }
            }
        }
        return true;
    }

    function getPosition(who, TopOrLeft) {//Returns the top and left position of the passed object.
        var T = 0, L = 0;
        while (who) {
            L += who.offsetLeft;
            T += who.offsetTop;
            who = who.offsetParent;
        }
        if (TopOrLeft == 'Top')
            return T;
        else if (TopOrLeft == 'Left')
            return L;
    }

    //-------------------------------------------------------------------------------------------------------------------------
    //In Internet Explorer the ajax drop down of insurance was gettign hidden under the select drop down towards the right side.
    //So an iframe is added to solve this issue.
    //-------------------------------------------------------------------------------------------------------------------------
    function show_frame_to_hide() {//Show the iframe
        if (document.getElementById("AjaxContainerInsurance")) {
            document.getElementById("frame_to_hide").style.top = getPosition(document.getElementById('ajax_div_insurance'), 'Top') + "px";
            document.getElementById("frame_to_hide").style.left = getPosition(document.getElementById('final_this_page_criteria'), 'Left') + "px";
            document.getElementById("frame_to_hide").style.display = "inline";
        }
    }

    function hide_frame_to_hide() {//Hide the iframe
        if (!document.getElementById("AjaxContainerInsurance")) {
            document.getElementById("frame_to_hide").style.display = "none";
        }
    }

    //-------------------------------------------------------------------------------------------------------------------------
    function TakeActionOnHide()//Action on clicking out side the ajax drop down of insurance.Hides the same.
    {
        HideTheAjaxDivs();
        hide_frame_to_hide();
    }

    //===================================================================================================================================
</script>
