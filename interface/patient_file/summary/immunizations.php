<?php

/**
 * Immunizations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/immunization_helper.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;


if (isset($_GET['mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    /*
     * THIS IS A BUG. IF NEW IMMUN IS ADDED AND USER PRINTS PDF,
     * WHEN BACK IS CLICKED, ANOTHER ITEM GETS ADDED
     */

    if ($_GET['mode'] == "add") {
        $sql = "REPLACE INTO immunizations set
            id = ?,
            uuid = ?,
            administered_date = if(?,?,NULL),
            immunization_id = ?,
            cvx_code = ?,
            manufacturer = ?,
            lot_number = ?,
            administered_by_id = if(?,?,NULL),
            administered_by = if(?,?,NULL),
            education_date = if(?,?,NULL),
            vis_date = if(?,?,NULL),
            note   = ?,
            patient_id   = ?,
            created_by = ?,
            updated_by = ?,
   			create_date = now(),
			amount_administered = ?,
			amount_administered_unit = ?,
			expiration_date = if(?,?,NULL),
			route = ?,
			administration_site = ? ,
            completion_status = ?,
            information_source = ?,
            refusal_reason = ?,
            reason_code = ?,
            reason_description = ?,
            ordering_provider = ?";
        $sqlBindArray = array(
            trim($_GET['id']),
            UuidRegistry::isValidStringUUID($_GET['uuid']) ? UuidRegistry::uuidToBytes($_GET['uuid']) : null,
            trim($_GET['administered_date']), trim($_GET['administered_date']),
            trim($_GET['form_immunization_id'] ?? ''),
            trim($_GET['cvx_code']),
            trim($_GET['manufacturer']),
            trim($_GET['lot_number'] ?? ''),
            trim($_GET['administered_by_id']), trim($_GET['administered_by_id']),
            trim($_GET['administered_by']), trim($_GET['administered_by']),
            trim($_GET['education_date']), trim($_GET['education_date']),
            trim($_GET['vis_date']), trim($_GET['vis_date']),
            trim($_GET['note']),
            $pid,
            $_SESSION['authUserID'],
            $_SESSION['authUserID'],
            trim($_GET['immuniz_amt_adminstrd']),
            trim($_GET['form_drug_units']),
            trim($_GET['immuniz_exp_date']), trim($_GET['immuniz_exp_date']),
            trim($_GET['immuniz_route']),
            trim($_GET['immuniz_admin_ste']),
            trim($_GET['immuniz_completion_status']),
            trim($_GET['immunization_informationsource']),
            trim($_GET['immunization_refusal_reason']),
            trim($_GET['reason_code']),
            trim($_GET['reason_description'] ?? ''),
            trim($_GET['ordered_by_id'])
        );
        $newid = sqlInsert($sql, $sqlBindArray);
        $administered_date = date('Y-m-d H:i');
        $education_date = date('Y-m-d');
        $immunization_id = $cvx_code = $manufacturer = $lot_number = $administered_by_id = $note = $id = $ordered_by_id = "";
        $administered_by = $vis_date = "";
        $newid = $_GET['id'] ? $_GET['id'] : $newid;
        if ($GLOBALS['observation_results_immunization']) {
            saveImmunizationObservationResults($newid, $_GET);
        }
    } elseif ($_GET['mode'] == "delete") {
        // log the event
        EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Immunization id " . $_GET['id'] . " deleted from pid " . $pid);
        // delete the immunization
        $sql = "DELETE FROM immunizations WHERE id =? LIMIT 1";
        sqlStatement($sql, array($_GET['id']));
    } elseif ($_GET['mode'] == "added_error") {
        $sql = "UPDATE immunizations " .
               "SET added_erroneously=? "  .
               "WHERE id=?";
        $sql_arg_array = array(
            ($_GET['isError'] === 'true'),
            $_GET['id']
        );
        sqlStatement($sql, $sql_arg_array);
    } elseif ($_GET['mode'] == "edit") {
        $sql = "select * from immunizations where id = ?";
        $result = sqlQuery($sql, array($_GET['id']));

        $administered_date = new DateTime($result['administered_date']);
        $uuid = null;
        if (isset($result['uuid']) && !UuidRegistry::isEmptyBinaryUUID($result['uuid'])) {
            $uuid = UuidRegistry::uuidToString($result['uuid']);
        }
        $administered_date = $administered_date->format('Y-m-d H:i');

        $immuniz_amt_adminstrd = $result['amount_administered'];
        $drugunitselecteditem = $result['amount_administered_unit'];
        $immunization_id = $result['immunization_id'];
        $immuniz_exp_date = $result['expiration_date'];
        $reason_code = trim($result['reason_code'] ?? '');
        $reason_code_text = trim($result['reason_description'] ?? "");

        $cvx_code = $result['cvx_code'];
        $code_text = '';
        if (!(empty($cvx_code))) {
            $query = "SELECT codes.code_text as `code_text`, codes.code as `code` " .
                     "FROM codes " .
                     "LEFT JOIN code_types on codes.code_type = code_types.ct_id " .
                     "WHERE code_types.ct_key = 'CVX' AND codes.code = ?";
            $result_code_text = sqlQuery($query, array($cvx_code));
            $code_text = $result_code_text['code_text'];
        }

        $manufacturer = $result['manufacturer'];
        $lot_number = $result['lot_number'];
        $administered_by_id = ($result['administered_by_id'] ? $result['administered_by_id'] : 0);
        $ordered_by_id      = ($result['ordering_provider'] ? $result['ordering_provider'] : 0);
        $entered_by_id      = ($result['created_by'] ? $result['created_by'] : 0);

        $administered_by = "";
        if (empty($result['administered_by']) && empty($row['administered_by_id'])) {
            $stmt = "select CONCAT(IFNULL(lname,''), ' ,',IFNULL(fname,'')) as full_name " .
                    "from users where " .
                    "id=?";
            $user_result = sqlQuery($stmt, array($result['administered_by_id']));
            $administered_by = $user_result['full_name'];
        }

        $education_date = $result['education_date'];
        $vis_date = $result['vis_date'];
        $immuniz_route = $result['route'];
        $immuniz_admin_ste = $result['administration_site'];
        $note = $result['note'];
        $isAddedError = $result['added_erroneously'];

        $immuniz_completion_status = $result['completion_status'];
        $immuniz_information_source = $result['information_source'];
        $immuniz_refusal_reason     = $result['refusal_reason'];
        //set id for page
        $id = $_GET['id'];

        $imm_obs_data = getImmunizationObservationResults();
    }
}

$observation_criteria = getImmunizationObservationLists('1');
$observation_criteria_value = getImmunizationObservationLists('2');
// Decide whether using the CVX list or the custom list in list_options
if ($GLOBALS['use_custom_immun_list']) {
    // user forces the use of the custom list
    $useCVX = false;
} else {
    if (!empty($_GET['mode']) && ($_GET['mode'] == "edit")) {
        //depends on if a cvx code is enterer already
        if (empty($cvx_code)) {
            $useCVX = false;
        } else {
            $useCVX = true;
        }
    } else { // $_GET['mode'] == "add"
        $useCVX = true;
    }
}

