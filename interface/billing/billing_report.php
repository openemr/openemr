<?php
/**
* Billing Report Program
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    Terry Hill <terry@lilysystems.com>
* @author    Brady Miller <brady.g.miller@gmail.com>
* @author    Jerry Padgett <sjpadgett@gmail.com>
* @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
* @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
* @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once "../globals.php";
require_once "../../library/acl.inc";
require_once "../../custom/code_types.inc.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/../interface/reports/report.inc.php"; // Criteria Section common php page
require_once "$srcdir/billrep.inc";
require_once "$srcdir/options.inc.php";

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$EXPORT_INC = "$webserver_root/custom/BillingExport.php";
// echo $GLOBALS['daysheet_provider_totals'];

$daysheet = false;
$daysheet_total = false;
$provider_run = false;

if ($GLOBALS['use_custom_daysheet'] != 0) {
    $daysheet = true;
    if ($GLOBALS['daysheet_provider_totals'] == 1) {
        $daysheet_total = true;
        $provider_run = false;
    }
    if ($GLOBALS['daysheet_provider_totals'] == 0) {
        $daysheet_total = false;
        $provider_run = true;
    }
}

$alertmsg = '';

if (isset($_POST['mode'])) {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }

    if ($_POST['mode'] == 'export') {
        $sql = ReturnOFXSql();
        $db = get_db();
        $results = $db->Execute($sql);
        $billings = array();
        if ($results->RecordCount() == 0) {
            echo "<fieldset id='error_info' style='border:1px solid #ff5d5a !Important; background-color: #ff5d5a !Important; color: #fff ! Important; font-weight: bold; font-family:sans-serif; border-radius:5px; padding:20px 5px !Important;'>";
            echo xlt("No Bills Found to Include in OFX Export") . "<br>";
            echo "</fieldset>";
        } else {
            while (!$results->EOF) {
                $billings[] = $results->fields;
                $results->MoveNext();
            }
            $ofx = new OFX($billings);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Disposition: attachment; filename=openemr_ofx.ofx");
            header("Content-Type: text/xml");
            echo $ofx->get_OFX();
            exit();
        }
    }
}

// global variables:
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
$code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'all';
$unbilled = isset($_POST['unbilled']) ? $_POST['unbilled'] : 'on';
$my_authorized = isset($_POST["authorized"]) ? $_POST["authorized"] : '';

// This tells us if only encounters that appear to be missing a "25" modifier
// are to be reported.
$missing_mods_only = (isset($_POST['missing_mods_only']) && !empty($_POST['missing_mods_only']));

$left_margin = isset($_POST["left_margin"]) ? $_POST["left_margin"] : $GLOBALS['cms_left_margin_default'];
$top_margin = isset($_POST["top_margin"]) ? $_POST["top_margin"] : $GLOBALS['cms_top_margin_default'];
if ($ub04_support) {
    $left_ubmargin = isset($_POST["left_ubmargin"]) ? $_POST["left_ubmargin"] : $GLOBALS['left_ubmargin_default'];
    $top_ubmargin = isset($_POST["top_ubmargin"]) ? $_POST["top_ubmargin"] : $GLOBALS['top_ubmargin_default'];
}
$ofrom_date = $from_date;
$oto_date = $to_date;
$ocode_type = $code_type;
$ounbilled = $unbilled;
$oauthorized = $my_authorized;
$x = new X12Partner();
$partners = $x->_utility_array($x->x12_partner_factory());
?>
<!DOCTYPE html >
<html>
<head>

<?php Header::setupHeader(['datetime-picker', 'common']); ?>
<style>
    /*.subbtn { margin-top:3px; margin-bottom:3px; margin-left:2px; margin-right:2px }*/
    .btn-group-pinch > .btn:nth-last-child(4):not(.dropdown-toggle) {
        border-top-right-radius: 3px !Important;
        border-bottom-right-radius: 3px !Important;
    }
