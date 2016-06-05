
<script language='JavaScript'>
    function submitme(validation_type,e,form_id) {
    //Use the old validation script if no parameter sent (backward compatibility)
        if (typeof validation_type === 'undefined') {
            var f = document.forms[0];
            if (validate_old(f)) {
                top.restoreSession();
                f.submit();
            }
        } else {

            <?php

            $constraints = LBF_Validation::generate_validate_constraints($form_id);

            ?>
            var constraints = <?=$constraints?>;
            var error_msg ='<?=xl('is not valid')?>';
            var form = document.querySelector("form#"+form_id);
            var errors = validate(form, constraints);
            if (typeof  errors !== 'undefined') {
                e.preventDefault();
                showErrors(form, errors);
            }

            function showErrors(form, errors) {

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {

                    var input = $('#'+key);
                    //append 'span' tag for error massages if not exist
                    if($("#error_" + key).length == 0) {

                    $(input).after("<span id='error_" + key +"' style='color:red;display:block;white-space: nowrap;font-weight: normal;font-size: 11px;'></span>");
                    }
                    //show error message
                    var title= form.elements.namedItem(key).title;
                    $("#error_" + key).text(title +' '+error_msg);

                    $(input).css('border', '1px solid red');
                        //bind hide function on focus/select again
                        $(input).on('click focus select', function(){
                        hideErrors(this);
                    });

                    }
                }
            }
            /*
            * hide error message
            * @param element
            * */
            function hideErrors(element){

                $(element).removeAttr('style');
                $(element).next().text('');
            }

        }
    }
</script>

