<?php

/**
 *
 * CQM NQF 0384 Initial Patient Population
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

class NQF_0384_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle()
    {
        return "Initial Patient Population";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $cancerCheckQry = "SELECT count(*) as cnt FROM form_encounter fe " .
                          "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid " .
                          "INNER JOIN lists l ON fe.pid = l.pid AND l.type = 'medical_problem' AND (l.diagnosis LIKE '%ICD9:153%' OR l.diagnosis LIKE '%ICD10:C18%' OR l.diagnosis LIKE '%ICD9:185%' OR l.diagnosis LIKE '%ICD10:C61%') " .
                          "WHERE opc.pc_catname = 'Office Visit' " .
                          "AND fe.pid = ? " .
                          "AND fe.date BETWEEN ? AND ? ";
        $check_cancer = sqlQuery($cancerCheckQry, array($patient->id, $beginDate, $endDate));
        if ($check_cancer['cnt'] > 0) {
            return true;
        } else {
            $radiotheraphyQry = "SELECT count(*) as cnt FROM form_encounter fe " .
                                "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid " .
                                "INNER JOIN procedure_order pr ON  fe.encounter = pr.encounter_id " .
                                "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                                "WHERE opc.pc_catname = 'Office Visit' " .
                                "AND (fe.date BETWEEN ? AND ?) " .
                                "AND fe.pid = ? " .
                                "AND prc.procedure_code = '77427' ";
            $check_radiotheraphy = sqlQuery($radiotheraphyQry, array( $beginDate, $endDate, $patient->id));
            if ($check_radiotheraphy['cnt'] > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
