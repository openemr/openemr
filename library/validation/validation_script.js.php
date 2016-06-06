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
            var errors = validate(form, constraints);
            if (typeof  errors !== 'undefined') {
                //prevent default if trigger is submit button
                if(typeof (e) !== 'undefined') {
                    e.preventDefault();
                }
                showErrors(form, errors);
                valid = false;
            } else {
                $("form#"+form_id).submit();
            }

            function showErrors(form, errors) {

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {

                        var input = $('#'+key);

                        //append 'span' tag for error massages if not exist
                        if($("#error_" + key).length == 0) {
                            //If have another element after the input
                            if($(input).next().length > 0) {

                                $(input).next().after("<span id='error_" + key +"' class='error-message' '></span>");

                            } else {
                                $(input).after("<span id='error_" + key +"' class='error-message'></span>");

                            }
                        }
                        //show error message
                        var title= form.elements.namedItem(key).title;
                        $("#error_" + key).text(title +' '+error_msg);

                        $(input).addClass('error-border');
                        //bind hide function on focus/select again
                        $(input).on('click focus select', function(){
                             hideErrors(this);
                        });
                        //for datepicker button
                        if($(input).next().is('img')){
                            $(input).next().click(function(){

                                hideErrors($(this).prev());
                            });
                        }

                    }
                }
            }
            /*
            * hide error message
            * @param element
            * */
            function hideErrors(input){
                $(input).removeClass('error-border');
                var id = $(input).attr('id');
                $("#error_" + id).text('');
            }
            return valid;
        }
    }
</script>

