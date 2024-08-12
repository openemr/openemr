<?php

/**
 *
 * Patient summary screen.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @author    Robert Down <robertdown@live.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2021-2022 Robert Down <robertdown@live.com
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

require_once("$srcdir/lists.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("../history/history.inc.php");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/group.inc.php");
require_once(__DIR__ . "/../../../library/appointments.inc.php");

use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent as CardRenderEvent;
use OpenEMR\Events\Patient\Summary\Card\SectionEvent;
use OpenEMR\Events\Patient\Summary\Card\RenderModel;
use OpenEMR\Events\Patient\Summary\Card\CardInterface;
use OpenEMR\Events\PatientDemographics\ViewEvent;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Patient\Cards\BillingViewCard;
use OpenEMR\Patient\Cards\DemographicsViewCard;
use OpenEMR\Patient\Cards\InsuranceViewCard;
use OpenEMR\Patient\Cards\PortalCard;
use OpenEMR\Reminder\BirthdayReminder;
use OpenEMR\Services\AllergyIntoleranceService;
use OpenEMR\Services\ConditionService;
use OpenEMR\Services\ImmunizationService;
use OpenEMR\Services\PatientIssuesService;
use OpenEMR\Services\PatientService;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Reset the previous name flag to allow normal operation.
// This is set in new.php so we can prevent new previous name from being added i.e no pid available.
OpenEMR\Common\Session\SessionUtil::setSession('disablePreviousNameAdds', 0);

$twig = new TwigContainer(null, $GLOBALS['kernel']);

// Set session for pid (via setpid). Also set session for encounter (if applicable)
if (isset($_GET['set_pid'])) {
    require_once("$srcdir/pid.inc.php");
    setpid($_GET['set_pid']);
    $ptService = new PatientService();
    $newPatient = $ptService->findByPid($pid);
    $ptService->touchRecentPatientList($newPatient);
    if (isset($_GET['set_encounterid']) && ((int)$_GET['set_encounterid'] > 0)) {
        $encounter = (int)$_GET['set_encounterid'];
        SessionUtil::setSession('encounter', $encounter);
    }
}

// Note: it would eventually be a good idea to move this into
// it's own module that people can remove / add if they don't
// want smart support in their system.
$smartLaunchController = new SMARTLaunchController($GLOBALS["kernel"]->getEventDispatcher());
$smartLaunchController->registerContextEvents();
$hiddenCards = getHiddenDashboardCards();

/**
 * @var EventDispatcher
 */
$ed = $GLOBALS['kernel']->getEventDispatcher();