// set the default sort method for the list of past immunizations
$sortby = $_GET['sortby'] ?? null;
if (!$sortby) {
    $sortby = 'vacc';
}

// set the default value of 'administered_by'
if (empty($administered_by) && empty($administered_by_id)) {
    $stmt = "select CONCAT(IFNULL(lname,''), ' ,',IFNULL(fname,'')) as full_name " .
            " from users where " .
            " id=?";
    $row = sqlQuery($stmt, array($_SESSION['authUserID']));
    $administered_by = $row['full_name'];
}

// get the entered username
if (!empty($entered_by_id)) {
    $stmt = "select CONCAT(IFNULL(lname,''), ' ,',IFNULL(fname,'')) as full_name " .
            " from users where " .
            " id=?";
    $row = sqlQuery($stmt, array($entered_by_id));
    $entered_by = $row['full_name'];
}

if (!empty($_POST['type']) && ($_POST['type'] == 'duplicate_row')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $observation_criteria = getImmunizationObservationLists('1');
    echo json_encode($observation_criteria);
    exit;
}

if (!empty($_POST['type']) && ($_POST['type'] == 'duplicate_row_2')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $observation_criteria_value = getImmunizationObservationLists('2');
    echo json_encode($observation_criteria_value);
    exit;
}

function getImmunizationObservationLists($k)
{
    if ($k == 1) {
        $observation_criteria_res = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity=1 ORDER BY seq, title", array('immunization_observation'));
        for ($iter = 0; $row = sqlFetchArray($observation_criteria_res); $iter++) {
            $observation_criteria[0]['option_id'] = '';
            $observation_criteria[0]['title']     = 'Unassigned';
            $observation_criteria[++$iter] = $row;
        }

        return $observation_criteria;
    } else {
        $observation_criteria_value_res = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity=1 ORDER BY seq, title", array('imm_vac_eligibility_results'));
        for ($iter = 0; $row = sqlFetchArray($observation_criteria_value_res); $iter++) {
            $observation_criteria_value[0]['option_id'] = '';
            $observation_criteria_value[0]['title']     = 'Unassigned';
            $observation_criteria_value[++$iter] = $row;
        }

        return $observation_criteria_value;
    }
}

function getImmunizationObservationResults()
{
    $obs_res_q = "SELECT
                  *
                FROM
                  immunization_observation
                WHERE imo_pid = ?
                  AND imo_im_id = ?";
    $res = sqlStatement($obs_res_q, array($_SESSION["pid"],$_GET['id']));
    $imm_obs_data = [];
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $imm_obs_data[$iter] = $row;
    }

    return $imm_obs_data;
}

function saveImmunizationObservationResults($id, $immunizationdata)
{
    $imm_obs_data = getImmunizationObservationResults();
    if (!empty($imm_obs_data) && count($imm_obs_data) > 0) {
        foreach ($imm_obs_data as $key => $val) {
            if ($val['imo_id'] && $val['imo_id'] != 0) {
                $sql2                   = " DELETE
                                            FROM
                                              immunization_observation
                                            WHERE imo_im_id = ?
                                              AND imo_pid = ?";
                $result2                = sqlQuery($sql2, array($val['imo_im_id'],$val['imo_pid']));
            }
        }
    }

    for ($i = 0; $i < $immunizationdata['tr_count']; $i++) {
        if ($immunizationdata['observation_criteria'][$i] == 'vaccine_type') {
            $code                     = $immunizationdata['cvx_vac_type_code'][$i];
            $code_text                = $immunizationdata['code_text_hidden'][$i];
            $code_type                = $immunizationdata['code_type_hidden'][$i];
            $vis_published_dateval    = $immunizationdata['vis_published_date'][$i] ? $immunizationdata['vis_published_date'][$i] : '';
            $vis_presented_dateval    = $immunizationdata['vis_presented_date'][$i] ? $immunizationdata['vis_presented_date'][$i] : '';
            $imo_criteria_value       = '';
        } elseif ($immunizationdata['observation_criteria'][$i] == 'disease_with_presumed_immunity') {
            $code                     = $immunizationdata['sct_code'][$i];
            $code_text                = $immunizationdata['codetext'][$i];
            $code_type                = $immunizationdata['codetypehidden'][$i];
            $imo_criteria_value       = '';
            $vis_published_dateval    = '';
            $vis_presented_dateval    = '';
        } elseif ($immunizationdata['observation_criteria'][$i] == 'funding_program_eligibility') {
            $imo_criteria_value       = $immunizationdata['observation_criteria_value'][$i];
            $code                     = '';
            $code_text                = '';
            $code_type                = '';
            $vis_published_dateval    = '';
            $vis_presented_dateval    = '';
        }

        if ($immunizationdata['observation_criteria'][$i] != '') {
            $sql                      = " INSERT INTO immunization_observation (
                                          imo_im_id,
                                          imo_pid,
                                          imo_criteria,
                                          imo_criteria_value,
                                          imo_user,
                                          imo_code,
                                          imo_codetext,
                                          imo_codetype,
                                          imo_vis_date_published,
                                          imo_vis_date_presented
                                        )
                                        VALUES
                                          (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $res                      = sqlQuery($sql, array($id,$_SESSION["pid"],$immunizationdata['observation_criteria'][$i],$imo_criteria_value,$_SESSION['authUserID'],$code, $code_text, $code_type,$vis_published_dateval,$vis_presented_dateval));
        }
    }

    return;
}
?>
<html>
<head>

<?php Header::setupHeader(['datetime-picker', 'select2']); ?>
<title><?php echo xlt('Immunizations'); ?></title>

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}
</style>

</head>

