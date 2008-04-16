<?php
 include_once("../../globals.php");
 include_once("$srcdir/acl.inc");

 // Session pid must be right or bad things can happen when demographics are saved!
 //
 include_once("$srcdir/pid.inc");
 $set_pid = $_GET["set_pid"] ? $_GET["set_pid"] : $_GET["pid"];
 if ($set_pid && $set_pid != $_SESSION["pid"]) {
  setpid($set_pid);
 }

 include_once("$srcdir/patient.inc");

$r      = getPatientData($pid); // regular patient data
$rnl    = getPatientDataNL($pid); // additional patient data
$ri     = getInsuranceData($pid,"primary");

// must be before the js
$pro = get_provider_DBC($pid);
$ref = ( !$pro['pro_referer'] ) ? get_referer_DBC($pid): FALSE;

// in db the value for sex is a word! so we must translate it for the form
switch ( $r['sex'] ) {
    case 'Male' : $rsex = 1; break;
    case 'Female': $rsex = 2; break;
    default: $rsex = 0;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<script type="text/javascript" src="../../../library/js/jquery.js"></script>
<script type="text/javascript" src="../../../library/js/jquery-calendar.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/jquery-calendar.css">

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
    $('#df_insdatum').calendar({autoPopUp: 'button', buttonImageOnly: false,
	buttonImage: '', buttonText: '...', yearRange: '-1:+1', dateFormat: 'YMD-'});


    $('#df_rfr_type').bind('change', function(){
        if ( $('#df_rfr_type').val() != '0' ) {
            $('#df_rfr_code').attr("disabled","disabled");
        } else {
            $('#df_rfr_code').attr("disabled","");
        }
    });

    <?php if ( $pro['pro_referer'] ) { ?>
        $('#df_rfr_block').hide();
    <?php } else { ?>
        $('#df_rfr_block').show();
    <?php } ?>
    $('#df_pcp_rfr').bind('click', function(){
        if ( $('#df_pcp_rfr').attr("checked") ) {
            $('#df_rfr_block').hide();
        } else {
            $('#df_rfr_block').show();
        }
    });
 
    $('#remove_referer').bind('click', function(){
        $.ajax({
            type: 'POST',
            url: '../encounter/as.php',
            data: 'remove=<?=$pid?>',
            async: false
        });
        $('#df_pcp_rfr').attr("checked" ,"checked");
        $('#df_rfr_block').hide();
    });

    $('#df_policy').attr("disabled","disabled");
    $('#df_insurance').bind('change', function(){
        if ( $('#df_insurance').val() == '0' ) {
            $('#df_policy').attr("disabled","disabled");
        } else {
            $('#df_policy').attr("disabled","");
        }
    });

});
</script>

<?php 
 $result2 = getEmployerData($pid);

 // Check authorization.
 $thisauth = acl_check('patients', 'demo');
 if ($pid) {
  if ($thisauth != 'write')
   die("Updating demographics is not authorized.");
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   die("You are not authorized to access this squad.");
 } else {
  if ($thisauth != 'write' && $thisauth != 'addonly')
   die("Adding demographics is not authorized.");
 }

$langi = getLanguages();
?>


<html>
<head>

<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">
<link rel=stylesheet href="../../themes/style_dbc.css" type="text/css">

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body <?php echo $top_bg_line; ?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form action='demographics_save_dutch.php' name='demographics_form' method='post'>
<input type=hidden name=mode value=save>

<?php if ($GLOBALS['concurrent_layout']) { ?>

    <?php if( $GLOBALS['dutchpc'] )
    { ?>
    <a href="demographics_dutch.php">
    <?php } else 
    { ?>
    <a href="demographics.php">
    <?php } ?>
    
<?php } else { ?>
    <a href="patient_summary.php" target=Main>
<?php } ?>
<font class=title><?php xl('Demographics','e'); ?></font>
<font class=back><?php echo $tback;?></font></a>

<h5>De velden met * moeten worden ingevuld.</h5>

<?php if ( $_SESSION['errormsg'] ) echo '<span class="error">' .$_SESSION['errormsg']. '</span>'; ?>

