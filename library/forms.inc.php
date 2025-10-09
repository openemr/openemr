<?php

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\FormService;

$GLOBALS['form_exit_url'] = "javascript:parent.closeTab(window.name, false)";

/**
 * @deprecated Use FormService::getFormByEncounter() instead
 * @param $attendant_id
 * @param $encounter
 * @param $cols
 * @param $name
 * @param $orderby
 * @return array
 */
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

/**
 * @deprecated Use FormService::addForm() instead
 * @param $encounter
 * @param $form_name
 * @param $form_id
 * @param $formdir
 * @param $pid
 * @param $authorized
 * @param $date
 * @param $user
 * @param $group
 * @param $therapy_group
 * @return int
 */
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
    $formService = new FormService();
    return $formService->addForm($encounter, $form_name, $form_id, $formdir, $pid, $authorized, $date, $user, $group, $therapy_group);
}

function authorizeForm($id, $authorized = "1"): void
{
    sqlQuery("UPDATE forms SET authorized = ? WHERE id = ? AND deleted = 0", [$authorized, $id]);
}

function getEncounters($pid, $dateStart = '', $dateEnd = '', $encounterRuleType = '')
{
    $arraySqlBind = [];

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
    return sqlQuery($sql, [$encounter]);
}

function getProviderIdOfEncounter($encounter)
{
        global $attendant_type;
        $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
        $sql = "SELECT provider_id FROM " . escape_table_name($table) . " WHERE encounter=? ORDER BY date";
        $res = sqlQuery($sql, [$encounter]);
        return $res['provider_id'];
}

function getFormNameByFormdirAndFormid($formdir, $form_id)
{
    return sqlQuery("SELECT form_name FROM forms WHERE formdir = ? AND form_id = ? AND deleted = 0", [$formdir, $form_id]);
}

function getFormIdByFormdirAndFormid($formdir, $form_id)
{
    $result = sqlQuery("select id from forms where formdir = ? and form_id = ? and deleted = 0 ", [ $formdir, $form_id ]);
    return $result['id'];
}

function getFormNameByFormdir($formdir)
{
    return sqlQuery("SELECT form_name FROM forms WHERE formdir = ? AND deleted = 0", [$formdir]);
}

function getDocumentsByEncounter($patientID = null, $encounterID = null)
{
    $allDocuments = null;
    $currentEncounter = $encounterID ?: $_SESSION['encounter'];
    $currentPatient = $patientID ?: $_SESSION['pid'];

    if ($currentPatient != "" && $currentEncounter != "") {
        $sql = "SELECT d.id, d.type, d.url, d.name as document_name, d.docdate, d.list_id, c.name, d.encounter_id FROM documents AS d, categories_to_documents AS cd,
			categories AS c WHERE d.foreign_id = ? AND d.encounter_id=? AND cd.document_id = d.id AND c.id = cd.category_id ORDER BY d.docdate DESC, d.id DESC";
        $res = sqlStatement($sql, [$currentPatient,$currentEncounter]);

        while ($row = sqlFetchArray($res)) {
            $allDocuments[] = $row;
        }
    }

    return $allDocuments;
}

function hasFormPermission($formDir)
{
    // get the aco spec from registry table
    $formRow = sqlQuery("SELECT aco_spec FROM registry WHERE directory = ?", [$formDir]);
    $permission = explode('|', ($formRow['aco_spec'] ?? ''));
    return AclMain::aclCheckCore($permission[0], $permission[1] ?? null);
}
