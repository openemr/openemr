<?php 
include_once("../globals.php");

// Determine if the registration date should be requested.
$crow = sqlQuery("SELECT count(*) AS count FROM layout_options WHERE " .
  "form_id = 'DEM' AND field_id = 'regdate' AND uor > 0");
$regstyle = $crow['count'] ? "" : " style='display:none'";
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo xl($css_header,'e');?>" type="text/css">
<link rel="stylesheet" href="../themes/style_dbc.css" type="text/css">

<script type="text/javascript" src="../../library/js/jquery.js"></script>
<script type="text/javascript" src="../../library/js/jquery-calendar.js"></script>
<link rel="stylesheet" type="text/css" href="../../library/js/jquery-calendar.css">

<script type="text/javascript">
$(document).ready(function(){
    popUpCal.regional['nl'] = {
        clearText: 'sterge | ',
        closeText: 'inchide',
        prevText: '&laquo;&nbsp; | ',
        nextText: ' | &nbsp;&raquo;',
        currentText: 'Azi',

        firstDay: 1,
        dayNames: [
                'D', 'L', 'Ma', 'Mi', 'J', 'V', 'S'
        ],
        monthNames: [
                'Januarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Junie',
                'Julie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'
        ],

        dateFormat: 'YMD-'
    };

    //popUpCal.setDefaults(popUpCal.regional['nl']);
    $('#dbc_insdatum').calendar({autoPopUp: 'button', buttonImageOnly: false,
	buttonImage: '', buttonText: '...', yearRange: '-1:+1', dateFormat: 'YMD-'});
    
    $('#dbc_policy').attr("disabled","disabled");
    $('#dbc_insdatum').attr("disabled","disabled");

    $('#dbc_insurance').bind('change', function(){
        if ( $('#dbc_insurance').val() == '0' ) {
            $('#dbc_policy').attr("disabled","disabled");
            $('#dbc_insdatum').attr("disabled","disabled");
        } else {
            $('#dbc_policy').attr("disabled","");
            $('#dbc_insdatum').attr("disabled","");
        }
    });

    $('#dbc_insdatum').bind('change', function(){
        checkdate($('#dbc_insdatum').val());
    });

    $('#dbc_geboort').bind('change', function(){
        checkdate($('#dbc_geboort').val());
    });

})
</script>

<script type="text/javascript">
/*
 * CHECKING DATE FUNCTION
 */
function checkdate(date) {
   var pattern = new RegExp("19|20[0-9]{2}-0|1[0-9]-[0-3][0-9]");

if (date.match(pattern)) {
    var date_array = date.value.split('-');
    var day = date_array[0];

    // Attention! Javascript consider months in the range 0 - 11
    var month = date_array[1] - 1;
    var year = date_array[2];

    // This instruction will create a date object
    source_date = new Date(year,month,day);

    if(year != source_date.getFullYear()) {
         alert('Year is not valid!');
         return false;
    }

    if(month != source_date.getMonth()) {
         alert('Month is not valid!');
         return false;
    }

    if(day != source_date.getDate()) {
         alert('Day is not valid!');
         return false;
    }
} else {
    alert('Date format is not valid! The format should be: YYYY-MM-DD');
    return false;
}

  return true;
}


/*
 * FORM VALIDATION CLIENT SIDE
 */
function validate() {
    valid = true;

fname = document.new_patient.fname.value;
lname = document.new_patient.lname.value;
voorletters = document.new_patient.dbc_voorletters.value;
geboort =  document.new_patient.dbc_geboort.value;
straat =  document.new_patient.dbc_straat.value;
nummer = document.new_patient.dbc_nummer.value;
plaats = document.new_patient.dbc_plaats.value;
postal = document.new_patient.dbc_postal.value;
insurance = document.new_patient.dbc_insurance.value;
policy = document.new_patient.dbc_policy.value;
insdatum = document.new_patient.dbc_insdatum.value;

if ( fname == "" ) {
    alert ( "Please fill in the 'Voornaam' box." ); valid = false;
}

if ( lname == "" ) {
    alert ( "Please fill in the 'Achternaam' box." ); valid = false;
}

if ( voorletters == "" ) {
    alert ( "Please fill in the 'Voorletters' box." ); valid = false;
}

if ( geboort == "" && !checkdate(geboort) ) {
    alert ( "Please fill in the 'Geboort' box." ); valid = false;
    document.new_patient.dbc_geboort.value = '';
}

if ( straat == "" ) {
    alert ( "Please fill in the 'Straat' box." ); valid = false;
}

if ( nummer == "" ) {
    alert ( "Please fill in the 'Nummer' box." ); valid = false;
}

if ( plaats == "" ) {
    alert ( "Please fill in the 'Plaats' box." ); valid = false;
}

if ( postal == "" ) {
    alert ( "Please fill in the 'Postcode' box." ); valid = false;
}

if ( insurance != 0 ) {
    if ( policy == "" || insdatum == "") {
        alert ( "Please fill all the insurance details." ); valid = false;
    }
}

    return valid;
}

