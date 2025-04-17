<?php

/**
 *
 * CQM NQF 0013 Exclusion
 *
 * Copyright (C) 2015 Ensoftek, Inc
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
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

class NQF_0013_Exclusion implements CqmFilterIF
{
    public function getTitle()
    {
        return "Exclusion";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        //Also exclude patients with a diagnosis of pregnancy during the measurement period.
        if (Helper::check(ClinicalType::DIAGNOSIS, Diagnosis::PREGNANCY, $patient, $beginDate, $beginDate)  || Helper::check(ClinicalType::DIAGNOSIS, Diagnosis::END_STAGE_RENAL_DISEASE, $patient, $beginDate, $beginDate) || Helper::check(ClinicalType::DIAGNOSIS, Diagnosis::CHRONIC_KIDNEY_DISEASE, $patient, $beginDate, $beginDate)) {
            return true;
        }

        $procedure_code = implode(',', Codes::lookup(Procedure::DIALYSIS_SERVICE, 'SNOMED'));
        //Dialysis procedure exists exclude the patient
        $sql = "SELECT count(*) as cnt FROM procedure_order pr " .
               "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
               "WHERE pr.patient_id = ? " .
               "AND prc.procedure_code IN ($procedure_code) " .
               "AND (pr.date_ordered BETWEEN ? AND ?)";
        //echo $sql;
        $check = sqlQuery($sql, array($patient->id, $beginDate, $endDate));
        if ($check['cnt'] > 0) {
            return true;
        }

        return false;
    }
}