$active_reminders = false;
$all_allergy_alerts = false;
if ($GLOBALS['enable_cdr']) {
    //CDR Engine stuff
    if ($GLOBALS['enable_allergy_check'] && $GLOBALS['enable_alert_log']) {
        //Check for new allergies conflicts and throw popup if any exist(note need alert logging to support this)
        $new_allergy_alerts = allergy_conflict($pid, 'new', $_SESSION['authUser']);
        if (!empty($new_allergy_alerts)) {
            $pod_warnings = '';
            foreach ($new_allergy_alerts as $new_allergy_alert) {
                $pod_warnings .= js_escape($new_allergy_alert) . ' + "\n"';
            }
            $allergyWarningMessage = '<script>alert(' . xlj('WARNING - FOLLOWING ACTIVE MEDICATIONS ARE ALLERGIES') . ' + "\n" + ' . $pod_warnings . ')</script>';
        }
    }

    if ((empty($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && $GLOBALS['enable_cdr_crp']) {
        // showing a new patient, so check for active reminders and allergy conflicts, which use in active reminder popup
        $active_reminders = active_alert_summary($pid, "reminders-due", '', 'default', $_SESSION['authUser'], true);
        if ($GLOBALS['enable_allergy_check']) {
            $all_allergy_alerts = allergy_conflict($pid, 'all', $_SESSION['authUser'], true);
        }
    }
    SessionUtil::setSession('alert_notify_pid', $pid);
    // can not output html until after above setSession call
    if (!empty($allergyWarningMessage)) {
        echo $allergyWarningMessage;
    }
}
//Check to see is only one insurance is allowed
if ($GLOBALS['insurance_only_one']) {
    $insurance_array = array('primary');
} else {
    $insurance_array = array('primary', 'secondary', 'tertiary');
}

function getHiddenDashboardCards(): array
{
    $hiddenList = [];
    $ret = sqlStatement("SELECT gl_value FROM `globals` WHERE `gl_name` = 'hide_dashboard_cards'");
    while ($row = sqlFetchArray($ret)) {
        $hiddenList[] = $row['gl_value'];
    }

    return $hiddenList;
}

function print_as_money($money)
{
    preg_match("/(\d*)\.?(\d*)/", $money, $moneymatches);
    $tmp = wordwrap(strrev($moneymatches[1]), 3, ",", 1);
    $ccheck = strrev($tmp);
    if ($ccheck[0] == ",") {
        $tmp = substr($ccheck, 1, strlen($ccheck) - 1);
    }

    if ($moneymatches[2] != "") {
        return "$ " . strrev($tmp) . "." . $moneymatches[2];
    } else {
        return "$ " . strrev($tmp);
    }
}

// get an array from Photos category
function pic_array($pid, $picture_directory)
{
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
        "on documents.id = categories_to_documents.document_id " .
        "join categories on categories.id = categories_to_documents.category_id " .
        "where categories.name like ? and documents.foreign_id = ? and documents.deleted = 0";
    if ($query = sqlStatement($sql_query, array($picture_directory, $pid))) {
        while ($results = sqlFetchArray($query)) {
            array_push($pics, $results['id']);
        }
    }

    return ($pics);
}

// Get the document ID's in a specific catg.
// this is only used in one place, here for id photos
function get_document_by_catg($pid, $doc_catg, $limit = 1)
{
    $results = null;

    if ($pid and $doc_catg) {
        $query = sqlStatement("SELECT d.id, d.date, d.url
            FROM documents AS d, categories_to_documents AS cd, categories AS c
            WHERE d.foreign_id = ?
            AND cd.document_id = d.id
            AND c.id = cd.category_id
            AND c.name LIKE ?
            ORDER BY d.date DESC LIMIT " . escape_limit($limit), array($pid, $doc_catg));
    }
    while ($result = sqlFetchArray($query)) {
        $results[] = $result['id'];
    }
    return ($results ?? false);
}

function isPortalEnabled(): bool
{
    if (
        !$GLOBALS['portal_onsite_two_enable']
    ) {
        return false;
    }

    return true;
}

function isPortalSiteAddressValid(): bool
{
    if (
        // maybe can use filter_var() someday but the default value in GLOBALS
        // fails with FILTER_VALIDATE_URL
        !isset($GLOBALS['portal_onsite_two_address'])
    ) {
        return false;
    }

    return true;
}

function isPortalAllowed($pid): bool
{
    $return = false;

    $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid = ?", [$pid]);
    if ($portalStatus['allow_patient_portal'] == 'YES') {
        $return = true;
    }
    return $return;
}

function isApiAllowed($pid): bool
{
    $return = false;

    $apiStatus = sqlQuery("SELECT prevent_portal_apps FROM patient_data WHERE pid = ?", [$pid]);
    if (strtoupper($apiStatus['prevent_portal_apps'] ?? '') != 'YES') {
        $return = true;
    }
    return $return;
}

function areCredentialsCreated($pid): bool
{
    $return = false;
    $credentialsCreated = sqlQuery("SELECT date_created FROM `patient_access_onsite` WHERE `pid`=?", [$pid]);
    if ($credentialsCreated['date_created'] ?? null) {
        $return = true;
    }

    return $return;
}

function isContactEmail($pid): bool
{
    $return = false;

    $email = sqlQuery("SELECT email, email_direct FROM patient_data WHERE pid = ?", [$pid]);
    if (!empty($email['email']) || !empty($email['email_direct'])) {
        $return = true;
    }
    return $return;
}

function isEnforceSigninEmailPortal(): bool
{
    if (
        $GLOBALS['enforce_signin_email']
    ) {
        return true;
    }

    return false;
}

function deceasedDays($days_deceased)
{
    $deceased_days = intval($days_deceased['days_deceased'] ?? '');
    if ($deceased_days == 0) {
        $num_of_days = xl("Today");
    } elseif ($deceased_days == 1) {
        $num_of_days = $deceased_days . " " . xl("day ago");
    } elseif ($deceased_days > 1 && $deceased_days < 90) {
        $num_of_days = $deceased_days . " " . xl("days ago");
    } elseif ($deceased_days >= 90 && $deceased_days < 731) {
        $num_of_days = "~" . round($deceased_days / 30) . " " . xl("months ago");  // function intdiv available only in php7
    } elseif ($deceased_days >= 731) {
        $num_of_days = xl("More than") . " " . round($deceased_days / 365) . " " . xl("years ago");
    }

    if (strlen($days_deceased['date_deceased'] ?? '') > 10 && $GLOBALS['date_display_format'] < 1) {
        $deceased_date = substr($days_deceased['date_deceased'], 0, 10);
    } else {
        $deceased_date = oeFormatShortDate($days_deceased['date_deceased'] ?? '');
    }

    return xlt("Deceased") . " - " . text($deceased_date) . " (" . text($num_of_days) . ")";
}

$deceased = is_patient_deceased($pid);


// Display image in 'widget style'
function image_widget($doc_id, $doc_catg)
{
    global $pid, $web_root;
    $docobj = new Document($doc_id);
    $image_file = $docobj->get_url_file();
    $image_file_name = $docobj->get_name();
    $image_width = $GLOBALS['generate_doc_thumb'] == 1 ? '' : 'width=100';
    $extension = substr($image_file_name, strrpos($image_file_name, "."));
    $viewable_types = array('.png', '.jpg', '.jpeg', '.png', '.bmp', '.PNG', '.JPG', '.JPEG', '.PNG', '.BMP');
    if (in_array($extension, $viewable_types)) { // extension matches list
        $to_url = "<td> <a href = '$web_root" .
            "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false&original_file=true&disable_exit=false&show_original=true'" .
            " onclick='top.restoreSession();' class='image_modal'>" .
            " <img src = '$web_root" .
            "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false'" .
            " $image_width alt='" . attr($doc_catg) . ":" . attr($image_file_name) . "'>  </a> </td> <td class='align-middle'>" .
            text($doc_catg) . '<br />&nbsp;' . text($image_file_name) . "</td>";
    } else {
        $to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
            "&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "'" .
            " onclick='top.restoreSession()' class='btn btn-primary btn-sm'>" .
            "<span>" .
            xlt("View") . "</a> &nbsp;" .
            text("$doc_catg - $image_file_name") .
            "</span> </td>";
    }

    echo "<table><tr>";
    echo $to_url;
    echo "</tr></table>";
}

// Determine if the Vitals form is in use for this site.
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE directory = 'vitals' AND state = 1");
$vitals_is_registered = $tmp['count'];

// Get patient/employer/insurance information.
//
$result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$result2 = getEmployerData($pid);
$result3 = getInsuranceData(
    $pid,
    "primary",
    "copay,
    provider,
    DATE_FORMAT(`date`,'%Y-%m-%d') as effdate,
    DATE_FORMAT(`date_end`,'%Y-%m-%d') as effdate_end"
);
$insco_name = "";
if (!empty($result3['provider'])) {   // Use provider in case there is an ins record w/ unassigned insco
    $insco_name = getInsuranceProvider($result3['provider']);
}

$arrOeUiSettings = array(
    'page_id' => 'core.mrd',
    'heading_title' => xl('Medical Record Dashboard'),
    'include_patient_name' => true,
    'expandable' => true,
    'expandable_files' => array('demographics_xpd'), //all file names need suffix _xpd
    'action' => "", //conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "", //only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "medical_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
<!DOCTYPE html>
<html>

<head>
    <?php
    Header::setupHeader(['common', 'utility']);
    require_once("$srcdir/options.js.php");
    ?>
    <script>
        // Process click on diagnosis for referential cds popup.
        function referentialCdsClick(codetype, codevalue) {
            top.restoreSession();
            // Force a new window instead of iframe to address cross site scripting potential
            dlgopen('../education.php?type=' + encodeURIComponent(codetype) + '&code=' + encodeURIComponent(codevalue), '_blank', 1024, 750, true);
        }

        function oldEvt(apptdate, eventid) {
            let title = <?php echo xlj('Appointments'); ?>;
            dlgopen('../../main/calendar/add_edit_event.php?date=' + encodeURIComponent(apptdate) + '&eid=' + encodeURIComponent(eventid), '_blank', 800, 500, '', title);
        }

        function advdirconfigure() {
            dlgopen('advancedirectives.php', '_blank', 400, 500);
        }

        function refreshme() {
            top.restoreSession();
            location.reload();
        }

        // Process click on Delete link.
        function deleteme() { // @todo don't think this is used any longer!!
            dlgopen('../deleter.php?patient=' + <?php echo js_url($pid); ?> +'&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450, '', '', {
                allowResize: false,
                allowDrag: false,
                dialogId: 'patdel',
                type: 'iframe'
            });
            return false;
        }

        // Called by the deleteme.php window on a successful delete.
        function imdeleted() {
            top.clearPatient();
        }

        function newEvt() {
            let title = <?php echo xlj('Appointments'); ?>;
            let url = '../../main/calendar/add_edit_event.php?patientid=' + <?php echo js_url($pid); ?>;
            dlgopen(url, '_blank', 800, 500, '', title);
            return false;
        }

        function toggleIndicator(target, div) {
            // <i id="show_hide" class="fa fa-lg small fa-eye-slash" title="Click to Hide"></i>
            $mode = $(target).find(".indicator").text();
            if ($mode == <?php echo xlj('collapse'); ?>) {
                $(target).find(".indicator").text(<?php echo xlj('expand'); ?>);
                $("#" + div).hide();
                $.post("../../../library/ajax/user_settings.php", {
                    target: div,
                    mode: 0,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                });
            } else {
                $(target).find(".indicator").text(<?php echo xlj('collapse'); ?>);
                $("#" + div).show();
                $.post("../../../library/ajax/user_settings.php", {
                    target: div,
                    mode: 1,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                });
            }
        }

        // edit prescriptions dialog.
        // called from stats.php.
        //
        function editScripts(url) {
            var AddScript = function () {
                var __this = $(this);
                __this.find("#clearButton").css("display", "");
                __this.find("#backButton").css("display", "");
                __this.find("#addButton").css("display", "none");

                var iam = top.frames.editScripts;
                iam.location.href = '<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&edit&id=0&pid=' + <?php echo js_url($pid); ?>;
            };
            var ListScripts = function () {
                var __this = $(this);
                __this.find("#clearButton").css("display", "none");
                __this.find("#backButton").css("display", "none");
                __this.find("#addButton").css("display", "");
                var iam = top.frames.editScripts
                iam.location.href = '<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&list&id=' + <?php echo js_url($pid); ?>;
            };

            let title = <?php echo xlj('Prescriptions'); ?>;
            let w = 960; // for weno width

            dlgopen(url, 'editScripts', w, 400, '', '', {
                buttons: [{
                    text: <?php echo xlj('Add'); ?>,
                    close: false,
                    id: 'addButton',
                    class: 'btn-primary btn-sm',
                    click: AddScript
                },
                    {
                        text: <?php echo xlj('Clear'); ?>,
                        close: false,
                        id: 'clearButton',
                        style: 'display:none;',
                        class: 'btn-primary btn-sm',
                        click: AddScript
                    },
                    {
                        text: <?php echo xlj('Back'); ?>,
                        close: false,
                        id: 'backButton',
                        style: 'display:none;',
                        class: 'btn-primary btn-sm',
                        click: ListScripts
                    },
                    {
                        text: <?php echo xlj('Quit'); ?>,
                        close: true,
                        id: 'doneButton',
                        class: 'btn-secondary btn-sm'
                    }
                ],
                onClosed: 'refreshme',
                allowResize: true,
                allowDrag: true,
                dialogId: 'editscripts',
                type: 'iframe'
            });
            return false;
        }

        /**
         * async function fetchHtml(...)
         *
         * @param {*} url
         * @param {boolean} embedded
         * @param {boolean} sessionRestore
         * @returns {text}
         */
        async function fetchHtml(url, embedded = false, sessionRestore = false) {
            if (sessionRestore === true) {
                // restore cookie before fetch.
                top.restoreSession();
            }
            let csrf = new FormData;
            // a security given.
            csrf.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
            if (embedded === true) {
                // special formatting in certain widgets.
                csrf.append("embeddedScreen", true);
            }

            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                body: csrf
            });
            return await response.text();
        }

        /**
         * async function placeHtml(...) will await fetch of html then place in divId.
         * This function will return a promise for use to init various items regarding
         * inserted HTML if needed.
         * If divId does not exist, then will skip.
         * Example
         *
         * @param {*} url
         * @param {string} divId id
         * @param {boolean} embedded
         * @param {boolean} sessionRestore
         * @returns {object} promise
         */
        async function placeHtml(url, divId, embedded = false, sessionRestore = false) {
            const contentDiv = document.getElementById(divId);
            if (contentDiv) {
                await fetchHtml(url, embedded, sessionRestore).then(fragment => {
                    contentDiv.innerHTML = fragment;
                });
            }
        }

        if (typeof load_location === 'undefined') {
            function load_location(location) {
                top.restoreSession();
                document.location = location;
            }
        }

        $(function () {
            var msg_updation = '';
            <?php
            if ($GLOBALS['erx_enable']) {
                $soap_status = sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')", array($pid));
                while ($row_soapstatus = sqlFetchArray($soap_status)) { ?>
            top.restoreSession();
            let reloadRequired = false;
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_patientfullmedication.php",
                dataType: "html",
                data: {
                    patient: <?php echo js_escape($row_soapstatus['pid']); ?>,
                },
                async: false,
                success: function (thedata) {
                    if (!thedata.includes("Nothing")) {
                        reloadRequired = true;
                    }
                    msg_updation += thedata;
                },
                error: function () {
                    alert('ajax error');
                }
            });

            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_allergy.php",
                dataType: "html",
                data: {
                    patient: <?php echo js_escape($row_soapstatus['pid']); ?>,
                },
                async: false,
                success: function (thedata) {
                    if (!thedata.includes("Nothing")) {
                        reloadRequired = true;
                    }
                    msg_updation += "\n" + thedata;
                },
                error: function () {
                    alert('ajax error');
                }
            });

            if (reloadRequired) {
                document.location.reload();
            }

                    <?php
                    if ($GLOBALS['erx_import_status_message']) { ?>
            if (msg_updation)
                alert(msg_updation);
                        <?php
                    }
                }
            }
            ?>

            // load divs
            placeHtml("stats.php", "stats_div", true).then(() => {
                $('[data-toggle="collapse"]').on('click', function (e) {
                    updateUserVisibilitySetting(e);
                });
            });
            placeHtml("pnotes_fragment.php", 'pnotes_ps_expand').then(() => {
                // must be delegated event!
                $(this).on("click", ".complete_btn", function () {
                    let btn = $(this);
                    let csrf = new FormData;
                    csrf.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
                    fetch("pnotes_fragment.php?docUpdateId=" + encodeURIComponent(btn.attr('data-id')), {
                        method: "POST",
                        credentials: 'same-origin',
                        body: csrf
                    }).then(function () {
                        placeHtml("pnotes_fragment.php", 'pnotes_ps_expand');
                    });
                });
            });
            placeHtml("disc_fragment.php", "disclosures_ps_expand");
            placeHtml("labdata_fragment.php", "labdata_ps_expand");
            placeHtml("track_anything_fragment.php", "track_anything_ps_expand");
            <?php if ($vitals_is_registered && AclMain::aclCheckCore('patients', 'med')) { ?>
            // Initialize the Vitals form if it is registered and user is authorized.
            placeHtml("vitals_fragment.php", "vitals_ps_expand");
            <?php } ?>

            <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
            placeHtml("clinical_reminders_fragment.php", "clinical_reminders_ps_expand", true, true).then(() => {
                // (note need to place javascript code here also to get the dynamic link to work)
                $(".medium_modal").on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dlgopen('', '', 800, 200, '', '', {
                        buttons: [{
                            text: <?php echo xlj('Close'); ?>,
                            close: true,
                            style: 'secondary btn-sm'
                        }],
                        onClosed: 'refreshme',
                        allowResize: false,
                        allowDrag: true,
                        dialogId: 'demreminder',
                        type: 'iframe',
                        url: $(this).attr('href')
                    });
                });
            });
            <?php } // end crw
            ?>

            <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
            placeHtml("patient_reminders_fragment.php", "patient_reminders_ps_expand", false, true);
            <?php } // end prw
            ?>

            <?php
            // Initialize for each applicable LBF form.
            $gfres = sqlStatement("SELECT grp_form_id
                FROM layout_group_properties
                WHERE grp_form_id LIKE 'LBF%'
                    AND grp_group_id = ''
                    AND grp_repeats > 0
                    AND grp_activity = 1
                ORDER BY grp_seq, grp_title");
            while ($gfrow = sqlFetchArray($gfres)) { ?>
            $(<?php echo js_escape("#" . $gfrow['grp_form_id'] . "_ps_expand"); ?>).load("lbf_fragment.php?formname=" + <?php echo js_url($gfrow['grp_form_id']); ?>, {
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            });
            <?php } ?>
            tabbify();

            // modal for dialog boxes
            $(".large_modal").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 1000, 600, '', '', {
                    buttons: [{
                        text: <?php echo xlj('Close'); ?>,
                        close: true,
                        style: 'secondary btn-sm'
                    }],
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".rx_modal").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var title = <?php echo xlj('Amendments'); ?>;
                dlgopen('', 'editAmendments', 800, 300, '', title, {
                    onClosed: 'refreshme',
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            // modal for image viewer
            $(".image_modal").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 400, 300, '', <?php echo xlj('Patient Images'); ?>, {
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".deleter").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 600, 360, '', '', {
                    buttons: [{
                        text: <?php echo xlj('Close'); ?>,
                        close: true,
                        style: 'secondary btn-sm'
                    }],
                    //onClosed: 'imdeleted',
                    allowResize: false,
                    allowDrag: false,
                    dialogId: 'patdel',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".iframe1").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 350, 300, '', '', {
                    buttons: [{
                        text: <?php echo xlj('Close'); ?>,
                        close: true,
                        style: 'secondary btn-sm'
                    }],
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });
            // for patient portal
            $(".small_modal").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 550, 550, '', '', {
                    buttons: [{
                        text: <?php echo xlj('Close'); ?>,
                        close: true,
                        style: 'secondary btn-sm'
                    }],
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            function openReminderPopup() {
                top.restoreSession()
                dlgopen('', 'reminders', 500, 250, '', '', {
                    buttons: [{
                        text: <?php echo xlj('Close'); ?>,
                        close: true,
                        style: 'secondary btn-sm'
                    }],
                    allowResize: true,
                    allowDrag: true,
                    dialogId: '',
                    type: 'iframe',
                    url: $("#reminder_popup_link").attr('href')
                });
            }

            <?php if ($GLOBALS['patient_birthday_alert']) {
            // To display the birthday alert:
            //  1. The patient is not deceased
            //  2. The birthday is today (or in the past depending on global selection)
            //  3. The notification has not been turned off (or shown depending on global selection) for this year
                $birthdayAlert = new BirthdayReminder($pid, $_SESSION['authUserID']);
                if ($birthdayAlert->isDisplayBirthdayAlert()) {
                    ?>
            // show the active reminder modal
            dlgopen('', 'bdayreminder', 300, 170, '', false, {
                allowResize: false,
                allowDrag: true,
                dialogId: '',
                type: 'iframe',
                url: $("#birthday_popup").attr('href')
            });

                <?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
            openReminderPopup();
            <?php } ?>
            <?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
            openReminderPopup();
            <?php } ?>
        });

        /**
         * Change the preference to expand/collapse a given card.
         *
         * For the given e element, find the corresponding card body, determine if it is collapsed
         * or shown, and then save the state to the user preferences via an async fetch call POST'ing
         * the updated setting.
         *
         * @var e element The Button that was clicked to collapse/expand the card
         */
        async function updateUserVisibilitySetting(e) {
            const targetID = e.target.getAttribute("data-target");
            const target = document.querySelector(targetID);
            const targetStr = targetID.substring(1);
            // test ensure at least an element we want.
            if (target.classList.contains("collapse")) {
                // who is icon. Easier to catch BS event than create one specific for this decision..
                // Should always be icon target
                let iconTarget = e.target.children[0] || e.target;
                // toggle
                if (iconTarget.classList.contains("fa-expand")) {
                    iconTarget.classList.remove('fa-expand');
                    iconTarget.classList.add('fa-compress');
                } else {
                    iconTarget.classList.remove('fa-compress');
                    iconTarget.classList.add('fa-expand');
                }
            }
            let formData = new FormData();
            formData.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
            formData.append("target", targetStr);
            formData.append("mode", (target.classList.contains("show")) ? 0 : 1);
            top.restoreSession();
            const response = await fetch("../../../library/ajax/user_settings.php", {
                method: "POST",
                credentials: 'same-origin',
                body: formData,
            });

            return await response.text();
        }

        // JavaScript stuff to do when a new patient is set.
        //
        function setMyPatient() {
            <?php
            if (isset($_GET['set_pid'])) {
                $date_of_death = is_patient_deceased($pid);
                if (!empty($date_of_death)) {
                    $date_of_death = $date_of_death['date_deceased'];
                }
                ?>
            parent.left_nav.setPatient(<?php echo js_escape($result['fname'] . " " . $result['lname']) .
                    "," . js_escape($pid) . "," . js_escape($result['pubpid']) . ",'',";
            if (empty($date_of_death)) {
                echo js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($result['DOB_YMD']));
            } else {
                echo js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age at death') . ": " . oeFormatAge($result['DOB_YMD'], $date_of_death));
            } ?>);
            var EncounterDateArray = [];
            var CalendarCategoryArray = [];
            var EncounterIdArray = [];
            var Count = 0;
                <?php
            //Encounter details are stored to javacript as array.
                $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
                " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
                if (sqlNumRows($result4) > 0) {
                    while ($rowresult4 = sqlFetchArray($result4)) { ?>
            EncounterIdArray[Count] = <?php echo js_escape($rowresult4['encounter']); ?>;
            EncounterDateArray[Count] = <?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
            CalendarCategoryArray[Count] = <?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
            Count++;
                        <?php
                    }
                }
                ?>
            parent.left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray);
                <?php
            } // end setting new pid
            ?>
            parent.left_nav.syncRadios();
            <?php if ((isset($_GET['set_pid'])) && (isset($_GET['set_encounterid'])) && (intval($_GET['set_encounterid']) > 0)) {
                $query_result = sqlQuery("SELECT `date` FROM `form_encounter` WHERE `encounter` = ?", array($encounter)); ?>
            encurl = 'encounter/encounter_top.php?set_encounter=' + <?php echo js_url($encounter); ?> +'&pid=' + <?php echo js_url($pid); ?>;
            parent.left_nav.setEncounter(<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date'])))); ?>, <?php echo js_escape($encounter); ?>, 'enc');
            top.restoreSession();
            parent.left_nav.loadFrame('enc2', 'enc', 'patient_file/' + encurl);
            <?php } // end setting new encounter id (only if new pid is also set)
            ?>
        }

        $(window).on('load', function () {
            setMyPatient();
        });
    </script>

    <style>
      /* Bad practice to override here, will get moved to base style theme */
      .card {
        box-shadow: 1px 1px 1px hsl(0 0% 0% / .2);
        border-radius: 0;
      }

      /* Short term fix. This ensures the problem list, allergies, medications, and immunization cards handle long lists without interuppting
         the UI. This should be configurable and should go in a more appropriate place
      .pami-list {
          max-height: 200px;
          overflow-y: scroll;
      } */

      <?php
        if (!empty($GLOBALS['right_justify_labels_demographics']) && ($_SESSION['language_direction'] == 'ltr')) { ?>
      div.tab td.label_custom, div.label_custom {
        text-align: right !important;
      }

      div.tab td.data, div.data {
        padding-left: 0.5em;
        padding-right: 2em;
      }

            <?php
        } ?>

      <?php
      // This is for layout font size override.
        $grparr = array();
        getLayoutProperties('DEM', $grparr, 'grp_size');
        if (!empty($grparr['']['grp_size'])) {
            $FONTSIZE = round($grparr['']['grp_size'] * 1.333333);
            $FONTSIZE = round($FONTSIZE * 0.0625, 2);
            ?>

      /* Override font sizes in the theme. */
      #DEM .groupname {
        font-size: <?php echo attr($FONTSIZE); ?>rem;
      }

      #DEM .label {
        font-size: <?php echo attr($FONTSIZE); ?>rem;
      }

      #DEM .data {
        font-size: <?php echo attr($FONTSIZE); ?>rem;
      }

      #DEM .data td {
        font-size: <?php echo attr($FONTSIZE); ?>rem;
      }

      <?php } ?>
      :root {
        --white: #fff;
        --bg: hsl(0 0% 90%);
      }

      body {
        background: var(--bg) !important;
      }

      section {
        background: var(--white);
        margin-top: .25em;
        padding: .25em;
      }

      .section-header-dynamic {
        border-bottom: none;
      }
    </style>
    <title><?php echo xlt("Dashboard{{patient file}}"); ?></title>
