<?php

/**
 *
 * CQM NQF 0002 Exclusion
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

class NQF_0002_Exclusion implements CqmFilterIF
{
    public function getTitle()
    {
        return "Exclusion";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
       //Children who are taking antibiotics in the 30 days prior to the diagnosis of pharyngitis
        $antibiotics  = implode(',', Codes::lookup(Medication::ANTIBIOTIC_FOR_PHARYNGITIS, 'RXNORM'));
        $pharyngitis_snomed_codes   = $pharyngitis_icd9_codes = $pharyngitis_icd10_codes = array();
        foreach (Codes::lookup(Diagnosis::ACUTE_PHARYNGITIS, 'SNOMED-CT') as $code) {
            $pharyngitis_snomed_codes[] = "SNOMED-CT:" . $code;
        }

        foreach (Codes::lookup(Diagnosis::ACUTE_PHARYNGITIS, 'ICD9') as $code) {
            $pharyngitis_icd9_codes[] = "ICD9:" . $code;
        }

        foreach (Codes::lookup(Diagnosis::ACUTE_PHARYNGITIS, 'ICD10') as $code) {
            $pharyngitis_icd10_codes[] = "ICD10:" . $code;
        }

        $pharyngitis_snomed_codes = "'" . implode("','", $pharyngitis_snomed_codes) . "'";
        $pharyngitis_icd9_codes   = "'" . implode("','", $pharyngitis_icd9_codes) . "'";
        $pharyngitis_icd10_codes  = "'" . implode("','", $pharyngitis_icd10_codes) . "'";

        $tonsillitis_snomed_codes = $tonsillitis_icd9_codes = $tonsillitis_icd10_codes = array();
        foreach (Codes::lookup(Diagnosis::ACUTE_TONSILLITIS, 'SNOMED-CT') as $code) {
            $tonsillitis_snomed_codes[] = "SNOMED-CT:" . $code;
        }

        foreach (Codes::lookup(Diagnosis::ACUTE_TONSILLITIS, 'ICD9') as $code) {
            $tonsillitis_icd9_codes[] = "ICD9:" . $code;
        }

        foreach (Codes::lookup(Diagnosis::ACUTE_TONSILLITIS, 'ICD10') as $code) {
            $tonsillitis_icd10_codes[] = "ICD10:" . $code;
        }

        $tonsillitis_snomed_codes = "'" . implode("','", $tonsillitis_snomed_codes) . "'";
        $tonsillitis_icd9_codes   = "'" . implode("','", $tonsillitis_icd9_codes) . "'";
        $tonsillitis_icd10_codes  = "'" . implode(',', $tonsillitis_icd10_codes) . "'";


        $query = "SELECT count(*) as cnt FROM form_encounter fe " .
                 "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid " .
                 " INNER JOIN lists l on l.type='medical_problem' and fe.pid = l.pid " .
                 "INNER JOIN prescriptions p ON fe.pid = p.patient_id " .
                 "WHERE opc.pc_catname = 'Office Visit' AND fe.pid = ? AND fe.date BETWEEN ? and ? " .
                 " AND p.rxnorm_drugcode in ( $antibiotics )" .
                 " AND (l.diagnosis in ($pharyngitis_snomed_codes) or l.diagnosis in ($pharyngitis_icd9_codes) or l.diagnosis in($pharyngitis_icd10_codes) " .
                 " or l.diagnosis in($tonsillitis_snomed_codes) or l.diagnosis in ($tonsillitis_icd9_codes) or l.diagnosis in ($tonsillitis_icd10_codes)) " .
                 " AND DATEDIFF(l.date,p.date_added) between 0 and 30 AND p.active = 1";

        $check = sqlQuery($query, array($patient->id, $beginDate, $endDate));
        if ($check['cnt'] >= 1) {//more than one medication it will exclude
            return true;
        } else {
            return false;
        }
    }
}