<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Immunizations'); ?></h2>
            </div>
            <div class="col-12">
                <?php
                    $list_id = ""; // to indicate nav item is active, count and give correct id
                    $menuPatient = new PatientMenuRole();
                    $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
            <div class="col-12">
                <form class="jumbotron p-4" action="immunizations.php" name="add_immunization" id="add_immunization">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                    <input type="hidden" name="mode" id="mode" value="add" />
                    <input type="hidden" name="id" id="id" value="<?php echo attr($id ?? ''); ?>" />
                    <input type="hidden" name="pid" id="pid" value="<?php echo attr($pid); ?>" />
                    <input type="hidden" name="uuid" id="uuid" value="<?php echo attr($uuid ?? ''); ?>" />

                    <?php
                    if (!empty($isAddedError)) {
                        echo "<p class='text-danger font-weight-bold'>" . xlt("Entered in Error") . "</p>";
                    }
                    ?>

                    <?php if (!($useCVX)) { ?>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization'); ?></label>
                        <?php
                        // Modified 7/2009 by BM to incorporate the immunization items into the list_options listings
                        generate_form_field(array('data_type' => 1,'field_id' => 'immunization_id','list_id' => 'immunizations','empty_title' => 'SKIP'), $immunization_id);
                        ?>
                    </div>
                    <?php } else { ?>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization'); ?> (<?php echo xlt('CVX Code'); ?>)</label>
                        <input type='text' class='form-control' size='10' name='cvx_code' id='cvx_code'
                            value='<?php echo attr($cvx_code ?? ''); ?>' onclick='sel_cvxcode(this)'
                            title='<?php echo xla('Click to select or change CVX code'); ?>'/>
                        <div id='cvx_description' class='d-inline float-right p-1 ml-2'>
                            <?php echo xlt($code_text ?? ''); ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Date & Time Administered'); ?></label>
                        <input type='text' size='14' class='datetimepicker form-control' name="administered_date" id="administered_date"
                            value='<?php echo (!empty($administered_date)) ? attr($administered_date) : date('Y-m-d H:i'); ?>'
                            title='<?php echo xla('yyyy-mm-dd Hours(24):minutes'); ?>'/>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Amount Administered'); ?></label>
                        <input class='text form-control mb-2' type='text' name="immuniz_amt_adminstrd" size="25" value="<?php echo attr($immuniz_amt_adminstrd ?? ''); ?>" />
                        <?php echo generate_select_list("form_drug_units", "drug_units", ($drugunitselecteditem ?? ''), 'Select Drug Unit', ''); ?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization Expiration Date'); ?></label>
                        <input type='text' size='10' class='datepicker form-control' name="immuniz_exp_date" id="immuniz_exp_date"
                            value='<?php echo (!empty($immuniz_exp_date)) ? attr($immuniz_exp_date) : ''; ?>'
                            title='<?php echo xla('yyyy-mm-dd'); ?>' />
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization Manufacturer'); ?></label>
                        <?php echo generate_select_list('manufacturer', 'Immunization_Manufacturer', ($manufacturer ?? ''), 'Select Manufacturer', ' ');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization Lot Number'); ?></label>
                        <br>
                        <select class='auto form-control' type='text' name="lot_number" size="25" value="<?php echo attr($lot_number ?? ''); ?>"></select>
                    </div>
                    <div class="form-row mt-3">
                        <div class="col-12">
                            <label><?php echo xlt('Name and Title of Immunization Administrator'); ?></label>
                        </div>
                        <div class="col-12 col-sm-5">
                            <input type="text" class="form-control" name="administered_by" id="administered_by" size="25" value="<?php echo attr($administered_by); ?>" />
                        </div>
                        <div class="col-12 col-sm-2 text-center">
                            <?php echo xlt('or choose'); ?>
                        </div>
                        <div class="col-12 col-sm-5">
                        <!-- NEEDS WORK -->
                        <select class="form-control" name="administered_by_id" id='administered_by_id'>
                                    <option value=""></option>
                                        <?php
                                        $sql = "select id, CONCAT_WS(' ',lname,fname) as full_name " .
                                            "from users where username != '' and password != 'NoLogin' " .
                                            "order by full_name";

                                        $result = sqlStatement($sql);
                                        while ($row = sqlFetchArray($result)) {
                                            echo '<OPTION VALUE=' . attr($row['id']);
                                            echo (isset($administered_by_id) && $administered_by_id != "" ? $administered_by_id : $_SESSION['authUserID']) == $row['id'] ? ' selected>' : '>';
                                            echo text($row['full_name']) . '</OPTION>';
                                        }
                                        ?>
                                </select>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Date Immunization Information Statements Given'); ?></label>
                        <input type='text' size='10' class='datepicker form-control' name="education_date" id="education_date"
                            value='<?php echo (!empty($education_date)) ? attr($education_date) : date('Y-m-d'); ?>'
                            title='<?php echo xla('yyyy-mm-dd'); ?>' />
                    </div>
                    <div class="form-group mt-3">
                        <label>
                            <?php echo xlt('Date of VIS Statement'); ?>
                            (<a href="https://www.cdc.gov/vaccines/pubs/vis/default.htm" title="<?php echo xla('Help'); ?>" rel="noopener" target="_blank">?</a>)
                        </label>
                        <input type='text' size='10' class='datepicker  form-control' name="vis_date" id="vis_date"
                            value='<?php echo (!empty($vis_date)) ? attr($vis_date) : date('Y-m-d'); ?>'
                            title='<?php echo xla('yyyy-mm-dd'); ?>' />
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Route'); ?></label>
                        <?php echo generate_select_list('immuniz_route', 'drug_route', ($immuniz_route ?? ''), 'Select Route', '');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Administration Site'); ?></label>
                        <?php echo generate_select_list('immuniz_admin_ste', 'immunization_administered_site', ($immuniz_admin_ste ?? ''), 'Select Administration Site', ' ', '', '', '', null, false, 'proc_body_site');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Notes'); ?></label>
                        <textarea class="form-control" name="note" id="note" rows="5" cols="25"><?php echo text($note ?? ''); ?></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Information Source'); ?></label>
                        <?php echo generate_select_list('immunization_informationsource', 'immunization_informationsource', ($immuniz_information_source ?? ''), 'Select Information Source', ' ');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Completion Status'); ?></label>
                        <?php echo generate_select_list('immuniz_completion_status', 'Immunization_Completion_Status', ($immuniz_completion_status ?? ''), 'Select Completion Status', ' ');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Substance Refusal Reason'); ?></label>
                        <?php echo generate_select_list('immunization_refusal_reason', 'immunization_refusal_reason', ($immuniz_refusal_reason ?? ''), 'Select Refusal Reason', ' ');?>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Reason Code'); ?></label>
                        <input class="code-selector-popup form-control immunizationReasonCode"
                               name="reason_code" type="text" value="<?php echo attr($reason_code ?? ''); ?>"
                               placeholder="<?php echo xla("Select a reason code"); ?>"
                        />
                        <input type="hidden" name="reason_code_text" value="<?php echo attr($reason_code_text ?? ''); ?>" />
                        <p class="reason_code_text d-inline float-right p-1 ml-2 <?php echo empty($reason_code_text) ? "" : "d-none"; ?>"></p>
                    </div>
                    <div class="form-group mt-3">
                        <label><?php echo xlt('Immunization Ordering Provider'); ?></label>
                        <select class="form-control" name="ordered_by_id" id='ordered_by_id'>
                            <option value=""></option>
                                <?php
                                $sql = "select id, CONCAT(IFNULL(lname,''), ' ,',IFNULL(fname,'')) as full_name " .
                                    "from users where username != '' and password != 'NoLogin' " .
                                    "order by full_name";

                                $result = sqlStatement($sql);
                                while ($row = sqlFetchArray($result)) {
                                    echo '<OPTION VALUE=' . attr($row['id']);
                                    echo (isset($ordered_by_id) && $ordered_by_id != "" ? $ordered_by_id : $_SESSION['authUserID']) == $row['id'] ? ' selected>' : '>';
                                    echo text($row['full_name']) . '</OPTION>';
                                }
                                ?>
                        </select>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <?php
                            if (!empty($entered_by)) { ?>
                                <p><?php echo xlt('Entered By'); ?> <?php echo text($entered_by); ?></p>
                            <?php } ?>

                            <?php if ($GLOBALS['observation_results_immunization']) { ?>
                            <button type="button" class="btn btn-primary" onclick="showObservationResultSection();" title='<?php echo xla('Click here to see observation results'); ?>'>
                                <?php echo xlt('See observation results'); ?>
                            </button>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="observation_results" style="display:none;">
                        <fieldset class="obs_res_head">
                            <legend><?php echo xlt('Observation Results'); ?></legend>
                            <div class="obs_res_table">
                                <?php
                                if (!empty($imm_obs_data) && count($imm_obs_data) > 0) {
                                    foreach ($imm_obs_data as $key => $value) {
                                        $key_snomed = 0;
                                        $key_cvx = 0;
                                        $style = '';?>
                                        <div class="form-row" id="or_tr_<?php echo attr(($key + 1)); ?>">
                                            <?php
                                            if ($id == 0) {
                                                if ($key == 0) {
                                                    $style = 'display: table-cell;width:765px !important';
                                                } else {
                                                    $style = 'display: none;width:765px !important';
                                                }
                                            } else {
                                                $style = 'display : table-cell;width:765px !important';
                                            }
                                            ?>
                                            <div class="form-group col" id="observation_criteria_td_<?php echo attr(($key + 1)); ?>" style="<?php echo $style;?>">
                                                <label><?php echo xlt('Observation Criteria');?></label>
                                                <br>
                                                <select class="form-control" id="observation_criteria_<?php echo attr(($key + 1)); ?>" name="observation_criteria[]" onchange="selectCriteria(this.id,this.value);">
                                                    <?php foreach ($observation_criteria as $keyo => $valo) { ?>
                                                        <option value="<?php echo attr($valo['option_id']);?>" <?php echo ($valo['option_id'] == $value['imo_criteria'] && $id != 0) ? 'selected = "selected"' : ''; ?> ><?php echo text($valo['title']);?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div <?php echo ($value['imo_criteria'] != 'funding_program_eligibility' || $id == 0) ? 'style="display: none;"' : ''; ?> class="form-group col observation_criteria_value_td" id="observation_criteria_value_td_<?php echo attr(($key + 1)); ?>">
                                                <label><?php echo xlt('Observation Criteria Value'); ?></label>
                                                <br>
                                                <select class="form-control" name="observation_criteria_value[]" id="observation_criteria_value_<?php echo attr(($key + 1)); ?>">
                                                    <?php foreach ($observation_criteria_value as $keyoc => $valoc) { ?>
                                                        <option value="<?php echo attr($valoc['option_id']);?>" <?php echo ($valoc['option_id'] == $value['imo_criteria_value']  && $id != 0) ? 'selected = "selected"' : ''; ?>><?php echo text($valoc['title']);?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div <?php echo ($value['imo_criteria'] != 'disease_with_presumed_immunity' || $id == 0) ? 'style="display: none;"' : ''; ?> class="form-group col code_serach_td" id="code_search_td_<?php echo attr(($key + 1)); ?>">
                                                <?php $key_snomed = ($key > 0) ? (($key * 2) + 2) : ($key + 2);?>
                                                <label><?php echo xlt('SNOMED-CT Code'); ?></label>
                                                <br>
                                                <input type="text" id="sct_code_<?php echo attr($key_snomed); ?>" name="sct_code[]" class="form-control code" value="<?php echo ($id != 0 && $value['imo_criteria'] == 'disease_with_presumed_immunity') ? attr($value['imo_code']) : ''; ?>" onclick='sel_code(this.id);' />
                                                <br />
                                                <span id="displaytext_<?php echo attr($key_snomed); ?>" class="displaytext d-block text-primary">
                                                    <?php echo text($value['imo_codetext']);?>
                                                </span>
                                                <input type="hidden" id="codetext_<?php echo attr($key_snomed); ?>" name="codetext[]" class="codetext" value="<?php echo attr($value['imo_codetext']); ?>" />
                                                <input type="hidden" value="SNOMED-CT" name="codetypehidden[]" id="codetypehidden<?php echo attr($key_snomed); ?>" />
                                            </div>
                                            <div <?php echo ($value['imo_criteria'] != 'vaccine_type' || $id == 0) ? 'style="display: none;"' : ''; ?> class="form-group col code_serach_vaccine_type_td" id="code_serach_vaccine_type_td_<?php echo attr(($key + 1)); ?>">
                                                <label><?php echo xlt('CVX Code');?></label>
                                                <br>
                                                <?php $key_cvx = ($key > 0) ? (($key * 2) + 3) : ($key + 3);?>
                                                <input type="text" class="form-control" id="cvx_code<?php echo attr($key_cvx); ?>" name="cvx_vac_type_code[]" onclick="sel_cvxcode(this);"
                                                    value="<?php echo ($id != 0 && $value['imo_criteria'] == 'vaccine_type') ? attr($value['imo_code']) : ''; ?>" />
                                                <div class="imm-imm-add-12" id="imm-imm-add-12<?php echo attr($key_cvx); ?>">
                                                    <?php echo ($id != 0 && $value['imo_criteria'] == 'vaccine_type') ? text($value['imo_codetext']) : ''; ?>
                                                </div>
                                                <input type="hidden"  value="CVX" name="code_type_hidden[]" id="code_type_hidden<?php echo attr($key_cvx); ?>" />
                                                <input type="hidden" class="code_text_hidden" name="code_text_hidden[]" id="code_text_hidden<?php echo attr($key_cvx); ?>" value="<?php echo ($id != 0 && $value['imo_criteria'] == 'vaccine_type') ? attr($value['imo_codetext']) : ''; ?>"/>
                                            </div>
                                            <div <?php echo ($value['imo_criteria'] != 'vaccine_type' || $id == 0) ? 'style="display: none;"' : ''; ?> class="form-group col vis_published_date_td" id="vis_published_date_td_<?php echo attr(($key + 1)); ?>">
                                                <label><?php echo xlt('Date VIS Published'); ?></label>
                                                <br>
                                                <?php
                                                $vis_published_dateval = $value['imo_vis_date_published'] ? $value['imo_vis_date_published'] : '';
                                                ?>
                                                <input type="text" class='datepicker form-control' name="vis_published_date[]" value="<?php echo ($id != 0 && $vis_published_dateval != 0) ? attr($vis_published_dateval) : ''; ?>" id="vis_published_date_<?php echo attr(($key + 1)); ?>" />
                                            </div>
                                            <div <?php echo ($value['imo_criteria'] != 'vaccine_type' || $id == 0) ? 'style="display: none;"' : ''; ?> class="form-group col vis_presented_date_td" id="vis_presented_date_td_<?php echo attr(($key + 1)); ?>">
                                                <label><?php echo xlt('Date VIS Presented'); ?></label>
                                                <br>
                                                <?php
                                                $vis_presented_dateval = $value['imo_vis_date_presented'] ? $value['imo_vis_date_presented'] : '';
                                                ?>
                                                <input type="text" class='datepicker form-control' name="vis_presented_date[]" value="<?php echo ($id != 0 && $vis_presented_dateval != 0) ? attr($vis_presented_dateval) : ''; ?>" id="vis_presented_date_<?php echo attr(($key + 1)); ?>" />
                                            </div>
                                            <?php if ($key != 0 && $id != 0) {?>
                                            <div class="form-group col">
                                                <button type="button" class="btn btn-danger btn-delete" id ="<?php echo attr(($key + 1)); ?>"  onclick="RemoveRow(this.id);" title='<?php echo xla('Click here to delete the row'); ?>'>
                                                    <?php echo xlt('Delete'); ?>
                                                </button>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <?php
                                    }
                                } else {?>
                                        <div class="form-row" id="or_tr_1">
                                            <div class="form-group col" id="observation_criteria_td_1">
                                                <label><?php echo xlt('Observation Criteria'); ?></label>
                                                <br>
                                                <select class="form-control" id="observation_criteria_1" name="observation_criteria[]" onchange="selectCriteria(this.id,this.value);">
                                                <?php foreach ($observation_criteria as $keyo => $valo) { ?>
                                                    <option value="<?php echo attr($valo['option_id']);?>" <?php echo (!empty($value['imo_criteria']) && ($valo['option_id'] == $value['imo_criteria']) && $id != 0) ? 'selected = "selected"' : ''; ?> ><?php echo text($valo['title']);?></option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                            <div <?php echo (empty($value['imo_criteria']) || (!empty($value['imo_criteria']) && ($value['imo_criteria'] != 'funding_program_eligibility'))) ? 'style="display: none;"' : ''; ?> class="form-group col observation_criteria_value_td" id="observation_criteria_value_td_1">
                                                <label><?php echo xlt('Observation Criteria Value'); ?></label>
                                                <br>
                                                <select class="form-control" id="observation_criteria_value_1" name="observation_criteria_value[]">
                                                <?php foreach ($observation_criteria_value as $keyoc => $valoc) { ?>
                                                    <option value="<?php echo attr($valoc['option_id']);?>" <?php echo (!empty($value['imo_criteria_value']) && ($valoc['option_id'] == $value['imo_criteria_value']) && $id != 0) ? 'selected = "selected"' : ''; ?>><?php echo text($valoc['title']);?></option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                            <div <?php echo (empty($value['imo_criteria']) || (!empty($value['imo_criteria']) && ($value['imo_criteria'] != 'disease_with_presumed_immunity')) || empty($id)) ? 'style="display: none;"' : ''; ?> class="form-group col code_serach_td" id="code_search_td_1">
                                                <label><?php echo xlt('SNOMED-CT Code');?></label>
                                                <br />
                                                <input type="text" id="sct_code_2" name="sct_code[]" class="code form-control" value="<?php echo (!empty($id) && !empty($value['imo_criteria']) && ($value['imo_criteria'] == 'disease_with_presumed_immunity')) ? attr($value['imo_code']) : ''; ?>"  onclick='sel_code(this.id);' />
                                                <span id="displaytext_2" class="displaytext d-block text-primary">
                                                    <?php echo text($value['imo_codetext'] ?? '');?>
                                                </span>
                                                <input type="hidden" id="codetext_2" name="codetext[]" class="codetext" value="<?php echo attr($value['imo_codetext'] ?? ''); ?>" />
                                                <input type="hidden" value="SNOMED-CT" name="codetypehidden[]" id="codetypehidden2" />
                                            </div>
                                            <div <?php echo (empty($value['imo_criteria']) || (!empty($value['imo_criteria']) && ($value['imo_criteria'] != 'vaccine_type')) || empty($id)) ? 'style="display: none;"' : ''; ?> class="form-group col code_serach_vaccine_type_td" id="code_serach_vaccine_type_td_1">
                                                <label><?php echo xlt('CVX Code'); ?></label>
                                                <br>
                                                <input type="text" class="form-control" id="cvx_code3" name="cvx_vac_type_code[]" onclick="sel_cvxcode(this);"
                                                    value="<?php echo (!empty($id) && (!empty($value['imo_criteria']) && ($value['imo_criteria'] == 'vaccine_type'))) ? attr($value['imo_code']) : ''; ?>" />
                                                <div class="imm-imm-add-12" id="imm-imm-add-123">
                                                    <?php echo (!empty($id) && (!empty($value['imo_criteria']) && ($value['imo_criteria'] == 'vaccine_type'))) ? text($value['imo_codetext']) : ''; ?>
                                                </div>
                                                <input type="hidden" value="CVX" name="code_type_hidden[]" id="code_type_hidden3"/>
                                                <input type="hidden" class="code_text_hidden" name="code_text_hidden[]" id="code_text_hidden3" value="<?php echo (!empty($id) && (!empty($value['imo_criteria']) && ($value['imo_criteria'] == 'vaccine_type'))) ? attr($value['imo_codetext']) : ''; ?>"/>
                                            </div>
                                            <div <?php echo (empty($value['imo_criteria']) || (!empty($value['imo_criteria']) && ($value['imo_criteria'] != 'vaccine_type')) || empty($id)) ? 'style="display: none;"' : ''; ?> class="form-group col vis_published_date_td" id="vis_published_date_td_1">
                                                <label><?php echo xlt('Date VIS Published'); ?></label>
                                                <br>
                                                <?php
                                                $vis_published_dateval = (!empty($value['imo_vis_date_published'])) ? $value['imo_vis_date_published'] : '';
                                                ?>
                                                <input type="text" class='datepicker form-control' name="vis_published_date[]" value="<?php echo (!empty($id) && $vis_published_dateval != 0) ? attr($vis_published_dateval) : ''; ?>" id="vis_published_date_1" />
                                            </div>
                                            <div <?php echo (empty($value['imo_criteria']) || (!empty($value['imo_criteria']) && ($value['imo_criteria'] != 'vaccine_type')) || empty($id)) ? 'style="display: none;"' : ''; ?> class="form-group col vis_presented_date_td" id="vis_presented_date_td_1">
                                                <label><?php echo xlt('Date VIS Presented'); ?></label>
                                                <br>
                                                <?php
                                                $vis_presented_dateval = (!empty($value['imo_vis_date_presented'])) ? $value['imo_vis_date_presented'] : '';
                                                ?>
                                                <input type="text" class='datepicker form-control' name="vis_presented_date[]" value="<?php echo (!empty($id) && $vis_presented_dateval != 0) ? attr($vis_presented_dateval) : ''; ?>" id="vis_presented_date_1" />
                                            </div>
                                        </div>
                                <?php } ?>
                            </div>

                            <div class="row">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-primary btn-sm btn-add" onclick="addNewRow();" title='<?php echo xla('Click here to add new row'); ?>'>
                                        <?php echo xlt('Add'); ?>
                                    </button>
                                </div>
                            </div>

                            <input type ="hidden" name="tr_count" id="tr_count" value="<?php echo (!empty($imm_obs_data) && count($imm_obs_data) > 0) ? attr(count($imm_obs_data)) : 1 ;?>" />
                            <input type="hidden" id="clickId" value="" />
                        </fieldset>
                    </div>
                    <div class="btn-group mt-3">
                        <button type="button" class="btn btn-primary btn-save" name="save" id="save" value="<?php echo xla('Save Immunization'); ?>">
                            <?php echo xlt('Save Immunization'); ?>
                        </button>
                        <button type="button" class="btn btn-primary btn-print" name="print" id="print" value="<?php echo attr(xl('Print Record') . ' (' . xl('PDF') . ')'); ?>">
                            <?php echo xlt('Print Record (PDF)'); ?>
                        </button>
                        <button type="button" class="btn btn-primary btn-print" name="printHtml" id="printHtml" value="<?php echo attr(xl('Print Record') . ' (' . xl('HTML') . ')'); ?>">
                            <?php echo xlt('Print Record (HTML)'); ?>
                        </button>
                        <button type="reset" class="btn btn-secondary btn-cancel" name="clear" id="clear" value="<?php echo xla('Clear'); ?>">
                            <?php echo xlt('Clear'); ?>
                        </button>
                    </div>
                </form>

                <div class="table-responsive" id="immunization_list">
                    <table class="table">
                        <!-- some columns are sortable -->
                        <tr>
                            <th>
                                <a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=vacc';" title='<?php echo xla('Sort by vaccine'); ?>'>
                                    <?php echo xlt('Vaccine'); ?>
                                </a>
                                <span class='small'>
                                    <?php echo ($sortby == 'vacc') ? 'v' : ''; ?>
                                </span>
                            </th>
                            <th>
                                <a href="javascript:top.restoreSession();location.href='immunizations.php?sortby=date';" title='<?php echo xla('Sort by date'); ?>'>
                                    <?php echo xlt('Date'); ?>
                                </a>
                                <span class='small'>
                                    <?php echo ($sortby == 'date') ? 'v' : ''; ?>
                                </span>
                            </th>
                            <th><?php echo xlt('Amount'); ?></th>
                            <th><?php echo xlt('Expiration'); ?></th>
                            <th><?php echo xlt('Manufacturer'); ?></th>
                            <th><?php echo xlt('Lot Number'); ?></th>
                            <th><?php echo xlt('Administered By'); ?></th>
                            <th><?php echo xlt('Education Date'); ?></th>
                            <th><?php echo xlt('Route'); ?></th>
                            <th><?php echo xlt('Administered Site'); ?></th>
                            <th><?php echo xlt('Notes'); ?></th>
                            <th><?php echo xlt('Completion Status'); ?></th>
                            <th><?php echo xlt('Error'); ?></th>
                            <th>&nbsp;</th>
                        </tr>

                        <?php
                        $result = getImmunizationList($pid, ($_GET['sortby'] ?? null), true);

                        while ($row = sqlFetchArray($result)) {
                            $isError = $row['added_erroneously'];

                            if ($isError) {
                                $tr_title = 'title="' . xla("Entered in Error") . '"';
                            } else {
                                $tr_title = "";
                            }

                            if (!empty($id) && ($row["id"] == $id)) {
                                echo "<tr " . $tr_title . " class='immrow text selected' id='" . attr($row["id"]) . "'>";
                            } else {
                                echo "<tr " . $tr_title . " class='immrow text' id='" . attr($row["id"]) . "'>";
                            }

                            // Figure out which name to use (ie. from cvx list or from the custom list)
                            if ($GLOBALS['use_custom_immun_list']) {
                                $vaccine_display = generate_display_field(array('data_type' => '1','list_id' => 'immunizations'), $row['immunization_id']);
                            } else {
                                if (!empty($row['code_text_short'])) {
                                    $vaccine_display = xlt($row['code_text_short']);
                                } else {
                                    $vaccine_display = generate_display_field(array('data_type' => '1','list_id' => 'immunizations'), $row['immunization_id']);
                                }
                            }

                            if ($isError) {
                                $del_tag_open = "<del>";
                                $del_tag_close = "</del>";
                            } else {
                                $del_tag_open = "";
                                $del_tag_close = "";
                            }

                            echo "<td>" . $del_tag_open . $vaccine_display . $del_tag_close . "</td>";

                            if ($row["administered_date"]) {
                                $administered_date_summary = new DateTime($row['administered_date']);
                                $administered_date_summary = $administered_date_summary->format('Y-m-d H:i');
                            } else {
                                $administered_date_summary = "";
                            }

                            echo "<td>" . $del_tag_open . text($administered_date_summary) . $del_tag_close . "</td>";
                            if ($row["amount_administered"] > 0) {
                                echo "<td>" . $del_tag_open . text($row["amount_administered"]) . " " . generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row['amount_administered_unit']) . $del_tag_close . "</td>";
                            } else {
                                echo "<td>&nbsp</td>";
                            }

                            echo "<td>" . $del_tag_open . text($row["expiration_date"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . text($row["manufacturer"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . text($row["lot_number"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . text($row["administered_by"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . text($row["education_date"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . generate_display_field(array('data_type' => '1','list_id' => 'drug_route'), $row['route']) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . generate_display_field(array('data_type' => '1','list_id' => 'immunization_administered_site'), $row['administration_site']) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . text($row["note"]) . $del_tag_close . "</td>";
                            echo "<td>" . $del_tag_open . generate_display_field(array('data_type' => '1','list_id' => 'Immunization_Completion_Status'), $row['completion_status']) . $del_tag_close . "</td>";

                            if ($isError) {
                                $checkbox = "checked";
                            } else {
                                $checkbox = "";
                            }

                                echo "<td><input type='checkbox' class='error' id='" . attr($row["id"]) . "' value='" . xlt('Error') . "' " . $checkbox . " /></td>";

                                echo "<td><button type='button' class='delete btn btn-danger btn-delete' id='" . attr($row["id"]) . "' value='" . xlt('Delete') . "'>" . xlt('Delete') . "</button></td>";
                                echo "</tr>";
                        }
                        ?>
                    </table>
                </div> <!-- end immunizations -->
            </div>
        </div>
    </div>
</body>

<script>
var tr_count = $('#tr_count').val();

// jQuery stuff to make the page a little easier to use

$(function () {
    <?php if (!($useCVX)) { ?>
      $("#save").on("click", function() { SaveForm(); });
    <?php } else { ?>
      $("#save").on("click", function() {
        if (validate_cvx()) {
          SaveForm();
        }
        else {
          return;
        }
      });
    <?php } ?>
    $("#print").on("click", function() { PrintForm("pdf"); });
    $("#printHtml").on("click", function() { PrintForm("html"); });
    $(".immrow").on("click", function() { EditImm(this); });
    $(".error").on("click", function(event) { ErrorImm(this); event.stopPropagation(); });
    $(".delete").on("click", function(event) { DeleteImm(this); event.stopPropagation(); });

    $(".immrow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".immrow").on("mouseout", function() { $(this).toggleClass("highlight"); });

    $("#administered_by_id").on("change", function() { $("#administered_by").val($("#administered_by_id :selected").text()); });

    $("#form_immunization_id").on("change", function() {
        if ( $(this).val() != "" ) {
            $("#cvx_code").val( "" );
            $("#cvx_description").text( "" );
            $("#cvx_code").trigger("change");
        }
    });
    $(".immunizationReasonCode").click(sel_reasonCode);

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  // special cases to deal with datepicker items that are added dynamically
  $(document).on('mouseover','.datepicker_dynamic', function(){
    $(this).datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
  });
});

var PrintForm = function(typ) {
    top.restoreSession();
    newURL='shot_record.php?output=' + encodeURIComponent(typ) + '&sortby=' + <?php echo js_url($sortby); ?>;
    window.open(newURL, '_blank', "menubar=1,toolbar=1,scrollbars=1,resizable=1,width=600,height=450");
}

var SaveForm = function() {
    top.restoreSession();
    $("#add_immunization").submit();
}

var EditImm = function(imm) {
    top.restoreSession();
    location.href='immunizations.php?mode=edit&id=' + encodeURIComponent(imm.id) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
}

var DeleteImm = function(imm) {
    if (confirm(<?php echo xlj('This action cannot be undone.'); ?> + "\n" + <?php echo xlj('Do you wish to PERMANENTLY delete this immunization record?'); ?>)) {
        top.restoreSession();
        location.href='immunizations.php?mode=delete&id=' + encodeURIComponent(imm.id) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
    }
}

var ErrorImm = function(imm) {
    top.restoreSession();
    location.href='immunizations.php?mode=added_error&id=' + encodeURIComponent(imm.id) + '&isError=' + encodeURIComponent(imm.checked) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
}

//This is for callback by the find-code popup.
//Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {

    if(codetype == 'CVX') {
    var f = document.forms[0][current_sel_name];
        if(!f.length) {
    var s = f.value;

    if (code) {
        s = code;
    }
    else {
        s = '';
    }

    f.value = s;
            if(f.name != 'cvx_vac_type_code[]'){
    $("#cvx_description").text( codedesc );
    $("#form_immunization_id").attr( "value", "" );
    $("#form_immunization_id").trigger("change");
            }else{
                id_arr = f.id.split('cvx_code');
                counter = id_arr[1];
                $('#imm-imm-add-12'+counter).html(codedesc);
                $('#code_text_hidden'+counter).val(codedesc);
            }
        }else {
            var index = document.forms[0][current_sel_name].length -1;
            var elem = document.forms[0][current_sel_name][index];
            var ss = elem.value;
            if (code) {
                ss = code;
            }
            else {
                ss = '';
}

            elem.value = ss;
            arr = elem.id.split('cvx_code');
            count = arr[1];
            $('#imm-imm-add-12'+count).html(codedesc);
            $('#code_text_hidden'+count).val(codedesc);
        }
    }else {
        var checkId = $('#clickId').val();
        $("#sct_code_" + checkId).val(code);
        $("#codetext_" + checkId).val(codedesc);
        $("#displaytext_" + checkId).html(codedesc);
    }
}
function sel_reasonCode(e) {
    let target = e.currentTarget;
    if (!(target && target.name)) {
        console.error("Could not find DOMNode name for event target");
        return;
    }
    current_sel_name = e.currentTarget.name;

    let previousSelCodeFunction = window.set_related;
    let restoreCallback = function() {
        window.set_related = previousSelCodeFunction;
    };

    let set_relatedReasonCode = function(codetype, code, selector, codedesc) {

        let field = document.forms[0][current_sel_name];
        let field_text = document.forms[0][current_sel_name + "_text"];
        let text = document.querySelector("." + current_sel_name + "_text");
        if (typeof code == 'string' && code.trim() != '') {
            field.value = codetype + ":" + code;
            if (field_text && text) {
                field_text.value = codedesc;
                text.classList.remove("d-none"); // remove anything hiding the attribute.
                text.innerText = codedesc;
            }
        } else {
            field.value = "";
            if (field_text && text) {
                field_text.value = "";
                text.classList.add("d-none");
                text.innerText = "";
            }
        }
    };

    let opts = {
        callBack: {
            call: restoreCallback
        }
    };
    // we are going to replace our global function for the time, and it will get setback in the callback

    window.set_related = set_relatedReasonCode;
    window.top.restoreSession();
    window.dlgopen("../encounter/find_code_popup.php?default=SNOMED-CT"
        , '_blank', 700, 400, false, undefined, opts);
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
    return new Array();
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
    var e = document.forms[0][current_sel_name];
    e.value = '';
    $("#cvx_description").text('');
    $("#form_immunization_id").attr("value", "");
    $("#form_immunization_id").trigger("change");
}

// This invokes the find-code popup.
function sel_cvxcode(e) {
 current_sel_name = e.name;
 dlgopen('../encounter/find_code_dynamic.php?codetype=CVX,VALUESET', '_blank', 900, 600);
}

// This ensures the cvx centric entry is filled.
function validate_cvx() {
 if (document.add_immunization.cvx_code.value>0) {
  return true;
 }
 else {
  document.add_immunization.cvx_code.style.backgroundColor="red";
  document.add_immunization.cvx_code.focus();
  return false;
 }
}

function showObservationResultSection()
{
    $('.observation_results').slideToggle();
}

function selectCriteria(id,value)
{
    var arr = id.split('observation_criteria_');
    var key = arr[1];
    if (value == 'funding_program_eligibility') {
        if(key > 1) {
            var target = $("#observation_criteria_value_"+key);
            $.ajax({
                type: "POST",
                url:  "immunizations.php",
                dataType: "json",
                data: {
                    type : 'duplicate_row_2',
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                },
                success: function(thedata){
                    $.each(thedata,function(i,item) {
                        target.append($('<option />').val(item.option_id).text(item.title));
                    });
                    $('#observation_criteria_value_'+key+' option[value=""]').prop('selected', true);
                },
                error:function(){
                  alert("ajax error");
                }
            });
        }
        $("#code_search_td_"+key).hide();
        $("#vis_published_date_td_"+key).hide();
        $("#vis_presented_date_td_"+key).hide();
        $("#code_serach_vaccine_type_td_"+key).hide();
        $("#observation_criteria_value_td_"+key).show();
    }
    if (value == 'vaccine_type')
    {
        $("#observation_criteria_value_td_"+key).hide();
        $("#code_search_td_"+key).hide();
        $("#code_serach_vaccine_type_td_"+key).show();
        $("#vis_published_date_td_"+key).show();
        $("#vis_presented_date_td_"+key).show();
        if(key == 1) {
            key = parseInt(key) + 2;
        }
        else {
            key = (parseInt(key) * 2) + 1;
        }
        $("#cvx_code"+key).css("background-color", "red");
        $("#cvx_code"+key).focus();
        return false;
    }
    if (value == 'disease_with_presumed_immunity')
    {
        $("#observation_criteria_value_td_"+key).hide();
        $("#vis_published_date_td_"+key).hide();
        $("#vis_presented_date_td_"+key).hide();
        $("#code_serach_vaccine_type_td_"+key).hide();
        $("#code_search_td_"+key).show();
        if(key == 1) {
            key = parseInt(key) + 1;
        }
        else {
            key = (parseInt(key) * 2);
        }
        $("#sct_code_"+key).css("background-color", "red");
        $("#sct_code_"+key).focus();
        return false;
    }
    if (value == '')
    {
        $("#observation_criteria_value_td_"+key).hide();
        $("#vis_published_date_td_"+key).hide();
        $("#vis_presented_date_td_"+key).hide();
        $("#code_serach_vaccine_type_td_"+key).hide();
        $("#code_search_td_"+key).hide();
    }
}

function RemoveRow(id)
{
    tr_count = parseInt($("#tr_count").val());
    new_tr_count = tr_count-1;
    $("#tr_count").val(new_tr_count);
    $("#or_tr_"+id).remove();
}

function addNewRow()
{
    tr_count = parseInt($("#tr_count").val());
    new_tr_count = tr_count+1;
    new_tr_count_2 = (new_tr_count * 2);
    new_tr_count_3 = (new_tr_count *2) + 1;
    $("#tr_count").val(new_tr_count);
    label1 = <?php echo xlj('Observation Criteria'); ?>;
    label2 = <?php echo xlj('Observation Criteria Value'); ?>;
    label3 = <?php echo xlj('SNOMED-CT Code'); ?>;
    label4 = <?php echo xlj('CVX Code'); ?>;
    label5 = <?php echo xlj('Date VIS Published'); ?>;
    label6 = <?php echo xlj('Click here to choose a date'); ?>;
    label7 = <?php echo xlj('Date VIS Presented'); ?>;
    label8 = <?php echo xlj('Click here to choose a date'); ?>;
    label9 = <?php echo xlj('Click here to delete the row'); ?>;
    label10 = <?php echo xlj('Delete'); ?>;
    str = '<div class="form-row" id ="or_tr_'+new_tr_count+'">'+
              '<div class="form-group col" id ="observation_criteria_td_'+new_tr_count+'"><label>'+label1+'</label><select class="form-control" id="observation_criteria_'+new_tr_count+'" name="observation_criteria[]" onchange="selectCriteria(this.id,this.value);"></select>'+
              '</div>'+
              '<div id="observation_criteria_value_td_'+new_tr_count+'" class="form-group col observation_criteria_value_td" style="display: none;"><label>'+label2+'</label><select class="form-control" name="observation_criteria_value[]" id="observation_criteria_value_'+new_tr_count+'"></select>'+
              '</div>'+
              '<div class="form-group col code_serach_td" id="code_search_td_'+new_tr_count+'" style="display: none;"><label>'+label3+'</label>'+
                '<input type="text" id="sct_code_'+new_tr_count_2+'" name="sct_code[]" class="code form-control" onclick=sel_code(this.id) /><br />'+
                '<span id="displaytext_'+new_tr_count_2+'" class="displaytext d-block text-primary"></span>'+
                '<input type="hidden" id="codetext_'+new_tr_count_2+'" name="codetext[]" class="codetext" />'+
                '<input type="hidden"  value="SNOMED-CT" name="codetypehidden[]" id="codetypehidden'+new_tr_count_2+'" /> '+
             '</div>'+
             '<div class="form-group col code_serach_vaccine_type_td" id="code_serach_vaccine_type_td_'+new_tr_count+'" style="display: none;"><label>'+label4+'</label>'+
               '<input type="text" class="form-control" id="cvx_code'+new_tr_count_3+'" name="cvx_vac_type_code[]" onclick=sel_cvxcode(this); />'+
               '<div class="imm-imm-add-12" id="imm-imm-add-12'+new_tr_count_3+'"></div> '+
               '<input type="hidden"  value="CVX" name="code_type_hidden[]" id="code_type_hidden'+new_tr_count_3+'" /> '+
               '<input type="hidden" class="code_text_hidden" name="code_text_hidden[]" id="code_text_hidden'+new_tr_count_3+'" value="" />'+
             '</div>'+
             '<div id="vis_published_date_td_'+new_tr_count+'" class="form-group col vis_published_date_td" style="display: none;"><label>'+label5+'</label><input type="text" class="datepicker_dynamic form-control" name= "vis_published_date[]" id ="vis_published_date_'+new_tr_count+'" />'+
             '</div>'+
             '<div id="vis_presented_date_td_'+new_tr_count+'" class="form-group col vis_presented_date_td" style="display: none;"><label>'+label7+'</label><input type="text" class="datepicker_dynamic form-control" name= "vis_presented_date[]" id ="vis_presented_date_'+new_tr_count+'" />'+
             '</div>'+
             '<div class="form-group col d-flex align-items-end justify-content-center"><button type="button" class="btn btn-danger btn-delete" id="' + new_tr_count +'" onclick="RemoveRow(this.id);" title="' + label9 + '">' + label10 + '</button></div></div>';

    $(".obs_res_table").append(str);

    var ajax_url = 'immunizations.php';
    var target = $("#observation_criteria_"+new_tr_count);
    $.ajax({
        type: "POST",
        url: ajax_url,
        dataType: "json",
        data: {
            type : 'duplicate_row',
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        },
        success: function(thedata){
            $.each(thedata,function(i,item) {
                target.append($('<option></option>').val(item.option_id).text(item.title));
            });
            $('#observation_criteria_'+new_tr_count+' option[value=""]').prop('selected', true);
        },
        error:function(){
          alert("ajax error");
        }
    });
}

function sel_code(id)
{
    id = id.split('sct_code_');
    var checkId = id[1];
    $('#clickId').val(checkId);
    dlgopen('<?php echo $GLOBALS['webroot'] . "/interface/patient_file/encounter/" ?>find_code_popup.php', '_blank', 700, 400);
}

$(function () {
  $(".auto").select2({
        width: '100%',
        ajax: {
            url: "../../../library/ajax/imm_autocomplete/search.php",
            dataType: 'json',
            data: function(params) {
                return {
                  csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                  term: params.term
                };
            },
            processResults: function(data) {
                return  {
                    results: $.map(data, function(item, index) {
                        return {
                            text: item,
                            id: index
                        }
                    })
                };
                return x;
            },
            cache: true
        },
        minimumInputLength: 1,
        tags: true
    })

});
</script>

</html>