</head>

<body class="mt-1 patient-demographic bg-light">

    <?php
    // Create and fire the patient demographics view event
    $viewEvent = new ViewEvent($pid);
    $viewEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($viewEvent, ViewEvent::EVENT_HANDLE, 10);
    $thisauth = AclMain::aclCheckCore('patients', 'demo');

    if (!$thisauth || !$viewEvent->authorized()) {
        echo $twig->getTwig()->render('core/unauthorized-partial.html.twig', ['pageTitle' => xl("Medical Dashboard")]);
        exit();
    }
    ?>

    <div id="container_div" class="<?php echo $oemr_ui->oeContainer(); ?> mb-2">
        <a href='../reminder/active_reminder_popup.php' id='reminder_popup_link' style='display: none' onclick='top.restoreSession()'></a>
        <a href='../birthday_alert/birthday_pop.php?pid=<?php echo attr_url($pid); ?>&user_id=<?php echo attr_url($_SESSION['authUserID']); ?>' id='birthday_popup' style='display: none;' onclick='top.restoreSession()'></a>
        <?php

        if ($thisauth) {
            if ($result['squad'] && !AclMain::aclCheckCore('squads', $result['squad'])) {
                $thisauth = 0;
            }
        }

        if ($thisauth) :
            require_once("$include_root/patient_file/summary/dashboard_header.php");
        endif;

        $list_id = "dashboard"; // to indicate nav item is active, count and give correct id
        // Collect the patient menu then build it
        $menuPatient = new PatientMenuRole($twig);
        $menuPatient->displayHorizNavBarMenu();
        // Get the document ID of the patient ID card if access to it is wanted here.
        $idcard_doc_id = false;
        if ($GLOBALS['patient_id_category_name']) {
            $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name'], 3);
        }
        ?>
        <div class="main mb-1">
            <!-- start main content div -->
            <div class="row">
                <?php
                $t = $twig->getTwig();

                $allergy = (AclMain::aclCheckIssue('allergy') ? 1 : 0) && !in_array('card_allergies', $hiddenCards) ? 1 : 0;
                $pl = (AclMain::aclCheckIssue('medical_problem') ? 1 : 0) && !in_array('card_medicalproblems', $hiddenCards) ? 1 : 0;
                $meds = (AclMain::aclCheckIssue('medication') ? 1 : 0) && !in_array('card_medication', $hiddenCards) ? 1 : 0;
                $rx = !$GLOBALS['disable_prescriptions'] && AclMain::aclCheckCore('patients', 'rx') && !in_array('card_prescriptions', $hiddenCards) ? 1 : 0;
                $cards = max(1, ($allergy + $pl + $meds));
                $col = "p-1 ";
                $colInt = 12 / $cards;
                $col .= "col-md-" . $colInt;

                /**
                 * Helper function to return only issues with an outcome not equal to resolved
                 *
                 * @param array $i An array of issues
                 * @return array
                 */
                function filterActiveIssues(array $i): array
                {
                    return array_filter($i, function ($_i) {
                        return ($_i['outcome'] != 1) && (empty($_i['enddate']) || (strtotime($_i['enddate']) > strtotime('now')));
                    });
                }

                // ALLERGY CARD
                if ($allergy === 1) {
                    $allergyService = new AllergyIntoleranceService();
                    $_rawAllergies = filterActiveIssues($allergyService->getAll(['lists.pid' => $pid])->getData());
                    $id = 'allergy_ps_expand';
                    $viewArgs = [
                        'title' => xl('Allergies'),
                        'card_container_class_list' => ['flex-fill', 'mx-1', 'card'],
                        'id' => $id,
                        'forceAlwaysOpen' => false,
                        'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                        'linkMethod' => "javascript",
                        'list' => $_rawAllergies,
                        'listTouched' => (!empty(getListTouch($pid, 'allergy'))) ? true : false,
                        'auth' => true,
                        'btnLabel' => 'Edit',
                        'btnLink' => "return load_location('{$GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=allergy')"
                    ];
                    echo "<div class=\"$col\">";
                    echo $t->render('patient/card/allergies.html.twig', $viewArgs);
                    echo "</div>";
                }

                $patIssueService = new PatientIssuesService();

                // MEDICAL PROBLEMS CARD
                if ($pl === 1) {
                    $_rawPL = $patIssueService->search(['lists.pid' => $pid, 'lists.type' => 'medical_problem'])->getData();
                    $id = 'medical_problem_ps_expand';
                    $viewArgs = [
                        'title' => xl('Medical Problems'),
                        'card_container_class_list' => ['flex-fill', 'mx-1', 'card'],
                        'id' => $id,
                        'forceAlwaysOpen' => false,
                        'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                        'linkMethod' => "javascript",
                        'list' => filterActiveIssues($_rawPL),
                        'listTouched' => (!empty(getListTouch($pid, 'medical_problem'))) ? true : false,
                        'auth' => true,
                        'btnLabel' => 'Edit',
                        'btnLink' => "return load_location('{$GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=medical_problem')"
                    ];
                    echo "<div class=\"$col\">";
                    echo $t->render('patient/card/medical_problems.html.twig', $viewArgs);
                    echo "</div>";
                }

                // MEDICATION CARD
                if ($meds === 1) {
                    $_rawMedList = $patIssueService->search(['lists.pid' => $pid, 'lists.type' => 'medication'])->getData();
                    $id = 'medication_ps_expand';
                    $viewArgs = [
                        'title' => xl('Medications'),
                        'card_container_class_list' => ['flex-fill', 'mx-1', 'card'],
                        'id' => $id,
                        'forceAlwaysOpen' => false,
                        'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                        'linkMethod' => "javascript",
                        'list' => filterActiveIssues($_rawMedList),
                        'listTouched' => (!empty(getListTouch($pid, 'medication'))) ? true : false,
                        'auth' => true,
                        'btnLabel' => 'Edit',
                        'btnLink' => "return load_location('{$GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=medication')"
                    ];
                    echo "<div class=\"$col\">";
                    echo $t->render('patient/card/medication.html.twig', $viewArgs);
                    echo "</div>";
                }

                // Render the Prescriptions card if turned on
                if ($rx === 1) :
                    if ($GLOBALS['erx_enable'] && $display_current_medications_below == 1) {
                        $sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND active = '1'";
                        $res = sqlStatement($sql, [$pid]);

                        $rxArr = [];
                        while ($row = sqlFetchArray($res)) {
                            $row['unit'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_units'), $row['unit']);
                            $row['form'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_form'), $row['form']);
                            $row['route'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_route'), $row['route']);
                            $row['interval'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_interval'), $row['interval']);
                            $rxArr[] = $row;
                        }
                        $id = "current_prescriptions_ps_expand";
                        $viewArgs = [
                            'title' => xl('Current Medications'),
                            'id' => $id,
                            'forceAlwaysOpen' => false,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'auth' => false,
                            'rxList' => $rxArr,
                        ];

                        echo $t->render('patient/card/erx.html.twig', $viewArgs);
                    }

                    $id = "prescriptions_ps_expand";
                    $viewArgs = [
                        'title' => xl("Prescriptions"),
                        'card_container_class_list' => ['flex-fill', 'mx-1', 'card'],
                        'id' => $id,
                        'forceAlwaysOpen' => false,
                        'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                        'btnLabel' => "Edit",
                        'auth' => AclMain::aclCheckCore('patients', 'rx', '', ['write', 'addonly']),
                    ];

                    if ($GLOBALS['erx_enable']) {
                        $viewArgs['title'] = 'Prescription History';
                        $viewArgs['btnLabel'] = 'Add';
                        $viewArgs['btnLink'] = "{$GLOBALS['webroot']}/interface/eRx.php?page=compose";
                    } else {
                        $viewArgs['btnLink'] = "editScripts('{$GLOBALS['webroot']}/controller.php?prescription&list&id=" . attr_url($pid) . "')";
                        $viewArgs['linkMethod'] = "javascript";
                        $viewArgs['btnClass'] = "iframe";
                    }

                    $cwd = getcwd();
                    chdir("../../../");
                    $c = new Controller();
                    // This is a hacky way to get a Smarty template from the controller and injecting it into
                    // a Twig template. This reduces the amount of refactoring that is required but ideally the
                    // Smarty template should be upgraded to Twig
                    ob_start();
                    echo $c->act(['prescription' => '', 'fragment' => '', 'patient_id' => $pid]);
                    $viewArgs['content'] = ob_get_contents();
                    ob_end_clean();

                    echo "<div class=\"col\">";
                    echo $t->render('patient/card/rx.html.twig', $viewArgs); // render core prescription card
                    echo "</div>";
                endif;
                ?>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <?php
                    if ($deceased > 0) :
                        echo $twig->getTwig()->render('patient/partials/deceased.html.twig', [
                            'deceasedDays' => deceasedDays($deceased),
                        ]);
                    endif;

                    $sectionRenderEvents = $ed->dispatch(new SectionEvent('primary'), SectionEvent::EVENT_HANDLE);
                    $sectionRenderEvents->addCard(new DemographicsViewCard($result, $result2, ['dispatcher' => $ed]));

                    if (!$GLOBALS['hide_billing_widget']) {
                        $sectionRenderEvents->addCard(new BillingViewCard($pid, $insco_name, $result['billing_note'], $result3, ['dispatcher' => $ed]));
                    }

                    if (!in_array('card_insurance', $hiddenCards)) {
                        $sectionRenderEvents->addCard(new InsuranceViewCard($pid, ['dispatcher' => $ed]));
                    }
                    // Get the cards to render
                    $sectionCards = $sectionRenderEvents->getCards();

                    // if anyone wants to render anything before the patient demographic list
                    $GLOBALS["kernel"]->getEventDispatcher()->dispatch(new RenderEvent($pid), RenderEvent::EVENT_SECTION_LIST_RENDER_BEFORE, 10);

                    foreach ($sectionCards as $card) {
                        $_auth = $card->getAcl();
                        if (!empty($_auth) && !AclMain::aclCheckCore($_auth[0], $_auth[1])) {
                            continue;
                        }

                        $btnLabel = false;
                        if ($card->canAdd()) {
                            $btnLabel = 'Add';
                        } elseif ($card->canEdit()) {
                            $btnLabel = 'Edit';
                        }

                        $viewArgs = [
                            'title' => $card->getTitle(),
                            'id' => $card->getIdentifier(),
                            'initiallyCollapsed' => $card->isInitiallyCollapsed(),
                            'card_bg_color' => $card->getBackgroundColorClass(),
                            'card_text_color' => $card->getTextColorClass(),
                            'forceAlwaysOpen' => !$card->canCollapse(),
                            'btnLabel' => $btnLabel,
                            'btnLink' => 'test',
                        ];

                        echo $t->render($card->getTemplateFile(), array_merge($viewArgs, $card->getTemplateVariables()));
                    }

                    if (AclMain::aclCheckCore('patients', 'notes')) :
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('note'), CardRenderEvent::EVENT_HANDLE);
                        // Notes expand collapse widget
                        $id = "pnotes_ps_expand";
                        $viewArgs = [
                            'title' => xl("Messages"),
                            'id' => $id,
                            'btnLabel' => "Edit",
                            'btnLink' => "pnotes_full.php?form_active=1",
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'linkMethod' => "html",
                            'bodyClass' => "notab",
                            'auth' => AclMain::aclCheckCore('patients', 'notes', '', 'write'),
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                    endif; // end if notes authorized

                    if (AclMain::aclCheckCore('patients', 'reminder') && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) :
                        // patient reminders collapse widget
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('reminder'), CardRenderEvent::EVENT_HANDLE);
                        $id = "patient_reminders_ps_expand";
                        $viewArgs = [
                            'title' => xl('Patient Reminders'),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Edit',
                            'btnLink' => '../reminder/patient_reminders.php?mode=simple&patient_id=' . attr_url($pid),
                            'linkMethod' => 'html',
                            'bodyClass' => 'notab collapse show',
                            'auth' => AclMain::aclCheckCore('patients', 'reminder', '', 'write'),
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        if (!in_array('card_patientreminders', $hiddenCards)) {
                            echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                        }
                    endif; //end if prw is activated

                    if (AclMain::aclCheckCore('patients', 'disclosure')) :
                        $authWriteDisclosure = AclMain::aclCheckCore('patients', 'disclosure', '', 'write');
                        $authAddonlyDisclosure = AclMain::aclCheckCore('patients', 'disclosure', '', 'addonly');
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('disclosure'), CardRenderEvent::EVENT_HANDLE);
                        // disclosures expand collapse widget
                        $id = "disclosures_ps_expand";
                        $viewArgs = [
                            'title' => xl('Disclosures'),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Edit',
                            'btnLink' => 'disclosure_full.php',
                            'linkMethod' => 'html',
                            'bodyClass' => 'notab collapse show',
                            'auth' => ($authWriteDisclosure || $authAddonlyDisclosure),
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        if (!in_array('card_disclosure', $hiddenCards)) {
                            echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                        }
                    endif; // end if disclosures authorized

                    if ($GLOBALS['amendments'] && AclMain::aclCheckCore('patients', 'amendment')) :
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('amendment'), CardRenderEvent::EVENT_HANDLE);
                        // Amendments widget
                        $sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
                        $result = sqlStatement($sql, [$pid]);
                        $amendments = [];
                        while ($row = sqlFetchArray($result)) {
                            $amendments[] = $row;
                        }

                        $id = "amendments_ps_expand";
                        $viewArgs = [
                            'title' => xl('Amendments'),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Edit',
                            'btnLink' => $GLOBALS['webroot'] . "/interface/patient_file/summary/list_amendments.php?id=" . attr_url($pid),
                            'btnCLass' => '',
                            'linkMethod' => 'html',
                            'bodyClass' => 'notab collapse show',
                            'auth' => AclMain::aclCheckCore('patients', 'amendment', '', ['write', 'addonly']),
                            'amendments' => $amendments,
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        if (!in_array('card_amendments', $hiddenCards)) {
                            echo $twig->getTwig()->render('patient/card/amendments.html.twig', $viewArgs);
                        }
                    endif; // end amendments authorized

                    if (AclMain::aclCheckCore('patients', 'lab')) :
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('lab'), CardRenderEvent::EVENT_HANDLE);
                        // labdata expand collapse widget
                        // check to see if any labdata exist
                        $spruch = "SELECT procedure_report.date_collected AS date
                            FROM procedure_report
                            JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id
                            WHERE procedure_order.patient_id = ?
                            ORDER BY procedure_report.date_collected DESC";
                        $existLabdata = sqlQuery($spruch, array($pid));
                        $widgetAuth = ($existLabdata) ? true : false;

                        $id = "labdata_ps_expand";
                        $viewArgs = [
                            'title' => xl('Labs'),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Trend',
                            'btnLink' => "../summary/labdata.php",
                            'linkMethod' => 'html',
                            'bodyClass' => 'collapse show',
                            'auth' => $widgetAuth,
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        if (!in_array('card_lab', $hiddenCards)) {
                            echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                        }
                    endif; // end labs authorized

                    if ($vitals_is_registered && AclMain::aclCheckCore('patients', 'med')) :
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('vital_sign'), CardRenderEvent::EVENT_HANDLE);
                        // vitals expand collapse widget
                        // check to see if any vitals exist
                        $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid));
                        $widgetAuth = ($existVitals) ? true : false;

                        $id = "vitals_ps_expand";
                        $viewArgs = [
                            'title' => xl('Vitals'),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Trend',
                            'btnLink' => "../encounter/trend_form.php?formname=vitals&context=dashboard",
                            'linkMethod' => 'html',
                            'bodyClass' => 'collapse show',
                            'auth' => $widgetAuth,
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        if (!in_array('card_vitals', $hiddenCards)) {
                            echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                        }
                    endif; // end vitals

                    // if anyone wants to render anything after the patient demographic list
                    $GLOBALS["kernel"]->getEventDispatcher()->dispatch(new RenderEvent($pid), RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, 10);

                    // This generates a section similar to Vitals for each LBF form that
                    // supports charting.  The form ID is used as the "widget label".
                    $gfres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec
                        FROM layout_group_properties
                        WHERE grp_form_id LIKE 'LBF%'
                        AND grp_group_id = ''
                        AND grp_repeats > 0
                        AND grp_activity = 1
                        ORDER BY grp_seq, grp_title");

                    while ($gfrow = sqlFetchArray($gfres)) :
                        // $jobj = json_decode($gfrow['notes'], true);
                        $LBF_ACO = empty($gfrow['grp_aco_spec']) ? false : explode('|', $gfrow['grp_aco_spec']);
                        if ($LBF_ACO && !AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1])) {
                            continue;
                        }

                        // vitals expand collapse widget
                        $widgetAuth = false;
                        if (!$LBF_ACO || AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1], '', 'write')) {
                            // check to see if any instances exist for this patient
                            $existVitals = sqlQuery("SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0", [$pid, $vitals_form_id]);
                            $widgetAuth = $existVitals;
                        }

                        $dispatchResult = $ed->dispatch(new CardRenderEvent($gfrow['title']), CardRenderEvent::EVENT_HANDLE);
                        $viewArgs = [
                            'title' => xl($gfrow['title']),
                            'id' => $vitals_form_id,
                            'initiallyCollapsed' => (getUserSetting($vitals_form_id) == 0) ? true : false,
                            'btnLabel' => 'Trend',
                            'btnLink' => "../encounter/trend_form.php?formname=vitals&context=dashboard",
                            'linkMethod' => 'html',
                            'bodyClass' => 'notab collapse show',
                            'auth' => $widgetAuth,
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                    endwhile; // end while
                    ?>
                </div> <!-- end left column div -->
                <div class="col-md-4">
                    <!-- start right column div -->
                    <?php
                    $_extAccess = [
                        $GLOBALS['portal_onsite_two_enable'],
                        $GLOBALS['rest_fhir_api'],
                        $GLOBALS['rest_api'],
                        $GLOBALS['rest_portal_api'],
                    ];
                    foreach ($_extAccess as $_) {
                        if ($_) {
                            $portalCard = new PortalCard($GLOBALS);
                            break;
                        }
                    }

                    $sectionRenderEvents = $ed->dispatch(new SectionEvent('secondary'), SectionEvent::EVENT_HANDLE);
                    $sectionCards = $sectionRenderEvents->getCards();

                    $t = $twig->getTwig();

                    foreach ($sectionCards as $card) {
                        $_auth = $card->getAcl();
                        $auth = AclMain::aclCheckCore($_auth[0], $_auth[1]);
                        if (!$auth) {
                            continue;
                        }

                        $btnLabel = false;
                        if ($card->canAdd()) {
                            $btnLabel = 'Add';
                        } elseif ($card->canEdit()) {
                            $btnLabel = 'Edit';
                        }

                        $viewArgs = [
                            'card' => $card,
                            'title' => $card->getTitle(),
                            'id' => $card->getIdentifier() . "_expand",
                            'auth' => $auth,
                            'linkMethod' => 'html',
                            'initiallyCollapsed' => $card->isInitiallyCollapsed(),
                            'card_bg_color' => $card->getBackgroundColorClass(),
                            'card_text_color' => $card->getTextColorClass(),
                            'forceAlwaysOpen' => !$card->canCollapse(),
                            'btnLabel' => $btnLabel,
                            'btnLink' => "javascript:$('#patient_portal').collapse('toggle')",
                        ];

                        echo $t->render($card->getTemplateFile(), array_merge($viewArgs, $card->getTemplateVariables()));
                    }

                    if ($GLOBALS['erx_enable']) :
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('demographics'), CardRenderEvent::EVENT_HANDLE);
                        echo $twig->getTwig()->render('patient/partials/erx.html.twig', [
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ]);
                    endif;

                    // If there is an ID Card or any Photos show the widget
                    $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
                    if ($photos or $idcard_doc_id) {
                        $id = "photos_ps_expand";
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('patient_photo'), CardRenderEvent::EVENT_HANDLE);
                        $viewArgs = [
                            'title' => xl("ID Card / Photos"),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => 'Edit',
                            'linkMethod' => "javascript",
                            'bodyClass' => 'collapse show',
                            'auth' => false,
                            'patientIDCategoryID' => $GLOBALS['patient_id_category_name'],
                            'patientPhotoCategoryName' => $GLOBALS['patient_photo_category_name'],
                            'photos' => $photos,
                            'idCardDocID' => $idcard_doc_id,
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        echo $twig->getTwig()->render('patient/card/photo.html.twig', $viewArgs);
                    }

                    // Advance Directives
                    if ($GLOBALS['advance_directives_warning']) {
                        // advance directives expand collapse widget

                        $counterFlag = false; //flag to record whether any categories contain ad records
                        $query = "SELECT id FROM categories WHERE name='Advance Directive'";
                        $myrow2 = sqlQuery($query);
                        $advDirArr = [];
                        if ($myrow2) {
                            $parentId = $myrow2['id'];
                            $query = "SELECT id, name FROM categories WHERE parent=?";
                            $resNew1 = sqlStatement($query, array($parentId));
                            while ($myrows3 = sqlFetchArray($resNew1)) {
                                $categoryId = $myrows3['id'];
                                $nameDoc = $myrows3['name'];
                                $query = "SELECT documents.date, documents.id
                                    FROM documents
                                    INNER JOIN categories_to_documents ON categories_to_documents.document_id=documents.id
                                    WHERE categories_to_documents.category_id=?
                                    AND documents.foreign_id=?
                                    AND documents.deleted = 0
                                    ORDER BY documents.date DESC";
                                $resNew2 = sqlStatement($query, array($categoryId, $pid));
                                $limitCounter = 0; // limit to one entry per category
                                while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                                    $dateTimeDoc = $myrows4['date'];
                                    // remove time from datetime stamp
                                    $tempParse = explode(" ", $dateTimeDoc);
                                    $dateDoc = $tempParse[0];
                                    $idDoc = $myrows4['id'];
                                    $tmp = [
                                        'pid' => $pid,
                                        'docID' => $idDoc,
                                        'docName' => $nameDoc,
                                        'docDate' => $dateDoc,
                                    ];
                                    $advDirArr[] = $tmp;
                                    $limitCounter = $limitCounter + 1;
                                    $counterFlag = true;
                                }
                            }

                            $id = "adv_directives_ps_expand";

                            $dispatchResult = $ed->dispatch(new CardRenderEvent('advance_directive'), CardRenderEvent::EVENT_HANDLE);
                            $viewArgs = [
                                'title' => xl("Advance Directives"),
                                'id' => $id,
                                'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                                'btnLabel' => 'Edit',
                                'linkMethod' => "javascript",
                                'btnLink' => "return advdirconfigure();",
                                'bodyClass' => 'collapse show',
                                'auth' => true,
                                'advDirArr' => $advDirArr,
                                'counterFlag' => $counterFlag,
                                'prependedInjection' => $dispatchResult->getPrependedInjection(),
                                'appendedInjection' => $dispatchResult->getAppendedInjection(),
                            ];
                            echo $twig->getTwig()->render('patient/card/adv_dir.html.twig', $viewArgs);
                        }
                    }  // close advanced dir block

                    // Show Clinical Reminders for any user that has rules that are permitted.
                    $clin_rem_check = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
                    $cdr = $GLOBALS['enable_cdr'];
                    $cdr_crw = $GLOBALS['enable_cdr_crw'];
                    if (!empty($clin_rem_check) && $cdr && $cdr_crw && AclMain::aclCheckCore('patients', 'alert')) {
                        // clinical summary expand collapse widget
                        $id = "clinical_reminders_ps_expand";
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('clinical_reminders'), CardRenderEvent::EVENT_HANDLE);
                        $viewArgs = [
                            'title' => xl("Clinical Reminders"),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLabel' => "Edit",
                            'btnLink' => "../reminder/clinical_reminders.php?patient_id=" . attr_url($pid),
                            'linkMethod' => "html",
                            'auth' => AclMain::aclCheckCore('patients', 'alert', '', 'write'),
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ];
                        echo $twig->getTwig()->render('patient/card/loader.html.twig', $viewArgs);
                    } // end if crw

                    $displayAppts = false;
                    $displayRecurrAppts = false;
                    $displayPastAppts = false;

                    // Show current and upcoming appointments.
                    // Recurring appointment support and Appointment Display Sets
                    // added to Appointments by Ian Jardine ( epsdky ).
                    if (isset($pid) && !$GLOBALS['disable_calendar'] && AclMain::aclCheckCore('patients', 'appt')) {
                        $displayAppts = true;
                        $current_date2 = date('Y-m-d');
                        $events = array();
                        $apptNum = (int)$GLOBALS['number_of_appts_to_show'];
                        $apptNum2 = ($apptNum != 0) ? abs($apptNum) : 10;

                        $mode1 = !$GLOBALS['appt_display_sets_option'];
                        $colorSet1 = $GLOBALS['appt_display_sets_color_1'];
                        $colorSet2 = $GLOBALS['appt_display_sets_color_2'];
                        $colorSet3 = $GLOBALS['appt_display_sets_color_3'];
                        $colorSet4 = $GLOBALS['appt_display_sets_color_4'];
                        $extraAppts = ($mode1) ? 1 : 6;
                        $extraApptDate = '';

                        $past_appts = [];
                        $recallArr = [];

                        $events = fetchNextXAppts($current_date2, $pid, $apptNum2 + $extraAppts, true);

                        if ($events) {
                            $selectNum = 0;
                            $apptNumber = count($events);
                            //
                            if ($apptNumber <= $apptNum2) {
                                $extraApptDate = '';
                                //
                            } elseif ($mode1 && $apptNumber == $apptNum2 + 1) {
                                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                                array_pop($events);
                                --$apptNumber;
                                $selectNum = 1;
                                //
                            } elseif ($apptNumber == $apptNum2 + 6) {
                                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                                array_pop($events);
                                --$apptNumber;
                                $selectNum = 2;
                                //
                            } else { // mode 2 - $apptNum2 < $apptNumber < $apptNum2 + 6
                                $extraApptDate = '';
                                $selectNum = 2;
                                //
                            }

                            $limitApptIndx = $apptNum2 - 1;
                            $limitApptDate = $events[$limitApptIndx]['pc_eventDate'] ?? '';

                            switch ($selectNum) {
                                case 2:
                                    $lastApptIndx = $apptNumber - 1;
                                    $thisNumber = $lastApptIndx - $limitApptIndx;
                                    for ($i = 1; $i <= $thisNumber; ++$i) {
                                        if ($events[$limitApptIndx + $i]['pc_eventDate'] != $limitApptDate) {
                                            $extraApptDate = $events[$limitApptIndx + $i]['pc_eventDate'];
                                            $events = array_slice($events, 0, $limitApptIndx + $i);
                                            break;
                                        }
                                    }
                                // Break in the loop to improve performance
                                case 1:
                                    $firstApptIndx = 0;
                                    for ($i = 1; $i <= $limitApptIndx; ++$i) {
                                        if ($events[$limitApptIndx - $i]['pc_eventDate'] != $limitApptDate) {
                                            $firstApptIndx = $apptNum2 - $i;
                                            break;
                                        }
                                    }
                                // Break in the loop to improve performance
                            }

                            if ($extraApptDate) {
                                if ($extraApptDate != $limitApptDate) {
                                    $apptStyle2 = " style='background-color:" . attr($colorSet3) . ";'";
                                } else {
                                    $apptStyle2 = " style='background-color:" . attr($colorSet4) . ";'";
                                }
                            }
                        }

                        $count = 0;
                        $toggleSet = true;
                        $priorDate = "";
                        $therapyGroupCategories = array();
                        $query = sqlStatement("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_cattype = 3 AND pc_active = 1");
                        while ($result = sqlFetchArray($query)) {
                            $therapyGroupCategories[] = $result['pc_catid'];
                        }

                        // Build the UI Loop
                        $appts = [];
                        foreach ($events as $row) {
                            $count++;
                            $dayname = date("D", strtotime($row['pc_eventDate']));
                            $displayMeridiem = ($GLOBALS['time_display_format'] == 0) ? "" : "am";
                            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
                            $dispmin = substr($row['pc_startTime'], 3, 2);
                            if ($disphour >= 12 && $GLOBALS['time_display_format'] == 1) {
                                $displayMeridiem = "pm";
                                if ($disphour > 12) {
                                    $disphour -= 12;
                                }
                            }

                            // Note the translaution occurs here instead of in teh Twig file for some specific concatenation needs
                            $etitle = xl('(Click to edit)');
                            if ($row['pc_hometext'] != "") {
                                $etitle = xl('Comments') . ": " . ($row['pc_hometext']) . "\r\n" . $etitle;
                            }

                            $row['etitle'] = $etitle;

                            if ($extraApptDate && $count > $firstApptIndx) {
                                $apptStyle = $apptStyle2;
                            } else {
                                if ($row['pc_eventDate'] != $priorDate) {
                                    $priorDate = $row['pc_eventDate'];
                                    $toggleSet = !$toggleSet;
                                }

                                $bgColor = ($toggleSet) ? $colorSet2 : $colorSet1;
                            }

                            $row['pc_eventTime'] = sprintf("%02d", $disphour) . ":{$dispmin}";
                            $row['pc_status'] = generate_display_field(array('data_type' => '1', 'list_id' => 'apptstat'), $row['pc_apptstatus']);
                            if ($row['pc_status'] == 'None') {
                                $row['pc_status'] = 'Scheduled';
                            }

                            if (in_array($row['pc_catid'], $therapyGroupCategories)) {
                                $row['groupName'] = getGroup($row['pc_gid'])['group_name'];
                            }

                            $row['uname'] = text($row['ufname'] . " " . $row['ulname']);
                            $row['bgColor'] = $bgColor;
                            $row['dayName'] = $dayname;
                            $row['displayMeridiem'] = $displayMeridiem;
                            $row['jsEvent'] = attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']);
                            $appts[] = $row;
                        }

                        if ($resNotNull) {
                            // Show Recall if one exists
                            $query = sqlStatement("SELECT * FROM `medex_recalls` WHERE `r_pid` = ?", [(int)$pid]);
                            $recallArr = [];
                            $count2 = 0;
                            while ($result2 = sqlFetchArray($query)) {
                                //tabYourIt('recall', 'main/messages/messages.php?go=' + choice);
                                //parent.left_nav.loadFrame('1', tabNAME, url);
                                $recallArr[] = [
                                    'date' => $result2['r_eventDate'],
                                    'reason' => $result2['r_reason'],
                                ];
                                $count2++;
                            }
                            $id = "recall_ps_expand";
                            $dispatchResult = $ed->dispatch(new CardRenderEvent('recall'), CardRenderEvent::EVENT_HANDLE);
                            echo $twig->getTwig()->render('patient/card/recall.html.twig', [
                                'title' => xl('Recall'),
                                'id' => $id,
                                'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                                'recalls' => $recallArr,
                                'recallsAvailable' => ($count < 1 && empty($count2)) ? false : true,
                                'prependedInjection' => $dispatchResult->getPrependedInjection(),
                                'appendedInjection' => $dispatchResult->getAppendedInjection(),
                            ]);
                        }
                    } // End of Appointments Widget.

                    /* Widget that shows recurrences for appointments. */
                    $recurr = [];
                    if (isset($pid) && !$GLOBALS['disable_calendar'] && $GLOBALS['appt_recurrences_widget'] && AclMain::aclCheckCore('patients', 'appt')) {
                        $displayRecurrAppts = true;
                        $count = 0;
                        $toggleSet = true;
                        $priorDate = "";

                        //Fetch patient's recurrences. Function returns array with recurrence appointments' category, recurrence pattern (interpreted), and end date.
                        $recurrences = fetchRecurrences($pid);
                        if (!empty($recurrences)) {
                            foreach ($recurrences as $row) {
                                if (!recurrence_is_current($row['pc_endDate'])) {
                                    continue;
                                }

                                if (ends_in_a_week($row['pc_endDate'])) {
                                    $row['close_to_end'] = true;
                                }
                                $recurr[] = $row;
                            }
                        }
                    }
                    /* End of recurrence widget */

                    // Show PAST appointments.
                    // added by Terry Hill to allow reverse sorting of the appointments
                    $direction = '1';
                    if ($GLOBALS['num_past_appointments_to_show'] < 0) {
                        $direction = '2';
                        ($showpast = -1 * $GLOBALS['num_past_appointments_to_show']);
                    } else {
                        $showpast = $GLOBALS['num_past_appointments_to_show'];
                    }

                    if (isset($pid) && !$GLOBALS['disable_calendar'] && $showpast > 0 && AclMain::aclCheckCore('patients', 'appt')) {
                        $displayPastAppts = true;

                        $pastAppts = fetchXPastAppts($pid, $showpast, $direction); // This line added by epsdky

                        $count = 0;

                        foreach ($pastAppts as $row) {
                            $count++;
                            $dayname = date("D", strtotime($row['pc_eventDate']));
                            $displayMeridiem = ($GLOBALS['time_display_format'] == 0) ? "" : "am";
                            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
                            $dispmin = substr($row['pc_startTime'], 3, 2);
                            if ($disphour >= 12) {
                                $displayMeridiem = "pm";
                                if ($disphour > 12 && $GLOBALS['time_display_format'] == 1) {
                                    $disphour -= 12;
                                }
                            }

                            $petitle = xl('(Click to edit)');
                            if ($row['pc_hometext'] != "") {
                                $petitle = xl('Comments') . ": " . ($row['pc_hometext']) . "\r\n" . $petitle;
                            }
                            $row['etitle'] = $petitle;

                            $row['pc_status'] = generate_display_field(array('data_type' => '1', 'list_id' => 'apptstat'), $row['pc_apptstatus']);

                            $row['dayName'] = $dayname;
                            $row['displayMeridiem'] = $displayMeridiem;
                            $row['pc_eventTime'] = sprintf("%02d", $disphour) . ":{$dispmin}";
                            $row['uname'] = text($row['ufname'] . " " . $row['ulname']);
                            $row['jsEvent'] = attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']);
                            $past_appts[] = $row;
                        }
                    }
                    // END of past appointments

                    // Display the Appt card
                    $id = "appointments_ps_expand";
                    $dispatchResult = $ed->dispatch(new CardRenderEvent('appointment'), CardRenderEvent::EVENT_HANDLE);
                    echo $twig->getTwig()->render('patient/card/appointments.html.twig', [
                        'title' => xl("Appointments"),
                        'id' => $id,
                        'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                        'btnLabel' => "Add",
                        'btnLink' => "return newEvt()",
                        'linkMethod' => "javascript",
                        'appts' => $appts,
                        'recurrAppts' => $recurr,
                        'pastAppts' => $past_appts,
                        'displayAppts' => $displayAppts,
                        'displayRecurrAppts' => $displayRecurrAppts,
                        'displayPastAppts' => $displayPastAppts,
                        'extraApptDate' => $extraApptDate,
                        'therapyGroupCategories' => $therapyGroupCategories,
                        'auth' => $resNotNull && (AclMain::aclCheckCore('patients', 'appt', '', 'write') || AclMain::aclCheckCore('patients', 'appt', '', 'addonly')),
                        'resNotNull' => $resNotNull,
                        'prependedInjection' => $dispatchResult->getPrependedInjection(),
                        'appendedInjection' => $dispatchResult->getAppendedInjection(),
                    ]);

                    echo "<div id=\"stats_div\"></div>";

                    // TRACK ANYTHING
                    // Determine if track_anything form is in use for this site.
                    $tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE directory = 'track_anything' AND state = 1");
                    $track_is_registered = $tmp['count'];
                    if ($track_is_registered) {
                        $spruch = "SELECT id FROM forms WHERE pid = ? AND formdir = ?";
                        $existTracks = sqlQuery($spruch, array($pid, "track_anything"));
                        $id = "track_anything_ps_expand";
                        $dispatchResult = $ed->dispatch(new CardRenderEvent('track_anything'), CardRenderEvent::EVENT_HANDLE);
                        echo $twig->getTwig()->render('patient/card/loader.html.twig', [
                            'title' => xl("Tracks"),
                            'id' => $id,
                            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
                            'btnLink' => "../../forms/track_anything/create.php",
                            'linkMethod' => "html",
                            'prependedInjection' => $dispatchResult->getPrependedInjection(),
                            'appendedInjection' => $dispatchResult->getAppendedInjection(),
                        ]);
                    }  // end track_anything

                    if ($thisauth) :
                        echo $twig->getTwig()->render('patient/partials/delete.html.twig', [
                            'isAdmin' => AclMain::aclCheckCore('admin', 'super'),
                            'allowPatientDelete' => $GLOBALS['allow_pat_delete'],
                            'csrf' => CsrfUtils::collectCsrfToken(),
                            'pid' => $pid
                        ]);
                    endif;
                    ?>
                </div> <!-- end right column div -->
            </div> <!-- end div.main > row:first  -->
        </div> <!-- end main content div -->
    </div><!-- end container div -->
    <?php $oemr_ui->oeBelowContainerDiv(); ?>
    <script>
        // Array of skip conditions for the checkSkipConditions() function.
        var skipArray = [
            <?php echo($condition_str ?? ''); ?>
        ];
        checkSkipConditions();


        var isPost = <?php echo js_escape($showEligibility ?? false); ?>;
        var listId = '#' + <?php echo js_escape($list_id); ?>;
        $(function () {
            $(listId).addClass("active");
            if (isPost === true) {
                $("#eligibility").click();
                $("#eligibility").get(0).scrollIntoView();
            }
        });
    </script>
</body>
<?php $ed->dispatch(new RenderEvent($pid), RenderEvent::EVENT_RENDER_POST_PAGELOAD, 10); ?>
</html>
