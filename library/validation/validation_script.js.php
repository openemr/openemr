<?php
/*If the validation (new) that uses the validate.js library was set on "on" in globals include the following libraries*/
if($GLOBALS['new_validate']) {
?>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/moment.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/validate/validate.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/validate/validate_extend.js"></script>
<?php
 }
?>

<script language='JavaScript'>
    <?php /*Added 2 parameters to the already existing submitme form*/
    /*new validate: Use the new validation library (comes from globals)*/
    /*e: event*/
    /*form id: used to get the validation rules*/?>

    function submitme(new_validate,e,form_id) {

    //Use the old validation script if no parameter sent (backward compatibility)
    //if we want to use the "old" validate function (set in globals) the validate function that will be called is the one that
    // was on code up to today (the validat library was not loaded ( look at the top of this file)
        if (new_validate !== 1) {
            var f = document.forms[0];
            if (validate(f)) {
                top.restoreSession();
                f.submit();
            } else { //If there was an error prevent the form submit in order to display them
                e.preventDefault();
            }
        } else { //If the new validation library is used :
            <?php
            /*Get the constraint from the DB-> LBF forms accordinf the form_id*/
            $constraints = LBF_Validation::generate_validate_constraints($form_id);
            ?>
            //Variables used for the validation library and validation mechanism
            var valid = true ;
            var constraints = <?php echo $constraints;?>;
            //We use a common error for all the errors because of the multilanguage capability of openemr
            //TODO: implement a traslation mechanism of the errors that the library returns
            var error_msg ='<?php echo xl('is not valid');?>';
            var form = document.querySelector("form#"+form_id);

            //gets all the "elements" in the form and sends them to the validate library
            //for more information @see https://validatejs.org/
            var elements = validate.collectFormValues(form);

            //custom validate for multiple select(failed validate.js)
            //the validate js cannot handle the LBF multiple select fields
            var element, new_key;
            for(var key in elements){

                element = $('[name="'+ key + '"]');

                if($(element).is('select[multiple]')) {

                    new_key = key.substring(0, key.length - 2);
                    if(validate.isObject(constraints[new_key])) {

                        if(constraints[new_key].presence && elements[key] === null) {

                            appendError(element, new_key);
                        }
                    }
                    //remove multi select key to prevent errors
                    delete elements[key];
                    delete constraints[new_key];
                }
            }

            //error conatins an list of the elements and their errors
            var errors = validate(elements, constraints);
            if (typeof  errors !== 'undefined') {
                //prevent default if trigger is submit button
                if(typeof (e) !== 'undefined') {
                    e.preventDefault();
                }
                showErrors(form, errors);
                valid = false;
            }

            //In case there were errors they are displayed with this functionn
            function showErrors(form, errors) {

                for (var key in errors) {
                    element = $('[name="'+ key + '"]');
                    if (errors.hasOwnProperty(key)) {

                        appendError(element, key)
                    }
                }
            }
            /**
            * append 'span' with error message
            */
            function appendError(input, id){

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
                var title= $(input).attr('title');
                if(title == undefined) { title = "" }
                $("#error_" + id).text(title +' '+error_msg);

                $(input).addClass('error-border');

                //mark the tub
                var parent_div = $(input).parents('div.tab');
                if($(parent_div).is('div')) {
                    var div_id = $(parent_div).attr('id');
                    var type_tab = div_id.substr(4);
                    $('a#header_tab_'+type_tab).css('color', 'red');
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
            * */
            function hideErrors(input, id){
                $(input).removeClass('error-border');
                $("#error_" + id).text('');

                var parent_div = $(input).parents('div.tab');
                if($(parent_div).is('div')) {
                    var div_id = $(parent_div).attr('id');
                    var type_tab = div_id.substr(4);
                    $('a#header_tab_'+type_tab).css('color', 'black');
                }
            }
            return valid;
        }
    }
</script>

