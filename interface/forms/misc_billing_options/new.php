<?php
/*
 * This program creates the misc_billing_form
 *
 * Copyright (C) 2007 Bo Huynh
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @link http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("date_qualifier_options.php");

if (! $encounter) { // comes from globals.php
 die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$formid   = 0 + formData('id', 'G');
$obj = $formid ? formFetch("form_misc_billing_options", $formid) : array();

formHeader("Form: misc_billing_options");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>
</head>
<body class="body_top">
<form method=post <?php echo "name='my_form' " .  "action='$rootdir/forms/misc_billing_options/save.php?id=" . attr($formid) . "'>\n";?>

<span class="title"><?php echo xlt('Misc Billing Options for HCFA-1500'); ?></span><br><br>
<span class=text><?php echo xlt('Checked box = yes ,  empty = no');?><br><br>
<label><span class=text><?php echo xlt('Box 10 A. Employment related '); ?>: </span><input type=checkbox name="employment_related" value="1" <?php if ($obj['employment_related'] == "1") echo "checked";?>></label><br><br>
<label><span class=text><?php echo xlt('Box 10 B. Auto Accident '); ?>: </span><input type=checkbox name="auto_accident" value="1" <?php if ($obj['auto_accident'] == "1") echo "checked";?>></label>
<span class=text><?php echo xlt('State'); ?>: </span><input type=entry name="accident_state" size=1 value="<?php echo attr($obj{"accident_state"});?>" ><br><br>
<label><span class=text><?php echo xlt('Box 10 C. Other Accident '); ?>: </span><input type=checkbox name="other_accident" value="1" <?php if ($obj['other_accident'] == "1") echo "checked";?>></label><br><br>
<span class=text><?php echo xlt('Box 10 D. EPSDT Referral Code');?> </span><input type=entry style="width: 25px;" size=2 name="medicaid_referral_code" value="<?php echo attr($obj{"medicaid_referral_code"});?>" >&nbsp;&nbsp;&nbsp;&nbsp;
<label><span class=text><?php echo xlt('EPSDT'); ?> : </span><input type=checkbox name="epsdt_flag" value="1" <?php if ($obj['epsdt_flag'] == "1") echo "checked";?>></label><br><br>
 <tr>
  <td><span class=text><?php echo xlt('Box 14. Populated from the Encounter Screen as the Onset Date needs a qualifier.');?>
    <?php generateDateQualifierSelect("box_14_date_qual",$box_14_qualifier_options,$obj); ?>
  </span></td>
 </tr><br><br>
 <tr>
   <td><span class=text><?php echo xlt('Box 15. Other Date with a qualifier to specify what the date indicates.');?>
     <?php $date_initial_treatment = $obj{"date_initial_treatment"}; ?>
       <input type=text style="width: 70px;" size=10 class='datepicker' name='date_initial_treatment' id='date_initial_treatment'
       value='<?php echo attr($date_initial_treatment); ?>'
       title='<?php echo xla('yyyy-mm-dd'); ?>' />
   </span></td>
   <td>
        <?php generateDateQualifierSelect("box_15_date_qual",$box_15_qualifier_options,$obj); ?>
   </td>

 </tr><br><br>
 <tr>
  <td><span class=text><?php echo xlt('Box 16. Date unable to work from');?>:</span></td>
  <td><?php $off_work_from = $obj{"off_work_from"}; ?>
    <input type=text style="width: 70px;" size=10 class='datepicker' name='off_work_from' id='off_work_from'
    value='<?php echo attr($off_work_from); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
  </td>
 </tr>
 &nbsp;&nbsp;
<tr>
 <td><span class=text><?php echo xlt('Box 16. Date unable to work to');?>:</span></td>
  <td><?php $off_work_to = $obj{"off_work_to"}; ?>
    <input type=text style="width: 70px;" size=10 class='datepicker' name='off_work_to' id='off_work_to'
    value='<?php echo attr($off_work_to); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
  </td>
 </tr>
    <br><br>

    <td class='label_custom'><?php echo xlt('Box 17. Provider') ?>:</td>
    <td><?php  # Build a drop-down list of providers. # Added (TLH)
               genProviderSelect('provider_id', '-- '.xl("Please Select").' --',$obj{"provider_id"});
		?></td>&nbsp;&nbsp;
	<td><span class=text><?php  echo xlt('Box 17. Provider Qualifier'); ?>: </span>
	<tr><td><?php
                echo generate_select_list('provider_qualifier_code', 'provider_qualifier_code',$obj{"provider_qualifier_code"}, 'Provider Qualifier Code');
            ?></td>
	</tr></td>
<br><br>
<tr>
 <td><span class=text><?php echo xlt('Box 18. Hospitalization date from');?>:</span></td>
 <td><?php $hospitalization_date_from = $obj{"hospitalization_date_from"}; ?>
    <input type=text style="width: 70px;" size=10 class='datepicker' name='hospitalization_date_from' id='hospitalization_date_from'
    value='<?php echo attr($hospitalization_date_from); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
  </td>
 </tr>
 &nbsp;&nbsp;
 <tr>
  <td><span class=text><?php echo xlt('Box 18. Hospitalization date to');?>:</span></td>
  <td><?php $hospitalization_date_to = $obj{"hospitalization_date_to"}; ?>
    <input type=text style="width: 70px;" size=10 class='datepicker' name='hospitalization_date_to' id='hospitalization_date_to'
    value='<?php echo attr($hospitalization_date_to); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
  </td>
 </tr>
    <br><br>
<span class=text><?php echo xlt('Box 20. Is Outside Lab used?'); ?>: </span><input type=checkbox name="outside_lab" value="1" <?php if ($obj['outside_lab'] == "1") echo "checked";?>>
<span class=text><?php echo xlt('Amount Charges'); ?>: </span><input type=entry size=7 align='right' name="lab_amount" value="<?php echo attr($obj{"lab_amount"});?>" ><br><br>
<span class=text><?php echo xlt('Box 22. Medicaid Resubmission Code (ICD-9) ');?></span><input type=entry size=9 name="medicaid_resubmission_code" value="<?php echo attr($obj{"medicaid_resubmission_code"});?>" >
<span class=text><?php echo xlt(' Medicaid Original Reference No. ');?></span><input type=entry size=15 name="medicaid_original_reference" value="<?php echo attr($obj{"medicaid_original_reference"});?>" ><br><br>
<span class=text><?php echo xlt('Box 23. Prior Authorization No. ');?></span><input type=entry size=15 name="prior_auth_number" value="<?php echo attr($obj{"prior_auth_number"});?>" ><br><br>
<label><span class=text><?php echo xlt('X12 only: Replacement Claim '); ?>: </span><input type=checkbox name="replacement_claim" value="1" <?php if ($obj['replacement_claim'] == "1") echo "checked";?>></label><br><br>
<span class=text><?php echo xlt('X12 only ICN resubmission No.');?> </span><input type=entry size=35 name="icn_resubmission_number" value="<?php echo attr($obj{"icn_resubmission_number"});?>" ><br><br>

<table>
<tr>
<td valign=top>
<span class=text><?php echo xlt('Additional Notes'); ?>: </span><br><textarea cols=40 rows=8 wrap=virtual name="comments" ><?php echo text($obj{"comments"});?></textarea><br>
</td>
</table>
<br>
</tr>

 <div>
<!-- Save/Cancel buttons -->
<input type="button" class="save" value="<?php echo xla('Save'); ?>"> &nbsp &nbsp &nbsp &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save Changes'); ?>"> &nbsp;
</div>
</form>
<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/encounter_top.php";?>'; });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});
</script>
</body>
</html>