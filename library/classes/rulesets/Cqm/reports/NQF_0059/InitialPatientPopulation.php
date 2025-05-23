<?php

/**
 * Copyright (C) 2016 Visolve <services@visolve.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  ViSolve Inc <services@visolve.com>
 * @link    http://www.open-emr.org
 */

class NQF_0059_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle()
    {
        return "Initial Patient Population";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $age = $patient->calculateAgeOnDate($beginDate);
        if ($age >= 18 && $age < 75 && Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_OFF_VIS, $patient, $beginDate, $endDate)) {
            $diabetes_codes = array();
            foreach (Codes::lookup(Diagnosis::DIABETES, 'SNOMED-CT') as $code) {
                $diabetes_codes[] = "SNOMED-CT:" . $code;
            }

            $diabetes_codes = "'" . implode("','", $diabetes_codes) . "'";

            $query = "select count(*) cnt from form_encounter fe " .
                     "inner join lists l on ( l.type='medical_problem' and l.pid = fe.pid )" .
                     "where fe.pid = ? and fe.date between ? and ? " .
                     "and l.diagnosis in ($diabetes_codes) and (l.begdate < ? or (l.begdate between ? and ? )) and (l.enddate is null or l.enddate > ? )";

            $sql = sqlQuery($query, array($patient->id,$beginDate,$endDate,$beginDate,$beginDate,$endDate,$endDate));
            if ($sql['cnt'] > 0) {
                return true;
            }

            return false;
        }

        return false;
    }
}