<!-- patient personal data -->
<hr />
<table border="0" cellpadding="0" class="tbl_demo">
    <tr>
        <td>Roepnaam</td>
        <td><input type="text" id="df_roepnaam" name="df_roepnaam" size="20" value="<?=$r['fname']?>" /></td>
        <td>Voorletters*/Voorvoegsel/Achternaam*</td>
        <td>
            <input type="text" id="df_voorletters" name="df_voorletters" size="10" value="<?=$rnl['pdn_initials']?>" />
            <input type="text" id="df_voorvoegsel" name="df_voorvoegsel" size="8" value="<?=$rnl['pdn_pxlast']?>" />
            <input type="text" id="df_achternaam" name="df_achternaam" size="20" value="<?=$r['lname']?>" /></td>
    </tr>
    <tr style="background-color: #80BAC8;">
        <td colspan="2">PARTNER</td>
        <td>Voorvoegsel/Achternaam</td>
        <td><input type="text" id="df_par_voorvoegsel" name="df_par_voorvoegsel" size="8" value="<?=$rnl['pdn_pxlastpar']?>"/>
            <input type="text" id="df_achternaam" name="df_par_achternaam" size="20" value="<?=$rnl['pdn_lastpar']?>"/></td>
    </tr>
    <tr>
        <td>Geboortedatum*</td>
        <td><input type="text" id="df_geboorte" name="df_geboorte" size="10" value="<?=$r['DOB']?>"/></td>
        <td>Burgerservicenummer
            <input type="text" id="df_burgerservicenummer" name="df_burgerservicenummer" size="11" value="<?=$r['ss']?>"/>
        </td>
        <td>Geslacht* <?php dropdown_sex($rsex, 'df_sex');?>
        &nbsp;Huwelijkse staat <?php dropdown_marital($r['status'], 'df_huwe');?>
        </td>
    </tr>
    <tr>
        <td>Licence/ID </td>
        <td><input type="text" id="df_licid" name="df_licid" size="20" value="<?=$r['drivers_license']?>" /></td>
        <td colspan="2">Email <input type="text" id="df_emailcon" name="df_emailcon" size="20" value="<?=$r['email']?>"/></td>
    </tr>
    <tr>
        <td>Behandelaar</td>
        <td colspan="4"><?php dropdown_providers($r['providerID'], 'df_behan');?></td>
    </tr>
</table>

<!-- patient address -->
<hr />
<table border="0" cellpadding="0" width='80%' class="tbl_demo">
    <tr colspan="2"><td><strong>Adres</strong></td></tr>
    <tr style="background-color: #80BAC8;">
        <td>Straat* <input type="text" id="df_straat" name="df_straat" size="30" value="<?=$rnl['pdn_street']?>"/>
            Huisnummer* <input type="text" id="df_nummer" name="df_nummer" size="5" value="<?=$rnl['pdn_number']?>"/>
            Toevoeging <input type="text" id="df_toevoe" name="df_toevoe" size="5" value="<?=$rnl['pdn_addition']?>"/></td>
        <td>Plaats* <input type="text" id="df_plaats" name="df_plaats" size="20" value="<?=$r['city']?>"/></td> 
    </tr>
    <tr>
        <td>Land* &nbsp;
            <?php if ( !$r["country_code"] ) $r["country_code"] = 150;
            dropdown_countries($r["country_code"], 'df_land'); ?>
        </td>
        <td>Postcode* <input type="text" id="df_postal" name="df_postal" size="6" maxlength="6" value="<?=$r['postal_code']?>"/></td> 
    </tr>
</table>