</style>
<script>
    var partners = <?php echo json_encode($partners); ?>;
    function onNewPayer(oEvent) {
        let p = oEvent.target.options[event.target.selectedIndex].dataset.partner;
        let partnerSelect = oEvent.target.options[event.target.selectedIndex].dataset.partner;
        partnerSelect = partnerSelect ? partnerSelect : -1;
        document.getElementById("partners").value = partnerSelect;
    }

    function select_all() {
        for ($i = 0; $i < document.update_form.length; $i++) {
            $name = document.update_form[$i].name;
            if ($name.substring(0, 7) == "claims[" && $name.substring($name.length -
                    6) == "[bill]") {
                document.update_form[$i].checked = true;
            }
        }
        set_button_states();
    }

    function set_button_states() {
        var f = document.update_form;
        var count0 = 0; // selected and not billed or queued
        var count1 = 0; // selected and queued
        var count2 = 0; // selected and billed
        for ($i = 0; $i < f.length; ++$i) {
            $name = f[$i].name;
            if ($name.substring(0, 7) == "claims[" && $name.substring($name.length -
                    6) == "[bill]" && f[$i].checked == true) {
                if (f[$i].value == '0') ++count0;
                else if (f[$i].value == '1' || f[$i].value == '5') ++count1;
                else ++count2;
            }
        }
        var can_generate = (count0 > 0 || count1 > 0 || count2 > 0);
        var can_mark = (count1 > 0 || count0 > 0 || count2 > 0);
        var can_bill = (count0 == 0 && count1 == 0 && count2 > 0);
        <?php if (file_exists($EXPORT_INC)) { ?>
        f.bn_external.disabled = !can_generate;
        <?php } else { ?>
        f.bn_x12_support.disabled = !can_generate;
        <?php if ($GLOBALS['support_encounter_claims']) { ?>
        f.bn_x12_encounter.disabled = !can_generate;
        <?php } ?>
        f.bn_process_hcfa_support.disabled = !can_generate;
        <?php if ($GLOBALS['preprinted_cms_1500']) { ?>
        f.bn_process_hcfa_form.disabled = !can_generate;
        <?php } ?>
        <?php if ($GLOBALS['ub04_support']) { ?>
        f.bn_process_ub04_support.disabled = !can_generate;
        <?php } ?>
        f.bn_hcfa_txt_file.disabled = !can_generate;
        f.bn_reopen.disabled = !can_bill;
        <?php } ?>
        f.bn_mark.disabled = !can_mark;
    }

    // Process a click to go to an encounter.
    function toencounter(pid, pubpid, pname, enc, datestr, dobstr) {
        top.restoreSession();
        encurl = 'patient_file/encounter/encounter_top.php?set_encounter=' + encodeURIComponent(enc) +
            '&pid=' + encodeURIComponent(pid);
        parent.left_nav.setPatient(pname, pid, pubpid, '', dobstr);
        <?php if ($GLOBALS['new_tabs_layout']) { ?>
        parent.left_nav.setEncounter(datestr, enc, 'enc');
        parent.left_nav.loadFrame('enc2', 'enc', encurl);
        <?php } else { ?>
        var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
        parent.left_nav.setEncounter(datestr, enc, othername);
        parent.frames[othername].location.href = '../' + encurl;
        <?php } ?>
    }

    // Process a click to go to an patient.
    function topatient(pid, pubpid, pname, enc, datestr, dobstr) {
        top.restoreSession();
        paturl = 'patient_file/summary/demographics_full.php?pid=' + encodeURIComponent(pid);
        parent.left_nav.setPatient(pname, pid, pubpid, '', dobstr);
        <?php if ($GLOBALS['new_tabs_layout']) { ?>
        parent.left_nav.loadFrame('ens1', 'enc',
            'patient_file/history/encounters.php?pid=' + encodeURIComponent(pid));
        parent.left_nav.loadFrame('dem1', 'pat', paturl);
        <?php } else { ?>
        var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
        parent.frames[othername].location.href = '../' + paturl;
        <?php } ?>
    }

    function popMBO(pid, enc, mboid) {
        if (!window.focus) return true;
        if (!ProcessBeforeSubmitting()) return false;
        top.restoreSession();
        let qstring = "&pid=" + encodeURIComponent(pid) + "&enc=" + encodeURIComponent(enc) + "&id=" + encodeURIComponent(mboid);
        let href = "<?php echo $GLOBALS['web_root']?>/interface/patient_file/encounter/view_form.php?formname=misc_billing_options&isBilling=1" + qstring;
        dlgopen(href, 'mbopop', 'modal-lg', 750, false, '', {
            sizeHeight: 'full' // override min height auto size.
        });
        return true;
    }

    function popUB04(pid, enc) {
        if (!window.focus) return true;
        if (!ProcessBeforeSubmitting()) return false;
        top.restoreSession();
        let href = "<?php echo $GLOBALS['web_root']?>/interface/billing/ub04_form.php?pid=" + encodeURIComponent(pid) + "&enc=" + encodeURIComponent(enc);
        dlgopen(href, 'ub04pop', 1175, 750, false, '', {
            sizeHeight: 'full' // override min height auto size.
        });
        return true;
    }

    var EncounterDateArray = new Array;
    var CalendarCategoryArray = new Array;
    var EncounterIdArray = new Array;
    var EncounterNoteArray = new Array;

    function SubmitTheScreen() { //Action on Update List link
        if (!ProcessBeforeSubmitting()) return false;
        $("#update-tooltip").replaceWith("<i class='fa fa-refresh fa-spin fa-1x' style=\"color:red\"></i>");
        top.restoreSession();
        document.the_form.mode.value = 'change';
        document.the_form.target = '_self';
        document.the_form.action = 'billing_report.php';
        document.the_form.submit();
        return true;
    }

    function SubmitTheScreenPrint() { //Action on View Printable Report link
        if (!ProcessBeforeSubmitting()) return false;
        top.restoreSession();
        document.the_form.target = 'new';
        document.the_form.action = 'print_billing_report.php';
        document.the_form.submit();
        return true;
    }

    function SubmitTheEndDayPrint() { //Action on View End of Day Report link
        if (!ProcessBeforeSubmitting()) return false;
        top.restoreSession();
        document.the_form.target = 'new';
        <?php if ($GLOBALS['use_custom_daysheet'] == 1) { ?>
        document.the_form.action = 'print_daysheet_report_num1.php';
        <?php } ?>
        <?php if ($GLOBALS['use_custom_daysheet'] == 2) { ?>
        document.the_form.action = 'print_daysheet_report_num2.php';
        <?php } ?>
        <?php if ($GLOBALS['use_custom_daysheet'] == 3) { ?>
        document.the_form.action = 'print_daysheet_report_num3.php';
        <?php } ?>
        document.the_form.submit();
        return true;
    }

    function SubmitTheScreenExportOFX() { //Action on Export OFX link
        if (!ProcessBeforeSubmitting()) return false;
        top.restoreSession();
        document.the_form.mode.value = 'export';
        document.the_form.target = '_self';
        document.the_form.action = 'billing_report.php';
        document.the_form.submit();
        return true;
    }

    function TestExpandCollapse() { //Checks whether the Expand All, Collapse All labels need to be placed.If any result set is there these will be placed.
        var set = -1;
        for (i = 1; i <= document.getElementById("divnos").value; i++) {
            var ele = document.getElementById("divid_" + i);
            if (ele) {
                set = 1;
                break;
            }
        }
        if (set == -1) {
            if (document.getElementById("ExpandAll")) {
                document.getElementById("ExpandAll").innerHTML = '';
                document.getElementById("CollapseAll").innerHTML = '';
            }
        }
    }

    function expandcollapse(atr) {
        if (atr == "expand") { //Called in the Expand All, Collapse All links(All items will be expanded or collapsed)
            for (i = 1; i <= document.getElementById("divnos").value; i++) {
                var mydivid = "divid_" + i;
                var myspanid = "spanid_" + i;
                var ele = document.getElementById(mydivid);
                var text = document.getElementById(myspanid);
                if (ele) {
                    ele.style.display = "inline";
                    text.innerHTML =
                        "<?php echo xla('Collapse'); ?>";
                }
            }
        } else {
            for (i = 1; i <= document.getElementById("divnos").value; i++) {
                var mydivid = "divid_" + i;
                var myspanid = "spanid_" + i;
                var ele = document.getElementById(mydivid);
                var text = document.getElementById(myspanid);
                if (ele) {
                    ele.style.display = "none";
                    text.innerHTML =
                        <?php echo xlj('Expand'); ?>;
                }
            }
        }
    }

    function divtoggle(spanid, divid) { //Called in the Expand, Collapse links(This is for a single item)
        var ele = document.getElementById(divid);
        if (ele) {
            var text = document.getElementById(spanid);
            if (ele.style.display == "inline") {
                ele.style.display = "none";
                text.innerHTML =
                    <?php echo xlj('Expand'); ?>;
            } else {
                ele.style.display = "inline";
                text.innerHTML =
                    <?php echo xlj('Collapse'); ?>;
            }
        }
    }

    function MarkAsCleared(Type) {
        CheckBoxBillingCount = 0;
        for (var CheckBoxBillingIndex = 0; ; CheckBoxBillingIndex++) {
            CheckBoxBillingObject = document.getElementById('CheckBoxBilling' +
                CheckBoxBillingIndex);
            if (!CheckBoxBillingObject) break;
            if (CheckBoxBillingObject.checked) {
                ++CheckBoxBillingCount;
            }
        }
        if (Type == 1) {
            Message = <?php echo xlj('After saving your batch, click[View Log] to check for errors.'); ?>;
        }
        if (Type == 2) {
            Message = <?php echo xlj('After saving the PDF, click[View Log] to check for errors.'); ?>;
        }
        if (Type == 3) {
            Message = <?php echo xlj('After saving the TEXT file(s), click[View Log] to check for errors.'); ?>;
        }
        if (confirm(Message + "\n\n\n" + <?php echo xlj('Total'); ?> + ' ' +
                CheckBoxBillingCount + ' ' +
                <?php echo xlj('Selected'); ?> + "\n" +
                <?php echo xlj('Would You Like them to be Marked as Cleared.'); ?> + "\n" +
                <?php echo xlj('Click OK to Clear or Cancel to continue processing.'); ?>
            )) {
            document.getElementById('HiddenMarkAsCleared').value = 'yes';
        } else {
            document.getElementById('HiddenMarkAsCleared').value = '';
        }
    }
</script>
<?php require_once "$srcdir/../interface/reports/report.script.php"; ?>
<!-- Criteria Section common javascript page-->
<!-- =============Included for Insurance ajax criteria==== -->
<?php require_once "{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"; ?>
<style>
    #ajax_div_insurance {
        position: absolute;
        z-index: 10;
        background-color: #FBFDD0;
        border: 1px solid #ccc;
        padding: 10px;
    }
    button[type="submit"].subbtn-warning {
        background: #ec971f !important;
        color: black !important;
    }
    button[type="submit"].subbtn-warning:hover {
        background: #da8104 !important;
        color: #fff !important;
    }
    @media only screen and (max-width: 1024px) {
        [class*="col-"] {
            width: 100%;
            text-align: left !Important;
        }
    }
    .table {
        margin: auto;
    }
    @media (min-width: 992px) {
        .modal-lg {
            width: 1000px !Important;
        }
    }
    .table th, .table td {
        border-top: none !important;
    }
    .form-control {
        font-family: "FontAwesome"
    }
    ul > li {
        line-height: 1.86em;
    }
    a, a:visited, a:hover {
        text-decoration: none;
        color: #000000;
    }
</style>
<script>
    document.onclick = TakeActionOnHide;
