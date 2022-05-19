<?php

/**
 * stats.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/sql.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$twigContainer = new TwigContainer(null, $kernel);
$t = $twigContainer->getTwig();

/**
 * Return an array of list data for a given issue type and patient
 *
 * @var $pid string Patient ID
 * @var $type string Issue Type
 * @return
 */
function getListData($pid, $type)
{
    $sqlArr = [
        "SELECT * FROM lists WHERE pid = ? AND type = ? AND",
        dateEmptySql('enddate')
    ];

    if ($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $type == 'medication') {
        $sqlArr[] = "and erx_uploaded != '1'";
    }

    if ($GLOBALS['erx_enable'] && $GLOBALS['erx_allergy_display'] && $type == 'allergy') {
        $sqlArr[] = "and erx_uploaded != '1'";
    }

    $sqlArr[] = "ORDER BY begdate";

    $sql = implode(" ", $sqlArr);
    $res = sqlStatement($sql, [$pid, $type]);
    $list = [];

    while ($row = sqlFetchArray($res)) {
        if (!$row['enddate'] && !$row['returndate']) {
            $rowclass = "noend_noreturn";
        } elseif (!$row['enddate'] && $row['returndate']) {
            $rowclass = "noend";
        } elseif ($row['enddate'] && !$row['returndate']) {
            $rowclass = "noreturn";
        }

        if ($type == "allergy") {
            $reaction = "";
            if (!empty($row['reaction'])) {
                $reaction = getListItemTitle("reaction", $row['reaction']);
                $row['reactionTitle'] = $reaction;
            }
            if (!empty($row['severity_al'])) {
                $severity = getListItemTitle("severity_ccda", $row['severity_al']);
                // Collapse the SNOMED-CT 272141005 List to 3 groups
                // Not great to hard code this here, this should be abstracted
                // to a better place to handle more comprehensive, centralized class
                // @todo Find a better home for this
                if (in_array($row['severity_al'], ['severe', 'life_threatening_severity', 'fatal'])) {
                    $row['critical'] = true;
                }
                $row['severity'] = $severity;
            }
        }

        $list[] = $row;
    }

    return $list;
}

function getPrescriptions($pid)
{
    $sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND active = '1'";
    $res = sqlStatement($sql, [$pid]);
    $rx = [];
    while ($row = sqlFetchArray($res)) {
        $row['unit'] = generate_display_field(['data_type' => '1', 'list_id' => 'drug_units'], $row['unit']);
        $row['form'] = generate_display_field(['data_type' => '1', 'list_id' => 'drug_form'], $row['form']);
        $row['route'] = generate_display_field(['data_type' => '1', 'list_id' => 'drug_route'], $row['route']);
        $row['interval'] = generate_display_field(['data_type' => '1', 'list_id' => 'drug_interval'], $row['interval']);

        $rx[] = $row;
    }
    return $rx;
}
?>

<script>
    if(typeof load_location === 'undefined') {
        function load_location(location) {
            top.restoreSession();
            document.location = location;
        }
    }
</script>

<?php
$erx_upload_complete = 0;
$old_key = "";
$display_current_medications_below = 1;