<!-- patient phones -->
<br /><hr />
<table border="0" cellpadding="0" width='80%' class="tbl_demo">
    <tr colspan="4"><td><strong>Tel.</strong></td></tr>
    <tr>
        <td width="30%">Contactpersoon bij noodgevallen </td>
        <td><input type="text" id="df_emcon" name="df_emcon" size="20" value="<?=$r['contact_relationship']?>" /></td>
        <td>Tel.prive</td>
        <td><input type="text" id="df_telh" name="df_telh" size="20" value="<?=$r['phone_home']?>" /></td>
    </tr>
    <tr>
        <td rowspan="2">Telefoonnummer bij noodgevallen</td>
        <td rowspan="2"><input type="text" id="df_emtel" name="df_emtel" size="20" value="<?=$r['phone_contact']?>" /></td>
        <td>Tel.werk</td>
        <td><input type="text" id="df_telw" name="df_telw" size="20" value="<?=$r['phone_biz']?>" /></td>
    </tr>
    <tr>
        <td>GSM</td>
        <td><input type="text" id="df_telm" name="df_telm" size="20" value="<?=$r['phone_cell']?>" /></td>
    </tr>
    <tr>
        <td>Voicemail toegestaan <?php $sel1 = ( $r['hipaa_voice'] == xl('Yes') )? 1 : 0; dropdown_yesno($sel1, 'df_allowvoice'); ?></td>
        <td>Email toegestaan <?php $sel2 = ( $r['hipaa_mail'] == xl('Yes') )? 1 : 0; dropdown_yesno($sel2, 'df_allowemail'); ?></td>
        <td colspan="2">Bij wie kunnen we een bericht achterlaten?
            <input type="text" id="df_allowmsg" name="df_allowmsg" size="20" value="<?=$r['hipaa_message']?>"/>
        </td>
    </tr>
</table>

<!-- primary care providers -->
<br /><hr />
<table border="0" class="tbl_demo">
<tr>
    <td valign='top' colspan='2'><strong>Huisarts</strong></td>
     <td>Praktijknaam</td>
     <td colspan="3">
        <input type="text" size="30" name="df_pcp_company" id="df_pcp_company" value="<?=$pro['pro_company']?>" />
    </td>
    <?php $checked = ( $pro['pro_referer'] ) ? 'checked' : '' ?>
    <td>Referer <input type="checkbox" name="df_pcp_rfr" id="df_pcp_rfr" value="1" <?=$checked?>/></td>
</tr>
<tr>
    <td>Voorletters</td>
    <td><input type="text" size="5" name="df_pcp_initials" value="<?=$pro['pro_initials']?>" /></td>
    <td>Voorvoegsel</td>
    <td><input type='entry' size='10' name='df_pcp_prefix' id="pcp_prefix" value="<?php echo $pro['pro_prefix'] ?>"/>
    <td>Achternaam</td>
    <td colspan="2"><input type="text" size="20" name="df_pcp_lname" value="<?php echo $pro['pro_lname'] ?>" /></td>
</tr>
<tr style="background-color: #80BAC8;">
    <td>Straat</td>
    <td><input type="text" size="20" name="df_pcp_street" value="<?php echo $pro['pro_street'] ?>" /></td>
    <td>Huisnummer
        <input type="text" size="4" name="df_pcp_number" value="<?php echo $pro['pro_number'] ?>" /></td>
    <td>Toevoeging
        <input type="text" size="4" name="df_pcp_addition" value="<?php echo $pro['pro_addition'] ?>" /></td>
    <td>Plaats
        <input type="text" size="20" name="df_pcp_city" value="<?php echo $pro['pro_city'] ?>" /></td>
    <td>Postcode
        <input type="text" size="6" maxlength="6" name="df_pcp_zipcode" value="<?php echo $pro['pro_zipcode'] ?>" /></td>
</tr>
<tr>
    <td>Tel.</td>
    <td><input type="text" size="10" name="df_pcp_phone" value="<?php echo $pro['pro_phone'] ?>" /></td>
    <td>Fax</td>
    <td><input type="text" size="10" name="df_pcp_fax" value="<?php echo $pro['pro_fax'] ?>" /></td>
    <td>Email</td>
    <td colspan="3"><input type="text" size="20" name="df_pcp_email" value="<?php echo $pro['pro_email'] ?>" /></td>
</tr>
</table>
<hr />

<!-- referer =============================== -->
<div id='df_rfr_block'>
<table border="0" class="tbl_demo">
<tr>
    <td valign='top' colspan='6'><strong>Verwijzer</strong></td>
