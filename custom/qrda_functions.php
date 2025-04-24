<?php

/**
 *
 * QRDA Functions
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

  use OpenEMR\Services\FacilityService;

  $facilityService = new FacilityService();

    // Functions for QRDA Category I (or) III 2014 XML format.

    //function for Stratification data getting for NQF# 0024 Rule
function getQRDAStratumInfo($patArr, $begin_date)
{
    $startumArr = array();
    if (count($patArr) > 0) {
        //Age Between 3 and 11
        $stratumOneQry = "SELECT FLOOR( DATEDIFF( '" . add_escape_custom($begin_date) . "' , DOB ) /365 ) as pt_age FROM patient_data WHERE pid IN (" . add_escape_custom(implode(",", $patArr)) . ") HAVING  (pt_age BETWEEN 1 AND 10) ";
        $stratumOneRes = sqlStatement($stratumOneQry);
        $stratumOneRows = sqlNumRows($stratumOneRes);

        //Age Between 12 and 17
        $stratumTwoQry = "SELECT FLOOR( DATEDIFF( '" . add_escape_custom($begin_date) . "' , DOB ) /365 ) as pt_age FROM patient_data WHERE pid IN (" . add_escape_custom(implode(",", $patArr)) . ") HAVING  (pt_age BETWEEN 11 AND 16) ";
        $stratumTwoRes = sqlStatement($stratumTwoQry);
        $stratumTwoRows = sqlNumRows($stratumTwoRes);
        $startumArr[1] = $stratumOneRows;
        $startumArr[2] = $stratumTwoRows;
    } else {
        $startumArr[1] = 0;
        $startumArr[2] = 0;
    }

    return $startumArr;
}

    //function for getting Payer(Insurance Type) Information for Export QRDA
function getQRDAPayerInfo($patArr)
{
    $payerCheckArr = array();
    $payerCheckArr['Medicare'] = 0;
    $payerCheckArr['Medicaid'] = 0;
    $payerCheckArr['Private Health Insurance'] = 0;
    $payerCheckArr['Other'] = 0;
    if (count($patArr) > 0) {
        $insQry = "SELECT insd.*, ic.ins_type_code FROM (SELECT pid, provider FROM insurance_data WHERE type = 'primary' ORDER BY id DESC) insd " .
                  "INNER JOIN  insurance_companies ic ON insd.provider = ic.id " .
                  "WHERE insd.pid IN (" . add_escape_custom(implode(",", $patArr)) . ")";
        $insRes = sqlStatement($insQry);
        while ($insRow = sqlFetchArray($insRes)) {
            if ($insRow['ins_type_code'] == 8) {//Self Pay (Private Insurance)
                $payerCheckArr['Private Health Insurance']++;
            } elseif ($insRow['ins_type_code'] == 2) {//Medicare
                $payerCheckArr['Medicare']++;
            } elseif ($insRow['ins_type_code'] == 3) {//Self Pay (Private Insurance)
                $payerCheckArr['Medicaid']++;
            } else {//Other
                $payerCheckArr['Other']++;
            }
        }
    }

    return $payerCheckArr;
}

    //function for getting Race, Ethnicity and Gender Information for Export QRDA
function getQRDAPatientNeedInfo($patArr)
{
    //Defining Array elements
    //Gender
    $genderArr = array();
    $genderArr['Male'] = 0;
    $genderArr['Female'] = 0;
    $genderArr['Unknown'] = 0;
    //Race
    $raceArr = array();
    $raceArr['American Indian or Alaska Native'] = 0;
    $raceArr['Asian'] = 0;
    $raceArr['Black or African American'] = 0;
    $raceArr['Native Hawaiian or Other Pacific Islander'] = 0;
    $raceArr['White'] = 0;
    $raceArr['Other'] = 0;
    //Ethnicity
    $ethincityArr = array();
    $ethincityArr['Not Hispanic or Latino'] = 0;
    $ethincityArr['Hispanic or Latino'] = 0;

    $mainArr = array();
    if (count($patArr) > 0) {
        $patRes = sqlStatement("SELECT pid, sex, race, ethnicity FROM patient_data WHERE pid IN (" . add_escape_custom(implode(",", $patArr)) . ")");
        while ($patRow = sqlFetchArray($patRes)) {
            //Gender Collection
            if ($patRow['sex'] == "Male") {
                $genderArr['Male']++;
            } elseif ($patRow['sex'] == "Female") {
                $genderArr['Female']++;
            } else {
                $genderArr['Unknown']++;
            }

            //Race Section
            if ($patRow['race'] == "amer_ind_or_alaska_native") {
                $raceArr['American Indian or Alaska Native']++;
            } elseif ($patRow['race'] == "Asian") {
                $raceArr['Asian']++;
            } elseif ($patRow['race'] == "black_or_afri_amer") {
                $raceArr['Black or African American']++;
            } elseif ($patRow['race'] == "native_hawai_or_pac_island") {
                $raceArr['Native Hawaiian or Other Pacific Islander']++;
            } elseif ($patRow['race'] == "white") {
                $raceArr['White']++;
            } elseif ($patRow['race'] == "Asian_Pacific_Island") {
                $raceArr['Other']++;
            } elseif ($patRow['race'] == "Black_not_of_Hispan") {
                $raceArr['Other']++;
            } elseif ($patRow['race'] == "Hispanic") {
                $raceArr['Other']++;
            } elseif ($patRow['race'] == "White_not_of_Hispan") {
                $raceArr['Other']++;
            } else {
                $raceArr['Other']++;
            }

            if ($patRow['ethnicity'] == "hisp_or_latin") {
                $ethincityArr['Hispanic or Latino']++;
            } elseif ($patRow['ethnicity'] == "not_hisp_or_latin") {
                $ethincityArr['Not Hispanic or Latino']++;
            }
        }
    }

    $mainArr['gender'] = $genderArr;
    $mainArr['race'] = $raceArr;
    $mainArr['ethnicity'] = $ethincityArr;

    return $mainArr;
}

function payerPatient($patient_id)
{
    $payer = 'Other';
    $insQry = "SELECT insd.*, ic.ins_type_code FROM (SELECT pid, provider FROM insurance_data WHERE type = 'primary' ORDER BY id DESC) insd " .
              "INNER JOIN  insurance_companies ic ON insd.provider = ic.id " .
              "WHERE insd.pid = ?";
    $insRes = sqlStatement($insQry, array($patient_id));
    while ($insRow = sqlFetchArray($insRes)) {
        if ($insRow['ins_type_code'] == 8) {//Self Pay (Private Insurance)
            $payer = 'Private Health Insurance';
        } elseif ($insRow['ins_type_code'] == 2) {//Medicare
            $payer = 'Medicare';
        } elseif ($insRow['ins_type_code'] == 3) {//Self Pay (Private Insurance)
            $payer = 'Medicaid';
        } else {//Other
            $payer = 'Other';
        }
    }

    return $payer;
}

function allEncPat($patient_id, $from_date, $to_date)
{
    $encArr = array();
    $patQry = "SELECT fe.encounter, fe.date,fe.pc_catid,opc.pc_catname FROM form_encounter fe inner join openemr_postcalendar_categories opc on opc.pc_catid = fe.pc_catid WHERE fe.pid = ? AND (DATE(fe.date) BETWEEN ? AND ?)";
    $patRes = sqlStatement($patQry, array($patient_id, $from_date, $to_date));
    while ($patRow = sqlFetchArray($patRes)) {
        $encArr[] = $patRow;
    }

    return $encArr;
}

function allListsPat($type, $patient_id, $from_date, $to_date)
{
    $diagArr = array();
    $diagQry = "SELECT * FROM lists WHERE TYPE = ? AND pid = ? AND (DATE(date) BETWEEN ? AND ?)";
    $diagRes = sqlStatement($diagQry, array($type, $patient_id, $from_date, $to_date));
    while ($diagRow = sqlFetchArray($diagRes)) {
        $diagArr[] = $diagRow;
    }

    return $diagArr;
}

function allOrderMedsPat($patient_id, $from_date, $to_date)
{
    $medArr = array();
    $medQry = "SELECT * FROM prescriptions where patient_id = ? AND active = 0 AND (DATE(date_added) BETWEEN ? AND ?)";
    $medRes = sqlStatement($medQry, array($patient_id, $from_date, $to_date));
    while ($medRow = sqlFetchArray($medRes)) {
        $medArr[] = $medRow;
    }

    return $medArr;
}

function allActiveMedsPat($patient_id, $from_date, $to_date)
{
    $medArr = array();
    $medQry = "SELECT * FROM prescriptions where patient_id = ? AND active = 1 AND (DATE(date_added) BETWEEN ? AND ?)";
    $medRes = sqlStatement($medQry, array($patient_id, $from_date, $to_date));
    while ($medRow = sqlFetchArray($medRes)) {
        $medArr[] = $medRow;
    }

    return $medArr;
}

function allProcPat(string $proc_type = null, $patient_id, $from_date, $to_date)
{
    if (!$proc_type) {
        $proc_type = "Procedure";
    }
    $procArr = array();
    $procQry = "SELECT poc.procedure_code, poc.procedure_name, po.date_ordered, fe.encounter,fe.date FROM form_encounter fe " .
            "INNER JOIN forms f ON f.encounter = fe.encounter AND f.deleted != 1 AND f.formdir = 'procedure_order' " .
            "INNER JOIN procedure_order po ON po.encounter_id = f.encounter " .
            "INNER JOIN procedure_order_code poc ON poc.procedure_order_id = po.procedure_order_id " .
            "WHERE poc.procedure_order_title = ? AND po.patient_id = ? " .
            "AND (po.date_ordered BETWEEN ? AND ?)";
    $procRes = sqlStatement($procQry, array($proc_type, $patient_id, $from_date, $to_date));
    while ($procRow = sqlFetchArray($procRes)) {
        $procArr[] = $procRow;
    }

    return $procArr;
}

function allVitalsPat($patient_id, $from_date, $to_date)
{
    $vitArr = array();
    $vitQry = "SELECT fe.encounter, v.bps, v.date,v.bpd,v.BMI as bmi FROM form_encounter fe " .
            "INNER JOIN forms f ON f.encounter = fe.encounter AND f.deleted != 1 AND f.formdir = 'vitals' " .
            "INNER JOIN form_vitals v ON v.id = f.form_id " .
            "WHERE v.pid = ? " .
            "AND (v.date BETWEEN ? AND ?)";
    $vitRes = sqlStatement($vitQry, array($patient_id, $from_date, $to_date));
    while ($vitRow = sqlFetchArray($vitRes)) {
        $vitArr[] = $vitRow;
    }

    return $vitArr;
}

function allImmuPat($patient_id, $from_date, $to_date)
{
    $immArr = array();
    $immQry =   "SELECT * FROM immunizations " .
            "WHERE patient_id = ? " .
            "AND (administered_date BETWEEN ? AND ?)";
    $immRes = sqlStatement($immQry, array($patient_id, $from_date, $to_date));
    while ($immRow = sqlFetchArray($immRes)) {
        $immArr[] = $immRow;
    }

    return $immArr;
}
function getPatData($patient_id)
{
    $patientRow = sqlQuery("SELECT * FROM patient_data WHERE pid= ?", array($patient_id));
    return $patientRow;
}

function getUsrDataCheck($provider_id)
{
    $userRow = array();
    if ($provider_id != "") {
        $userRow = sqlQuery("SELECT facility, facility_id, federaltaxid, npi, phone,fname, lname FROM users WHERE id= ?", array($provider_id));
    }

    return $userRow;
}

function getFacilDataChk($facility_id)
{
    global $facilityService;
    return $facilityService->getById($facility_id);
}

function patientQRDAHistory($patient_id)
{
    $patientHistRow = sqlQuery("SELECT tobacco, date FROM history_data WHERE pid= ? ORDER BY id DESC LIMIT 1", array($patient_id));
    return $patientHistRow;
}
