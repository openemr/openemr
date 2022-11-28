<?php

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\FormService;

$GLOBALS['form_exit_url'] = "javascript:parent.closeTab(window.name, false)";

function getFormByEncounter(
    $attendant_id,
    $encounter,
    $cols = "form_id, form_name",
    $name = "",
    $orderby = "FIND_IN_SET(formdir,'vitals') DESC, date DESC"
) {
    $formService = new FormService();
    return $formService->getFormByEncounter($attendant_id, $encounter, $cols, $name, $orderby);
}

function addForm(
    $encounter,
    $form_name,
    $form_id,
    $formdir,
    $pid,
    $authorized = "0",
    $date = "NOW()",
    $user = "",
    $group = "",
    $therapy_group = 'not_given'
) {

    global $attendant_type;
    if (!$user) {
        $user = $_SESSION['authUser'] ?? null;
    }

    if (!$group) {
        $group = $_SESSION['authProvider'] ?? null;
    }

    if ($therapy_group == 'not_given') {
        $therapy_group = $attendant_type == 'pid' ? null : $_SESSION['therapy_group'];
    }

    //print_r($_SESSION['therapy_group']);die;
        $arraySqlBind = array();
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
        "user, groupname, authorized, formdir, therapy_group_id) values (";
    if ($date == "NOW()") {
        $sql .= "$date";
    } else {
        $sql .= "?";
                array_push($arraySqlBind, $date);
    }

    $sql .= ", ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        array_push($arraySqlBind, $encounter, $form_name, $form_id, $pid, $user, $group, $authorized, $formdir, $therapy_group);
    return sqlInsert($sql, $arraySqlBind);
}

function authorizeForm($id, $authorized = "1")
{
    sqlQuery("UPDATE forms SET authorized = ? WHERE id = ? AND deleted = 0", array($authorized, $id));
}

function getEncounters($pid, $dateStart = '', $dateEnd = '', $encounterRuleType = '')
{
    $arraySqlBind = array();

    if ($encounterRuleType) {
        // Only collect certain type of encounters (list_options item from the rule_enc_types list that is mapped via enc_category_map table)
        $from = "form_encounter LEFT JOIN enc_category_map ON (form_encounter.pc_catid = enc_category_map.main_cat_id)";
        $where = "enc_category_map.rule_enc_id = ? and ";
        array_push($arraySqlBind, $encounterRuleType);
    } else {
        // Collect all encounters
        $from = "form_encounter";
    }

    if ($dateStart && $dateEnd) {
        $where .= "form_encounter.pid = ? and form_encounter.date >= ? and form_encounter.date <= ?";
        array_push($arraySqlBind, $pid, $dateStart, $dateEnd);
    } elseif ($dateStart && !$dateEnd) {
        $where .= "form_encounter.pid = ? and form_encounter.date >= ?";
        array_push($arraySqlBind, $pid, $dateStart);
    } elseif (!$dateStart && $dateEnd) {
        $where .= "form_encounter.pid = ? and form_encounter.date <= ?";
        array_push($arraySqlBind, $pid, $dateEnd);
    } else {
        $where .= "form_encounter.pid = ?";
        array_push($arraySqlBind, $pid);
    }

    //Not table escaping $from since this is hard-coded above and can include more than just a table name
    $res = sqlStatement("SELECT distinct encounter FROM " . $from . " WHERE " . $where . " ORDER by date desc", $arraySqlBind);

    $all = [];
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getEncounterDateByEncounter($encounter)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
    // $sql = "select date from forms where encounter='$encounter' order by date";
    $sql = "SELECT date FROM " . escape_table_name($table) . " WHERE encounter = ? ORDER BY date";
    return sqlQuery($sql, array($encounter));
}

function getProviderIdOfEncounter($encounter)
{
        global $attendant_type;
        $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
        $sql = "SELECT provider_id FROM " . escape_table_name($table) . " WHERE encounter=? ORDER BY date";
        $res = sqlQuery($sql, array($encounter));
        return $res['provider_id'];
}

function getFormNameByFormdirAndFormid($formdir, $form_id)
{
    return sqlQuery("SELECT form_name FROM forms WHERE formdir = ? AND form_id = ? AND deleted = 0", array($formdir, $form_id));
}

function getFormIdByFormdirAndFormid($formdir, $form_id)
{
    $result = sqlQuery("select id from forms where formdir = ? and form_id = ? and deleted = 0 ", array( $formdir, $form_id ));
    return $result['id'];
}

function getFormNameByFormdir($formdir)
{
    return sqlQuery("SELECT form_name FROM forms WHERE formdir = ? AND deleted = 0", array($formdir));
}

function getDocumentsByEncounter($patientID = null, $encounterID = null)
{
    $allDocuments = null;
    $currentEncounter = ( $encounterID ) ? $encounterID : $_SESSION['encounter'];
    $currentPatient = ( $patientID ) ? $patientID : $_SESSION['pid'];

    if ($currentPatient != "" && $currentEncounter != "") {
        $sql = "SELECT d.id, d.type, d.url, d.name as document_name, d.docdate, d.list_id, c.name, d.encounter_id FROM documents AS d, categories_to_documents AS cd,
			categories AS c WHERE d.foreign_id = ? AND d.encounter_id=? AND cd.document_id = d.id AND c.id = cd.category_id ORDER BY d.docdate DESC, d.id DESC";
        $res = sqlStatement($sql, array($currentPatient,$currentEncounter));

        while ($row = sqlFetchArray($res)) {
            $allDocuments[] = $row;
        }
    }

    return $allDocuments;
}

function hasFormPermission($formDir)
{
    // get the aco spec from registry table
    $formRow = sqlQuery("SELECT aco_spec FROM registry WHERE directory = ?", array($formDir));
    $permission = explode('|', $formRow['aco_spec']);
    return AclMain::aclCheckCore($permission[0], $permission[1]);
}
