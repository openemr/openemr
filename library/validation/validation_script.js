/**
 * library/validation/validation_script.js
 *
 * Validation functions that work with the validate.js library
 *
 * NOTE: This file currently does not support the LBF form constraints
 * TODO: @adunsulag add LBF form constraints support
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Sharon Cohen <sharonco@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @author Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2025 Mountain Valley Health <mvhinspire@mountainvalleyhealthinc.com>
 * @link    http://www.open-emr.org
 */
window.OeFormValidation = (function(window) {
    let xl = window.top.xl || function(str) { return str; };
    function submitme(new_validate,e,form_id, constraints, g_date_format) {

        top.restoreSession();

        if (!g_date_format) {
            g_date_format = window.top.jsGlobals.date_display_format || "";
        }

        //Use the old validation script if no parameter sent (backward compatibility)
        //if we want to use the "old" validate function (set in globals) the validate function that will be called is the one that
        // was on code up to today (the validate library was not loaded ( look at the top of this file)
        if (new_validate !== 1) {
            var f = document.forms[0];
            if (validate(f)) {
                somethingChanged = false;
                f.submit();
            } else { //If there was an error prevent the form submit in order to display them
                e.preventDefault();
            }
        } else { //If the new validation library is used :
            //Variables used for the validation library and validation mechanism
            if(constraints==undefined || constraints=='') {
                // TODO: @adunsulag we could make this an AJAX call to let the default fallback behavior happen...
                console.error(xl("Validation Constraints are missing"));
                return true; // nothing to validate
            }
            var valid = true ;

            //We use a common error for all the errors because of the multilanguage capability of openemr
            var form = document.querySelector("form#"+form_id);
            //gets all the "elements" in the form and sends them to the validate library
            //for more information @see https://validatejs.org/
            var elements = validate.collectFormValues(form);
            var element, new_key;

            //before catch all values - clear filed that in display none, this will enable to fail on this fields.
            for(var key in elements){
                //catch th element with the name because the id of select-multiple contain '[]'
                // and jquery throws error in those situation
                element = $('[name="'+ key + '"]');
                if(!$(element).is('select[multiple]')) {

                    if($(element).parent().prop('style') != undefined && ($(element).parent().prop('style').visibility == 'hidden'|| element.parent().parent().css('display')== 'none')){
                        $(element).val("");
                    }

                }
            }

            //get the input value after romoving hide fields
            elements = validate.collectFormValues(form);
            //custom validate for multiple select(failed validate.js)
            //the validate js cannot handle the LBF multiple select fields
            for(var key in elements){

                element = $('[name="'+ key + '"]');

                if($(element).is('select[multiple]')) {

                    new_key = key.substring(0, key.length - 2);
                    if(validate.isObject(constraints[new_key])) {
                        //check if select multiple does empty (empty or unassigned)
                        if(constraints[new_key].presence && (elements[key].length == 0 || elements[key][0] == null )) {

                            appendError(element, new_key);
                            e.preventDefault();
                            valid = false;
                        }
                    }
                    //remove multi select key to prevent errors
                    delete elements[key];
                    delete constraints[new_key];
                }
            }

            //error conatins an list of the elements and their errors
            //set false full message because the name of the input not can be translated
            var errors = validate(elements, constraints, {fullMessages: false});
            if (typeof  errors !== 'undefined') {
                //prevent default if trigger is submit button
                if(typeof (e) !== 'undefined') {
                    e.preventDefault();
                }
                showErrors(form, errors);
                valid = false;
            }else{
                somethingChanged = false;
            }

            //In case there were errors they are displayed with this functionn
            function showErrors(form, errors) {

                for (var key in errors) {
                    element = $('[name="'+ key + '"]');
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        appendError(element, key, errors[key][0])
                    }
                }
            }
            /**
             * append 'span' with error message
             */
            function appendError(input, id, message){

                //append 'span' tag for error massages if not exist
                if($("#error_" + id).length == 0) {
                    //If have another element after the input
                    if($(input).next().length > 0) {

                        $(input).next().after("<span id='error_" + id +"' class='error-message' '></span>");

                    } else {
                        $(input).after("<span id='error_" + id +"' class='error-message'></span>");

                    }
                }
                //show error message
                //Validate.js enables to overwrite the error messages by adding 'message' to constraints json. if you want to use your custom message instead
                //default message you need to add boolean property to the constraints json - 'custom_messages':true.
                var error_msg = (typeof constraints.custom_messages !== 'undefined' && constraints.custom_messages) ? message : getErrorMessage(message);

                var title= $(input).attr('title');
                //if it's long title remove it from error message (this could destroy the UI)
                if(title == undefined || title.length > 20) { title = "" }
                $("#error_" + id).text(title +' '+error_msg);

                $(input).addClass('error-border');

                //mark the tub
                var parent_div = $(input).parents('div.tab');
                if($(parent_div).is('div')) {
                    var div_id = $(parent_div).attr('id');
                    var type_tab = div_id.substr(4);
                    $('a#header_tab_'+type_tab).css('color', 'var(--danger)');
                }

                //open tab for new patient form
                parent_div = $(input).parents('div.section');
                if(parent_div !== undefined) {
                    $(parent_div).css('display' , 'block');
                    div_id = $(parent_div).attr('id');
                    if(div_id !== undefined) {
                        div_id = div_id.substr(-1);
                        var input_checkbox = document.getElementById('form_cb_' + div_id);
                        input_checkbox.checked = true;
                    }
                }


                //bind hide function on focus/select again
                $(input).on('click focus select', function(){
                    hideErrors(this, id);
                });
                //for datepicker button
                if($(input).next().is('img')){
                    $(input).next().click(function(){

                        hideErrors($(this).prev(), id);
                    });
                }

            }
            /*
            * hide error message
            * @param element
            **/
            function hideErrors(input, id){
                $(input).removeClass('error-border');
                $("#error_" + id).text('');

                var parent_div = $(input).parents('div.tab');
                if($(parent_div).is('div')) {
                    var div_id = $(parent_div).attr('id');
                    var type_tab = div_id.substr(4);
                    $('a#header_tab_'+type_tab).css('color', 'var(--black)');
                }
            }
            /*
            * Check if exist translation for current error message else return default message.
            * In addition you can adding custom error message to the constraints json according validate.js instructions and add the translation here
            * @param message
            **/
            function getErrorMessage(message){
                //enable to translate error message
                //todo - adding all the translations string from validate.js
                // console.log(message);
                switch (message){
                    case 'Patient Name Required':
                        return xl('Patient Name Required');
                    case 'An end date later than the start date is required for repeated events!':
                        return xl('An end date later than the start date is required for repeated events!');
                    case 'Required field missing: Please enter the User Name':
                        return xl('Required field missing: Please enter the User Name');
                    case 'Please enter the password':
                        return xl('Please enter the password');
                    case 'Required field missing: Please enter the First name':
                        return xl('Required field missing: Please enter the First name');
                    case 'Required field missing: Please enter the Last name':
                        return xl('Required field missing: Please enter the Last name');
                    case 'Please choose a patient':
                        return xl('Please choose a patient');
                    case 'Must be future date':
                        return xl('Must be future date');
                    case 'Recipient required unless status is Done':
                        return xl('Recipient required unless status is Done');
                    default:
                        return xl('is not valid');
                }
            }
            //the result of validation
            return valid;
        }
    }

    function init() {
        //enable submit button until load submitme function
        if(document.getElementById('submit_btn') != null) {
            document.getElementById('submit_btn').disabled = false;
        }
    }

    return {
        init: init
        ,submitme: submitme
    };
})(window);
