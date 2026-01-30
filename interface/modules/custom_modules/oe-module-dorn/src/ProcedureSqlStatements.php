<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

class ProcedureSqlStatements
{
    public function __construct()
    {
    }

    public static function getProcedureOrder($orderid)
    {
        $porow = sqlQuery("SELECT " .
            "po.date_collected, po.date_ordered, po.order_priority,po.billing_type,po.clinical_hx,po.account,po.order_diagnosis, " .
            "pp.*, " .
            "pd.pid, pd.pubpid, pd.fname, pd.lname, pd.mname, pd.DOB, pd.ss, pd.race, " .
            "pd.phone_home, pd.phone_biz, pd.sex, pd.street, pd.city, pd.state, pd.postal_code, " .
            "f.encounter, u.fname AS docfname, u.lname AS doclname, u.npi AS docnpi, u.id as user_id " .
            "FROM procedure_order AS po, procedure_providers AS pp, " .
            "forms AS f, patient_data AS pd, users AS u " .
            "WHERE " .
            "po.procedure_order_id = ? AND " .
            "pp.ppid = po.lab_id AND " .
            "f.formdir = 'procedure_order' AND " .
            "f.form_id = po.procedure_order_id AND " .
            "pd.pid = f.pid AND " .
            "u.id = po.provider_id", [$orderid]);
        return $porow;
    }
    public static function getProcedureCode($orderid)
    {
        $pcres = sqlStatement("SELECT " .
            "pc.procedure_code, pc.procedure_name, pc.procedure_order_seq, pc.diagnoses " .
            "FROM procedure_order_code AS pc " .
            "WHERE " .
            "pc.procedure_order_id = ? AND " .
            "pc.do_not_send = 0 " .
            "ORDER BY pc.procedure_order_seq", [$orderid]);
        return $pcres;
    }
    public static function getVitals($pid, $encounter)
    {
        $vitals = sqlQuery("SELECT * FROM form_vitals v join forms f on f.form_id=v.id WHERE f.pid=? and f.encounter=? ORDER BY v.date DESC LIMIT 1", [$pid, $encounter]);
        return $vitals;
    }
    public static function getSpecimen($procedureCode)
    {
        $vitals = sqlQuery("SELECT specimen FROM procedure_type WHERE procedure_code=?", [$procedureCode]);
        return $vitals;
    }
    public static function getProcedureAnswers($labId, $procedureCode, $orderId, $procOrderSeq)
    {
        $qres = sqlStatement("SELECT " .
            "a.question_code, a.answer, q.fldtype , q.tips " .
            "FROM procedure_answers AS a " .
            "LEFT JOIN procedure_questions AS q ON " .
            "q.lab_id = ? " .
            "AND q.procedure_code = ? AND " .
            "q.question_code = a.question_code " .
            "WHERE " .
            "a.procedure_order_id = ? AND " .
            "a.procedure_order_seq = ? " .
            "ORDER BY q.seq, a.answer_seq", [$labId, $procedureCode, $orderId, $procOrderSeq]);
        return $qres;
    }
}
