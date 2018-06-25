<?php
/*
 * This program creates the misc_billing_form
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2017 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("date_qualifier_options.php");

use OpenEMR\Core\Header;

if (isset($_REQUEST['isBilling'])) {
    $pid = $_SESSION['billpid'] = attr($_REQUEST['pid']);
    $encounter = $_SESSION['billencounter'] = attr($_REQUEST['enc']);
} elseif (isset($_SESSION['billencounter'])) {
    unset($_SESSION['billpid']);
    unset($_SESSION['billencounter']);
}

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}
//only one misc billing form per encounter so grab if exists
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
if (empty($formid)) {
    $mboquery = sqlquery("SELECT `fmbo`.`id` FROM `form_misc_billing_options` AS `fmbo`
                          INNER JOIN `forms` ON (`fmbo`.`id` = `forms`.`form_id`) WHERE
                          `forms`.`deleted` = 0 AND `forms`.`formdir` = 'misc_billing_options' AND
                          `forms`.`encounter` = ? ORDER BY `fmbo`.`id` DESC", array($encounter));
    if (!empty($mboquery['id'])) {
        $formid = 0 + $mboquery['id'];
    }
}
$obj = $formid ? formFetch("form_misc_billing_options", $formid) : array();
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt('Miscellaneous Billing Options for HCFA-1500'); ?></title>
    <style>
        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left !Important;
            }
    </style>
    <?php
    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 0) {
        $help_icon = '';
    }

    ?>
</head>
<body class="body_top">
<div class="container">
    <div class="row">

        <div class="page-header clearfix">
            <h2 id="header_title" class="clearfix"><span
                    id='header_text'><?php echo xlt('Misc Billing Options for HCFA-1500'); ?></span><?php echo $help_icon; ?>
            </h2>
        </div>

    </div>
    <div class="row">
        <form
            method=post <?php echo "name='my_form' " . "action='$rootdir/forms/misc_billing_options/save.php?id=" . attr($formid) . "'\n"; ?>>
            <fieldset>
                <legend><?php echo xlt('Select Options for Current Encounter') ?></legend>
                <div class='col-sm-11 col-offset-sm-1'>
                    <span class="text"><?php echo xlt('Checked box = yes, empty = no'); ?><br><br></span>
                    <div class="form-group">
                        <label><?php echo xlt('Box 10 A. Employment related'); ?>:
                            <input type="checkbox" name="employment_related" id="box10a" value="1"
                                <?php
                                if ($obj['employment_related'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 10 B. Auto Accident'); ?>:
                            <input type="checkbox" name="auto_accident" id="box10b" value="1"
                                <?php
                                if ($obj['auto_accident'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                        <label><?php echo xlt('State'); ?>:
                            <input type="text" name="accident_state" id="box10bstate" size="1"
                                   value="<?php echo attr($obj{"accident_state"}); ?>">
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 10 C. Other Accident'); ?>:
                            <input type="checkbox" name="other_accident" id="box10c" value="1"
                                <?php
                                if ($obj['other_accident'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 10 D. EPSDT Referral Code'); ?>
                            <input type="text" name="medicaid_referral_code" id="box10d"
                                   value="<?php echo attr($obj{"medicaid_referral_code"}); ?>">
                        </label>
                        <label><?php echo xlt('EPSDT'); ?> :
                            <input type="checkbox" name="epsdt_flag" id="box10depsdt" value="1"
                                <?php
                                if ($obj['epsdt_flag'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                    </div>
                    <div class="form-group clearfix">
                        <label><?php echo xlt('Box 14. Onset Date:'); ?>
                            <?php $onset_date = $obj{"onset_date"}; ?>
                            <input type="text" size="10" class='datepicker' name='onset_date' id='onset_date'
                                   value='<?php echo attr($onset_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
                        </label>
                        <label>
                            <?php echo generateDateQualifierSelect("box_14_date_qual", $box_14_qualifier_options, $obj); ?>
                        </label>
                    </div>
                    <div class="form-group clearfix">
                        <label><?php echo xlt('Box 15. Other Date:'); ?>
                            <?php $date_initial_treatment = $obj{"date_initial_treatment"}; ?>
                            <input type="text" size="10" class='datepicker' name='date_initial_treatment'
                                   id='date_initial_treatment' value='<?php echo attr($date_initial_treatment); ?>'
                                   title='<?php echo xla('yyyy-mm-dd'); ?>'>
                        </label>
                        <label>
                            <?php generateDateQualifierSelect("box_15_date_qual", $box_15_qualifier_options, $obj); ?>
                        </label>
                    </div>
                    <div class="form-group clearfix">
                        <label for='off_work_from'
                               class="col-sm-3 form-inline"><?php echo xlt('Box 16. Date unable to work from'); ?>
                            :</label>
                        <?php $off_work_from = $obj{"off_work_from"}; ?>
                        <input type="text" class='datepicker form-inline col-sm-2' name='off_work_from'
                               id='off_work_from' value='<?php echo attr($off_work_from); ?>'
                               title='<?php echo xla('yyyy-mm-dd'); ?>'>
                        <label for='off_work_to'
                               class="col-sm-3 form-inline"><?php echo xlt('Box 16. Date unable to work to'); ?>
                            :</label>
                        <?php $off_work_to = $obj{"off_work_to"}; ?>
                        <input type="text" class='datepicker col-sm-2' name='off_work_to' id='off_work_to'
                               value='<?php echo attr($off_work_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
                    </div>
                    <div class="form-group">
                        <label class="form-inline"><?php echo xlt('Box 17. Provider') ?>:
                            <?php genProviderSelect('provider_id', '-- ' . xl("Please Select") . ' --', $obj{"provider_id"}); ?>
                        </label>
                        <label class="form-inline"><?php echo xlt('Box 17. Provider Qualifier'); ?>:
                            <?php echo generate_select_list('provider_qualifier_code', 'provider_qualifier_code', $obj{"provider_qualifier_code"}, 'Provider Qualifier Code'); ?>
                        </label>
                    </div>
                    <div class="form-group clearfix">
                        <label for='hospitalization_date_from'
                               class="col-sm-3 form-inline"><?php echo xlt('Box 18. Hospitalization date from'); ?>
                            :</label>
                        <?php $hospitalization_date_from = $obj{"hospitalization_date_from"}; ?>
                        <input type="text" class='datepicker col-sm-2 ' name='hospitalization_date_from'
                               id='hospitalization_date_from' value='<?php echo attr($hospitalization_date_from); ?>'
                               title='<?php echo xla('yyyy-mm-dd'); ?>'>
                        <label for='off_work_to'
                               class="col-sm-3 form-inline"><?php echo xlt('Box 18. Hospitalization date to'); ?>
                            :</label>
                        <?php $hospitalization_date_to = $obj{"hospitalization_date_to"}; ?>
                        <input type="text" class='datepicker col-sm-2' name='hospitalization_date_to'
                               id='hospitalization_date_to' value='<?php echo attr($hospitalization_date_to); ?>'
                               title='<?php echo xla('yyyy-mm-dd'); ?>'>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 20. Is Outside Lab used?'); ?>:
                            <input type="checkbox" name="outside_lab" id="outside_lab" value="1"
                                <?php
                                if ($obj['outside_lab'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                        <label><?php echo xlt('Amount Charged'); ?>:
                            <input type="text" size="7" align='right' name="lab_amount" id="lab_amount"
                                   value="<?php echo attr($obj{"lab_amount"}); ?>">
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 22. Medicaid Resubmission Code (ICD-10)'); ?>:
                            <input type="text" name="medicaid_resubmission_code" id="medicaid_resubmission_code"
                                   value="<?php echo attr($obj{"medicaid_resubmission_code"}); ?>">
                        </label>
                        <label><?php echo xlt(' Medicaid Original Reference No.'); ?>:
                            <input type="text" name="medicaid_original_reference" id="medicaid_original_reference"
                                   value="<?php echo attr($obj{"medicaid_original_reference"}); ?>">
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('Box 23. Prior Authorization No.'); ?>:
                            <input type="text" name="prior_auth_number" id="prior_auth_number"
                                   value="<?php echo attr($obj{"prior_auth_number"}); ?>">
                        </label>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt('X12 only: Replacement Claim'); ?>:
                            <input type="checkbox" name="replacement_claim" id="replacement_claim" value="1"
                                <?php
                                if ($obj['replacement_claim'] == "1") {
                                    echo "checked";
                                } ?>>
                        </label>
                        <label><?php echo xlt('X12 only ICN resubmission No.'); ?>:
                            <input type="text" name="icn_resubmission_number" id="icn_resubmission_number"
                                   value="<?php echo attr($obj{"icn_resubmission_number"}); ?>">
                        </label>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Additional Notes'); ?></legend>
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-1">
                        <textarea name="comments" id="comments" class="form-control" cols="80"
                                  rows="3"><?php echo text($obj{"comments"}); ?></textarea>
                    </div>
                </div>
            </fieldset>
            <div class="form-group clearfix">
                <div class="col-sm-offset-1 col-sm-12 position-override">
                    <div class="btn-group btn-pinch" role="group">
                        <!-- Save/Cancel buttons -->
                        <button type="submit" class="btn btn-default btn-save save"> <?php echo xla('Save'); ?></button>
                        <button type="button"
                                class="dontsave btn btn-link btn-cancel btn-separate-left"><?php echo xla('Cancel'); ?></button>
                    </div>
                </div>
            </div>
        </form>
        <br>
        <br>
    </div>
</div><!--End of conrainer div-->
<br>
<?php
//home of the help modal ;)
//$GLOBALS['enable_help'] = 0; // Please comment out line if you want help modal to function on this page
if ($GLOBALS['enable_help'] == 1) {
    echo "<script>var helpFile = 'cms_1500_help.php'</script>";
    //help_modal.php lives in interface, set path accordingly
    require "../../help_modal.php";
}
?>

<script language="javascript">
    // jQuery stuff to make the page a little easier to use
    $(document).ready(function () {
        $(".dontsave").click(function () {
            <?php if (isset($_REQUEST['isBilling'])) { ?>
                dlgclose();
            <?php } else { ?>
                parent.closeTab(window.name, false);
            <?php } ?>
        });

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('select').addClass("form-control");
    });
</script>
</body>
</html>
