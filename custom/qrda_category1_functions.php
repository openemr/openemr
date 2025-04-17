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
// This program exports report to QRDA Category I 2014 XML format.


function mainQrdaCatOneGenerate($xml, $patient_id, $rule_id, $provider_id)
{
    //Open Main Clinical Document
    $xml->open_clinicaldocument();

    //Header Function
    getHeaderQRDA1($xml, $patient_id, $provider_id);

    //Component Function
    getComponentQRDA1($xml, $patient_id, $rule_id);

    //Close Main Clinical Document
    $xml->close_clinicaldocument();

    //Downloaded XML
    $xmlDynFileName = downloadQRDACat1($xml, $patient_id, $rule_id);

    return $xmlDynFileName;
}

    //Main Header Function
function getHeaderQRDA1($xml, $patient_id, $provider_id)
{
    global $mainQrdaRaceCodeArr, $mainEthiCodeArr, $from_date, $to_date;

    //Patient Info
    if ($patient_id != "") {
        $patientRow = getPatData($patient_id);
    }

    //User Info
    $userRow = getUsrDataCheck($provider_id);
    $facility_name = $userRow['facility'];
    $facility_id = $userRow['facility_id'];

    //Facility Info
    if ($facility_id != "") {
        $facilResRow = getFacilDataChk($facility_id);
    }

    ####################### HEADER ELEMENTS START ##########################################

    $xml->self_realmcode();

    $xml->self_typeid();

    $tempId = '2.16.840.1.113883.10.20.22.1.1';
    $xml->self_templateid($tempId);

    $tempId = '2.16.840.1.113883.10.20.24.1.1';
    $xml->self_templateid($tempId);

    $tempId = '2.16.840.1.113883.10.20.24.1.2';
    $xml->self_templateid($tempId);

    $tempId = '2.16.840.1.113883.10.20.24.1.3';
    $xml->self_templateid($tempId);

    $xml->unique_id = getUuid();
    $xml->self_id();

    $arr = array('code' => '55182-0', 'displayName' => 'Quality Measure Report', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC');
    $xml->self_codeCustom($arr);

    //Main Title Display to XML
    $main_title = "QRDA Category-I Report";
    $xml->add_title($main_title);

    //Effective date and time
    $eff_datetime = date('Ymdhis', strtotime($from_date));
    $xml->self_efftime($eff_datetime);

    $xml->self_confidentcode();

    //Language
    $xml->self_lang();

    //Record Target Elements
    $xml->open_recordTarget();

    //patientRole Open
    $xml->open_customTag('patientRole');

    $xml->self_customTag('id', array('root' => '2.16.840.1.113883.4.572', 'extension' => '112233'));

    $xml->add_patientAddress($patientRow);

    if ($patientRow['phone_home'] != "") {
        $xml->self_customTag('telecom', array('value' => $patientRow['phone_home'], 'use' => 'HP'));
    } else {
        $xml->self_customTag('telecom', array('nullFlavor' => "UNK"));
    }

    $xml->open_customTag('patient');

    $patNameArr = array('fname' => $patientRow['fname'], 'lname' => $patientRow['lname']);
    $xml->add_patName($patNameArr);

    if ($patientRow['sex'] == "Male") {
        $gender = "M";
    } elseif ($patientRow['sex'] == "Female") {
        $gender = "F";
    }

    $xml->self_customTag('administrativeGenderCode', array('codeSystem' => '2.16.840.1.113883.18.2', 'code' => $gender));

    $xml->self_customTag('birthTime', array('value' => date('Ymd', strtotime($patientRow['DOB']))));

    if ($mainQrdaRaceCodeArr[$patientRow['race']] == "") {
        $mainQrdaRaceCodeArr[$patientRow['race']] = "2131-1";
    }

    $xml->self_customTag('raceCode', array('codeSystem' => '2.16.840.1.113883.6.238', 'code' => $mainQrdaRaceCodeArr[$patientRow['race']]));
    $xml->self_customTag('ethnicGroupCode', array('codeSystem' => '2.16.840.1.113883.6.238', 'code' => $mainEthiCodeArr[$patientRow['ethnicity']]));

    //patient Close
    $xml->close_customTag();

    //patientRole Close
    $xml->close_customTag();

    $xml->close_recordTarget();

    ############### Author Info Start#######################
    $xml->open_author();

    //Author time
    $auth_dtime = date('Ymdhis');
    $xml->self_authorTime($auth_dtime);

    //Assigned Author
    $xml->open_assignAuthor();
    $npi_provider = empty($userRow['npi']) ? "FakeNPI" : $userRow['npi'];
    $xml->self_customTag('id', array('root' => '2.16.840.1.113883.4.6', 'extension' => $npi_provider));
    $xml->add_patientAddress($facilResRow);
    if (!empty($userRow['phone'])) {
        $xml->self_customTag('telecom', array('value' => $userRow['phone'], 'use' => 'WP'));
    } else {
        $xml->self_customTag('telecom', array("nullFlavor" => "UNK"));
    }




    //assignedAuthoringDevice Start
    $xml->open_customTag('assignedAuthoringDevice');
    $xml->element('manufacturerModelName', 'DrCloudEMR');
    $xml->element('softwareName', 'DrCloudEMR');
    //assignedAuthoringDevice Close
    $xml->close_customTag();

    $xml->close_assignAuthor();
    ################## Author Info End ##########################

    $xml->close_author();
    ############### Author Info End#######################

    ############### Custodian Info Start #######################
    $xml->open_custodian();
    $xml->open_assgnCustodian();
    $xml->add_represtCustodianOrginisation($facilResRow);
    $xml->close_assgnCustodian();
    $xml->close_custodian();
    ############### Custodian Info End #######################

    ############### Legal Authenticator Start#######################
    $xml->open_legalAuthenticator();
    $auth_dtime = date('Ymdhis');
    $xml->self_authorTime($auth_dtime);
    $xml->self_legalSignCode();

    $xml->open_assignedEntity();
    $assignedEntityId = getUuid();
    $xml->self_customId($assignedEntityId);
    $xml->add_facilAddress($facilResRow);
    if (!empty($facilResRow['phone'])) {
        $xml->self_customTag('telecom', array('value' => $facilResRow['phone'], 'use' => 'WP'));
    } else {
        $xml->self_customTag('telecom', array("nullFlavor" => "UNK"));
    }

    $xml->open_customTag('assignedPerson');

    //Provider Name
    $userNameArr = array('fname' => $userRow['fname'], 'lname' => $userRow['lname']);
    $xml->add_providerName($userNameArr);

    //assignedPerson Close
    $xml->close_customTag();

    //Represent Origination Name
    $xml->add_authReprestOrginisation($facilResRow);
    $xml->close_assignedEntity();

    $xml->close_legalAuthenticator();
    ############### Legal Authenticator End#######################

    ############### documentationOf  START  #######################
    $xml->open_customTag('documentationOf');

    $xml->open_customTag('serviceEvent', array('classCode' => 'PCPR'));

    $timeArr = array('low' => date('Ymdhis', strtotime($from_date)), 'high' => date('Ymdhis', strtotime($to_date)));
    $xml->add_entryEffectTime($timeArr);

    $xml->open_customTag('performer', array('typeCode' => 'PRF'));

    $timeArr = array('low' => date('Ymdhis', strtotime($from_date)), 'high' => date('Ymdhis', strtotime($to_date)));
    $xml->add_entryTime($timeArr);

    $xml->open_customTag('assignedEntity');

    $npi_provider = empty($userRow['npi']) ? "FakeNPI" : $userRow['npi'] ;
    $xml->self_customTag('id', array('root' => '2.16.840.1.113883.4.6', 'extension' => $npi_provider));

    if ($userRow['phone'] != "") {
        $xml->self_customTag('telecom', array('value' => $userRow['phone'], 'use' => 'WP'));
    } else {
        $xml->self_customTag('telecom', array("nullFlavor" => "UNK"));
    }

    $xml->open_customTag('assignedPerson');

    //Provider Name
    $userNameArr = array('fname' => $userRow['fname'], 'lname' => $userRow['lname']);
    $xml->add_providerName($userNameArr);

    //assignedPerson Close
    $xml->close_customTag();

    $xml->open_customTag('representedOrganization');

    $tin_provider = $userRow['federaltaxid'];
    if ($tin_provider != "") {
        $xml->self_customTag('id', array('root' => '2.16.840.1.113883.4.2', 'extension' => $tin_provider));
    }

    $xml->add_facilName($facility_name);

    $xml->add_facilAddress($facilResRow);

    //representedOrganization Close
    $xml->close_customTag();

    //assignedEntity Close
    $xml->close_customTag();

    //performer Close
    $xml->close_customTag();

    //serviceEvent Close
    $xml->close_customTag();

    //documentationOf Close
    $xml->close_customTag();
    ############### documentationOf  END  #######################
    ####################### HEADER ELEMENTS END #########################################
}

    //Component Function
function getComponentQRDA1($xml, $patient_id, $rule_id)
{
    //Component Open
    $xml->open_mainComponent();

    //structuredBody Open
    $xml->open_structuredBody();

    //Measure Section
    getMeasureSection($xml, $rule_id);

    //Reporting Parameters
    getReportingParam($xml);

    //Patient Data
    getQRDACat1PatientData($xml, $patient_id);

    //structuredBody Close
    $xml->close_structuredBody();

    //Component Close
    $xml->close_mainComponent();
}

    //Patient Data
function getQRDACat1PatientData($xml, $patient_id)
{
    $xml->open_loopComponent();
    $xml->open_section();

    $tempID = '2.16.840.1.113883.10.20.17.2.4';
    $xml->self_templateid($tempID);

    $tempID = '2.16.840.1.113883.10.20.24.2.1';
    $xml->self_templateid($tempID);

    $arr = array('code' => '55188-7', 'codeSystem' => '2.16.840.1.113883.6.1');
    $xml->self_codeCustom($arr);

    $title = "Patient Data";
    $xml->add_title($title);

    $xml->element('text', "Patient Data");

    //Insurance(Payer) Info
    payerQRDA($xml, $patient_id);

    //Patient History Info
    patCharactersticQRDA($xml, $patient_id);

    //Encounter Section
    getAllPatientEncounters($xml, $patient_id);

    //Physical Exams(vitals)
    getAllPhysicalExams($xml, $patient_id);

    //Diagnosis (Medical Problems)
    getAllMedicalProbs($xml, $patient_id);

    //Ordered Medications
    getAllOrderMedications($xml, $patient_id);

    // Active Medications
    getAllActiveMedications($xml, $patient_id);

    //Immunization
    getAllImmunization($xml, $patient_id);

    //Procedures
    getAllProcedures($xml, $patient_id);

    //Lab Tests
    getAllLabTests($xml, $patient_id);

    //Interventions
    getAllInterventionProcedures($xml, $patient_id);

    //Risk Category Assessment
    getAllRiskCatAssessment($xml, $patient_id);

    $xml->close_section();
    $xml->close_loopComponent();
}

    //Immunization
function getAllImmunization($xml, $patient_id)
{
    global $from_date, $to_date;
    $medArr = allImmuPat($patient_id, $from_date, $to_date);

    foreach ($medArr as $medRow) {
        $vset = sqlStatement("select * from valueset where code =? and code_type = 'cvx' and nqf_code =?", array($medRow['cvx_code'], $xml->nqf_code));
        foreach ($vset as $v) {
            if (!empty($v['valueset'])) {
            //Entry open
                $xml->open_entry();

            //act open
                $xml->open_customTag('act', array('classCode' => 'ACT', 'moodCode' => 'EVN'));

                $tempID = "2.16.840.1.113883.10.20.24.3.42";
                $xml->self_templateid($tempID);

                $refID = getUuid();
                $xml->self_customId($refID);

                $arr = array('code' => '416118004', 'codeSystemName' => 'SNOMED CT', 'codeSystem' => '2.16.840.1.113883.6.96', 'displayName' => 'Administration');
                $xml->self_codeCustom($arr);

                if ($medRow['status'] == "" || $medRow['status'] == "not_completed") {
                    $statusChk = "active";
                } else {
                    $statusChk = "completed";
                }

                $arr = array('code' => "completed");
                $xml->self_customTag('statusCode', $arr);

                $timeArr = array('low' => date('Ymdhis', strtotime($medRow['administered_date'])), 'high' => date('Ymdhis', strtotime($medRow['administered_date'])));
                $xml->add_entryEffectTimeQRDAMed($timeArr);

                $xml->open_customTag('entryRelationship', array('typeCode' => 'COMP'));

            //substanceAdministration Open
                $xml->open_customTag('substanceAdministration', array('classCode' => 'SBADM', 'moodCode' => 'EVN'));

                $tempID = "2.16.840.1.113883.10.20.22.4.16";
                $xml->self_templateid($tempID);

            //$tempID = "2.16.840.1.113883.10.20.24.3.41";
            //$xml->self_templateid($tempID);

                $refID = getUuid();
                $xml->self_customId($refID);

                $arr = array('code' => $statusChk);
                $xml->self_customTag('statusCode', $arr);

                $timeArr = array('low' => date('Ymdhis', strtotime($medRow['administered_date'])), 'high' => date('Ymdhis', strtotime($medRow['administered_date'])));
                $xml->add_entryEffectTimeQRDAMed($timeArr);

            //consumable open
                $xml->open_customTag('consumable');

            //manufacturedProduct Open
                $xml->open_customTag('manufacturedProduct', array('classCode' => 'MANU'));

                $tempID = "2.16.840.1.113883.10.20.22.4.23";
                $xml->self_templateid($tempID);

                $actId = getUuid();
                $xml->self_customId($actId);

            //manufacturedMaterial open
                $xml->open_customTag('manufacturedMaterial');

                $arr = array('code' => $v['code'], 'codeSystem' => $v['code_system'],'sdtc:valueSet' => $v['valueset']);
                $xml->self_codeCustom($arr);

            //manufacturerOrganization open
            /*$xml->open_customTag('manufacturerOrganization');

            $xml->element('name', 'Medication, Administered Vaccine');

            //manufacturerOrganization Close
            $xml->close_customTag();*/

            //manufacturedMaterial Close
                $xml->close_customTag();

            //manufacturedProduct Close
                $xml->close_customTag();

            //consumable Close
                $xml->close_customTag();

            //substanceAdministration Close
                $xml->close_customTag();

            //entryRelationship Close
                $xml->close_customTag();

            //act Close
                $xml->close_customTag();

            //Entry close
                $xml->close_entry();
            }
        }
    }
}

function getAllPhysicalExams($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date;

    $vitArr   = allVitalsPat($patient_id, $from_date, $to_date);
    $measures = array('bps' => array('name' => 'Blood Pressure Systolic','category' => 'Blood Pressure','unit' => 'mmHg','code' => '8480-6'),
                  'bpd' => array('name' => 'Blood Pressure Diastolic','category' => 'Blood Pressure','unit' => 'mmHg','code' => '8462-4'),
                  'bmi' => array('name' => 'Body Mass Index','category' => 'Body Mass Index', 'unit' => 'kg/m2','code' => '39156-5'));

    foreach ($vitArr as $vitRow) {
        //Entry open
        foreach ($measures as $measure_key => $measure) {
            $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ?", array($measure['code'],$xml->nqf_code));
            if (!empty($vset['valueset'])) {
                $xml->open_entry();

            //observation Open
                $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

                $tempID = "2.16.840.1.113883.10.20.22.4.2";
                $xml->self_templateid($tempID);

                $tempID = "2.16.840.1.113883.10.20.24.3.57";
                $xml->self_templateid($tempID);

            //$refID = getUuid();
                $refID = $encCheckUniqId[$vitRow['encounter']];
                $xml->self_customId($refID);

                $arr = array('code' => $measure['code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);

            //code Open
                $xml->open_customTag('code', $arr);
                $xml->element('originalText', "Physical Exam, Finding: " . $measure['measure']);
            //code Close
                $xml->close_customTag();

                $xml->element('text', "Physical Exam, Finding: " . $measure['category']);

                $arr = array('code' => 'completed');
                $xml->self_customTag('statusCode', $arr);

                $timeArr = array('low' => date('Ymdhis', strtotime($vitRow['date'])), 'high' => date('Ymdhis', strtotime($vitRow['date'])));
                $xml->add_entryEffectTimeQRDA($timeArr);

                $xml->self_customTag('value', array('xsi:type' => 'PQ', 'value' => $vitRow[$measure_key], 'unit' => $measure['unit']));

            //observation Close
                $xml->close_customTag();

            //Entry close
                $xml->close_entry();
            }
        }
    }
}

function getAllRiskCatAssessment($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date;
    $procArr = allProcPat("risk_category", $patient_id, $from_date, $to_date);
    foreach ($procArr as $procRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ?", array($procRow['procedure_code'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //observation Open
            $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.69";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.69";
            $xml->self_templateid($tempID);

            //$refID = getUuid();
            $refID = $encCheckUniqId[$procRow['encounter']];
            $xml->self_customId($refID);

            $arr = array('code' => $vset['code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            //code Open
            $xml->open_customTag('code', $arr);
            $xml->element('originalText', $procRow['procedure_name']);
            //code Close
            $xml->close_customTag();

            $xml->element('text', $procRow['procedure_name']);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($procRow['date_ordered'])), 'high' => date('Ymdhis', strtotime($procRow['date_ordered'])));
            $xml->add_entryEffectTimeQRDA($timeArr);

            $xml->self_customTag('value', array('xsi:type' => 'CD', 'nullFlavor' => 'UNK'));

            //observation Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

function getAllProcedures($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date;
    $procArr = allProcPat("Procedure", $patient_id, $from_date, $to_date);
    foreach ($procArr as $procRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ? ", array($procRow['procedure_code'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //procedure Open
            $xml->open_customTag('procedure', array('classCode' => 'PROC', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.24.3.64";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.22.4.14";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.38";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.40";
            $xml->self_templateid($tempID);

            //$refID = getUuid();
            $refID = $encCheckUniqId[$procRow['encounter']];
            $xml->self_customId($refID);


            $arr = array('code' => $procRow['procedure_code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            //code Open
            $xml->open_customTag('code', $arr);
            $xml->element('originalText', $procRow['procedure_name']);
            //code Close
            $xml->close_customTag();

            $xml->element('text', $procRow['procedure_name']);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($procRow['date_ordered'])), 'high' => date('Ymdhis', strtotime($procRow['date_ordered'])));
            $xml->add_entryEffectTimeQRDA($timeArr);

            //procedure Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

function getAllLabTests($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date;
    $procArr = allProcPat("laboratory_test", $patient_id, $from_date, $to_date);
    foreach ($procArr as $procRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ? ", array($procRow['procedure_code'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //procedure Open
            $xml->open_customTag('procedure', array('classCode' => 'PROC', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.24.3.38";
            $xml->self_templateid($tempID);

            //$refID = getUuid();
            $refID = $encCheckUniqId[$procRow['encounter']];
            $xml->self_customId($refID);


            $arr = array('code' => $procRow['procedure_code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            //code Open
            $xml->open_customTag('code', $arr);
            $xml->element('originalText', $procRow['procedure_name']);
            //code Close
            $xml->close_customTag();

            $xml->element('text', $procRow['procedure_name']);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($procRow['date_ordered'])), 'high' => date('Ymdhis', strtotime($procRow['date_ordered'])));
            $xml->add_entryEffectTimeQRDA($timeArr);

            //procedure Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}


function getAllInterventionProcedures($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date;
    $procArr = allProcPat("intervention", $patient_id, $from_date, $to_date);
    foreach ($procArr as $procRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ? ", array($procRow['procedure_code'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //act Open
            $xml->open_customTag('act', array('classCode' => 'ACT', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.12";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.32";
            $xml->self_templateid($tempID);

            //$refID = getUuid();
            $refID = $encCheckUniqId[$procRow['encounter']];
            $xml->self_customId($refID);


            $arr = array('code' => $procRow['procedure_code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            //code Open
            $xml->open_customTag('code', $arr);
            $xml->element('originalText', $procRow['procedure_name']);
            //code Close
            $xml->close_customTag();

            $xml->element('text', $procRow['procedure_name']);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($procRow['date_ordered'])), 'high' => date('Ymdhis', strtotime($procRow['date_ordered'])));
            $xml->add_entryEffectTimeQRDA($timeArr);

            //act Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

function getAllOrderMedications($xml, $patient_id)
{
    global $from_date, $to_date;
    $medArr = allOrderMedsPat($patient_id, $from_date, $to_date);

    foreach ($medArr as $medRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ? ", array($medRow['rxnorm_drugcode'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //substanceAdministration Open
            $xml->open_customTag('substanceAdministration', array('classCode' => 'SBADM', 'moodCode' => 'RQO'));

            $tempID = "2.16.840.1.113883.10.20.22.4.42";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.47";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);


            $arr = array('code' => 'new');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($medRow['start_date'])), 'high' => date('Ymdhis', strtotime($medRow['end_date'])));
            $xml->add_entryEffectTimeQRDAMed($timeArr);

            /*if($medRow['enddate'] == ""){
            if($medRow['drug_interval'] != "" && $medRow['drug_interval'] != "0")
                $xml->emptyelement('repeatNumber', array('value'=>$medRow['drug_interval']));

            if($medRow['quantity'] != "")
                $xml->emptyelement('doseQuantity', array('value'=>$medRow['quantity']));

            if($medRow['units'] != "" && $medRow['size_type'] != "")
                $xml->emptyelement('rateQuantity', array('units'=>$medRow['size_type'], 'value'=>$medRow['units']));
            }*/

            //consumable open
            $xml->open_customTag('consumable');

            //manufacturedProduct Open
            $xml->open_customTag('manufacturedProduct', array('classCode' => 'MANU'));

            $tempID = "2.16.840.1.113883.10.20.22.4.23";
            $xml->self_templateid($tempID);

            $actId = getUuid();
            $xml->self_customId($actId);

            //manufacturedMaterial open
            $xml->open_customTag('manufacturedMaterial');

            $arr = array('code' => $vset['code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            $xml->self_codeCustom($arr);

            //manufacturerOrganization open
            /*$xml->open_customTag('manufacturerOrganization');

            $xml->element('name', 'Medication Factory Inc.');

            //manufacturerOrganization Close
            $xml->close_customTag();*/

            //manufacturedMaterial Close
            $xml->close_customTag();

            //manufacturedProduct Close
            $xml->close_customTag();

            //consumable Close
            $xml->close_customTag();

            //substanceAdministration Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

function getAllActiveMedications($xml, $patient_id)
{
    global $from_date, $to_date;
    $medArr = allActiveMedsPat($patient_id, $from_date, $to_date);

    foreach ($medArr as $medRow) {
        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ? ", array($medRow['rxnorm_drugcode'],$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //substanceAdministration Open
            $xml->open_customTag('substanceAdministration', array('classCode' => 'SBADM', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.16";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.41";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);


            $arr = array('code' => 'active');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($medRow['start_date'])), 'high' => date('Ymdhis', strtotime($medRow['end_date'])));
            $xml->add_entryEffectTimeQRDAMed($timeArr);

            /*if($medRow['enddate'] == ""){
                if($medRow['drug_interval'] != "" && $medRow['drug_interval'] != "0")
                $xml->emptyelement('repeatNumber', array('value'=>$medRow['drug_interval']));

            if($medRow['quantity'] != "")
                $xml->emptyelement('doseQuantity', array('value'=>$medRow['quantity']));

            if($medRow['units'] != "" && $medRow['size_type'] != "")
                $xml->emptyelement('rateQuantity', array('units'=>$medRow['size_type'], 'value'=>$medRow['units']));
            }*/

            //consumable open
            $xml->open_customTag('consumable');

            //manufacturedProduct Open
            $xml->open_customTag('manufacturedProduct', array('classCode' => 'MANU'));

            $tempID = "2.16.840.1.113883.10.20.22.4.23";
            $xml->self_templateid($tempID);

            $actId = getUuid();
            $xml->self_customId($actId);

            //manufacturedMaterial open
            $xml->open_customTag('manufacturedMaterial');

            $arr = array('code' => $vset['code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']);
            $xml->self_codeCustom($arr);

            //manufacturerOrganization open
            /*$xml->open_customTag('manufacturerOrganization');

            $xml->element('name', 'Medication Factory Inc.');

            //manufacturerOrganization Close
            $xml->close_customTag();*/

            //manufacturedMaterial Close
            $xml->close_customTag();

            //manufacturedProduct Close
            $xml->close_customTag();

            //consumable Close
            $xml->close_customTag();

            //substanceAdministration Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

    //Medical problems
function getAllMedicalProbs($xml, $patient_id)
{
    global $from_date, $to_date;
    $diagArr = allListsPat('medical_problem', $patient_id, $from_date, $to_date);

    foreach ($diagArr as $diagRow) {
        $diagExpArr = explode(";", $diagRow['diagnosis']);
        /*foreach($diagExpArr as $diagExpVal){
            $diagDisp = explode(":", $diagExpVal);
            if($diagDisp[0] == "ICD9" || $diagDisp[0] == "ICD10") continue;
            $diagDispCode = $diagDisp[1];
        }*/

        $diagDisp = explode(":", $diagExpArr[0]);
        $diagDispCode = str_replace(".", "", $diagDisp[1]);

        $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ?", array($diagDispCode,$xml->nqf_code));
        if (!empty($vset['valueset'])) {
            //Entry open
            $xml->open_entry();

            //observation Open
            $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.4";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.11";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);

            $arr = array('code' => '282291009', 'codeSystemName' => 'SNOMED-CT', 'codeSystem' => '2.16.840.1.113883.6.96', 'displayName' => 'diagnosis');
            $xml->self_codeCustom($arr);

            $xml->textDispContent($diagRow['title']);

            $activeChk = "active";
            $endate = $diagRow['begdate'];
            if ($diagRow['enddate'] != "") {
                $activeChk = "completed";
                $endate = $diagRow['enddate'];
            }

            //$arr = array('code'=>$activeChk);
            $arr = array('code' => "completed");
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($diagRow['begdate'])), 'high' => date('Ymdhis', strtotime($endate)));
            $xml->add_entryEffectTime($timeArr);


            $xml->self_customTag('value', array('xsi:type' => 'CD', 'code' => $diagDispCode, 'codeSystem' => '2.16.840.1.113883.6.96', 'sdtc:valueSet' => $vset['valueset']));

            //entryRelationship Open
            $xml->open_customTag('entryRelationship', array('typeCode' => 'REFR'));

            //observation Open
            $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.6";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.94";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);

            $arr = array('code' => '33999-4', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC', 'displayName' => 'status');
            $xml->self_codeCustom($arr);

            //$arr = array('code'=>$activeChk);
            $arr = array('code' => "completed");
            $xml->self_customTag('statusCode', $arr);

            $xml->self_customTag('value', array('xsi:type' => 'CD', 'code' => '55561003', 'displayName' => 'active', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'));

            //observation Close
            $xml->close_customTag();

            //entryRelationship Close
            $xml->close_customTag();

            //observation Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

    //Encounters function
function getAllPatientEncounters($xml, $patient_id)
{
    global $encCheckUniqId, $from_date, $to_date,$EncounterCptCodes;
    $encArr = allEncPat($patient_id, $from_date, $to_date);

    foreach ($encArr as $encRow) {
        $encRow['encounter'];
        $cpt_code = $EncounterCptCodes[str_replace(' ', '_', strtolower($encRow['pc_catname']))];
        $cpt_code = empty($cpt_code) ? '99201' : $cpt_code;
        $vset = sqlStatement("select * from valueset where code = ? and nqf_code = ?", array('99201',$xml->nqf_code));
        foreach ($vset as $v) {
            //Entry open
            $xml->open_entry();

            //Encounter Open
            $xml->open_customTag('encounter', array('classCode' => 'ENC', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.49";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.23";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);
            $encCheckUniqId[$encRow['encounter']] = $refID;


            $arr = array('code' => $cpt_code, 'codeSystem' => $v['code_system'],'sdtc:valueSet' => $v['valueset']);
            $xml->self_codeCustom($arr);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($encRow['date'])), 'high' => date('Ymdhis', strtotime($encRow['date'])));
            $xml->add_entryEffectTime($timeArr);

            //Encounter Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }

    $encArr = allProcPat("enc_checkup_procedure", $patient_id, $from_date, $to_date);
    foreach ($encArr as $encRow) {
        $encRow['encounter'];
        $vset = sqlStatement("select * from valueset where code = ? and nqf_code = ?", array($encRow['procedure_code'],$xml->nqf_code));
        foreach ($vset as $v) {
            //Entry open
            $xml->open_entry();

            //Encounter Open
            $xml->open_customTag('encounter', array('classCode' => 'ENC', 'moodCode' => 'EVN'));

            $tempID = "2.16.840.1.113883.10.20.22.4.49";
            $xml->self_templateid($tempID);

            $tempID = "2.16.840.1.113883.10.20.24.3.23";
            $xml->self_templateid($tempID);

            $refID = getUuid();
            $xml->self_customId($refID);
            $encCheckUniqId[$encRow['encounter']] = $refID;


            $arr = array('code' => $v['code'], 'codeSystem' => $v['code_system'],'sdtc:valueSet' => $v['valueset']);
            $xml->self_codeCustom($arr);

            $arr = array('code' => 'completed');
            $xml->self_customTag('statusCode', $arr);

            $timeArr = array('low' => date('Ymdhis', strtotime($encRow['date'])), 'high' => date('Ymdhis', strtotime($encRow['date'])));
            $xml->add_entryEffectTime($timeArr);

            //Encounter Close
            $xml->close_customTag();

            //Entry close
            $xml->close_entry();
        }
    }
}

    //Patient Data Sub Function for Payer Data
function payerQRDA($xml, $patient_id)
{
    global $mainQrdaPayerCodeSendArr, $from_date, $to_date;

    //Insurance getting
    $payer = payerPatient($patient_id);

    //Entry open
    $xml->open_entry();

    //observation Open
    $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

    $tempID = "2.16.840.1.113883.10.20.24.3.55";
    $xml->self_templateid($tempID);

    $actId = getUuid();
    $xml->self_customId($actId);

    $arr = array('code' => '48768-6', 'displayName' => 'Payment source', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC');
    $xml->self_codeCustom($arr);

    $arr = array('code' => 'completed');
    $xml->self_customTag('statusCode', $arr);

    $timeArr = array('low' => date('Ymdhis', strtotime($from_date)), 'high' => date('Ymdhis', strtotime($to_date)));
    $xml->add_entryEffectTime($timeArr);

    $xml->self_customTag('value', array('xsi:type' => 'CD', 'code' => $mainQrdaPayerCodeSendArr[$payer], 'codeSystem' => '2.16.840.1.113883.3.221.5' , 'codeSystemName' => 'Source of Payment Typology', 'displayName' => $payer));

    //observation Close
    $xml->close_customTag();

    //Entry close
    $xml->close_entry();
}

    //Reporting Parameters function
function getReportingParam($xml)
{
    global $from_date, $to_date;

    $xml->open_loopComponent();
    $xml->open_section();

    $tempID = '2.16.840.1.113883.10.20.17.2.1';
    $xml->self_templateid($tempID);

    $arr = array('code' => '55187-9', 'codeSystem' => '2.16.840.1.113883.6.1');
    $xml->self_codeCustom($arr);

    $title = "Reporting Parameters";
    $xml->add_title($title);

    //Main Reporting Parameters display
    $xml->open_text();
    $xml->open_list();
    $item_title = "Reporting period: " . date('d M Y', strtotime($from_date)) . " - " . date('d M Y', strtotime($to_date));
    $xml->add_item($item_title);
    $xml->close_list();
    $xml->close_text();

    $typeCode = 'DRIV';
    $xml->open_entry($typeCode);
    $arr = array('classCode' => 'ACT', 'moodCode' => 'EVN');
    $xml->open_act($arr);

    $tempID = '2.16.840.1.113883.10.20.17.3.8';
    $xml->self_templateid($tempID);

    $arr = array('extension' => getUuid());
    $xml->self_customTag('id', $arr);

    $arr = array('code' => '252116004', 'codeSystem' => '2.16.840.1.113883.6.96', 'displayName' => 'Observation Parameters');
    $xml->self_codeCustom($arr);

    $timeArr = array('low' => date('Ymdhis', strtotime($from_date)), 'high' => date('Ymdhis', strtotime($to_date)));
    $xml->add_entryEffectTime($timeArr);

    $xml->close_act();
    $xml->close_entry();

    $xml->close_section();
    $xml->close_loopComponent();
}

    //Measure Section
function getMeasureSection($xml, $rule_id)
{
    global $preDefinedUniqIDRules;

    $xml->open_loopComponent();
    $xml->open_section();

    $tempID = '2.16.840.1.113883.10.20.24.2.2';
    $xml->self_templateid($tempID);

    $tempID = '2.16.840.1.113883.10.20.24.2.3';
    $xml->self_templateid($tempID);

    $arr = array('code' => '55186-1', 'codeSystem' => '2.16.840.1.113883.6.1');
    $xml->self_codeCustom($arr);

    $title = "Measure Section";
    $xml->add_title($title);

    //Main Measure display
    $xml->open_text();

    //Table Start
    $xml->open_customTag('table', $tabArr);
    //THEAD Start
    $xml->open_customTag('thead');
    //TR Start
    $xml->open_customTag('tr');

    $xml->add_trElementsTitles();

    //TR close
    $xml->close_customTag();

    //THEAD close
    $xml->close_customTag();
    //TBOBY START
    $xml->open_customTag('tbody');
    $xml->open_customTag('tr');

    if (!empty($rule_id)) {
        $tdTitle = "NQF:" . $rule_id;
    }

    $tdVersionNeutral = getUuid();
    $tdVersionSpecific = $preDefinedUniqIDRules[$rule_id];
    $uniqIdArr[] = $tdVersionSpecific;

    $dataArr = array(0 => $tdTitle, 1 => $tdVersionNeutral, 2 => $tdVersionSpecific);
    $xml->add_trElementsValues($dataArr);

    //TR close
    $xml->close_customTag();
    //TBODY close
    $xml->close_customTag();
    //Table Close
    $xml->close_customTag();

    $xml->close_text();

    //Entry open
    $xml->open_entry();

    //Organizer Start
    $arr = array('classCode' => 'CLUSTER', 'moodCode' => 'EVN');
    $xml->open_customTag('organizer', $arr);

    $tempID = "2.16.840.1.113883.10.20.24.3.98";
    $xml->self_templateid($tempID);

    $tempID = "2.16.840.1.113883.10.20.24.3.97";
    $xml->self_templateid($tempID);

    $arr = array('extension' => $uniqIdArr[0]);
    $xml->self_customTag('id', $arr);

    $arr = array('code' => 'completed');
    $xml->self_customTag('statusCode', $arr);

    //reference Start
    $arr = array('typeCode' => 'REFR');
    $xml->open_customTag('reference', $arr);

    //externalDocument Start
    $arr = array('classCode' => 'DOC', 'moodCode' => 'EVN');
    $xml->open_customTag('externalDocument', $arr);

    $exDocID = $uniqIdArr[0];
    $xml->self_customTag('id', array('root' => '2.16.840.1.113883.4.738', 'extension' => $exDocID));

    $xml->element('text', "NQF# " . $rule_id);

    $setidVal = getUuid();
    $xml->self_setid($setidVal);

    $arr = array('value' => '3');
    $xml->self_customTag('versionNumber', $arr);

    //externalDocument Close
    $xml->close_customTag();

    //reference Close
    $xml->close_customTag();

    //Organizer Close
    $xml->close_customTag();

    //Entry Close
    $xml->close_entry();

    $xml->close_section();
    $xml->close_loopComponent();
}

    //Download QRDA Category I
function downloadQRDACat1($xml, $patient_id, $rule_id)
{
    //Patient Info
    if ($patient_id != "") {
        $patientRow = getPatData($patient_id);
        $patFname = $patientRow['fname'];
        $patLname = $patientRow['lname'];
    }

    //QRDA File Download Folder in site/cqm_qrda folder
    $qrda_fname = $patFname . "_" . $patLname . "_NQF_" . $rule_id . ".xml";
    global $qrda_file_path;
    if (!file_exists($qrda_file_path)) {
        mkdir($qrda_file_path, 0777, true);
    }

    $qrda_file_name = $qrda_file_path . $qrda_fname;
    $fileQRDAOPen = fopen($qrda_file_name, "w");
    fwrite($fileQRDAOPen, trim($xml->getXml()));
    fclose($fileQRDAOPen);
    return $qrda_fname;
}

    //Patient History Info
function patCharactersticQRDA($xml, $patient_id)
{

    //Patient History
    $patHist = patientQRDAHistory($patient_id);

    $tobaccoArr = explode('|', $patHist['tobacco']);

    $query = sqlQuery("select codes from list_options where list_id ='smoking_status' and option_id = ?", array($tobaccoArr[3]));
    $tobacco = explode(':', $query['codes']);
    $tobacco_code = $tobacco[1];
    $vset = sqlQuery("select * from valueset where code = ? and nqf_code = ?", array($tobacco_code,$xml->nqf_code));
    if (!empty($vset['valueset'])) {
        //Entry open
        $xml->open_entry();

        //observation Open
        $xml->open_customTag('observation', array('classCode' => 'OBS', 'moodCode' => 'EVN'));

        $tempID = "2.16.840.1.113883.10.20.22.4.85";
        $xml->self_templateid($tempID);

        $actId = getUuid();
        $xml->self_customId($actId);

        $arr = array('code' => 'ASSERTION', 'displayName' => 'Assertion', 'codeSystem' => '2.16.840.1.113883.5.4', 'codeSystemName' => 'ActCode');
        $xml->self_codeCustom($arr);

        $arr = array('code' => 'completed');
        $xml->self_customTag('statusCode', $arr);

        $timeArr = array('low' => date('Ymdhis', strtotime($patHist['date'])), 'high' => date('Ymdhis', strtotime($patHist['date'])));
        $xml->add_entryEffectTime($timeArr);

        $xml->self_customTag('value', array('xsi:type' => 'CD', 'code' => $vset['code'], 'codeSystem' => $vset['code_system'],'sdtc:valueSet' => $vset['valueset']));

        //observation Close
        $xml->close_customTag();

        //Entry close
        $xml->close_entry();
    }
}
