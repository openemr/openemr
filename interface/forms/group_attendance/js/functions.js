
$(document).ready(function () {

    /* Initialise Datatable */
    var table = $('#group_attendance_form_table').DataTable({
        language: {
        },
        initComplete: function () {
            $('#group_attendance_form_table_filter').hide(); //hide searchbar
        }
    });

    $('.add_button').click(function () {
        $('#add_participant_element').show();
        $(this).hide();
    });

    $('.cancel_button').click(function () {
        $('#add_participant_element').hide();
        $('.add_button').show();
    });

    $('.patient').click(function(){
        $('.patient').css("border-color", "black");
        $('.error_wrap .error').html("");
    })

    $('.add_patient_button').click(function(e){
        var name = $('.patient').val();
        if(name == ""){
            e.preventDefault();
            $('.patient').css("border-color", "red");
            $('.error_wrap .error').html("Choose Patient");
        }
    });
});
function setpatient(pid, lname, fname, dob){
    $('.patient_id').val(pid);
    $('.patient').val(fname + " " + lname);
}
