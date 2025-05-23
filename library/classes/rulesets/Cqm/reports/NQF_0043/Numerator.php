<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0043_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $vac_medication     = implode(',', Codes::lookup(Medication::PNEUMOCOCCAL_VAC, 'CVX'));
        $vac_procedure      = implode(',', Codes::lookup(Procedure::PNEUMOCOCCAL_VACCINE, 'SNOMED'));

        $query = "select count(*) cnt from form_encounter fe " .
               "INNER JOIN procedure_order po on po.patient_id = fe.pid " .
               "INNER JOIN procedure_order_code poc on poc.procedure_order_id = po.procedure_order_id " .
               "WHERE fe.pid = ? AND fe.date between ? and ? " .
               "AND poc.procedure_code in ($vac_procedure) AND po.date_ordered between ? and ? ";

        $sql = sqlQuery($query, array($patient->id,$beginDate,$endDate,$beginDate,$endDate));
        if ($sql['cnt'] > 0) {
            return true;
        }

        $query = "select count(*) cnt from form_encounter fe " .
                 "INNER JOIN immunizations imm on imm.patient_id = fe.pid " .
                 "WHERE fe.pid = ? and fe.date between ? and  ? " .
                 "AND imm.cvx_code in ($vac_medication) AND imm.administered_date between ? and ?";

        $sql = sqlQuery($query, array($patient->id,$beginDate,$endDate,$beginDate,$endDate));
        if ($sql['cnt'] > 0) {
            return true;
        }

        return false;
    }
}
