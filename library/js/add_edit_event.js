function verify_selecteerbaar (a) {
    var f = document.forms[0];  var a;
    if (f.box5.value != 0) a = f.box5.value; 
    else if (f.box4.value != 0) a = f.box4.value; 
    else if (f.box3.value != 0) a = f.box3.value;
    else if (f.box2.value != 0) a = f.box2.value; 
    else if (f.box1.value != 0) a = f.box1.value;
    else { alert('You must choose an activity.'); return false; }

    var answer = $.ajax({
                    url: "<?=$link?>",
                    type: 'POST',
                    data: 'vcode='+a,
                    async: false
                }).responseText;
    if ( answer == 'false') { alert("Please select again"); return false; }
}


/*
==================================================
DROPDOWNS FOR ACTIVITIES

==================================================
*/
function boxes() {

$(document).ready(function(){
        var link_dbc = '../../../library/DBC_functions.php';
        $('#box2').hide();
        $('#box3').hide();     
        $('#box4').hide();
        $('#box5').hide();
        $('#box1').bind('change', function(){
            $('#box2').html(
                $.ajax({
                    type: 'POST',
                    url: link_dbc,
                    data: 'code=' + $('#box1').val(),
                    async: false,
                }).responseText
            )
            // validation for timing NOTA maybe not used anymore
            /*valbox = $('#box1').val();
            if ( valbox == 'act_1' || valbox == 'act_7' ) {
                this.form.form_duration.disabled = true; this.form.form_duration.value = 0;
                this.form.form_duration_indirect.disabled = true;
                this.form.form_duration_travel.disabled = true;
            } else {
                this.form.form_duration.disabled = false;
                this.form.form_duration_indirect.disabled = false;
                this.form.form_duration_travel.disabled = false;
            }*/
        });
    
        $('#box1').bind('focus', function(){
            $('#box2').show(); $('#box3').hide(); $('#box4').hide(); $('#box5').hide();
        });
        
        $('#box2').bind('change', function(){
            $('#box3').html(
                $.ajax({
                    type: 'POST',
                    url: link_dbc,
                    data: 'code=' + $('#box2').val(),
                    async: false,
                }).responseText
            )
        });
        // if empty box, prevent the next to show
        $('#box2').bind('focus', function(){
        if ( $('#box2').val() != 0 ) {
            $('#box3').show(); }
        else {
            $('#box3').hide();
        }
        $('#box4').hide(); $('#box5').hide();
        });
    
        $('#box3').bind('change', function(){
            $('#box4').html(
                $.ajax({
                    type: 'POST',
                    url: link_dbc,
                    data: 'code=' + $('#box3').val(),
                    async: false,
                }).responseText
            )
        });
        // if empty box, prevent the next to show
        $('#box3').bind('focus', function(){
        if ( $('#box3').val() != 0 ) {
            $('#box4').show(); }
        else {
            $('#box4').hide();
        }
        $('#box5').hide();
        });
        
        $('#box4').bind('change', function(){
            $('#box5').html(
                $.ajax({
                    type: 'POST',
                    url: link_dbc,
                    data: 'code=' + $('#box4').val(),
                    async: false
                }).responseText
            )
        });
        // if empty box, prevent the next to show
        $('#box4').bind('focus', function(){
        if ( $('#box4').val() != 0 ) {
            $('#box5').show(); }
        else {
            $('#box5').hide();
        }
        });
    
        // time validation client side
        $('#form_save').bind('click', function(){
            // validation for timing
            durbox = $('#form_duration').val();
            valbox = $('#box2').val();
            if ( valbox == 'act_3.2' && (!parseInt(durbox, 10)) ) {
                alert("For selected activity you must have a value for direct time!");
                window.close();
            }
        });
});

}


/*
==================================================
EDITING CASE

==================================================
*/
function editcase() {
$(document).ready(function(){
    var link_dbc = '../../../library/DBC_functions.php';
    $('#box1').attr('disabled', true);
    $('#addc').bind('click', function(){
        $('#box1').attr('disabled', false);
        $.ajax({
        url: link_dbc,
        type: 'POST',
        data: 'editactiv=1',
        async: false
        });
});
});

}