</script>
<!-- =============Included for Insurance ajax criteria==== -->
<title><?php echo xlt('Billing Manager'); ?></title>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Billing Manager'),
    'include_patient_name' => false,// use only in appropriate pages
    'expandable' => true,
    'expandable_files' => array('billing_report_xpd'),//all file names need suffix _xpd
    'action' => "conceal",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class="body_top" onLoad="TestExpandCollapse()">
<div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
<div class="row">
    <div class="col-sm-12">
        <div class="page-header">
            <!--<h2>
                <?php echo xlt('Billing Manager') ?> <i id="exp_cont_icon" class="fa <?php echo attr($expand_icon_class);?> oe-superscript-small expand_contract"
                title="<?php echo attr($expand_title); ?>" aria-hidden="true"></i> <i id="show_hide" class="fa fa-eye-slash fa-2x small"
                title="<?php echo xla('Click to Hide'); ?>"></i>
            </h2>-->
            <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <form name='the_form' method='post' action='billing_report.php' onsubmit='return top.restoreSession()' style="display:inline">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
            <input type='hidden' name='mode' value='change'>
            <div class="col-xs-9">
                <!-- ============================================================================================================================================= -->
                <!-- Criteria section Starts -->
                <!-- ============================================================================================================================================= -->
                <?php
                // The following are the search criteria per page.All the following variable which ends with 'Master' need to be filled properly.
                // Each item is seperated by a comma(,).
                // $ThisPageSearchCriteriaDisplayMaster ==>It is the display on screen for the set of criteria.
                // $ThisPageSearchCriteriaKeyMaster ==>Corresponding database fields in the same order.
                // $ThisPageSearchCriteriaDataTypeMaster ==>Corresponding data type in the same order.
                $ThisPageSearchCriteriaDisplayRadioMaster = array();
                $ThisPageSearchCriteriaRadioKeyMaster = array();
                $ThisPageSearchCriteriaQueryDropDownMaster = array();
                $ThisPageSearchCriteriaQueryDropDownMasterDefault = array();
                $ThisPageSearchCriteriaQueryDropDownMasterDefaultKey = array();
                $ThisPageSearchCriteriaIncludeMaster = array();

                if ($daysheet) {
                    $ThisPageSearchCriteriaDisplayMaster = array(
                        xl("Date of Service"),
                        xl("Date of Entry"),
                        xl("Date of Billing"),
                        xl("Claim Type"),
                        xl("Patient Name"),
                        xl("Patient Id"),
                        xl("Insurance Company"),
                        xl("Encounter"),
                        xl("Whether Insured"),
                        xl("Charge Coded"),
                        xl("Billing Status"),
                        xl("Authorization Status"),
                        xl("Last Level Billed"),
                        xl("X12 Partner"),
                        xl("User")
                    );
                    $ThisPageSearchCriteriaKeyMaster = "form_encounter.date,billing.date,claims.process_time,claims.target,patient_data.fname," . "form_encounter.pid,claims.payer_id,form_encounter.encounter,insurance_data.provider,billing.id,billing.billed," . "billing.authorized,form_encounter.last_level_billed,billing.x12_partner_id,billing.user";
                    $ThisPageSearchCriteriaDataTypeMaster = "datetime,datetime,datetime,radio,text_like," . "text,include,text,radio,radio,radio," . "radio_like,radio,query_drop_down,text";
                } else {
                    $ThisPageSearchCriteriaDisplayMaster = array(
                        xl("Date of Service"),
                        xl("Date of Entry"),
                        xl("Date of Billing"),
                        xl("Claim Type"),
                        xl("Patient Name"),
                        xl("Patient Id"),
                        xl("Insurance Company"),
                        xl("Encounter"),
                        xl("Whether Insured"),
                        xl("Charge Coded"),
                        xl("Billing Status"),
                        xl("Authorization Status"),
                        xl("Last Level Billed"),
                        xl("X12 Partner")
                    );
                    $ThisPageSearchCriteriaKeyMaster = "form_encounter.date,billing.date,claims.process_time,claims.target,patient_data.fname," . "form_encounter.pid,claims.payer_id,form_encounter.encounter,insurance_data.provider,billing.id,billing.billed," . "billing.authorized,form_encounter.last_level_billed,billing.x12_partner_id";
                    $ThisPageSearchCriteriaDataTypeMaster = "datetime,datetime,datetime,radio,text_like," . "text,include,text,radio,radio,radio," . "radio_like,radio,query_drop_down";
                }
                // The below section is needed if there is any 'radio' or 'radio_like' type in the $ThisPageSearchCriteriaDataTypeMaster
                // $ThisPageSearchCriteriaDisplayRadioMaster,$ThisPageSearchCriteriaRadioKeyMaster ==>For each radio data type this pair comes.
                // The key value 'all' indicates that no action need to be taken based on this.For that the key must be 'all'.Display value can be any thing.
                $ThisPageSearchCriteriaDisplayRadioMaster[1] = array(
                    xl("All"),
                    xl("eClaims"),
                    xl("Paper")
                ); // Display Value
                $ThisPageSearchCriteriaRadioKeyMaster[1] = "all,standard,hcfa"; // Key
                $ThisPageSearchCriteriaDisplayRadioMaster[2] = array(
                    xl("All"),
                    xl("Insured"),
                    xl("Non-Insured")
                ); // Display Value
                $ThisPageSearchCriteriaRadioKeyMaster[2] = "all,1,0"; // Key
                $ThisPageSearchCriteriaDisplayRadioMaster[3] = array(
                    xl("All"),
                    xl("Coded"),
                    xl("Not Coded")
                ); // Display Value
                $ThisPageSearchCriteriaRadioKeyMaster[3] = "all,not null,null"; // Key
                $ThisPageSearchCriteriaDisplayRadioMaster[4] = array(
                    xl("All"),
                    xl("Unbilled"),
                    xl("Billed"),
                    xl("Denied")
                ); // Display Value
                $ThisPageSearchCriteriaRadioKeyMaster[4] = "all,0,1,7"; // Key
                $ThisPageSearchCriteriaDisplayRadioMaster[5] = array(
                    xl("All"),
                    xl("Authorized"),
                    xl("Unauthorized")
                );
                $ThisPageSearchCriteriaRadioKeyMaster[5] = "%,1,0";
                $ThisPageSearchCriteriaDisplayRadioMaster[6] = array(
                    xl("All"),
                    xl("None"),
                    xl("Ins 1"),
                    xl("Ins 2 or Ins 3")
                );
                $ThisPageSearchCriteriaRadioKeyMaster[6] = "all,0,1,2";
                // The below section is needed if there is any 'query_drop_down' type in the $ThisPageSearchCriteriaDataTypeMaster
                $ThisPageSearchCriteriaQueryDropDownMaster[1] = "SELECT name,id FROM x12_partners;";
                $ThisPageSearchCriteriaQueryDropDownMasterDefault[1] = xl("All"); // Only one item will be here
                $ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[1] = "all"; // Only one item will be here
                // The below section is needed if there is any 'include' type in the $ThisPageSearchCriteriaDataTypeMaster
                // Function name is added here.Corresponding include files need to be included in the respective pages as done in this page.
                // It is labled(Included for Insurance ajax criteria)(Line:-279-299).
                $ThisPageSearchCriteriaIncludeMaster[1] = "InsuranceCompanyDisplay"; // This is php function defined in the file 'report.inc.php'

                if (!isset($_REQUEST['mode'])) {// default case
                    $_REQUEST['final_this_page_criteria'][0] = "(form_encounter.date between '" . date("Y-m-d 00:00:00") . "' and '" . date("Y-m-d 23:59:59") . "')";
                    $_REQUEST['final_this_page_criteria'][1] = "billing.billed = '0'";
                    $_REQUEST['final_this_page_criteria_text'][0] = xl("Date of Service = Today");
                    $_REQUEST['final_this_page_criteria_text'][1] = xl("Billing Status = Unbilled");
                    $_REQUEST['date_master_criteria_form_encounter_date'] = "today";
                    $_REQUEST['master_from_date_form_encounter_date'] = date("Y-m-d");
                    $_REQUEST['master_to_date_form_encounter_date'] = date("Y-m-d");
                    $_REQUEST['radio_billing_billed'] = 0;
                }
                ?>
                <div class="hideaway">
                    <?php require_once "$srcdir/../interface/reports/criteria.tab.php"; ?>
                </div>
                <?php
                // ============================================================================================================================================= -->
                // Criteria section Ends -->
                // ============================================================================================================================================= -->
                ?>

            </div>
            <div class="form-group col-xs-3 hideaway">
                <fieldset>
                    <legend><?php echo xlt('Select Action'); ?></legend>
                    <div class="form-group col-xs-12">
                        <div class='text'>
                            <ul>
                                <li><a class="link_submit" href="#"
                                       onclick="javascript:return SubmitTheScreen();"><strong><?php echo xlt('Update List') ?></strong>
                                    </a><i id='update-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></li>
                                <li><a class='link_submit' href="#"
                                       onclick="javascript:return SubmitTheScreenExportOFX();"><strong><?php echo xlt('Export OFX'); ?></strong></a>
                                </li>
                                <li><a class='link_submit' href="#" onclick="javascript:return SubmitTheScreenPrint();"><strong><?php echo xlt('View Printable Report'); ?></strong></a>
                                </li>
                                <span>
                                    <?php if ($daysheet) { ?>
                                        <li><a class='link_submit' href="#"
                                               onclick="javascript:return SubmitTheEndDayPrint();"><strong><?php echo xlt('End Of Day Report') . ' - ' ?></strong></a>
                                        <?php if ($daysheet_total) { ?>
                                            <span class="text"><strong><?php echo xlt('Totals'); ?></strong></span>
                                            <input name="end_of_day_totals_only" type="checkbox" value="1"></li>
                                        <?php } ?>
                                        <?php if ($provider_run) { ?>
                                            <span class="text"><strong><?php echo xlt('Provider'); ?></strong></span>
                                            <input name="end_of_day_provider_only" type="checkbox" value="1"></li>
                                        <?php } ?>
                                    <?php } ?>
                                    </span>
                                <?php if (!file_exists($EXPORT_INC)) { ?>
                                    <li><a href='#' id="view-log-link"  data-toggle="modal"  data-target="#myModal" class='link_submit'
                                           title='<?php echo xla('See messages from the last set of generated claims'); ?>'><strong><?php echo xlt('View Log'); ?></strong></a>
                                    </li>
                                <?php } ?>
                                <li><a class="link_submit"
                                       href="JavaScript:void(0);" onclick="select_all(); return false;"><strong><?php echo xlt('Select All'); ?></strong></a>
                                </li>
                            </ul>
                            <ul>
                        </div>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <form class="form-inline" name='update_form' method='post' action='billing_process.php' onsubmit='return top.restoreSession()' style="display:inline">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
            <div class="form-group clearfix">
                <div class="btn-group btn-group-pinch position-override" role="group">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                name="bn_x12_support"
                                title="<?php echo xla('A claim must be selected to enable this menu.') ?>"><?php echo xla('X12 OPTIONS') ?>
                            <span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <?php if (file_exists($EXPORT_INC)) { ?>
                                <li>
                                    <button type="submit" data-open-popup="true" class="btn btn-default btn-download"
                                            name="bn_external"
                                            title="<?php echo xla('Export to external billing system') ?>"
                                            value="<?php echo xla("Export Billing") ?>"><?php echo xlt("Export Billing") ?></button>
                                </li>
                                <li>
                                    <button type="submit" data-open-popup="true" class="btn btn-default btn-download"
                                            name="bn_mark"
                                            title="<?php echo xla('Mark as billed but skip billing') ?>"><?php echo xlt("Mark as Cleared") ?></button>
                                </li>
                            <?php } else { ?>
                                <li>
                                    <button type="submit" class="btn btn-default btn-download" name="bn_x12"
                                            onclick="MarkAsCleared(1)"
                                            title="<?php echo xla('Generate and download X12 batch') ?>"><?php echo xlt('Generate X12') ?></button>
                                </li>
                            <?php } ?>
                            <?php if ($GLOBALS['ub04_support']) { ?>
                                <li>
                                    <button type="submit" class="btn btn-default btn-download" name="bn_ub04_x12"
                                            title="<?php echo xla('Generate Institutional X12 837I') ?>"><?php echo xlt('Generate X12 837I') ?></button>
                                </li>
                            <?php } ?>
                            <?php if ($GLOBALS['support_encounter_claims']) { ?>
                                <li>
                                    <button type="submit" class="btn btn-default btn-download" name="bn_x12_encounter"
                                            onclick="MarkAsCleared(1)"
                                            title="<?php echo xla('Generate and download X12 encounter claim batch') ?>"><?php echo xlt('Generate X12 Encounter') ?></button>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                name="bn_process_hcfa_support"
                                title="<?php echo xla('A claim must be selected to enable this menu.') ?>"><?php echo xlt('HCFA FORM') ?>
                            <span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <button type="submit" class="btn btn-default btn-download" name="bn_process_hcfa"
                                        onclick="MarkAsCleared(2)"
                                        title="<?php echo xla('Generate and download CMS 1500 paper claims') ?>"><?php echo xlt('CMS 1500 PDF') ?></button>
                            </li>
                            <?php if ($GLOBALS['preprinted_cms_1500']) { ?>
                                <li>
                                    <button type="submit" class="btn btn-default btn-download"
                                            name="bn_process_hcfa_form" onclick="MarkAsCleared(2)"
                                            title="<?php echo xla('Generate and download CMS 1500 paper claims on Preprinted form') ?>"><?php echo xlt('CMS 1500 incl FORM') ?></button>
                                </li>
                            <?php } ?>
                            <li>
                                <button type="submit" class="btn btn-default btn-download" name="bn_hcfa_txt_file"
                                        onclick="MarkAsCleared(3)"
                                        title="<?php echo xla('Making batch text files for uploading to Clearing House and will mark as billed') ?>"><?php echo xlt('CMS 1500 TEXT') ?></button>
                            </li>
                        </ul>
                    </div>
                    <?php if ($GLOBALS['ub04_support']) { ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                    name="bn_process_ub04_support"
                                    title="<?php echo xla('A claim must be selected to enable this menu.') ?>"><?php echo xlt('UB04 FORM') ?>
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <button type="submit" class="btn btn-default btn-download"
                                            name="bn_process_ub04_form"
                                            title="<?php echo xla('Generate and download UB-04 CMS1450 with form') ?>"><?php echo xlt('UB04 FORM PDF') ?></button>
                                </li>
                                <li>
                                    <button type="submit" class="btn btn-default btn-download" name="bn_process_ub04"
                                            title="<?php echo xla('Generate and download UB-04 CMS1450') ?>"><?php echo xlt('UB04 TEXT PDF') ?></button>
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
                    <button class="btn btn-default btn-download" data-open-popup="true" name="bn_mark"
                            title="<?php echo xla('Post to accounting and mark as billed') ?>"
                            type="submit"><?php echo xla('Mark as Cleared') ?></button>
                    <button class="btn btn-default btn-undo" data-open-popup="true" name="bn_reopen"
                            title="<?php echo xla('Mark as not billed') ?>"
                            type="submit"><?php echo xlt('Re-Open') ?></button>
                </div>
                <div class="input-group">
                    <label for="left_margin"><?php echo xlt('CMS 1500 Margins Left'); ?>:</label>
                    <input type='text' size='2' id='left_margin' name='left_margin'
                           value='<?php echo attr($left_margin); ?>'
                           title='<?php echo xla('HCFA left margin in points'); ?>'/>
                    <label for="top_margin"><?php echo xlt('Top'); ?>:</label>
                    <input type='text' size='2' id='top_margin' name='top_margin'
                           value='<?php echo attr($top_margin); ?>'
                           title='<?php echo xla('HCFA top margin in points'); ?>'/>
                </div>
                <?php if ($ub04_support) { ?>
                    <div class="input-group">
                        <label for="left_ubmargin"><?php echo xlt('UB04 Margins Left'); ?>:</label>
                        <input type='text' size='2' id='left_ubmargin' name='left_ubmargin'
                               value='<?php echo attr($left_ubmargin); ?>'
                               title='<?php echo xla('UB04 left margin in points'); ?>'/>
                        <label for="top_ubmargin"><?php echo xlt('Top'); ?>:</label>
                        <input type='text' size='2' id='top_ubmargin' name='top_ubmargin'
                               value='<?php echo attr($top_ubmargin); ?>'
                               title='<?php echo xla('UB04 top margin in points'); ?>'/>
                    </div>
                <?php } ?>
            </div>
            <input id='HiddenMarkAsCleared' name='HiddenMarkAsCleared' type='hidden' value="">
            <input name='mode' type='hidden' value="bill">
            <input name='authorized' type='hidden' value="<?php echo attr($my_authorized); ?>">
            <input name='unbilled' type='hidden' value="<?php echo attr($unbilled); ?>">
            <input name='code_type' type='hidden' value="%">
            <input name='to_date' type='hidden' value="<?php echo attr($to_date); ?>">
            <input name='from_date' type='hidden' value="<?php echo attr($from_date); ?>">
            <?php
            if ($my_authorized == "on") {
                $my_authorized = "1";
            } else {
                $my_authorized = "%";
            }
            if ($unbilled == "on") {
                $unbilled = "0";
            } else {
                $unbilled = "%";
            }
            $list = getBillsListBetween("%");
            ?>
            <?php
            if (!isset($_POST["mode"])) {
                if (!isset($_POST["from_date"])) {
                    $from_date = date("Y-m-d");
                } else {
                    $from_date = $_POST["from_date"];
                }
                if (empty($_POST["to_date"])) {
                    $to_date = '';
                } else {
                    $to_date = $_POST["to_date"];
                }
                if (!isset($_POST["code_type"])) {
                    $code_type = "all";
                } else {
                    $code_type = $_POST["code_type"];
                }
                if (!isset($_POST["unbilled"])) {
                    $unbilled = "on";
                } else {
                    $unbilled = $_POST["unbilled"];
                }
                if (!isset($_POST["authorized"])) {
                    $my_authorized = "on";
                } else {
                    $my_authorized = $_POST["authorized"];
                }
            } else {
                $from_date = $_POST["from_date"];
                $to_date = $_POST["to_date"];
                $code_type = $_POST["code_type"];
                $unbilled = $_POST["unbilled"];
                $my_authorized = $_POST["authorized"];
            }

            if ($my_authorized == "on") {
                $my_authorized = "1";
            } else {
                $my_authorized = "%";
            }

            if ($unbilled == "on") {
                $unbilled = "0";
            } else {
                $unbilled = "%";
            }

            if (isset($_POST["mode"]) && $_POST["mode"] == "bill") {
                billCodesList($list);
            }
            ?>
            <div class="table-responsive">
                <table class="table table-condensed">
                    <?php
                    $divnos = 0;
                    if ($ret = getBillsBetween("%")) {
                        if (is_array($ret)) {
                            ?>
                            <tr>
                            <td colspan='9' align="right">
                                <table width="250" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="100" id='ExpandAll'><a
                                                onclick="expandcollapse('expand');" class='small'
                                                href="JavaScript:void(0);"><?php echo '(' . xlt('Expand All') . ')' ?></a>
                                        </td>
                                        <td width="100" id='CollapseAll'><a
                                                onclick="expandcollapse('collapse');" class='small'
                                                href="JavaScript:void(0);"><?php echo '(' . xlt('Collapse All') . ')' ?></a>
                                        </td>
                                        <td width="50">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <?php
                        }
                        $loop = 0;
                        $oldcode = "";
                        $last_encounter_id = "";
                        $lhtml = "";
                        $rhtml = "";
                        $lcount = 0;
                        $rcount = 0;
                        $bgcolor = "";
                        $skipping = false;

                        $mmo_empty_mod = false;
                        $mmo_num_charges = 0;

                        foreach ($ret as $iter) {
                        // We include encounters here that have never been billed. However
                        // if it had no selected billing items but does have non-selected
                        // billing items, then it is not of interest.
                            if (!$iter['id']) {
                                $res = sqlQuery(
                                    "SELECT count(*) AS count FROM billing WHERE " .
                                    "encounter = ? AND " .
                                    "pid=? AND " .
                                    "activity = 1",
                                    array(
                                    $iter['enc_encounter'],
                                    $iter['enc_pid']
                                    )
                                );
                                if ($res['count'] > 0) {
                                    continue;
                                }
                            }

                            $this_encounter_id = $iter['enc_pid'] . "-" . $iter['enc_encounter'];

                            if ($last_encounter_id != $this_encounter_id) {
                            // This dumps all HTML for the previous encounter.
                            //
                                if ($lhtml) {
                                    while ($rcount < $lcount) {
                                        $rhtml .= "<tr bgcolor='" . attr($bgcolor) . "'><td colspan='9'></td></tr>";
                                        ++$rcount;
                                    }
                                    // This test handles the case where we are only listing encounters
                                    // that appear to have a missing "25" modifier.
                                    if (!$missing_mods_only || ($mmo_empty_mod && $mmo_num_charges > 1)) {
                                        if ($DivPut == 'yes') {
                                            $lhtml .= '</div>';
                                            $DivPut = 'no';
                                        }
                                        echo "<tr bgcolor='" . attr($bgcolor) . "'>\n<td rowspan='" . attr($rcount) . "' valign='top'>\n$lhtml</td>$rhtml\n";
                                        echo "<tr bgcolor='" . attr($bgcolor) . "'><td colspan='9' height='5'></td></tr>\n\n";
                                        ++$encount;
                                    }
                                }

                                $lhtml = "";
                                $rhtml = "";
                                $mmo_empty_mod = false;
                                $mmo_num_charges = 0;

                            // If there are ANY unauthorized items in this encounter and this is
                            // the normal case of viewing only authorized billing, then skip the
                            // entire encounter.
                            //
                                $skipping = false;
                                if ($my_authorized == '1') {
                                    $res = sqlQuery(
                                        "SELECT count(*) AS count FROM billing WHERE " .
                                        "encounter = ? AND " .
                                        "pid=? AND " .
                                        "activity = 1 AND authorized = 0",
                                        array(
                                        $iter['enc_encounter'],
                                        $iter['enc_pid']
                                        )
                                    );
                                    if ($res['count'] > 0) {
                                        $skipping = true;
                                        $last_encounter_id = $this_encounter_id;
                                        continue;
                                    }
                                }
                            // Is there a MBO
                            //
                                $mboid = sqlQuery("SELECT forms.form_id FROM forms WHERE forms.encounter = ? AND forms.authorized = 1 AND forms.formdir = 'misc_billing_options' AND forms.deleted != 1 LIMIT 1", array($iter['enc_encounter']));
                                $iter['mboid'] = $mboid ? attr($mboid['form_id']) : 0;

                                $name = getPatientData($iter['enc_pid'], "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

                            // Check if patient has primary insurance and a subscriber exists for it.
                            // If not we will highlight their name in red.
                            // TBD: more checking here.
                            //
                                $res = sqlQuery(
                                    "SELECT count(*) AS count FROM insurance_data WHERE " .
                                    "pid = ? AND " .
                                    "type='primary' AND " .
                                    "subscriber_lname IS NOT NULL AND " .
                                    "subscriber_lname != '' LIMIT 1",
                                    array(
                                    $iter['enc_pid']
                                    )
                                );
                                $namecolor = ($res['count'] > 0) ? "black" : "#ff7777";

                                $bgcolor = "#" . (($encount & 1) ? "FFFAEF" : "F8F8FF");
                                echo "<tr bgcolor='" . attr($bgcolor) . "'><td colspan='9' height='5'></td></tr>\n";
                                $lcount = 1;
                                $rcount = 0;
                                $oldcode = "";

                                $ptname = $name['fname'] . " " . $name['lname'];
                                $raw_encounter_date = date("Y-m-d", strtotime($iter['enc_date']));
                                $billing_note = $name['billing_note'];
                            // Add Encounter Date to display with "To Encounter" button 2/17/09 JCH
                                $lhtml .= "<span class=bold><font color='" . attr($namecolor) . "'>" . text($ptname) . "</font></span><span class=small>&nbsp;(" . text($iter['enc_pid']) . "-" . text($iter['enc_encounter']) . ")</span>";

                            // Encounter details are stored to javacript as array.
                                $result4 = sqlStatement(
                                    "SELECT fe.encounter,fe.date,fe.billing_note,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
                                    " LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date DESC",
                                    array(
                                    $iter['enc_pid']
                                    )
                                );
                                if (sqlNumRows($result4) > 0) {
                                    ;
                                } ?>
                            <script>
                            Count = 0;
                            EncounterDateArray[<?php echo attr($iter['enc_pid']); ?>] = new Array;
                            CalendarCategoryArray[<?php echo attr($iter['enc_pid']); ?>] = new Array;
                            EncounterIdArray[<?php echo attr($iter['enc_pid']); ?>] = new Array;
                            EncounterNoteArray[<?php echo attr($iter['enc_pid']); ?>] = new Array;
                            <?php
                            while ($rowresult4 = sqlFetchArray($result4)) {
                            ?>
                            EncounterIdArray[<?php echo attr($iter['enc_pid']); ?>][Count] = <?php echo js_escape($rowresult4['encounter']); ?>;
                            EncounterDateArray[<?php echo attr($iter['enc_pid']); ?>][Count] = <?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
                            CalendarCategoryArray[<?php echo attr($iter['enc_pid']); ?>][Count] = <?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
                            EncounterNoteArray[<?php echo attr($iter['enc_pid']); ?>][Count] = <?php echo js_escape($rowresult4['billing_note']); ?>;
                            Count++;
                            <?php
                            $enc_billing_note = $rowresult4['billing_note'];
                            }
                            ?>
                            </script>
                            <?php
                            $lhtml .= "<div class='button-group'>";
                            // Not sure why the next section seems to do nothing except post "To Encounter" button 2/17/09 JCH
                            $lhtml .= "<a class=\"btn btn-xs btn-default\" role=\"button\" " . "href=\"javascript:window.toencounter(" . attr_js($iter['enc_pid']) . "," . attr_js($name['pubpid']) . "," . attr_js($ptname) . "," . attr_js($iter['enc_encounter']) . "," . attr_js(oeFormatShortDate($raw_encounter_date)) . "," . attr_js(" ".xl('DOB').": ".oeFormatShortDate($name['DOB_YMD'])." ".xl('Age').": ".getPatientAge($name['DOB_YMD'])) . ");
                         top.window.parent.left_nav.setPatientEncounter(EncounterIdArray[" . attr($iter['enc_pid']) . "],EncounterDateArray[" . attr($iter['enc_pid']) . "], CalendarCategoryArray[" . attr($iter['enc_pid']) . "])\">" . xlt('Encounter') . " " . text(oeFormatShortDate($raw_encounter_date)) . "</a>";

                            // Changed "To xxx" buttons to allow room for encounter date display 2/17/09 JCH
                            $lhtml .= "<a class=\"btn btn-xs btn-default\" role=\"button\" " . "href=\"javascript:window.topatient(" . attr_js($iter['enc_pid']) . "," . attr_js($name['pubpid']) . "," . attr_js($ptname) . "," . attr_js($iter['enc_encounter']) . "," . attr_js(oeFormatShortDate($raw_encounter_date)) . "," . attr_js(" ".xl('DOB').": ".oeFormatShortDate($name['DOB_YMD'])." ".xl('Age').": ".getPatientAge($name['DOB_YMD'])) . ");
                        top.window.parent.left_nav.setPatientEncounter(EncounterIdArray[" . attr($iter['enc_pid']) . "],EncounterDateArray[" . attr($iter['enc_pid']) . "], CalendarCategoryArray[" . attr($iter['enc_pid']) . "])\">" . xlt('Patient') . "</a>";
                            $is_edited = $iter['mboid'] ? 'btn-success' : 'btn-default';
                            $title = $iter['mboid'] ? xlt("This claim has HCFA 1500 miscellaneous billing options") : xlt("Click to add HCFA 1500 miscellaneous billing options");
                            $lhtml .= "<a class='btn btn-xs $is_edited' role='button' title='" . attr($title) . "' onclick='popMBO(" . attr_js($iter['enc_pid']) . "," . attr_js($iter['enc_encounter']) . "," . attr_js($iter['mboid']) . "); return false;'>" . xlt('MBO ') . "</a>";
                            if ($ub04_support && isset($iter['billed'])) {
                                $c = sqlQuery(
                                    "SELECT submitted_claim AS status FROM claims WHERE " .
                                    "encounter_id = ? AND " .
                                    "patient_id=? " .
                                    "ORDER BY version DESC LIMIT 1",
                                    array(
                                    $iter['enc_encounter'],
                                    $iter['enc_pid']
                                    )
                                );
                                $is_edited = $c['status'] ? 'btn-success' : 'btn-warning';
                                $bname = $c['status'] ? xl('Reviewed') : xl('Review UB04');
                                $lhtml .= "<a class='btn btn-xs $is_edited' role='button' onclick='popUB04(" . attr_js($iter['enc_pid']) . "," . attr_js($iter['enc_encounter']) . "); return false;'>" . text($bname) . "</a>";
                            }
                            $lhtml .= "</div>";
                            $divnos = $divnos + 1;
                            $lhtml .= "&nbsp;&nbsp;&nbsp;<a onclick='divtoggle(" . attr_js("spanid_".$divnos) . "," . attr_js("divid_".$divnos) . ");' class='small' id='aid_" . attr($divnos) . "' href=\"JavaScript:void(0);" . "\">(<span id=spanid_" . attr($divnos) . " class=\"indicator\">" . xlt('Expand') . '</span>)<br></a>';
                            if ($GLOBALS['notes_to_display_in_Billing'] == 2 || $GLOBALS['notes_to_display_in_Billing'] == 3) {
                                $lhtml .= '<span style="margin-left: 20px; font-weight: bold; color: red">' . text($billing_note) . '</span>';
                            }

                            if ($iter['id']) {
                                $lcount += 2;
                                $lhtml .= "<br />\n";
                                $lhtml .= "&nbsp;<span class='form-group'>" . xlt('Bill') . ": ";
                                $lhtml .= "<select name='claims[" . attr($this_encounter_id) . "][payer]' onchange='onNewPayer(event)' style='background-color:" . attr($bgcolor) . "'>";

                                $query = "SELECT id.provider AS id, id.type, id.date, " .
                                "ic.x12_default_partner_id AS ic_x12id, ic.name AS provider " .
                                "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
                                "ic.id = id.provider AND " .
                                "id.pid = ? AND " .
                                "id.date <= ? " .
                                "ORDER BY id.type ASC, id.date DESC";

                                $result = sqlStatement(
                                    $query,
                                    array(
                                    $iter['enc_pid'],
                                    $raw_encounter_date
                                    )
                                );
                                $count = 0;
                                $default_x12_partner = $iter['ic_x12id'];
                                $prevtype = '';

                                while ($row = sqlFetchArray($result)) {
                                    if (strcmp($row['type'], $prevtype) == 0) {
                                        continue;
                                    }
                                    $prevtype = $row['type'];
                                    if (strlen($row['provider']) > 0) {
                                        // This preserves any existing insurance company selection, which is
                                        // important when EOB posting has re-queued for secondary billing.
                                        $lhtml .= "<option value=\"" . attr(substr($row['type'], 0, 1) . $row['id']) . "\"";
                                        if (($count == 0 && !$iter['payer_id']) || $row['id'] == $iter['payer_id']) {
                                            $lhtml .= " selected";
                                            if (!is_numeric($default_x12_partner)) {
                                                $default_x12_partner = $row['ic_x12id'];
                                            }
                                        }
                                        $lhtml .= " data-partner='" . attr($row['ic_x12id']) . "'>" . text($row['type']) . ": " . text($row['provider']) . "</option>";
                                    }
                                    $count++;
                                }

                                $lhtml .= "<option value='-1'>" . xlt("Unassigned") . "</option>\n";
                                $lhtml .= "</select>&nbsp;&nbsp;\n";
                                $lhtml .= "&nbsp;<span class='form-group'>X12: ";
                                $lhtml .= "<select id='partners' name='claims[" . attr($this_encounter_id) . "][partner]' style='margin-top:5px; background-color:" . attr($bgcolor) . "'>";
                                $lhtml .= "<option value='-1' label='Unassigned'>" . xlt("Partner not configured") . "</option>\n";
                                foreach ($partners as $xid => $xname) {
                                    if (empty(trim($xname))) {
                                        continue;
                                    }
                                    $lhtml .= '<option label="' . attr($xname) . '" value="' . attr($xid) . '"';
                                    if ($xid == $default_x12_partner) {
                                        $lhtml .= "selected";
                                    }
                                    $lhtml .= '>' . text($xname) . '</option>';
                                }
                                $lhtml .= "</select></span>";
                                $DivPut = 'yes';

                                if ($GLOBALS['notes_to_display_in_Billing'] == 1 || $GLOBALS['notes_to_display_in_Billing'] == 3) {
                                    $lhtml .= "<br><span style='margin-left: 20px; font-weight: bold; color: green'>" . text($enc_billing_note) . "</span>";
                                }
                                $lhtml .= "<br>\n&nbsp;<div id='divid_" . attr($divnos) . "' style='display:none'>" . text(oeFormatShortDate(substr($iter['date'], 0, 10))) . text(substr($iter['date'], 10, 6)) . " " . xlt("Encounter was coded");

                                $query = "SELECT * FROM claims WHERE patient_id = ? AND encounter_id = ? ORDER BY version";
                                $cres = sqlStatement(
                                    $query,
                                    array(
                                    $iter['enc_pid'],
                                    $iter['enc_encounter']
                                    )
                                );

                                $lastcrow = false;

                                while ($crow = sqlFetchArray($cres)) {
                                    $query = "SELECT id.type, ic.name " .
                                        "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
                                        "id.pid = ? AND " .
                                        "id.provider = ? AND " .
                                        "id.date <= ? AND " .
                                        "ic.id = id.provider " .
                                        "ORDER BY id.type ASC, id.date DESC";

                                    $irow = sqlQuery(
                                        $query,
                                        array(
                                        $iter['enc_pid'],
                                        $crow['payer_id'],
                                        $raw_encounter_date
                                        )
                                    );

                                    if ($crow['bill_process']) {
                                        $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['bill_time'], 0, 10))) . text(substr($crow['bill_time'], 10, 6)) . " " . xlt("Queued for") . " " . text($irow['type']) . " " . text($crow['target']) . " " . xlt("billing to ") . text($irow['name']);
                                        ++$lcount;
                                    } elseif ($crow['status'] < 6) {
                                        if ($crow['status'] > 1) {
                                            $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['bill_time'], 0, 10))) . text(substr($crow['bill_time'], 10, 6)) . " " . xlt("Marked as cleared");
                                            ++$lcount;
                                        } else {
                                            $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['bill_time'], 0, 10))) . text(substr($crow['bill_time'], 10, 6)) . " " . xlt("Re-opened");
                                            ++$lcount;
                                        }
                                    } elseif ($crow['status'] == 6) {
                                        $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['bill_time'], 0, 10))) . text(substr($crow['bill_time'], 10, 6)) . " " . xlt("This claim has been forwarded to next level.");
                                        ++$lcount;
                                    } elseif ($crow['status'] == 7) {
                                        $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['bill_time'], 0, 10))) . text(substr($crow['bill_time'], 10, 6)) . " " . xlt("This claim has been denied.Reason:-");
                                        if ($crow['process_file']) {
                                            $code_array = explode(',', $crow['process_file']);
                                            foreach ($code_array as $code_key => $code_value) {
                                                $lhtml .= "<br>\n&nbsp;&nbsp;&nbsp;";
                                                $reason_array = explode('_', $code_value);
                                                if (!isset($adjustment_reasons[$reason_array[3]])) {
                                                    $lhtml .= xlt("For code") . ' [' . text($reason_array[0]) . '] ' . xlt("and modifier") . ' [' . text($reason_array[1]) . '] ' . xlt("the Denial code is") . ' [' . text($reason_array[2]) . ' ' . text($reason_array[3]) . ']';
                                                } else {
                                                    $lhtml .= xlt("For code") . ' [' . text($reason_array[0]) . '] ' . xlt("and modifier") . ' [' . text($reason_array[1]) . '] ' . xlt("the Denial Group code is") . ' [' . text($reason_array[2]) . '] ' . xlt("and the Reason is") . ':- ' . text($adjustment_reasons[$reason_array[3]]);
                                                }
                                            }
                                        } else {
                                            $lhtml .= xlt("Not Specified.");
                                        }
                                                                            ++$lcount;
                                    }

                                    if ($crow['process_time']) {
                                        $lhtml .= "<br>\n&nbsp;" . text(oeFormatShortDate(substr($crow['process_time'], 0, 10))) . text(substr($crow['process_time'], 10, 6)) . " " . xlt("Claim was generated to file") . " " . "<a href='get_claim_file.php?key=" . attr_url($crow['process_file']) . "&csrf_token_form=" . attr_url(collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($crow['process_file']) . "</a>";
                                        ++$lcount;
                                    }

                                    $lastcrow = $crow;
                                } // end while ($crow = sqlFetchArray($cres))

                                if ($lastcrow && $lastcrow['status'] == 4) {
                                    $lhtml .= "<br>\n&nbsp;" . xlt("This claim has been closed.");
                                    ++$lcount;
                                }

                                if ($lastcrow && $lastcrow['status'] == 5) {
                                    $lhtml .= "<br>\n&nbsp;" . xlt("This claim has been canceled.");
                                    ++$lcount;
                                }
                            } // end if ($iter['id'])
                            } // end if ($last_encounter_id != $this_encounter_id)

                            if ($skipping) {
                                continue;
                            }

                        // Collect info related to the missing modifiers test.
                            if ($iter['fee'] > 0) {
                                ++$mmo_num_charges;
                                $tmp = substr($iter['code'], 0, 3);
                                if (($tmp == '992' || $tmp == '993') && empty($iter['modifier'])) {
                                    $mmo_empty_mod = true;
                                }
                            }

                            ++$rcount;

                            if ($rhtml) {
                                $rhtml .= "<tr bgcolor='$bgcolor'>\n";
                            }
                            $rhtml .= "<td width='50'>";
                            if ($iter['id'] && $oldcode != $iter['code_type']) {
                                $rhtml .= "<span class=text>" . text($iter['code_type']) . ": </span>";
                            }

                            $oldcode = $iter['code_type'];
                            $rhtml .= "</td>\n";
                            $justify = "";

                            if ($iter['id'] && $code_types[$iter['code_type']]['just']) {
                                $js = explode(":", $iter['justify']);
                                $counter = 0;
                                foreach ($js as $j) {
                                    if (!empty($j)) {
                                        if ($counter == 0) {
                                            $justify .= " (<b>" . text($j) . "</b>)";
                                        } else {
                                            $justify .= " (" . text($j) . ")";
                                        }
                                        $counter++;
                                    }
                                }
                            }

                            $rhtml .= "<td><span class='text'>" . ($iter['code_type'] == 'COPAY' ? text(oeFormatMoney($iter['code'])) : text($iter['code']));
                            if ($iter['modifier']) {
                                $rhtml .= ":" . text($iter['modifier']);
                            }
                            $rhtml .= "</span><span style='font-size:8pt;'>$justify</span></td>\n";

                            $rhtml .= '<td align="right"><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                            if ($iter['id'] && $iter['fee'] > 0) {
                                $rhtml .= text(oeFormatMoney($iter['fee']));
                            }
                            $rhtml .= "</span></td>\n";
                            $rhtml .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                            if ($iter['id']) {
                                $rhtml .= getProviderName(empty($iter['provider_id']) ? text($iter['enc_provider_id']) : text($iter['provider_id']));
                            }
                            $rhtml .= "</span></td>\n";
                            $rhtml .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                            if ($GLOBALS['display_units_in_billing'] != 0) {
                                if ($iter['id']) {
                                    $rhtml .= xlt("Units") . ":" . text($iter{"units"});
                                }
                            }
                            $rhtml .= "</span></td>\n";
                            $rhtml .= '<td width=100>&nbsp;&nbsp;&nbsp;<span style="font-size:8pt;">';
                            if ($iter['id']) {
                                $rhtml .= text(oeFormatSDFT(strtotime($iter{"date"})));
                            }
                            $rhtml .= "</span></td>\n";
                        // This error message is generated if the authorized check box is not checked
                            if ($iter['id'] && $iter['authorized'] != 1) {
                                $rhtml .= "<td><span class=alert>" . xlt("Note: This code has not been authorized.") . "</span></td>\n";
                            } else {
                                $rhtml .= "<td></td>\n";
                            }
                            if ($iter['id'] && $last_encounter_id != $this_encounter_id) {
                                $tmpbpr = $iter['bill_process'];
                                if ($tmpbpr == '0' && $iter['billed']) {
                                    $tmpbpr = '2';
                                }
                                $rhtml .= "<td><input type='checkbox' value='" . attr($tmpbpr) . "' name='claims[" . attr($this_encounter_id) . "][bill]' onclick='set_button_states()' id='CheckBoxBilling" . attr($CheckBoxBilling * 1) . "'>&nbsp;</td>\n";
                                $CheckBoxBilling++;
                            } else {
                                $rhtml .= "<td></td>\n";
                            }
                            if ($last_encounter_id != $this_encounter_id) {
                                $rhtml2 = "";
                                $rowcnt = 0;
                                $resMoneyGot = sqlStatement(
                                    "SELECT pay_amount AS PatientPay,date(post_time) AS date FROM ar_activity WHERE " .
                                    "pid = ? AND encounter = ? AND payer_type=0 AND account_code='PCP'",
                                    array(
                                    $iter['enc_pid'],
                                    $iter['enc_encounter']
                                    )
                                );
                                // new fees screen copay gives account_code='PCP'
                                if (sqlNumRows($resMoneyGot) > 0) {
                                    $lcount += 2;
                                    $rcount++;
                                }
                                // checks whether a copay exists for the encounter and if exists displays it.
                                while ($rowMoneyGot = sqlFetchArray($resMoneyGot)) {
                                    $rowcnt++;
                                    $PatientPay = $rowMoneyGot['PatientPay'];
                                    $date = $rowMoneyGot['date'];
                                    if ($PatientPay > 0) {
                                        if ($rhtml) {
                                            $rhtml2 .= "<tr bgcolor='$bgcolor'>\n";
                                        }
                                        $rhtml2 .= "<td width='50'>";
                                        $rhtml2 .= "<span class='text'>" . xlt('COPAY') . ": </span>";
                                        $rhtml2 .= "</td>\n";
                                        $rhtml2 .= "<td><span class='text'>" . text(oeFormatMoney($PatientPay)) . "</span><span style='font-size:8pt;'>&nbsp;</span></td>\n";
                                        $rhtml2 .= '<td align="right"><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                                        $rhtml2 .= "</span></td>\n";
                                        $rhtml2 .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                                        $rhtml2 .= "</span></td>\n";
                                        $rhtml2 .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
                                        $rhtml2 .= "</span></td>\n";
                                        $rhtml2 .= '<td width=100>&nbsp;&nbsp;&nbsp;<span style="font-size:8pt;">';
                                        $rhtml2 .= text(oeFormatSDFT(strtotime($date)));
                                        $rhtml2 .= "</span></td>\n";
                                        if ($iter['id'] && $iter['authorized'] != 1) {
                                            $rhtml2 .= "<td><span class=alert>" . xlt("Note: This copay was entered against billing that has not been authorized. Please review status.") . "</span></td>\n";
                                        } else {
                                            $rhtml2 .= "<td></td>\n";
                                        }
                                        if (!$iter['id'] && $rowcnt == 1) {
                                            $rhtml2 .= "<td><input type='checkbox' value='0' name='claims[" . attr($this_encounter_id) . "][bill]' onclick='set_button_states()' id='CheckBoxBilling" . attr($CheckBoxBilling * 1) . "'>&nbsp;</td>\n";
                                            $CheckBoxBilling++;
                                        } else {
                                            $rhtml2 .= "<td></td>\n";
                                        }
                                    }
                                }
                                $rhtml .= $rhtml2;
                            }
                            $rhtml .= "</tr>\n";
                            $last_encounter_id = $this_encounter_id;
                        } // end foreach

                        if ($lhtml) {
                            while ($rcount < $lcount) {
                                $rhtml .= "<tr bgcolor='" . attr($bgcolor) . "'><td colspan='9'></td></tr>";
                                ++$rcount;
                            }
                            if (!$missing_mods_only || ($mmo_empty_mod && $mmo_num_charges > 1)) {
                                if ($DivPut == 'yes') {
                                    $lhtml .= '</div>';
                                    $DivPut = 'no';
                                }
                                echo "<tr bgcolor='" . attr($bgcolor) . "'>\n<td rowspan='" . attr($rcount) . "' valign='top' width='25%'>\n$lhtml</td>$rhtml\n";
                                echo "<tr bgcolor='" . attr($bgcolor) . "'><td colspan='9' height='5'></td></tr>\n";
                            }
                        }
                    }

                    ?>

                </table>
        </form>
    </div>
