<?php

/**
 * FormService refactored getFormByEncounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    <Unknown> Authorship was not listed in encounter.inc.php
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\BaseForm;

class FormService
{
    public function getFormByEncounter(
        $attendant_id,
        $encounter,
        $cols = "form_id, form_name",
        $name = "",
        $orderby = "FIND_IN_SET(formdir,'vitals') DESC, date DESC"
    ) {

        global $attendant_type;
        $arraySqlBind = [];
        $sql = "select " . escape_sql_column_name(process_cols_escape($cols), ['forms']) . " from forms where encounter = ? and deleted = 0 ";
        array_push($arraySqlBind, $encounter);
        if (!empty($name)) {
            $sql .= "and form_name=? ";
            array_push($arraySqlBind, $name);
        }

        if ($attendant_type == 'pid') {
            $sql .= " and pid=? and therapy_group_id IS NULL ";
        } else {
            $sql .= " and therapy_group_id = ? and pid IS NULL ";
        }

        array_push($arraySqlBind, $attendant_id);

        // Default $orderby puts vitals first in the list, and newpatient last:
        $sql .= "ORDER BY $orderby";

        $res = sqlStatement($sql, $arraySqlBind);

        $all = [];
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        // TODO: @adunsulag fire off a module filter event here letting us modify / restrict / add data to the form list.
        return $all;
    }

    public function addForm(
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
        $arraySqlBind = [];
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
        return QueryUtils::sqlInsert($sql, $arraySqlBind);
    }

    public function saveEncounterForm(BaseForm $form): BaseForm
    {
        // first we insert the form

        $data = $form->getFormTableDataForSave();
        $columns = implode(",", array_keys($data));
        $bind = array_values($data);
        $bindings = str_repeat("?,", count($data) - 1) . "?";
        $sql = "INSERT INTO " . \escape_table_name($form->getFormTableName()) . " (" . $columns . ") VALUES (" . $bindings . ")";
        $insert = QueryUtils::sqlInsert($sql, $bind);
        $form->setFormId($insert);

        $encounterFormData = $form->getEncounterFormDataForSave();
        $encounterFormColumns = implode(",", array_keys($encounterFormData));
        $encounterFormBind = array_values($encounterFormData);
        $encounterFormBindings = str_repeat("?,", count($encounterFormData) - 1) . "?";
        $encounterFormSql = "INSERT INTO forms (" . $encounterFormColumns
            . ") VALUES (" . $encounterFormBindings . ")";
        $id = QueryUtils::sqlInsert($encounterFormSql, $encounterFormBind);
        $form->setId($id);
        return $form;
    }

    public function hasFormPermission($formDir)
    {
        // get the aco spec from registry table
        $acoSpec = QueryUtils::fetchSingleValue(
            "SELECT aco_spec FROM registry WHERE directory = ?",
            'aco_spec',
            [$formDir]
        );
        $permission = explode('|', ($acoSpec ?? ''));
        return AclMain::aclCheckCore($permission[0], $permission[1] ?? null);
    }
}
