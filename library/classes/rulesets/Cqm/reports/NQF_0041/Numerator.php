<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0041_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $periodPlus89Days   = date('Y-m-d 00:00:00', strtotime('+89 day', strtotime($beginDate)));
        $periodMinus153Days = date('Y-m-d 00:00:00', strtotime('-153 day', strtotime($beginDate)));
        $influenza_procedure = implode(',', Codes::lookup(Procedure::INFLU_VACCINE, 'SNOMED'));
        $influenza_medication = implode(',', Codes::lookup(Medication::INFLUENZA_VACCINE, 'CVX'));
        $provider_communication = implode(',', Codes::lookup(Communication::PREV_RECEIPT_VACCINE, 'SNOMED'));

        // Influenza vaccine procedure check
        $query = "select count(*) as cnt from form_encounter fe " .
               "INNER JOIN procedure_order po on po.patient_id = fe.pid " .
               "INNER JOIN procedure_order_code poc on po.procedure_order_id = poc.procedure_order_id " .
               "WHERE pid = ? and fe.date between ? and  ? " .
               "AND poc.procedure_code in ($influenza_procedure) and ( po.date_ordered <= ? or po.date_ordered <= ? )";

        $sql = sqlQuery($query, array($patient->id,$beginDate,$endDate,$periodMinus153Days,$periodPlus89Days));
        if ($sql['cnt'] > 0) {
            return true;
        }

        $query = "select count(*) as cnt from form_encounter fe " .
               "INNER JOIN immunizations imm on imm.patient_id = fe.pid " .
               "WHERE pid = ? and fe.date between ? and ? " .
               "AND imm.cvx_code in ($influenza_medication) and (imm.administered_date <= ? or imm.administered_date <= ?) ";
        $sql = sqlQuery($query, array($patient->id,$beginDate,$endDate,$periodMinus153Days,$periodPlus89Days));
        if ($sql['cnt'] > 0) {
            return true;
        }

        return false;
    }
}