</div>
</div><!--end of container div -->
<?php $oemr_ui->oeBelowContainerDiv();?>
<script>
set_button_states();
<?php
if ($alertmsg) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}
?>
$(document).ready(function () {
    $("#view-log-link").click( function() {
        top.restoreSession();
        dlgopen('customize_log.php', '_blank', 500, 400);
    });

    $('button[type="submit"]').click(function () {
        top.restoreSession();
        $(this).attr('data-clicked', true);
    });

    $('form[name="update_form"]').submit(function (e) {
        var clickedButton = $("button[type=submit][data-clicked='true'")[0];
        // clear clicked button indicator
        $('button[type="submit"]').attr('data-clicked', false);

        if (!clickedButton || $(clickedButton).attr("data-open-popup") !== "true") {
            $(this).removeAttr("target");
            return top.restoreSession();
        } else {
            top.restoreSession();
            var w = window.open('about:blank', 'Popup_Window', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=400,height=300,left = 312,top = 234');
            this.target = 'Popup_Window';
        }
    });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'; ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    // jquery-ui tooltip converted to bootstrap tooltip
    $('#update-tooltip').attr("title", <?php echo xlj('Click Update List to display billing information filtered by the selected Current Criteria'); ?>).tooltip();

});
</script>
<input type="hidden" name="divnos" id="divnos" value="<?php echo attr($divnos) ?>"/>
<input type='hidden' name='ajax_mode' id='ajax_mode' value=''/>
</body>
</html>
