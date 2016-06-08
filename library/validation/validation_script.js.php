<?php
if($GLOBALS['new_validate']) {
?>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/moment.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/validate/validate.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['rootdir'] ?>/../library/js/vendors/validate/validate_extend.js"></script>
<?php
 }
?>

<script language='JavaScript'>

    function submitme(new_validate,e,form_id) {

    //Use the old validation script if no parameter sent (backward compatibility)
        if (new_validate !== 1) {
            var f = document.forms[0];
            if (validate(f)) {
                top.restoreSession();
                f.submit();
            } else {
                e.preventDefault();
            }
        } else {

            <?php

            $constraints = LBF_Validation::generate_validate_constraints($form_id);

            ?>
            var valid = true ;
            var constraints = <?=$constraints?>;
            var error_msg ='<?=xl('is not valid')?>';
            var form = document.querySelector("form#"+form_id);
            var elements = validate.collectFormValues(form);

            //custom validate for multiple select(failed validate.js)
            for(key in elements){
                if(key.slice(-2) == '[]'){
                    new_key = key.substring(0, key.length - 2);
                    elements[new_key] = elements[key];

                    if(validate.isObject(constraints[new_key]) && elements[key] == null){
                        var select = $('[name="'+ key + '"]');
                        appendError(select, new_key);
                    }
                    delete elements[key];
                }
            }

            var errors = validate(elements, constraints);
            if (typeof  errors !== 'undefined') {
                //prevent default if trigger is submit button
                if(typeof (e) !== 'undefined') {
                    e.preventDefault();
                }
                showErrors(form, errors);
                valid = false;
            }

            function showErrors(form, errors) {

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {

                        appendError($('#'+key), key)
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

