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
        $sql = "SELECT fe.encounter as id,
                       fe.date,
                       fe.reason,
                       fe.facility,
                       fe.facility_id,
                       fe.pid,
                       fe.encounter as id,
                       fe.onset_date,
                       fe.sensitivity,
                       fe.billing_note,
                       fe.pc_catid,
                       fe.last_level_billed,
                       fe.last_level_closed,
                       fe.last_stmt_date,
                       fe.stmt_count,
                       fe.provider_id,
                       fe.supervisor_id,
                       fe.invoice_refno,
                       fe.referral_source,
                       fe.billing_facility,
                       fe.external_id,
                       fe.pos_code,
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
        $sql = "SELECT fe.encounter as id,
                       fe.date,
                       fe.reason,
                       fe.facility,
                       fe.facility_id,
                       fe.pid,
                       fe.onset_date,
                       fe.sensitivity,
                       fe.billing_note,
                       fe.pc_catid,
                       fe.last_level_billed,
                       fe.last_level_closed,
                       fe.last_stmt_date,
                       fe.stmt_count,
                       fe.provider_id,
                       fe.supervisor_id,
                       fe.invoice_refno,
                       fe.referral_source,
                       fe.billing_facility,
                       fe.external_id,
                       fe.pos_code,
                       opc.pc_catname,
                       fa.name AS billing_facility_name
                       FROM form_encounter as fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility
                       WHERE pid=? and fe.encounter=?
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

    public function updateVital($pid, $eid, $vid, $data)
    {
        $sql  = " UPDATE form_vitals SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid='" . add_escape_custom($pid) . "',";
        $sql .= "     bps='" . add_escape_custom($data["bps"]) . "',";
        $sql .= "     bpd='" . add_escape_custom($data["bpd"]) . "',";
        $sql .= "     weight='" . add_escape_custom($data["weight"]) . "',";
        $sql .= "     height='" . add_escape_custom($data["height"]) . "',";
        $sql .= "     temperature='" . add_escape_custom($data["temperature"]) . "',";
        $sql .= "     temp_method='" . add_escape_custom($data["temp_method"]) . "',";
        $sql .= "     pulse='" . add_escape_custom($data["pulse"]) . "',";
        $sql .= "     respiration='" . add_escape_custom($data["respiration"]) . "',";
        $sql .= "     note='" . add_escape_custom($data["note"]) . "',";
        $sql .= "     waist_circ='" . add_escape_custom($data["waist_circ"]) . "',";
        $sql .= "     head_circ='" . add_escape_custom($data["head_circ"]) . "',";
        $sql .= "     oxygen_saturation='" . add_escape_custom($data["oxygen_saturation"]) . "'";
        $sql .= "     where id='" . add_escape_custom($vid) . "'";

        return sqlStatement($sql);
    }

    public function insertVital($pid, $eid, $data)
    {
        $vitalSql  = " INSERT INTO form_vitals SET";
        $vitalSql .= "     date=NOW(),";
        $vitalSql .= "     activity=1,";
        $vitalSql .= "     pid='" . add_escape_custom($pid) . "',";
        $vitalSql .= "     bps='" . add_escape_custom($data["bps"]) . "',";
        $vitalSql .= "     bpd='" . add_escape_custom($data["bpd"]) . "',";
        $vitalSql .= "     weight='" . add_escape_custom($data["weight"]) . "',";
        $vitalSql .= "     height='" . add_escape_custom($data["height"]) . "',";
        $vitalSql .= "     temperature='" . add_escape_custom($data["temperature"]) . "',";
        $vitalSql .= "     temp_method='" . add_escape_custom($data["temp_method"]) . "',";
        $vitalSql .= "     pulse='" . add_escape_custom($data["pulse"]) . "',";
        $vitalSql .= "     respiration='" . add_escape_custom($data["respiration"]) . "',";
        $vitalSql .= "     note='" . add_escape_custom($data["note"]) . "',";
        $vitalSql .= "     waist_circ='" . add_escape_custom($data["waist_circ"]) . "',";
        $vitalSql .= "     head_circ='" . add_escape_custom($data["head_circ"]) . "',";
        $vitalSql .= "     oxygen_saturation='" . add_escape_custom($data["oxygen_saturation"]) . "'";

        $vitalResults = sqlInsert($vitalSql);

        if (!$vitalResults) {
            return false;
        }

        $formSql = "INSERT INTO forms SET";
        $formSql .= "     date=NOW(),";
        $formSql .= "     encounter='" . add_escape_custom($eid) . "',";
        $formSql .= "     form_name='Vitals',";
        $formSql .= "     authorized='1',";
        $formSql .= "     form_id='" . add_escape_custom($vitalResults) . "',";
        $formSql .= "     pid='" . add_escape_custom($pid) . "',";
        $formSql .= "     formdir='vitals'";

        $formResults = sqlInsert($formSql);

        return array($vitalResults, $formResults);
    }

    public function getVitals($pid, $eid)
    {
        $sql  = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_vitals fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.pid = ?";

        $statementResults = sqlStatement($sql, array($eid, $pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getVital($pid, $eid, $vid)
    {
        $sql  = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_vitals fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.id = ?";
        $sql .= "    AND fs.pid = ?";

        return sqlQuery($sql, array($eid, $vid, $pid));
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