</script>

</head>

<body class="body_top" onload="javascript:document.new_patient.fname.focus();">

<?php if ($GLOBALS['concurrent_layout']) { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 onsubmit='return validate()'>
<span class='title'>Nieuwe Patiënt</span>
<?php } else { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 target='_top' onsubmit='return validate()'>
<a class="title" href="../main/main_screen.php" target="_top" onclick="top.restoreSession()">
Nieuwe Patiënt</a>
<?php } ?>

<br><br>

<div class="tblcontainer">
<table class="tbllist">
    <tr>
        <td><span class='bold'>Voornaam / Voorvoegsel / Achternaam</span></td>
        <td><input type='text' size='15' name='fname' /></td>
        <td align="right"><input type='text' size='7' name='dbc_prefix' /></td>
        <td><input type='text' size='15' name='lname' /></td>
    </tr>
    <tr>
        <td><span class='bold'>Voorletters:</span></td>
        <td><input type='text' size='5' name='dbc_voorletters' /></td>
        <td><span class='bold'>Geslacht:</span></td>
        <td><?php dropdown_sex($rsex, 'dbc_sex');?></td>
    </tr>
    <tr style="background-color: #79B0BE">
        <td><span class='bold'>Voorvoegsel /Achternaam partner:</span></td>
        <td><input type='text' size='7' name='dbc_prefix_partner' /></td>
        <td><input type='text' size='15' name='dbc_lastname_partner' /></td>
        <td></td>
    </tr>
    <tr>
        <td><span class='bold'>Geboortedatum:</span></td>
        <td><input type='text' size='10' maxlenght='10' name='dbc_geboort' id='dbc_geboort'/></td>
        <td></td>
        <td></td>
    </tr>
</table>

<br />
<table class="tbllist">
    <tr><td colspan="5">Adres</td></tr>
    <tr>
        <td><span class='bold'>Straat</span></td>
        <td><input type="text" id="dbc_straat" name="dbc_straat" size="30" /></td>
        <td><span class='bold'>Huisnummer/Toevoeging</span></td>
        <td><input type="text" id="dbc_nummer" name="dbc_nummer" size="5" /></td>
        <td><input type="text" id="dbc_toevoe" name="dbc_toevoe" size="5" /></td>
    </tr>
    <tr>
        <td><span class='bold'>Land</span></td>
        <td>
            <?php if ( !$r["country_code"] ) $r["country_code"] = 150;
            dropdown_countries($r["country_code"], 'dbc_land'); ?>
        </td>
        <td><span class='bold'>Plaats/Postcode</span></td>
        <td><input type="text" id="dbc_plaats" name="dbc_plaats" size="20" /></td>
        <td><input type="text" id="df_postal" name="dbc_postal" size="6" maxlength="6" /></td> 
    </tr>
 
</table>

<br />
<table class="tbllist">
    <tr><td colspan="3">Insurance</td></tr>
    <tr>
        <td><span class='bold'>Zorgverzekeraar</span></td>
        <td colspan="2"><?=dropdown_insurance(0, 'dbc_insurance');?></td>
    </tr>
    <tr>
        <td><span class='bold'>Polisnummer</span></td>
        <td><input type="text" size="16" name="dbc_policy" id="dbc_policy" /></td>
        <td>
            <span class='bold'>Startdatum</span>
            <input type="text" size="10" name="dbc_insdatum" id="dbc_insdatum" />
        </td>
    </tr>


    <tr>
        <td colspan='2'>&nbsp;<br>
        <input type='submit' name='form_create' value=<?php xl('Create New Patient','e'); ?> /></td>
        <td></td>
    </tr>
</table>
</div>

<!-- needed in saving function -->
<input type="hidden" name="regdate" id="regdate" value="<?php date('Y-m-d')?>" />
<input type="hidden" name="mname" id="mname" value="" />
<input type="hidden" name="db_id" id="db_id" value="" />
<input type="hidden" name="title" id="title" value="" />

</form>

</body>
</html>
