<?php
/**
 * EncounterService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

class EncounterService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function getEncountersForPatient($pid)
    {
        $sql = "SELECT fe.*,
                       opc.pc_catname,
                       fa.name AS billing_facility_name
                       FROM form_encounter as fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility
                       WHERE pid=?
                       ORDER BY fe.id
                       DESC";

        $statementResults = sqlStatement($sql, $pid);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getEncounterForPatient($pid, $eid)
    {
        $sql = "SELECT fe.*,
                       opc.pc_catname,
                       fa.name AS billing_facility_name
                       FROM form_encounter as fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility
                       WHERE pid=? and fe.id=?
                       ORDER BY fe.id
                       DESC";

        return sqlQuery($sql, array($pid, $eid));
    }

    public function insertSoapNote($pid, $eid, $data)
    {
        $soapSql  = " INSERT INTO form_soap SET";
        $soapSql .= "     date=NOW(),";
        $soapSql .= "     activity=1,";
        $soapSql .= "     pid='" . add_escape_custom($pid) . "',";
        $soapSql .= "     subjective='" . add_escape_custom($data["subjective"]) . "',";
        $soapSql .= "     objective='" . add_escape_custom($data["objective"]) . "',";
        $soapSql .= "     assessment='" . add_escape_custom($data["assessment"]) . "',";
        $soapSql .= "     plan='" . add_escape_custom($data["plan"]) . "'";

        $soapResults = sqlInsert($soapSql);

        if (!$soapResults) {
            return false;
        }

        $formSql = "INSERT INTO forms SET";
        $formSql .= "     date=NOW(),";
        $formSql .= "     encounter='" . add_escape_custom($eid) . "',";
        $formSql .= "     form_name='SOAP',";
        $formSql .= "     authorized='1',";
        $formSql .= "     form_id='" . add_escape_custom($soapResults) . "',";
        $formSql .= "     pid='" . add_escape_custom($pid) . "',";
        $formSql .= "     formdir='soap'";

        $formResults = sqlInsert($formSql);

        return array($soapResults, $formResults);
    }

    public function updateSoapNote($pid, $eid, $sid, $data)
    {
        $sql  = " UPDATE form_soap SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid='" . add_escape_custom($pid) . "',";
        $sql .= "     subjective='" . add_escape_custom($data["subjective"]) . "',";
        $sql .= "     objective='" . add_escape_custom($data["objective"]) . "',";
        $sql .= "     assessment='" . add_escape_custom($data["assessment"]) . "',";
        $sql .= "     plan='" . add_escape_custom($data["plan"]) . "'";
        $sql .= "     where id='" . add_escape_custom($sid) . "'";

        return sqlStatement($sql);
    }

    public function getSoapNotes($pid, $eid)
    {
        $sql  = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_soap fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.pid = ?";

        $statementResults = sqlStatement($sql, array($eid, $pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getSoapNote($pid, $eid, $sid)
    {
        $sql  = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_soap fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.id = ?";
        $sql .= "    AND fs.pid = ?";

        return sqlQuery($sql, array($eid, $sid, $pid));
    }
}