</tr>
<tr>
    <td>Soort verwijzer</td>
    <td>
        <select name="df_rfr_type" id="df_rfr_type">
            <?php 
            $prefixes = array("0" => '', "0100" => "Huisarts", "0300" => 'Medisch Specialist', "1400" => 'Bedrijfsarts');
            foreach ( $prefixes as $k => $v ) {
                $sel = ( $k == $ref['ref_code'] ) ? 'selected' : '';
                echo "<option value='$k' $sel>$v</option>";
            }
            ?>
        </select>
    </td>
    <?php 
    // display in dropdown or text box
    $ref_code = ( array_key_exists($ref["ref_code"], $prefixes) ) ? '' : $ref["ref_code"];
    ?>
    <td>VEKTIS code:</td>
    <td><input type="text" size="8" name="df_rfr_code" id="df_rfr_code" value="<?php echo $ref_code; ?>" <?php if ( !$ref_code) echo "disabled='disabled'"; ?>/></td>
    <td>Praktijknaam</td>
    <td colspan="3"><input type="text" size="30" name="df_rfr_company" value="<?php echo $ref['ref_company'] ?>" /></td>
</tr>
<tr>
    <td>Voorletters:</td>
    <td><input type="text" size="5" name="df_rfr_initials" value="<?php echo $ref['ref_initials'] ?>" /></td>
    <td>Voorvoegsel:</td>
    <td><input type='entry' size='10' name='df_rfr_prefix' id="df_rfr_prefix" value="<?php echo $ref['ref_prefix'] ?>"/>
    <td>Achternaam:</td>
    <td><input type="text" size="20" name="df_rfr_lname" value="<?php echo $ref['ref_lname'] ?>" /></td>
</tr>
<tr style="background-color: #80BAC8;">
   <td>Straat</td>
    <td><input type="text" size="20" name="df_rfr_street" value="<?php echo $ref['ref_street'] ?>" /></td>
    <td>Huisnummer
        <input type="text" size="4" name="df_rfr_number" value="<?php echo $ref['ref_number'] ?>" /></td>
    <td>Toevoeging
        <input type="text" size="4" name="df_rfr_addition" value="<?php echo $ref['ref_addition'] ?>" /></td>
    <td>Plaats
        <input type="text" size="20" name="df_rfr_city" value="<?php echo $ref['ref_city'] ?>" /></td>
    <td>Postcode
        <input type="text" size="6" maxlength="6" name="df_rfr_zipcode" value="<?php echo $ref['ref_zipcode'] ?>" /></td>
</tr>
<tr>
    <td>Tel.</td>
    <td><input type="text" size="10" name="df_rfr_phone" value="<?php echo $ref['ref_phone'] ?>" /></td>
    <td>Fax</td>
    <td><input type="text" size="10" name="df_rfr_fax" value="<?php echo $ref['ref_fax'] ?>" /></td>
    <td>Email</td>
    <td colspan="3"><input type="text" size="20" name="df_rfr_email" value="<?php echo $ref['ref_email'] ?>" /></td>
</tr>
<tr><td colspan="8"><a href="#" class=link_submit id="remove_referer">[Verwijder verwijzer]</a></td></tr>
</table>
</div>

<?php
// for the first insurer we put the ztn's opening date in df_insdatum
// retrieve opened ztn and find the date
if ( !total_insurers($pid) ) {
    $oztn = lists_ztn(1);
    //$calval = ( $oztn[0]['cn_dopen'] ) ? $oztn[0]['cn_dopen'] : date('Y-m-d');
    $calval = date('Y') . '-01-01';
} else {
    $calval = '  ';
}
?>

<!-- primary insurance provider --->
<hr />
<table class="tbl_demo">
    <tr>
        <td>Huidige zorgverzekeraar</td>
        <td colspan="2"><?php $in = get_insurers_nl($pid, 1); echo $in['name']; ?></td>
        <td rowspan="3" valign="top"><a href="insurance_history.php" target="_blank">Eerdere verzekeraars</a></td>
    </tr>
    <tr>
        <td>Zorgverzekeraar</td>
        <td colspan="2"><?=dropdown_insurance(0, 'df_insurance');?></td>
    </tr>
    <tr>
        <td>Polisnummer</td>
        <td><input type="text" size="16" name="df_policy" id="df_policy" value="<?=$ri['policy_number']?>" /></td>
        <td>Startdatum <input type="text" size="10" name="df_insdatum" id="df_insdatum" value="<?=$calval?>"/></td>
    </tr>
</table>

<hr />
<a href="javascript:document.demographics_form.submit();" class=link_submit>[Patiëntgegevens opslaan]</a>

</form>

</body>
</html>
