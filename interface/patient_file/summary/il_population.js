
    $(document).ready(function() {
        // Using just replace
        var populationILButton = " <input type=\'button\' value=\'Check population DB\' id=\'populationIL\'>";
        $("#form_ILpopulation").replaceWith(populationILButton);

        $("#populationIL").click(function(){

        $.ajax({
            url: 'il_population_api.php',
            data: {
                format: 'json'
            },
            error: function() {
                alert('error');
            },
            dataType: 'json',
            success: function(data) {

                $.each( data, function( id, value ) {
                    var field_id="form_" + id;
                    $("#"+field_id).val(value);
                });

            },
            type: 'GET'
        });
      });
    });
