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
                        var title= form.elements.namedItem(key).title;
                        document.getElementById("error_" + key).innerHTML =title +' '+error_msg;
                    }
                }
            }

        }
    }
</script>