// Process Medical Problems, Allergies, and Medications
foreach ($ISSUE_TYPES as $key => $arr) {
    // Skip if user has no access to this issue type.
    if (!AclMain::aclCheckIssue($key)) {
        continue;
    }

    if ($old_key == "medication" && $GLOBALS['erx_enable'] && $erx_upload_complete == 1) {
        $display_current_medications_below = 0;

        if ($GLOBALS['erx_enable']) {
            $res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id=? AND active='1'", [$pid]);
            $list = [];
            $rxArr = [];
            while ($row = sqlFetchArray($res)) {
                $row['unit'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_units'), $row['unit']);
                $row['form'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_form'), $row['form']);
                $row['route'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_route'), $row['route']);
                $row['interval'] = generate_display_field(array('data_type' => '1', 'list_id' => 'drug_interval'), $row['interval']);
                $unit = ($row['size'] > 0) ? text($row['size']) . " " . $row['unit'] : "";
                $row['unit'] = $unit;
                $rxArr[] = $row;
            }

            $id = "current_prescriptions";
            $viewArgs = [
                'title' => xl('Current Medications'),
                'id' => $id,
                'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
                'auth' => false,
                'rxList' => $rxArr,
            ];

            echo $t->render('patient/card/erx.html.twig', $viewArgs);

            $old_key = '';
        }
    }

    $issues = getListData($pid, $key);

    //
    if (count($issues) > 0 || $arr[4] == 1) {
        $old_key = $key;
        if ($GLOBALS['erx_enable'] && $key = "medication") {
            $sqlUploadedArr = [
                "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND",
                dateEmptySql('enddate'),
                "AND erx_uploaded != '1' ORDER BY begdate",
            ];
            $sqlUploaded = implode(" ", $sqlUploadedArr);
            $resUploaded = sqlStatement($sqlUploaded, [$pid]);
            if (sqlNumRows($resUploaded) == 0) {
                $erx_upload_complete = 1;
                continue;
            }
        }

        $listData = getListData($pid, $key);
        $id = $key . "_ps_expand";
        $viewArgs = [
            'title' => xl($arr[0]),
            'id' => $id,
            'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
            'linkMethod' => "javascript",
            'list' => $listData,
            'auth' => AclMain::aclCheckIssue($key, '', ['write', 'addonly'])
        ];

        $btnLinkBase = "return load_location('{$GLOBALS['webroot']}/interface/__page__')";
        if (in_array($key, ["allergy", "medication"]) && $GLOBALS["erx_enable"]) {
            $viewArgs['btnLabel'] = "Add";
            $btnLinkPage = "eRx.php?page=medentry";
        } else {
            $viewArgs['btnLabel'] = "Edit";
            $btnLinkPage = "patient_file/summary/stats_full.php?active=all&category=" . attr_url($key);
        }
        $viewArgs['btnLink'] = str_replace("__page__", $btnLinkPage, $btnLinkBase);

        if (count($listData) == 0) {
            $viewArgs['listTouched'] = (getListTouch($pid, $key)) ? true : false;
        }

        echo $t->render('patient/card/medical_problems.html.twig', $viewArgs);
    }
}

// Render Cards for 2 specific forms
foreach (['treatment_protocols', 'injury_log'] as $formname) {
    if (sqlNumRows(sqlStatement("SHOW TABLES LIKE 'form_{$formname}'")) > 0) {
        $sql = "SELECT tp.id, tp.value
            FROM forms, form_{$formname} AS tp
            WHERE forms.pid = ?
            AND forms.formdir = ?
            AND tp.id = forms.form_id
            AND tp.rownbr = -1
            AND tp.colnbr = -1
            AND tp.value LIKE '0%'
            ORDER BY tp.value DESC";
        $dres = sqlStatement($sql, [$pid, $formname]);
        if (sqlNumRows($dres) > 0 && $need_head) {
            $formRows = [];
            while ($row = sqlFetchArray($dres)) {
                list($completed, $start_date, $template_name) = explode('|', $row['value'], 3);
                $formRows['startDate'] = $start_date;
                $formRws['templateName'] = $template_name;
                $formRows['id'] = $row['id'];
            }

            $id = "injury_log";
            echo $t->render('patient/card/tp_il.html.twig', [
                'title' => xl("Injury Log"),
                'id' => $id,
                'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
                'formName' => $formname,
                'formRows' => $formRows,
            ]);
        }
    }
}

// Render the Immunizations card if turned on
if (!$GLOBALS['disable_immunizations'] && !$GLOBALS['weight_loss_clinic']) :
    $sql = "SELECT i1.id AS id, i1.immunization_id AS immunization_id, i1.cvx_code AS cvx_code, c.code_text_short AS cvx_text,
                IF(i1.administered_date, concat(i1.administered_date,' - ',c.code_text_short),
                IF(i1.note,substring(i1.note,1,20),c.code_text_short)) AS immunization_data
            FROM immunizations i1
            LEFT JOIN code_types ct ON ct.ct_key = 'CVX'
            LEFT JOIN codes c ON c.code_type = ct.ct_id AND i1.cvx_code = c.code
            WHERE i1.patient_id = ?
                AND i1.added_erroneously = 0
            ORDER BY i1.administered_date DESC";
    $result = sqlStatement($sql, [$pid]);

    $imxList = [];
    while ($row = sqlFetchArray($result)) {
        $row['immunization_data'] = text($row['immunization_data']);

        // Figure out which name to use (ie. from cvx list or from the custom list)
        if ($GLOBALS['use_custom_immun_list']) {
            $row['field'] = generate_display_field(array('data_type' => '1', 'list_id' => 'immunizations'), $row['immunization_id']);
        } else {
            if (!(empty($row['cvx_text']))) {
                $row['field'] = htmlspecialchars(xl($row['cvx_text']), ENT_NOQUOTES);
            } else {
                $row['field'] = generate_display_field(array('data_type' => '1', 'list_id' => 'immunizations'), $row['immunization_id']);
            }
        }

        $row['url'] = attr_js("immunizations.php?mode=edit&id=" . urlencode($row['id']) . "&csrf_token_form=" . urlencode(CsrfUtils::collectCsrfToken()));
        $imxList[] = $row;
    }
    $id = "immunizations_ps_expand";
    echo $t->render('patient/card/immunizations.html.twig', [
        'title' => xl('Immunizations'),
        'id' => $id,
        'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
        'btnLabel' => 'Edit',
        'btnLink' => 'immunizations.php',
        'linkMethod' => 'html',
        'auth' => true,
        'imx' => $imxList,
    ]);
endif; // End immunizations

// Render the Prescriptions card if turned on
if (!$GLOBALS['disable_prescriptions'] && AclMain::aclCheckCore('patients', 'rx')) :
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
            'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
            'auth' => false,
            'rxList' => $rxArr,
        ];

        echo $t->render('patient/card/erx.html.twig', $viewArgs);
    }

    $id = "prescriptions_ps_expand";
    $viewArgs = [
        'title' => xl("Prescriptions"),
        'id' => $id,
        'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
        'linkMethod' => "html",
        'btnLabel' => "Edit",
        'auth' => AclMain::aclCheckCore('patients', 'rx', '', ['write', 'addonly']),
    ];

    if ($GLOBALS['erx_enable']) {
        $viewArgs['title'] = 'Prescription History';
        $viewArgs['btnLabel'] = 'Add';
        $viewArgs['btnLink'] = "{$GLOBALS['webroot']}/interface/eRx.php?page=compose";
    } elseif ($GLOBALS['weno_rx_enable']) {
        // weno plus button which opens their iframe
        $viewArgs['weno'] = true;
        $viewArgs['title'] = "WENO ComposeRx";
        $viewArgs['btnLabel'] = 'Add';
        $viewArgs['btnLink'] = "{$GLOBALS['webroot']}/interface/weno/indexrx.php";
        $viewArgs['oemrBtnClass'] = "iframe rx_modal";
        $viewArgs['oemrLinkMethod'] = "javascript";
        $viewArgs['oemrBtnLink'] = "editScripts('{$GLOBALS['webroot']}/controller.php?prescription&list&id=" . attr_url($pid) . "')";
        $viewArgs['oemrBtnIcon'] = "fa-pencil-alt";
    } else {
        $viewArgs['btnLink'] = "editScripts('{$GLOBALS['webroot']}/controller.php?prescription&list&id=" . attr_url($pid) . "')";
        $viewArgs['linkMethod'] = "javascript";
        $viewArgs['btnClass'] = "iframe rx_modal";
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

    echo $t->render('patient/card/rx.html.twig', $viewArgs);
endif;

// Render Old Medications card
if ($erx_upload_complete == 1) {
    $sql = [
        "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND",
        dateEmptySql('enddate'),
        "ORDER BY begdate"
    ];
    $res = sqlStatement(implode(" ", $sql), [$pid]);

    $rxList = [];
    while ($row = sqlFetchArray($res)) {
        $rxList[] = $row;
    }

    $id = "old_medication_ps_expand";
    $viewArgs = [
        'title' => xl('Old Medication'),
        'label' => $id,
        'initiallyCollapsed' => (getUserSetting($id) == 0) ? false : true,
        'btnLabel' => 'Edit',
        'btnLink' => "return load_location(\"${GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=medication\")",
        'linkMethod' => 'javascript',
        'auth' => true,
        'list' => $rxList,
    ];

    echo $t->render('patient/card/medical_problems.html.twig', $viewArgs);
}
?>
