<?php
/*
 * This program creates the misc_billing_form
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2017 Stephen Waite <stephen.waite@cmsvt.com>
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

//only one misc billing form so grab if exists
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
if (empty($formid)) {
    $mboquery = sqlquery("SELECT `fmbo`.`id` FROM `form_misc_billing_options` AS `fmbo`
                          INNER JOIN `forms` ON (`fmbo`.`id` = `forms`.`form_id`) WHERE
                          `forms`.`deleted` = 0 AND `forms`.`form_name` = 'Misc Billing Options' AND
                          `forms`.`encounter` = ? ORDER BY `fmbo`.`id` DESC", array($encounter));
    if (!empty($mboquery['id'])) {
        $formid = 0 + $mboquery['id'];
    }
}

$obj = $formid ? formFetch("form_misc_billing_options", $formid) : array();

?>
<html>
  <head>
    <title><?php echo xlt('Miscellaneous Billing Options for HCFA-1500'); ?></title>
    <?php html_header_show(); ?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js">
    </script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>">
    </script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js">
    </script>
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
            <?php // can add any additional javascript settings to datetimepicker here;
                // need to prepend first setting with a comma ?>
        });
      });
    </script>
  </head>
  <body class="body_top">
    <form method="post" <?php echo "name='my_form' " . "action='$rootdir/forms/misc_billing_options/save.php?id=" . attr($formid) . "'>\n";?>
      <h4><?php echo xlt('Miscellaneous Billing Options for HCFA-1500'); ?></h4>
      <label for="box10a"><?php echo xlt('Box 10. a. Employment related:'); ?></label>
      <input type="checkbox" name="employment_related" id="box10a" value="1" <?php if ($obj['employment_related'] == "1") {
            echo "checked";
} ?> ><br><br>
      <label for="box10b"><?php echo xlt('Box 10. b. Auto Accident:'); ?></label>
      <input type="checkbox" name="auto_accident" id="box10b" value="1" <?php if ($obj['auto_accident'] == "1") {
            echo "checked";
} ?> >
      <label for="box10bstate"><?php echo xlt('State:'); ?></label>
      <input type="entry" name="accident_state" id="box10bstate" size="1" value="<?php echo attr($obj{"accident_state"}); ?>" /><br><br>
      <label for="box10c"><?php echo xlt('Box 10. c. Other Accident:'); ?></label>
      <input type="checkbox" name="other_accident" id="box10c" value="1" <?php if ($obj['other_accident'] == "1") {
            echo "checked" ;
} ?> ><br><br>
      <label for="box10d"><?php echo xlt('Box 10. d. EPSDT Referral Code:'); ?></label>
      <input type="entry" size="2" name="medicaid_referral_code" id="box10d" value="<?php echo attr($obj{"medicaid_referral_code"}); ?>" >
      <label for="box10depsdt"><?php echo xlt('EPSDT:'); ?></label>
      <input type="checkbox" name="epsdt_flag" id="box10depsdt" value="1" <?php if ($obj['epsdt_flag'] == "1") {
            echo "checked";
} ?> ><br><br>
      <label for="onset_date"><?php echo xlt('Box 14. Onset Date:');
        $onset_date = $obj{"onset_date"}; ?></label>
      <input type="text" size="10" class='datepicker' name='onset_date' id='onset_date' value='<?php echo attr($onset_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' >
      <label for="box_14_date_qual_box"><?php echo generateDateQualifierSelect("box_14_date_qual", $box_14_qualifier_options, $obj); ?><br><br>
      <label for="date_initial_treament"><?php echo xlt('Box 15. Other Date:');
        $date_initial_treatment = $obj{"date_initial_treatment"}; ?></label>
      <input type="text" size="10" class='datepicker' name='date_initial_treatment' id='date_initial_treatment' value='<?php echo attr($date_initial_treatment); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' >
      <label for="box_15_date_qual"><?php generateDateQualifierSelect("box_15_date_qual", $box_15_qualifier_options, $obj); ?><br><br>
      <label for="off_work_from"><?php echo xlt('Box 16. Date unable to work from:');
        $off_work_from = $obj{"off_work_from"}; ?></label>
      <input type="text" size="10" class='datepicker' name='off_work_from' id='off_work_from' value='<?php echo attr($off_work_from); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' >
      <label for="off_work_to"><?php echo xlt('Box 16. Date unable to work to:');
        $off_work_to = $obj{"off_work_to"}; ?></label>
      <input type="text" size="10" class='datepicker' name='off_work_to' id='off_work_to' value='<?php echo attr($off_work_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' ><br><br>
      <label for="provider_id"><?php echo xlt('Box 17. Provider:'); ?></label>
        <?php  genProviderSelect('provider_id', '-- ' . xl("Please Select") . ' --', $obj{"provider_id"}); ?>
      <label for="provider_qualifier_code"><?php echo xlt('Box 17. Provider Qualifier:'); ?></label>
        <?php  echo generate_select_list('provider_qualifier_code', 'provider_qualifier_code', $obj{"provider_qualifier_code"}, 'Provider Qualifier Code'); ?><br><br>
      <label for="hospitalization_date_from"><?php echo xlt('Box 18. Hospitalization date from:');
        $hospitalization_date_from = $obj{"hospitalization_date_from"}; ?></label>
      <input type="text" size="10" class='datepicker' name='hospitalization_date_from' id='hospitalization_date_from' value='<?php echo attr($hospitalization_date_from); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' >
      <label for="hospitalization_date_to"><?php echo xlt('Box 18. Hospitalization date to:');
        $hospitalization_date_to = $obj{"hospitalization_date_to"}; ?></label>
      <input type="text" size="10" class='datepicker' name='hospitalization_date_to' id='hospitalization_date_to' value='<?php echo attr($hospitalization_date_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' ><br><br>
      <label for="outside_lab"><?php echo xlt('Box 20. Is Outside Lab used?'); ?></label>
      <input type="checkbox" name="outside_lab" id="outside_labe" value="1" <?php if ($obj['outside_lab'] == "1") {
            echo "checked";
}?> >
      <label for="lab_amount"><?php echo xlt('Amount Charges:'); ?></label>
      <input type="entry" size="7" align='right' name="lab_amount" id="lab_amount" value="<?php echo attr($obj{"lab_amount"});?>" ><br><br>
      <label for="medicaid_resubmission_code"><?php echo xlt('Box 22. Medicaid Resubmission Code (ICD-9) ');?></label>
      <input type="entry" size="9" name="medicaid_resubmission_code" id="medicaid_resubmission_code" value="<?php echo attr($obj{"medicaid_resubmission_code"}); ?>" >
      <label for="medicaid_original_reference"><?php echo xlt(' Medicaid Original Reference No. '); ?></label>
      <input type="entry" size="15" name="medicaid_original_reference" id="medicaid_original_reference" value="<?php echo attr($obj{"medicaid_original_reference"}); ?>" ><br><br>
      <label for="prior_auth_number"><?php echo xlt('Box 23. Prior Authorization No. ');?></label>
      <input type="entry" size="15" name="prior_auth_number" id="prior_auth_number" value="<?php echo attr($obj{"prior_auth_number"}); ?>" ><br><br>
      <label for="replacement_claim"><?php echo xlt('X12 only replacement claim:'); ?></label>
      <input type="checkbox" name="replacement_claim" id="replacement_claim" value="1" <?php if ($obj['replacement_claim'] == "1") {
            echo "checked";
} ?> ><br><br>
      <label for="icn_resubmission_number"><?php echo xlt('X12 only ICN resubmission No.'); ?></label>
      <input type="entry" size="35" name="icn_resubmission_number" id="icn_resubmission_number" value="<?php echo attr($obj{"icn_resubmission_number"}); ?>" ><br><br>
      <label for="addl_notes"><?php echo xlt('Additional Notes:'); ?>
      <textarea cols="40" rows="8" wrap="virtual" name="comments" id="addl_notes"><?php echo text($obj{"comments"}); ?></textarea ><br><br>
      <div>
      <!-- Save/Cancel buttons -->
      <input type="button" class="save" value="<?php echo xla('Save'); ?>" >
      <input type="button" class="dontsave" value="<?php echo xla('Don\'t Save Changes'); ?>" >
      </div>
    </form>
  </body>
</html>
