<?php

/**
 * interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Bindia Nandakumar <bindia@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Immunization\Controller;

use Application\Model\ApplicationTable;
use Immunization\Model\ImmunizationTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Immunization\Form\ImmunizationForm;
use Application\Listener\Listener;

class ImmunizationController extends AbstractActionController
{
    protected $immunizationTable;

    protected $listenerObject;

    protected $date_format;

    protected $appTable;

    public function __construct(ImmunizationTable $table)
    {
        $this->appTable = new ApplicationTable();
        $this->immunizationTable = $table;
        $this->listenerObject = new Listener();
    }

    /**
     * Index Page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $form = new ImmunizationForm();
        $request = $this->getRequest();
        $form->setData($request->getPost());
        $isPost = '';
        $data = $request->getPost();
        $isFormRefresh = 'true';
        $form_code = isset($data['codes']) ? $data['codes'] : array();
        $from_date = $request->getPost('from_date', null) ? $this->CommonPlugin()->date_format($request->getPost('from_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7));
        $to_date = $request->getPost('to_date', null) ? $this->CommonPlugin()->date_format($request->getPost('to_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
        $form_get_hl7 = '';
        $patient_id = $request->getPost('patient_id', null);
        //pagination
        $results = $request->getPost('form_results', 100);
        $results = ($results > 0) ? $results : 100;
        $current_page = $request->getPost('form_current_page', 1);
        $end = $current_page * $results;
        $start = ($end - $results);
        $new_search = $request->getPost('form_new_search', null);
        //end pagination

        if (empty($patient_id)) {
            $query_pids = '';
        } else {
            $pid_arr = explode(',', $patient_id);
            $query_pids = '(';
            foreach ($pid_arr as $pid_val) {
                $query_pids .= "p.pid = ( '";
                $query_pids .= $pid_val . "' ) or ";
                $query_pids .= "p.fname like ( '%";
                $query_pids .= $pid_val . "%' ) or ";
                $query_pids .= "p.mname like ( '%";
                $query_pids .= $pid_val . "%' ) or ";
                $query_pids .= "p.lname like ( '%";
                $query_pids .= $pid_val . "%' ) or ";
            }

            $query_pids = trim($query_pids);
            $query_pids = rtrim($query_pids, 'or');
            $query_pids .= ') and ';
        }

        if (empty($form_code)) {
            $query_codes = '';
        } else {
            $query_codes = 'c.id in ( ';
            foreach ($form_code as $code) {
                $query_codes .= $code . ",";
            }

            $query_codes = substr($query_codes, 0, -1);
            $query_codes .= ') and ';
        }

        $params = array(
            'form_from_date' => $from_date,
            'form_to_date' => $to_date,
            'form_get_hl7' => $form_get_hl7,
            'query_codes' => $query_codes,
            'results' => $results,
            'current_page' => $current_page,
            'limit_start' => $start,
            'limit_end' => $end,
            'query_pids' => $query_pids,
            'patient_id' => $patient_id,
        );

        if ($new_search) {
            $count = $this->getImmunizationTable()->immunizedPatientDetails($params, 1);
        } else {
            $count = $request->getPost('form_count');
            if ($count == '') {
                $count = $this->getImmunizationTable()->immunizedPatientDetails($params, 1);
            }
        }

        $totalpages = ceil($count / $results);
        $details = $this->getImmunizationTable()->immunizedPatientDetails($params);
        $rows = array();
        foreach ($details as $row) {
            $rows[] = $row;
        }

        $params['res_count'] = $count;
        $params['total_pages'] = $totalpages;

        $codes = $this->getAllCodes($data);
        if ($codes != '') {
            $form->get('codes')->setValueOptions($codes);
        }

        $view = new ViewModel(array(
            'listenerObject' => $this->listenerObject,
            'form' => $form,
            'view' => $rows,
            'isFormRefresh' => $isFormRefresh,
            'form_data' => $params,
            'commonplugin' => $this->CommonPlugin(),
        ));
        return $view;
    }

    /**
     * function getAllCodes
     * List All Codes in the combobox
     */
    public function getAllCodes($data)
    {
        $defaultCode = isset($data['codes']) ? $data['codes'] : '';
        $res = $this->getImmunizationTable()->codeslist();
        $i = 0;
        foreach ($res as $value) {
            if ($value == $defaultCode) {
                $select = true;
            } else {
                $select = false;
            }

            $rows[$i] = array(
                'value' => $value['id'],
                'label' => $value['NAME'],
                'selected' => $select,
            );
            $i++;
        }

        return $rows;
    }

    /**
     * function getHL7
     * generating HL7 format
     */
    public function reportAction()
    {
        $request = $this->getRequest();
        $data = $request->getPost();
        $key_val = '';
        if (isset($data['hl7button'])) {
            $form_code = isset($data['codes']) ? $data['codes'] : array();
            $from_date = $request->getPost('from_date', null) ? $this->CommonPlugin()->date_format($request->getPost('from_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7));
            $to_date = $request->getPost('to_date', null) ? $this->CommonPlugin()->date_format($request->getPost('to_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
            $form_get_hl7 = 'true';
            $patient_id = $request->getPost('patient_id', null);
            //pagination
            $results = $request->getPost('form_results', 100);
            $results = ($results > 0) ? $results : 100;
            $current_page = $request->getPost('form_current_page', 1);
            $end = $current_page * $results;
            $start = ($end - $results);
            $new_search = $request->getPost('form_new_search', null);
            //endpagination

            if (empty($form_code)) {
                $query_codes = '';
            } else {
                $query_codes = 'c.id in ( ';
                foreach ($form_code as $code) {
                    $query_codes .= $code . ",";
                }

                $query_codes = substr($query_codes, 0, -1);
                $query_codes .= ') and ';
            }

            if (empty($patient_id)) {
                $query_pids = '';
            } else {
                $pid_arr = explode(',', $patient_id);
                $query_pids = '(';
                foreach ($pid_arr as $pid_val) {
                    $query_pids .= "p.pid = ( '";
                    $query_pids .= $pid_val . "' ) or ";
                    $query_pids .= "p.fname like ( '%";
                    $query_pids .= $pid_val . "%' ) or ";
                    $query_pids .= "p.mname like ( '%";
                    $query_pids .= $pid_val . "%' ) or ";
                    $query_pids .= "p.lname like ( '%";
                    $query_pids .= $pid_val . "%' ) or ";
                }

                $query_pids = trim($query_pids);
                $query_pids = rtrim($query_pids, 'or');
                $query_pids .= ') and ';
            }

            $params = array(
                'form_from_date' => $from_date,
                'form_to_date' => $to_date,
                'form_get_hl7' => $form_get_hl7,
                'query_codes' => $query_codes,
                'results' => $results,
                'current_page' => $current_page,
                'limit_start' => $start,
                'limit_end' => $end,
                'query_pids' => $query_pids,
            );

            if ($new_search) {
                $count = $this->getImmunizationTable()->immunizedPatientDetails($params, 1);
            } else {
                $count = $request->getPost('form_count');
                if ($count == '') {
                    $count = $this->getImmunizationTable()->immunizedPatientDetails($params, 1);
                }
            }

            $totalpages = ceil($count / $results);

            $details = $this->getImmunizationTable()->immunizedPatientDetails($params);
            $rows = array();
            foreach ($details as $row) {
                $rows[] = $row;
            }

            $D = "\r";
            $nowdate = date('YmdHis');
            $now = date('YmdGi');
            $now1 = date('Y-m-d G:i');
            $filename = "imm_reg_" . $now . ".hl7";

            // GENERATE HL7 FILE
            if ($form_get_hl7 === 'true') {
                $content = '';
                $patient_id = '';
                foreach ($rows as $r) {
                    $race_code = $administered_unit_title = $administered_route_title = '';
                    $race_title = $ethnicity = $ethnicity_code = $ethnicity_title = '';
                    $administered_site_code = $guardian_relationship_code = $manufacturer_code = '';
                    $immunization_info_source_code = $email = $race = $units = $manufacturer = '';
                    $information_source = $completion_status = $refusal_reason_code = '';
                    $immunization_refusal = $ordering_provider = $entered_by = $publicity_code_val = '';
                    $publicity_code = $imm_registry_status_code = $protection_indicator = '';
                    $guardiansname = '';

                    if ($r['patientid'] != $patient_id) {
                        $content .= "MSH|^~\&|OPENEMR|" . $r['facility_code'] . "||NIST Test Iz Reg|$nowdate||" .
                            "VXU^V04^VXU_V04|OPENEMR-110316102457117|P|2.5.1|||AL|ER" .
                            "$D";
                        $race_code = $this->getImmunizationTable()->getNotes($r['race'], 'race');
                        $race_title = $this->CommonPlugin()->getListtitle('race', $r['race']);
                        $ethnicity_code = $this->getImmunizationTable()->getNotes($r['ethnicity'], 'ethnicity');
                        $ethnicity_title = $this->CommonPlugin()->getListtitle('ethnicity', $r['ethnicity']);
                        $guardianarray = explode(' ', $r['guardiansname']);
                        $guardianname = $guardianarray[1] . '^' . $guardianarray[0];
                        if ($r['sex'] === 'Male') {
                            $r['sex'] = 'M';
                        }

                        if ($r['sex'] === 'Female') {
                            $r['sex'] = 'F';
                        }

                        if ($r['status'] === 'married') {
                            $r['status'] = 'M';
                        }

                        if ($r['status'] === 'single') {
                            $r['status'] = 'S';
                        }

                        if ($r['status'] === 'divorced') {
                            $r['status'] = 'D';
                        }

                        if ($r['status'] === 'widowed') {
                            $r['status'] = 'W';
                        }

                        if ($r['status'] === 'separated') {
                            $r['status'] = 'A';
                        }

                        if ($r['status'] === 'domestic partner') {
                            $r['status'] = 'P';
                        }

                        if ($r['email']) {
                            $email = '~^NET^Internet^' . $r['email'];
                        }

                        if ($r['race']) {
                            $race = $race_code . '^' . $race_title . '^HL70005';
                        }

                        if ($r['ethnicity']) {
                            $ethnicity = $ethnicity_code . '^' . $ethnicity_title . '^CDCREC';
                        }

                        $r['ss'] = $r['ss'] ? "~" . $r['ss'] . "^^^MAA^SS" : "";
                        $content .= "PID|" . // [[ 3.72 ]]
                            "1|" . // 1. Set id
                            "|" . // 2. (B)Patient id
                            $r['pubpid'] . "^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR" . $r['ss'] . "|" . // 3. (R) Patient identifier list. TODO: Hard-coded the OID from NIST test.
                            "|" . // 4. (B) Alternate PID
                            $r['patientname'] . "^^^^L|" . // 5.R. Name
                            $guardianname . "|" . // 6. Mather Maiden Name
                            $r['DOB'] . "|" . // 7. Date, time of birth
                            $r['sex'] . "|" . // 8. Sex
                            "|" . // 9.B Patient Alias
                            $race . "|" . // 10. Race // Ram change
                            $r['address'] . "^L" . "|" . // 11. Address. Default to address type  Mailing Address(M)
                            "|" . // 12. county code
                            "^PRN^PH^^^" . $this->format_phone($r['phone_home']) . "^^" . $email . "|" . // 13. Phone Home. Default to Primary Home Number(PRN)
                            "^WPN^PH^^^" . $this->format_phone($r['phone_biz']) . "^^|" . // 14. Phone Work.
                            $r['language'] . "|" . // 15. Primary language
                            $r['status'] . "|" . // 16. Marital status
                            "|" . // 17. Religion
                            "|" . // 18. patient Account Number
                            "|" . // 19.B SSN Number
                            "|" . // 20.B Driver license number
                            "|" . // 21. Mathers Identifier
                            $ethnicity . "|" . // 22. Ethnic Group
                            "|" . // 23. Birth Plase
                            "|" . // 24. Multiple birth indicator
                            "|" . // 25. Birth order
                            "|" . // 26. Citizenship
                            "|" . // 27. Veteran military status
                            "|" . // 28.B Nationality
                            "|" . // 29. Patient Death Date and Time
                            "|" . // 30. Patient Death Indicator
                            "|" . // 31. Identity Unknown Indicator
                            "|" . // 32. Identity Reliability Code
                            "|" . // 33. Last Update Date/Time
                            "|" . // 34. Last Update Facility
                            "|" . // 35. Species Code
                            "|" . // 36. Breed Code
                            "|" . // 37. Breed Code
                            "|" . // 38. Production Class Code
                            "" . // 39. Tribal Citizenship
                            "$D";

                        if ($r['publicity_code']) {
                            $publicity_code_val = $this->getImmunizationTable()->getNotes($r['publicity_code'], 'publicity_code');
                            $publicity_code = $publicity_code_val . "^" . $r['publicity_code'] . "^HL70215";
                        }

                        $imm_registry_status_code = $this->getImmunizationTable()->getNotes($r['imm_reg_status'], 'immunization_registry_status');
                        $protection_indicator = $this->getImmunizationTable()->getNotes($r['protect_indicator'], 'yesno');
                        if ($publicity_code || $protection_indicator || $imm_registry_status_code) {
                            $content .= "PD1|" . // Patient Additional Demographic Segment
                                "|" . // 1. Living Dependancy
                                "|" . // 2. Living Arrangement
                                $r['fac_name'] . "|" . // 3. Patient Primary Facility
                                $r['primary_care_provider_details'] . "|" . // 4. Patient Primary Care Provider NPI and Provider name
                                "|" . // 5. Student Indicator
                                "|" . // 6. Handicap
                                "|" . // 7. Living Will Code
                                "|" . // 8. Organ Donor Code
                                "|" . // 9. Separate Bill
                                "|" . // 10. Duplicate Patient
                                $publicity_code . "|" . // 11. Publicity Code
                                $protection_indicator . "|" . // 12. Protection Indicator
                                $r['protection_effective_date'] . "|" . // 13. Protection Indicator Effective Date[If PD1-12(Protection Indicator) is valued)]
                                "|" . // 14. Place of worship
                                "|" . // 15. Advance Directive Code
                                $imm_registry_status_code . "|" . // 16. Immunization Registry Status
                                $r['immunization_registry_status_effective_date'] . "|" . // 17. Immunization Registry Status Effective Date [If the PD1-16 (Registry Status)field is valued.]
                                $r['publicity_code_effective_date'] . "|" . // 18. Publicity Code Effective Date [If the PD1-11 (Publicity Code)field is valued.]
                                "|" . // 19. Military Branch
                                "|" . // 20. Military Rank/grade
                                "" . //21. Military Status
                                "$D";
                        }

                        if ($r['guardiansex'] === 'male') {
                            $r['guardiansex'] = 'M';
                        }

                        if ($r['guardiansex'] === 'female') {
                            $r['guardiansex'] = 'F';
                        }

                        $guardian_relationship_code = $this->getImmunizationTable()->getNotes($r['guardianrelationship'], 'next_of_kin_relationship');
                        if ($r['guardiansname'] && $r['guardianrelationship']) {
                            $content .= "NK1|" . // Nearest of kin
                                "1|" . // Set ID
                                $guardianname . "^^^^^L|" .  // 2. Legal Name of next of kin
                                $guardian_relationship_code . "^" . $r['guardianrelationship'] . "^HL70063|" . // 3. Relationship of next of kin with patient
                                $r['guardian_address'] . "|" . // 4. Address of next of kin
                                "^PRN^PH^^^" . $this->format_phone($r['guardianphone']) . "|" . //  5. Phone Home of next of kin. Default to Primary Home Number(PRN)
                                "^WPN^PH^^^" . $this->format_phone($r['guardianworkphone']) .  // 6. Phone Business of next of kin.
                                "|" . //7. Contact Role
                                "|" . //8. Start Date
                                "|" . //9. End Date
                                "|" . // 10. Next of kin/Associated parties job title
                                "|" . //11. Next of kin/Associated parties job code/class
                                "|" . //12. Next of kin/Associated parties employee number
                                "|" . //13. Organization name
                                "|" . //14. Marital status
                                $r['guardiansex'] . "|" . //  15. Administrative Sex of next of kin
                                "|" . // 16. Date, time of birth of next of kin
                                "|" . //17. Living Dependancy
                                "|" . //18. Ambulatory Status
                                "|" . //19. Citizenship
                                "|" . // 20. Primary Language
                                "|" . //21. Living Arrangement
                                "|" . //22. Publicity Code
                                "|" . //23. Protection Indicator
                                "|" . //24. Student Indicator
                                "|" . // 25. Religion
                                "|" . //26. Mother's Maiden Name
                                "|" . //27. Nationality
                                "|" . //28. Ethnic Group
                                "" . //29. Contact Reason
                                "$D";
                        }
                    }

                    if ($r['completion_status'] == 'Refused') {
                        $r['immunizationid'] = '9999';
                    }

                    if ($r['administered_by_id'] == 0 && $r['information_source'] == 'hist_inf_src_unspecified') {
                        $ordering_provider = "";
                    } elseif ($r['ordering_provider']) {
                        $ordering_provider = $r['ordering_provider'] . "^" . $r['ordering_provider_name'] . "^^^^^NIST-AA-1^L";
                    }

                    if ($r['created_by']) {
                        $entered_by = $r['created_by'] . "^" . $r['entered_by_name'] . "^^^^^NIST-AA-1";
                    }

                    $content .= "ORC" . // ORC mandatory for RXA
                        "|" .
                        "RE|" . //1. Order Control
                        "|" . //2. Placer Order Number
                        $r['immunizationid'] . "^NDA|" . //3. Filler Order Number 9999 for refusal and identifier for historic immunization
                        "|" . //4. Placer Group Number
                        "|" . //5. Order Status
                        "|" . //6. Response Flag
                        "|" . //7. Quantity/Timing
                        "|" . //8. Parent
                        "|" . //9. Date/time of transaction
                        $entered_by . "|" . //10. Entered By
                        "|" . //11. Verified By
                        $ordering_provider . "|" . //12. Ordering Provider
                        "|" . //13. Enterer's location
                        "|" . //14. Call Back Phone number
                        "|" . //15. Order effective date/time
                        "|" . //16. Order control code reason
                        "|" . //17. Entering organization
                        "|" . //18. Entering device
                        "|" . //19. Action by
                        "|" . //20. Advanced Beneficiary Notice Code
                        "|" . //21. Ordering Facility Name
                        "|" . //22. Ordering Facility Address
                        "|" . //23. Ordering Facility Phone Number
                        "|" . //24. Ordering Provider Address
                        "|" . //25. Order Status Modifier
                        "|" . //26. Advanced Beneficiary Notice Override reason
                        "|" . //27. Filler's Expected Availability date/time
                        "|" . //28. Confidentiality Code
                        "|" . //29. Order Type
                        "|" . //30. Enterer Authorization Mode
                        "" . //31. Parent Universal Service Identifier
                        "$D";
                    $administered_unit_title = $this->CommonPlugin()->getListtitle('drug_units', $r['administered_unit']);
                    $manufacturer_code = $this->getImmunizationTable()->getNotes($r['manufacturer'], 'Immunization_Manufacturer');
                    $immunization_info_source_code = $this->getImmunizationTable()->getNotes($r['information_source'], 'immunization_informationsource');
                    if ($administered_unit_title) {
                        $units = $administered_unit_title . '^' . $administered_unit_title . '^UCUM^^^';
                    }

                    if ($r['manufacturer']) {
                        $manufacturer = $manufacturer_code . "^" . $r['manufacturer'] . "^" . "MVX";
                    }

                    if ($r['information_source']) {
                        $information_source = $immunization_info_source_code . "^" . $r['information_source'] . "^NIP001";
                    }

                    if ($r['providername'] != null && $r['information_source'] == 'new_immunization_record') {
                        $r['providername'] = $r['users_id'] . "^" . $r['providername'];
                    }

                    $refusal_reason_code = $this->getImmunizationTable()->getNotes($r['refusal_reason'], 'immunization_refusal_reason');
                    $completion_status = $this->getImmunizationTable()->getNotes($r['completion_status'], 'Immunization_Completion_Status');
                    if ($r['refusal_reason']) {
                        $completion_status = 'RE';
                        $immunization_refusal = $refusal_reason_code . "^" . $r['refusal_reason'] . "^NIP002";
                    }

                    if ($r['code'] == '998') {
                        $completion_status = 'NA';
                    }

                    $content .= "RXA|" .
                        "0|" . // 1. Give Sub-ID Counter
                        "1|" . // 2. Administrattion Sub-ID Counter
                        $r['administered_date'] . "|" . // 3. Date/Time Start of Administration
                        "|" . // 4. Date/Time End of Administration
                        $r['code'] . "^" . $r['immunizationtitle'] . "^" . "CVX" . "|" . // 5. Administration Code(CVX)
                        $r['administered_amount'] . "|" . // 6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)
                        $units . "|" . // 7. Administered Units
                        "|" . // 8. Administered Dosage Form
                        $information_source . "|" . // 9. Administration Notes
                        $r['providername'] . "|" . // 10. Administering Provider
                        $r['facility_address'] . "|" . // 11. Administered-at Location
                        "|" . // 12. Administered Per (Time Unit)
                        "|" . // 13. Administered Strength
                        "|" . // 14. Administered Strength Units
                        $r['lot_number'] . "|" . // 15. Substance Lot Number
                        $r['expiration_date'] . "|" . // 16. Substance Expiration Date
                        $manufacturer . "|" . // 17. Substance Manufacturer Name
                        $immunization_refusal . "|" . // 18. Substance/Treatment Refusal Reason
                        "|" . // 19.Indication
                        $completion_status . "|" . // 20.Completion Status
                        "A" . // 21.Action Code - RXA
                        "$D";
                    $administered_route_title = $this->CommonPlugin()->getListtitle('drug_route', $r['route']);
                    $administered_site_code = $this->getImmunizationTable()->getNotes($r['administration_site'], 'immunization_administered_site');
                    if ($r['route_code'] || $r['administration_site']) {
                        $content .= "RXR|" .
                            $r['route_code'] . "^" . $administered_route_title . "^HL70162|" . //1. Route
                            $administered_site_code . "^" . $r['administration_site'] . "^HL70163" . // 2. Administration Site
                            "|" . // 3. Administration Device
                            "|" . // 4. Administration Method
                            "|" . // 5. Routing Instruction
                            "" . // 6. Administration Site Modifier
                            "$D";
                    }

                    $imm_obs_res = $this->getImmunizationTable()->getImmunizationObservationResultsData($r['patientid'], $r['immunizationid']);
                    if (count($imm_obs_res > 0)) {
                        $last_key = 1;
                        foreach ($imm_obs_res as $key_obs => $val_obs) {
                            $criteria_code = $criteria_notes = $obs_value_notes = $obs_value = $obs_method = $date_obs = $value_type = $criteria_value = '';
                            $criteria_code = $this->getImmunizationTable()->getCodes($val_obs['imo_criteria'], 'immunization_observation');
                            $criteria_notes = $this->getImmunizationTable()->getNotes($val_obs['imo_criteria'], 'immunization_observation');
                            $obs_value_notes = $this->getImmunizationTable()->getNotes($val_obs['imo_criteria_value'], 'imm_vac_eligibility_results');
                            $criteria_value = $criteria_code . "^" . $val_obs['imo_criteria'] . "^" . $criteria_notes;
                            $date_obs = preg_replace('/-/', '', substr($val_obs['imo_date_observation'], 0, 10));
                            if ($val_obs['imo_criteria'] == 'funding_program_eligibility') {
                                $obs_value = $obs_value_notes . "^" . $val_obs['imo_criteria_value'] . "^HL70064";
                                $obs_method = "VXC40^per immunization^CDCPHINVS";
                                $value_type = "CE";
                            } elseif ($val_obs['imo_criteria'] == 'vaccine_type') {
                                $obs_value = $val_obs['imo_code'] . "^" . $val_obs['imo_codetext'] . "^" . $val_obs['imo_codetype'];
                                $value_type = "CE";
                            } elseif ($val_obs['imo_criteria'] == 'disease_with_presumed_immunity') {
                                $value_type = "CE";
                                $obs_value = $val_obs['imo_code'] . "^" . $val_obs['imo_codetext'] . "^SCT";
                            }

                            if ($key_obs > 1) {
                                if ($last_key > 4) {
                                    $key_val = $last_key + 1;
                                } else {
                                    $key_val = $key_val + 1;
                                }
                            } else {
                                $key_val = $key_obs + 1;
                            }

                            $content .= "OBX|" .
                                $key_val . "|" .        //1. Set ID
                                $value_type . "|" .         //2. Value Type
                                $criteria_value . "|" .     //3. Observation Identifier
                                $key_val . "|" .       //4. Observation Sub ID
                                $obs_value . "|" .          //5. Observation Value
                                "|" .                     //6. Units
                                "|" .
                                "|" .
                                "|" .
                                "|" .
                                "F|" .                    //11. Observation Result Status
                                "|" .
                                "|" .
                                $date_obs . "|" .           //14. Date/Time Of Observation
                                "|" .
                                "|" .
                                $obs_method .             //17. Observation Method
                                "$D";
                            $last_key = $key_val;

                            if ($val_obs['imo_vis_date_published'] != 0) {
                                $value_type = "TS";
                                $criteria_value = "29768-9^Date vaccine information statement published^LN";
                                $obs_value = preg_replace('/-/', '', $val_obs['imo_vis_date_published']);
                                if ($key_obs > 1) {
                                    if ($last_key == 4) {
                                        $key_val = $last_key + 1;
                                    } else {
                                        $key_val = $key_val + 1;
                                    }
                                } else {
                                    $key_val = $last_key + 1;
                                }

                                $content .= "OBX|" .
                                    $key_val . "|" .     //1. Set ID
                                    $value_type . "|" .      //2. Value Type
                                    $criteria_value . "|" .  //3. Observation Identifier
                                    $last_key . "|" .    //4. Observation Sub ID
                                    $obs_value . "|" .       //5. Observation Value
                                    "|" .                  //6. Units
                                    "|" .
                                    "|" .
                                    "|" .
                                    "|" .
                                    "F|" .                 //11. Observation Result Status
                                    "|" .
                                    "|" .
                                    $date_obs . "|" .        //14. Date/Time Of Observation
                                    "|" .
                                    "|" .
                                    "" .                   //17. Observation Method
                                    "$D";
                            }

                            $last_key = $key_val;
                            if ($val_obs['imo_vis_date_presented'] != 0) {
                                $value_type = "TS";
                                $criteria_value = "29769-7^Date vaccine information statement presented^LN";
                                $obs_value = preg_replace('/-/', '', $val_obs['imo_vis_date_presented']);
                                if ($key_obs > 1) {
                                    if ($last_key == 5) {
                                        $key_val = $last_key + 1;
                                    } else {
                                        $key_val = $key_val + 1;
                                    }
                                } else {
                                    $key_val = $last_key + 1;
                                }

                                $content .= "OBX|" .
                                    $key_val . "|" .     //1. Set ID
                                    $value_type . "|" .      //2. Value Type
                                    $criteria_value . "|" .  //3. Observation Identifier
                                    ($last_key - 1) . "|" .    //4. Observation Sub ID
                                    $obs_value . "|" .       //5. Observation Value
                                    "|" .                  //6. Units
                                    "|" .
                                    "|" .
                                    "|" .
                                    "|" .
                                    "F|" .                 //11. Observation Result Status
                                    "|" .
                                    "|" .
                                    $date_obs . "|" .        //14. Date/Time Of Observation
                                    "|" .
                                    "|" .
                                    "" .                   //17. Observation Method
                                    "$D";
                            }

                            $last_key = $key_val;
                        }
                    }

                    $patient_id = $r['patientid'];
                }

                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename=' . $filename);

                // put the content in the file
                echo($content);
                exit;
            }
        }
    }

    /**
     *
     * @param type $ethnicity
     * @return type
     */
    public function format_ethnicity($ethnicity)
    {
        switch ($ethnicity) {
            case "hisp_or_latin":
                return ("H^Hispanic or Latino^HL70189");
            case "not_hisp_or_latin":
                return ("N^not Hispanic or Latino^HL70189");
            default: // Unknown
                return ("U^Unknown^HL70189");
        }
    }

    /**
     *
     * @param type $a
     * @return type
     */
    public function tr($a)
    {
        return (str_replace(' ', '^', $a));
    }

    /**
     *
     * @param type $cvx_code
     * @return type
     */
    public function format_cvx_code($cvx_code)
    {
        if ($cvx_code < 10) {
            return "0$cvx_code";
        }

        return $cvx_code;
    }

    /**
     *
     * @param   $phone      String          phone number
     * @return              String          formatted phone
     */
    public function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        switch (strlen($phone)) {
            case 7:
                return $this->tr(preg_replace("/([0-9]{3})([0-9]{4})/", "000 $1$2", $phone));
            case 10:
                return $this->tr(preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2$3", $phone));
            default:
                return $this->tr("000 0000000");
        }
    }

    /*
    *   Table Gateway
    */
    public function getImmunizationTable()
    {
        return $this->immunizationTable;
    }
}
