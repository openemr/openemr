<?php
/**
 * EncounterService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Services;

use Particle\Validator\Validator;

class EncounterService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function validateSoapNote($soapNote)
    {
        $validator = new Validator();

        $validator->optional('subjective')->lengthBetween(2, 65535);
        $validator->optional('objective')->lengthBetween(2, 65535);
        $validator->optional('assessment')->lengthBetween(2, 65535);
        $validator->optional('plan')->lengthBetween(2, 65535);

        return $validator->validate($soapNote);
    }

    public function validateVital($vital)
    {
        $validator = new Validator();

        $validator->optional('temp_method')->lengthBetween(1, 255);
        $validator->optional('note')->lengthBetween(1, 255);
        $validator->optional('BMI_status')->lengthBetween(1, 255);
        $validator->optional('bps')->numeric();
        $validator->optional('bpd')->numeric();
        $validator->optional('weight')->numeric();
        $validator->optional('height')->numeric();
        $validator->optional('temperature')->numeric();
        $validator->optional('pulse')->numeric();
        $validator->optional('respiration')->numeric();
        $validator->optional('BMI')->numeric();
        $validator->optional('waist_circ')->numeric();
        $validator->optional('head_circ')->numeric();
        $validator->optional('oxygen_saturation')->numeric();

        return $validator->validate($vital);
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

        $statementResults = sqlStatement($sql, array($pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getEncountersBySearch($search)
    {
        $sqlBindArray = array();

        $sql = "SELECT fe.encounter as id,
                       fe.date,
                       fe.reason,
                       fe.facility,
                       fe.facility_id,
                       fe.pid,
                       fe.encounter,
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
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility";

        if ($search['pid'] || $search['provider_id']) {
            $sql .= " WHERE ";

            $whereClauses = array();
            if ($search['pid']) {
                array_push($whereClauses, "pid=?");
                array_push($sqlBindArray, $search['pid']);
            }
            if ($search['provider_id']) {
                array_push($whereClauses, "provider_id=?");
                array_push($sqlBindArray, $search['provider_id']);
            }

            $sql .= implode(" AND ", $whereClauses);
        } else {
            return false;
        }
        $sql .= " ORDER BY fe.id DESC";
        $statementResults = sqlStatement($sql, $sqlBindArray);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getEncounter($eid)
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
                       WHERE fe.encounter=?
                       ORDER BY fe.id
                       DESC";

        return sqlQuery($sql, array($eid));
    }

    // @todo recm changing routes
    // encounter id is system unique so pid is not needed
    // resources should be independent where possible
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
        $soapSql .= "     pid=?,";
        $soapSql .= "     subjective=?,";
        $soapSql .= "     objective=?,";
        $soapSql .= "     assessment=?,";
        $soapSql .= "     plan=?";

        $soapResults = sqlInsert(
            $soapSql,
            array(
                $pid,
                $data["subjective"],
                $data["objective"],
                $data["assessment"],
                $data["plan"]
            )
        );

        if (!$soapResults) {
            return false;
        }

        $formSql = "INSERT INTO forms SET";
        $formSql .= "     date=NOW(),";
        $formSql .= "     encounter=?,";
        $formSql .= "     form_name='SOAP',";
        $formSql .= "     authorized='1',";
        $formSql .= "     form_id=?,";
        $formSql .= "     pid=?,";
        $formSql .= "     formdir='soap'";

        $formResults = sqlInsert(
            $formSql,
            array(
                $eid,
                $soapResults,
                $pid
            )
        );

        return array($soapResults, $formResults);
    }

    public function updateSoapNote($pid, $eid, $sid, $data)
    {
        $sql  = " UPDATE form_soap SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid=?,";
        $sql .= "     subjective=?,";
        $sql .= "     objective=?,";
        $sql .= "     assessment=?,";
        $sql .= "     plan=?";
        $sql .= "     where id=?";

        return sqlStatement(
            $sql,
            array(
                $pid,
                $data["subjective"],
                $data["objective"],
                $data["assessment"],
                $data["plan"],
                $sid
            )
        );
    }

    public function updateVital($pid, $eid, $vid, $data)
    {
        $sql  = " UPDATE form_vitals SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid=?,";
        $sql .= "     bps=?,";
        $sql .= "     bpd=?,";
        $sql .= "     weight=?,";
        $sql .= "     height=?,";
        $sql .= "     temperature=?,";
        $sql .= "     temp_method=?,";
        $sql .= "     pulse=?,";
        $sql .= "     respiration=?,";
        $sql .= "     note=?,";
        $sql .= "     waist_circ=?,";
        $sql .= "     head_circ=?,";
        $sql .= "     oxygen_saturation=?";
        $sql .= "     where id=?";

        return sqlStatement(
            $sql,
            array(
                $pid,
                $data["bps"],
                $data["bpd"],
                $data["weight"],
                $data["height"],
                $data["temperature"],
                $data["temp_method"],
                $data["pulse"],
                $data["respiration"],
                $data["note"],
                $data["waist_circ"],
                $data["head_circ"],
                $data["oxygen_saturation"],
                $vid
            )
        );
    }

    public function insertVital($pid, $eid, $data)
    {
        $vitalSql  = " INSERT INTO form_vitals SET";
        $vitalSql .= "     date=NOW(),";
        $vitalSql .= "     activity=1,";
        $vitalSql .= "     pid=?,";
        $vitalSql .= "     bps=?,";
        $vitalSql .= "     bpd=?,";
        $vitalSql .= "     weight=?,";
        $vitalSql .= "     height=?,";
        $vitalSql .= "     temperature=?,";
        $vitalSql .= "     temp_method=?,";
        $vitalSql .= "     pulse=?,";
        $vitalSql .= "     respiration=?,";
        $vitalSql .= "     note=?,";
        $vitalSql .= "     waist_circ=?,";
        $vitalSql .= "     head_circ=?,";
        $vitalSql .= "     oxygen_saturation=?";

        $vitalResults = sqlInsert(
            $vitalSql,
            array(
                $pid,
                $data["bps"],
                $data["bpd"],
                $data["weight"],
                $data["height"],
                $data["temperature"],
                $data["temp_method"],
                $data["pulse"],
                $data["respiration"],
                $data["note"],
                $data["waist_circ"],
                $data["head_circ"],
                $data["oxygen_saturation"]
            )
        );

        if (!$vitalResults) {
            return false;
        }

        $formSql = "INSERT INTO forms SET";
        $formSql .= "     date=NOW(),";
        $formSql .= "     encounter=?,";
        $formSql .= "     form_name='Vitals',";
        $formSql .= "     authorized='1',";
        $formSql .= "     form_id=?,";
        $formSql .= "     pid=?,";
        $formSql .= "     formdir='vitals'";

        $formResults = sqlInsert(
            $formSql,
            array(
                $eid,
                $vitalResults,
                $pid
            )
        );

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
