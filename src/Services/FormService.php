<?php

/**
 * FormService refactored getFormByEncounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    <Unknown> Authorship was not listed in encounter.inc
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

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
        $arraySqlBind = array();
        $sql = "select " . escape_sql_column_name(process_cols_escape($cols), array('forms')) . " from forms where encounter = ? and deleted = 0 ";
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

        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        return $all;
    }
}
