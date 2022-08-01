<?php

/*
 * This program creates the misc_billing_form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2017-2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/user.inc");
require_once("$srcdir/pid.inc");
require_once("$srcdir/encounter.inc");

use OpenEMR\Billing\MiscBillingOptions;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

if (isset($_REQUEST['isBilling'])) {
    $pid = $_REQUEST['pid'];
    SessionUtil::setSession('billpid', $pid);

    if ($pid != $_SESSION["pid"]) {
        setpid($pid);
    }

    $encounter = $_REQUEST['enc'];
    SessionUtil::setSession('billencounter', $encounter);

    if ($encounter != $_SESSION["encounter"]) {
        setencounter($encounter);
    }
} elseif (isset($_SESSION['billencounter'])) {
    SessionUtil::unsetSession(['billpid', 'billencounter']);
}

$MBO = new OpenEMR\Billing\MiscBillingOptions();

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}
//only one misc billing form per encounter so grab if exists
$formid = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
if (empty($formid)) {
    $mboquery = sqlquery("SELECT `fmbo`.`id` FROM `form_misc_billing_options` AS `fmbo`
                          INNER JOIN `forms` ON (`fmbo`.`id` = `forms`.`form_id`) WHERE
                          `forms`.`deleted` = 0 AND `forms`.`formdir` = 'misc_billing_options' AND
                          `forms`.`encounter` = ? ORDER BY `fmbo`.`id` DESC", array($encounter));
    if (!empty($mboquery['id'])) {
        $formid = (int) $mboquery['id'];
    }
}
$obj = $formid ? formFetch("form_misc_billing_options", $formid) : array();
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'opener']); ?>
    <title><?php echo xlt('Miscellaneous Billing Options for HCFA-1500'); ?></title>
    <?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Miscellaneous Billing Options for HCFA-1500'),
        'include_patient_name' => true,// use only in appropriate pages
        'expandable' => false,
        'expandable_files' => array(""),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => true,
        'help_file_name' => "cms_1500_help.php"
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
            <form method=post <?php echo "name='my_form' " . "action='$rootdir/forms/misc_billing_options/save.php?id=" . attr_url($formid) . "'\n"; ?>>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <fieldset>
                    <legend><?php echo xlt('Select Options for Current Encounter') ?></legend>
                    <div class="container">
                        <span class="text"><?php echo xlt('Checked box = yes, empty = no'); ?><br /><br /></span>
                        <div class="form-group">
                            <label><?php echo xlt('Box 10 A. Employment related'); ?>:
                                <input type="checkbox" name="employment_related" id="box10a" value="1"
                                    <?php
                                    if (!empty($obj['employment_related']) && ($obj['employment_related'] == "1")) {
                                        echo "checked";
                                    } ?> />
                            </label>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 10 B. Auto Accident'); ?>:</label>
                                <input type="checkbox" name="auto_accident" id="box10b" value="1"
                                    <?php
                                    if (!empty($obj['auto_accident']) && ($obj['auto_accident'] == "1")) {
                                        echo "checked";
                                    } ?> />
                            </div>
                            <div class="col-md">
                                <label><?php echo xlt('State'); ?>:</label>
                                <input type="text" class="form-control" name="accident_state" id="box10bstate" size="1"
                                    value="<?php echo attr($obj["accident_state"] ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo xlt('Box 10 C. Other Accident'); ?>:</label>
                            <input type="checkbox" name="other_accident" id="box10c" value="1"
                                <?php
                                if (!empty($obj['other_accident']) && ($obj['other_accident'] == "1")) {
                                    echo "checked";
                                } ?> />
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 10 D. EPSDT Referral Code'); ?></label>
                                <input type="text" class="form-control" name="medicaid_referral_code" id="box10d"
                                    value="<?php echo attr($obj["medicaid_referral_code"] ?? ''); ?>" />
                            </div>
                            <div class="col-md">
                                <label><?php echo xlt('EPSDT'); ?> :</label>
                                <input type="checkbox" name="epsdt_flag" id="box10depsdt" value="1"
                                    <?php
                                    if (!empty($obj['epsdt_flag']) && ($obj['epsdt_flag'] == "1")) {
                                        echo "checked";
                                    } ?> />
                            </div>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 14. Onset Date:'); ?></label>
                            </div>
                            <div class="col-md">
                                <?php $onset_date = $obj["onset_date"] ?? null; ?>
                                <input type="text" size="10" class='datepicker form-control' name='onset_date' id='onset_date'
                                    value='<?php echo attr($onset_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                            <div class="col-md">
                                <?php echo $MBO->generateDateQualifierSelect("box_14_date_qual", $MBO->box_14_qualifier_options, $obj); ?>
                            </div>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 15. Other Date:'); ?></label>
                            </div>
                            <div class="col-md">
                                <?php $date_initial_treatment = $obj["date_initial_treatment"] ?? null; ?>
                                <input type="text" size="10" class='datepicker form-control' name='date_initial_treatment'
                                    id='date_initial_treatment' value='<?php echo attr($date_initial_treatment); ?>'
                                    title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                            <div class="col-md">
                                <?php $MBO->generateDateQualifierSelect("box_15_date_qual", $MBO->box_15_qualifier_options, $obj); ?>
                            </div>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label for='off_work_from'>
                                    <?php echo xlt('Box 16. Date unable to work from'); ?>:
                                </label>
                            </div>
                            <div class="col-md">
                                <?php $off_work_from = $obj["off_work_from"] ?? null; ?>
                                <input type="text" class='datepicker form-control' name='off_work_from'
                                id='off_work_from' value='<?php echo attr($off_work_from); ?>'
                                title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                            <div class="col-md">
                                <label for='off_work_to'>
                                    <?php echo xlt('Box 16. Date unable to work to'); ?>:
                                </label>
                            </div>
                            <div class="col-md">
                                <?php $off_work_to = $obj["off_work_to"] ?? null; ?>
                                <input type="text" class='datepicker form-control' name='off_work_to' id='off_work_to'
                                    value='<?php echo attr($off_work_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-inline"><?php echo xlt('Box 17. Provider') ?>:</label>
                            <?php
                            if (!empty($obj["provider_id"])) {
                                $MBO->genReferringProviderSelect('provider_id', '-- ' . xl("Please Select") . ' --', $obj["provider_id"]);
                            } else { // defalut to the patient's ref_prov
                                $MBO->genReferringProviderSelect('provider_id', '-- ' . xl("Please Select") . ' --', getPatientData($pid, "ref_providerID")['ref_providerID']);
                            } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-inline"><?php echo xlt('Box 17. Provider Qualifier'); ?>:</label>
                            <?php echo generate_select_list('provider_qualifier_code', 'provider_qualifier_code', ($obj["provider_qualifier_code"] ?? null), 'Provider Qualifier Code'); ?>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label for='hospitalization_date_from'>
                                    <?php echo xlt('Box 18. Hospitalization date from'); ?>:
                                </label>
                            </div>
                            <div class="col-md">
                                <?php $hospitalization_date_from = $obj["hospitalization_date_from"] ?? null; ?>
                                <input type="text" class='datepicker form-control' name='hospitalization_date_from'
                                    id='hospitalization_date_from' value='<?php echo attr($hospitalization_date_from); ?>'
                                    title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                            <div class="col-md">
                                <label for='off_work_to'>
                                    <?php echo xlt('Box 18. Hospitalization date to'); ?>:
                                </label>
                            </div>
                            <div class="col-md">
                                <?php $hospitalization_date_to = $obj["hospitalization_date_to"] ?? null; ?>
                                <input type="text" class='datepicker form-control' name='hospitalization_date_to'
                                    id='hospitalization_date_to' value='<?php echo attr($hospitalization_date_to); ?>'
                                    title='<?php echo xla('yyyy-mm-dd'); ?>' />
                            </div>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 20. Is Outside Lab used?'); ?>:</label>
                                <input type="checkbox" name="outside_lab" id="outside_lab" value="1"
                                    <?php
                                    if (!empty($obj['outside_lab']) && ($obj['outside_lab'] == "1")) {
                                        echo "checked";
                                    } ?> />
                            </div>
                            <div class="col-md">
                                <label><?php echo xlt('Amount Charged'); ?>:</label>
                                <input type="text" size="7" class="form-control" name="lab_amount" id="lab_amount"
                                    value="<?php echo attr($obj["lab_amount"] ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <label><?php echo xlt('Box 22. Medicaid Resubmission Code (ICD-10)'); ?>:</label>
                                <input type="text" class="form-control" name="medicaid_resubmission_code" id="medicaid_resubmission_code"
                                    value="<?php echo attr($obj["medicaid_resubmission_code"] ?? ''); ?>" />
                            </div>
                            <div class="col-md">
                                <label><?php echo xlt('Medicaid Original Reference No.'); ?>:</label>
                                <input type="text" class="form-control" name="medicaid_original_reference" id="medicaid_original_reference"
                                    value="<?php echo attr($obj["medicaid_original_reference"] ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label><?php echo xlt('Box 23. Prior Authorization No.'); ?>:</label>
                            <input type="text" class="form-control" name="prior_auth_number" id="prior_auth_number"
                                value="<?php echo attr($obj["prior_auth_number"] ?? ''); ?>" />
                        </div>
                        <div class="form-row mt-3">
                            <div class="col-md">
                                <input type="radio" class="btn-check" name="replacement_claim" id="replacement_claim" autocomplete="Off" value="1"
                                    <?php
                                    if (!empty($obj['replacement_claim']) && ($obj['replacement_claim'] == "1")) {
                                        echo "checked";
                                    } ?> />
                                <label class="btn btn-secondary" for="replacement_claim"><?php echo xlt('X12 only: Replacement Claim'); ?>:</label>
    
                                <input type="radio" class="btn-check" name="replacement_claim" id="void_claim" autocomplete="Off" value="2"
                                    <?php
                                    if (!empty($obj['replacement_claim']) && ($obj['replacement_claim'] == "2")) {
                                        echo "checked";
                                    } ?> />    
                                <label class="btn btn-secondary" for="void_claim"><?php echo xlt('Void Claim'); ?>:</label>    

                                <input type="radio" class="btn-check" name="replacement_claim" id="new_claim" autocomplete="Off" value="0"
                                    <?php
                                    if (empty($obj['replacement_claim'])) {
                                        echo "checked";
                                    } ?> />    
                                <label class="btn btn-secondary" for="new_claim"><?php echo xlt('New Claim'); ?>:</label>    
                            </div>
                            <div class="col-md">
                                <label><?php echo xlt('X12 only ICN resubmission No.'); ?>:</label>
                                <input type="text" class="form-control" name="icn_resubmission_number" id="icn_resubmission_number"
                                    value="<?php echo attr($obj["icn_resubmission_number"] ?? ''); ?>" />
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend><?php echo xlt('Additional Notes'); ?></legend>
                    <div class="container">
                        <div class="form-group">
                            <textarea name="comments" id="comments" class="form-control" cols="80"
                                rows="3"><?php echo text($obj["comments"] ?? ''); ?></textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-sm-12 position-override">
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-primary btn-save save"><?php echo xlt('Save'); ?></button>
                            <button type="button" class="dontsave btn btn-secondary btn-cancel"><?php echo xlt('Cancel'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div><!--End of container div-->
<?php $oemr_ui->oeBelowContainerDiv();?>
<script>
    // jQuery stuff to make the page a little easier to use
    $(function () {
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

    $(function () {
        $('select').addClass("form-control");
    });
</script>
</body>
</html>
