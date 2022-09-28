<?php

/**
 * CdaTemplateImportDispose Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use Application\Model\ApplicationTable;
use Carecoordination\Model\CarecoordinationTable;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\ListService;

class CdaTemplateImportDispose
{
    protected $codeService;

    public function __construct()
    {
        $this->userauthorized = 1; // @todo unsure what to do with this flag for forms.
        $this->codeService = new CodeTypesService();
    }

    /**
     * @param $time
     * @return false|int
     */
    private function str_to_time($time)
    {
        $test = explode('-', $time);
        if (count($test ?? []) === 2) {
            $time = $test[0];
        }

        return strtotime($time);
    }

    /**
     * @param                       $allergy_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertAllergies($allergy_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($allergy_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($allergy_array as $key => $value) {
            $active = 1;

            $allergy_begdate_value = null;
            $allergy_enddate_value = null;
            if (!empty($value['begdate']) && $revapprove == 0) {
                $allergy_begdate = $carecoordinationTable->formatDate($value['begdate'], 1);
                $allergy_begdate_value = fixDate($allergy_begdate);
            } elseif (!empty($value['begdate']) && $revapprove == 1) {
                $allergy_begdate_value = ApplicationTable::fixDate($value['begdate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            }

            if (!empty($value['enddate']) && $revapprove == 0) {
                $allergy_enddate = $carecoordinationTable->formatDate($value['enddate'], 1);
                $allergy_enddate_value = fixDate($allergy_enddate);
            } elseif ($value['enddate'] != 0 && $revapprove == 1) {
                $allergy_enddate_value = ApplicationTable::fixDate($value['enddate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            }

            if ($revapprove == 1) {
                if ($value['resolved'] == 1) {
                    if (!$allergy_enddate_value) {
                        $allergy_enddate_value = date('y-m-d');
                    }
                } else {
                    $allergy_enddate_value = (null);
                }
            }

            $severity_option_id = $carecoordinationTable->getOptionId('severity_ccda', '', 'SNOMED-CT:' . $value['severity_al']);
            $severity_text = $carecoordinationTable->getListTitle($severity_option_id, 'severity_ccda', 'SNOMED-CT:' . $value['severity_al']);
            if ($severity_option_id == '' || $severity_option_id == null) {
                $q_max_option_id = "SELECT MAX(CAST(option_id AS SIGNED))+1 AS option_id
                                FROM list_options
                                WHERE list_id=?";
                $res_max_option_id = $appTable->zQuery($q_max_option_id, array('severity_ccda'));
                $res_max_option_id_cur = $res_max_option_id->current();
                $severity_option_id = $res_max_option_id_cur['option_id'];
                $q_insert_units_option = "INSERT INTO list_options
                             (
                              list_id,
                              option_id,
                              title,
                              activity
                             )
                             VALUES
                             (
                              'severity_ccda',
                              ?,
                              ?,
                              1
                             )";
                if ($severity_text) {
                    $appTable->zQuery($q_insert_units_option, array($severity_option_id, $severity_text));
                }
            }

            $reaction_option_id = $carecoordinationTable->getOptionId('Reaction', $value['reaction_text'], '');
            if ($reaction_option_id == '' || $reaction_option_id == null) {
                $q_max_option_id = "SELECT MAX(CAST(option_id AS SIGNED))+1 AS option_id
                                FROM list_options
                                WHERE list_id=?";
                $res_max_option_id = $appTable->zQuery($q_max_option_id, array('Reaction'));
                $res_max_option_id_cur = $res_max_option_id->current();
                $reaction_option_id = $res_max_option_id_cur['option_id'];
                $q_insert_units_option = "INSERT INTO list_options
                             (
                              list_id,
                              option_id,
                              title,
                              activity
                             )
                             VALUES
                             (
                              'Reaction',
                              ?,
                              ?,
                              1
                             )";
                if ($value['reaction_text']) {
                    $appTable->zQuery($q_insert_units_option, array($reaction_option_id, $value['reaction_text']));
                }
            }

            if (!empty($value['extension'])) {
                $q_sel_allergies = "SELECT *
                              FROM lists
                              WHERE external_id=? AND type='allergy' AND pid=?";
                $res_q_sel_allergies = $appTable->zQuery($q_sel_allergies, array($value['extension'], $pid));
            }
            if (empty($value['extension']) || $res_q_sel_allergies->count() == 0) {
                $query = "INSERT INTO lists
                  ( pid,
                    date,
                    begdate,
                    enddate,
                    type,
                    title,
                    diagnosis,
                    severity_al,
                    activity,
                    reaction,
                    external_id
                  )
                  VALUES
                  (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                  )";
                $result = $appTable->zQuery($query, array($pid,
                    date('y-m-d H:i:s'),
                    $allergy_begdate_value,
                    $allergy_enddate_value,
                    'allergy',
                    $value['list_code_text'],
                    'RXNORM' . ':' . $value['list_code'],
                    $severity_option_id,
                    $active,
                    $reaction_option_id ?: 0,
                    $value['extension']));
                $list_id = $result->getGeneratedValue();
            } else {
                $q_upd_allergies = "UPDATE lists
                            SET pid=?,
                                date=?,
                                begdate=?,
                                enddate=?,
                                title=?,
                                diagnosis=?,
                                severity_al=?,
                                reaction=?
                            WHERE external_id=? AND type='allergy' AND pid=?";
                $appTable->zQuery($q_upd_allergies, array($pid,
                    date('y-m-d H:i:s'),
                    $allergy_begdate_value,
                    $allergy_enddate_value,
                    $value['list_code_text'],
                    'RXNORM' . ':' . $value['list_code'],
                    $severity_option_id,
                    $reaction_option_id ? $reaction_option_id : 0,
                    $value['extension'],
                    $pid));
            }
        }
    }

    /**
     * @param                       $care_plan_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertCarePlan($care_plan_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($care_plan_array)) {
            return;
        }

        $newid = '';
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery("SELECT MAX(id) as largestId FROM `form_care_plan`");
        foreach ($res as $val) {
            if ($val['largestId']) {
                $newid = $val['largestId'] + 1;
            } else {
                $newid = 1;
            }
        }

        foreach ($care_plan_array as $key => $value) {
            $query_sel_enc = "SELECT encounter
                            FROM form_encounter
                            WHERE date=? AND pid=?";
            $res_query_sel_enc = $appTable->zQuery($query_sel_enc, array(date('Y-m-d H:i:s'), $pid));

            if ($res_query_sel_enc->count() == 0) {
                $res_enc = $appTable->zQuery("SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1", array($pid));
                $res_enc_cur = $res_enc->current();
                $encounter_for_forms = $res_enc_cur['encounter'];
            } else {
                foreach ($res_query_sel_enc as $value2) {
                    $encounter_for_forms = $value2['encounter'];
                }
            }

            $plan_date_value = $value['date'] ? date("Y-m-d H:i:s", $this->str_to_time($value['date'])) : null;
            $end_date = $value['end_date'] ? date("Y-m-d H:i:s", $this->str_to_time($value['end_date'])) : null;
            $low_date = $value['reason_date_low'] ? date("Y-m-d H:i:s", $this->str_to_time($value['reason_date_low'])) : null;
            $high_date = $value['reason_date_high'] ? date("Y-m-d H:i:s", $this->str_to_time($value['reason_date_high'])) : null;

            $query_insert = "INSERT INTO `form_care_plan` (`id`,`pid`,`groupname`,`user`,`encounter`,`activity`,`code`,`codetext`,`description`,`date`,`care_plan_type`, `date_end`, `reason_code`, `reason_description`, `reason_date_low`, `reason_date_high`, `reason_status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $res = $appTable->zQuery($query_insert, array($newid, $pid, $_SESSION["authProvider"], $_SESSION["authUser"], $encounter_for_forms, 1, $value['code'], $value['text'], $value['description'], $plan_date_value, $value['plan_type'], $end_date, $value['reason_code'], $value['reason_code_text'], $low_date, $high_date, $value['reason_status'] ?? null));
        }

        if (count($care_plan_array) > 0) {
            $query = "INSERT INTO forms(date,encounter,form_name,form_id,pid,user,groupname,formdir) VALUES(?,?,?,?,?,?,?,?)";
            $appTable->zQuery($query, array(date('Y-m-d'), $encounter_for_forms, 'Care Plan Form', $newid, $pid, $_SESSION["authUser"], $_SESSION["authProvider"], 'care_plan'));
        }
    }

    /**
     * @param                       $proc_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertProcedures($proc_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1): void
    {
        if (empty($proc_array)) {
            return;
        }
        $encounter_for_billing = 0;
        $appTable = new ApplicationTable();
        foreach ($proc_array as $key => $value) {
            $procedure_date_value = null;
            if (!empty($value['date']) && ($revapprove == 0 || $revapprove == 1)) {
                $procedure_date_value = !empty($value['date']) ? date("Y-m-d H:i:s", $this->str_to_time($value['date'])) : null;
                $end_date = !empty($value['end_date']) ? date("Y-m-d H:i:s", $this->str_to_time($value['end_date'])) : null;
            }
            //facility1
            if (empty($value['represented_organization1'])) {
                $value['represented_organization1'] = CarecoordinationTable::ORGANIZATION_SAMPLE;
            }
            if (!empty($value['represented_organization1'])) {
                $query3 = "SELECT *
                 FROM users
                 WHERE abook_type='external_org' AND organization=?";
                $res3 = $appTable->zQuery($query3, array($value['represented_organization1']));
            }
            if (!empty($value['represented_organization1']) && $res3->count() > 0) {
                foreach ($res3 as $value3) {
                    $facility_id = $value3['id'];
                }
            } else {
                $query4 = "INSERT INTO users
                        ( username,
                          organization,
                          street,
                          city,
                          state,
                          zip,
                          active,
                          abook_type
                        )
                        VALUES
                        ( ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          1,
                          'external_org'
                        )";
                $res4 = $appTable->zQuery($query4, array(
                    '',
                    $value['represented_organization1'],
                    $value['represented_organization_address1'],
                    $value['represented_organization_city1'],
                    $value['represented_organization_state1'],
                    $value['represented_organization_postalcode1']));
                $facility_id = $res4->getGeneratedValue();
            }

            //facility2
            if (empty($value['represented_organization2'])) {
                $value['represented_organization2'] = CarecoordinationTable::ORGANIZATION2_SAMPLE;
            }
            if (!empty($value['represented_organization2'])) {
                $query6 = "SELECT *
                 FROM users
                 WHERE abook_type='external_org' AND organization=?";
                $res6 = $appTable->zQuery($query6, array($value['represented_organization2']));
            }
            if (!empty($value['represented_organization2']) && $res6->count() > 0) {
                foreach ($res6 as $value6) {
                    $facility_id2 = $value6['id'];
                }
            } else {
                $query7 = "INSERT INTO users
                        ( username,
                          organization,
                          street,
                          city,
                          state,
                          zip,
                          active,
                          abook_type
                        )
                        VALUES
                        ( ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          1,
                          'external_org'
                        )";
                $res7 = $appTable->zQuery($query7, array('',
                    $value['represented_organization2'],
                    $value['represented_organization_address2'],
                    $value['represented_organization_city2'],
                    $value['represented_organization_state2'],
                    $value['represented_organization_postalcode2']));
                $facility_id2 = $res7->getGeneratedValue();
            }

            $query_sel_enc = "SELECT encounter
                            FROM form_encounter
                            WHERE date=? AND pid=?";
            $res_query_sel_enc = $appTable->zQuery($query_sel_enc, array($procedure_date_value, $pid));

            if ($res_query_sel_enc->count() == 0) {
                $res_enc = $appTable->zQuery("SELECT encounter
                                                   FROM form_encounter
                                                   WHERE pid=?
                                                   ORDER BY id DESC
                                                   LIMIT 1", array($pid));
                if ($res_enc->count() == 0) {
                    // need to create a form_encounter for the patient since the patient does not have any encounters
                    $data[0]['date'] = $value['date'];
                    $this->InsertEncounter($data, $pid, $carecoordinationTable, 0);
                }
                $res_enc = $appTable->zQuery("SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1", array($pid));
                $res_enc_cur = $res_enc->current();
                $encounter_for_billing = $res_enc_cur['encounter'] ?? null;
            } else {
                foreach ($res_query_sel_enc as $val) {
                    $encounter_for_billing = $val['encounter'];
                }
            }

            $query_select_ct = "SELECT ct_id FROM code_types WHERE ct_key = ? ";
            $result_ct = $appTable->zQuery($query_select_ct, array($value['codeSystemName']));
            foreach ($result_ct as $val_ct) {
                $ct_id = $val_ct['ct_id'];
            }

            $q_select = "SELECT * FROM codes WHERE code_type = ? AND code = ? AND active = ?";
            $res = $appTable->zQuery($q_select, array($value['codeSystemName'], $value['code'], 1));
            if (count($res) === 0) {
                //codes
                $qc_insert = "INSERT INTO codes(code_text,code_text_short,code,code_type,active) VALUES (?,?,?,?,?)";
                $appTable->zQuery($qc_insert, array($value['code_text'], $value['code_text'], $value['code'], $value['codeSystemName'], 1));
            }

            $query_selectB = "SELECT * FROM external_procedures WHERE ep_code = ? AND ep_code_type = ? AND ep_encounter = ? AND ep_pid = ?";
            $result_selectB = $appTable->zQuery($query_selectB, array($value['code'], $value['codeSystemName'], $encounter_for_billing, $pid));
            if ($result_selectB->count() === 0) {
                //external_procedures
                $qB_insert = "INSERT INTO external_procedures(ep_date,ep_code,ep_code_type,ep_code_text,ep_pid,ep_encounter,ep_facility_id,ep_external_id) VALUES (?,?,?,?,?,?,?,?)";
                $appTable->zQuery($qB_insert, array($procedure_date_value, $value['code'], $value['codeSystemName'], $value['code_text'], $pid, $encounter_for_billing, ($facility_id2 ?? null), $value['extension']));
            }

            $code = $value['code'];
            // format code
            if (stripos($value['code'], 'OID:') === false) {
                $code = $this->codeService->getCodeWithType($value['code'], $value['codeSystemName'], true);
            }

            $pro_name = xlt('External Procedure');
            $query_select_pro = 'SELECT * FROM procedure_providers WHERE name = ?';
            $result_pro = $appTable->zQuery($query_select_pro, array($pro_name));
            if ($result_pro->count() == 0) {
                $query_insert_pro = 'INSERT INTO procedure_providers(name) VALUES (?)';
                $result_pro = $appTable->zQuery($query_insert_pro, array($pro_name));
                $pro_id = $result_pro->getGeneratedValue();
            } else {
                foreach ($result_pro as $value1) {
                    $pro_id = $value1['ppid'];
                }
            }
            $query_select_pt = 'SELECT * FROM procedure_type WHERE procedure_code = ? AND lab_id = ?';
            $result_types = sqlQuery($query_select_pt, array($code, $pro_id));
            $ptid = (int)($result_types['procedure_type_id'] ?? 0);
            if ($ptid === 0) {
                $query_insert = 'INSERT INTO procedure_type(name,lab_id,procedure_code,procedure_type,activity,procedure_type_name) VALUES (?,?,?,?,?,?)';
                $ptid = sqlInsert(
                    $query_insert,
                    array(
                        $value['code_text'], $pro_id, $code, 'ord', 1, $value['procedure_type'])
                );
                $query_update_pt = 'UPDATE procedure_type SET parent = ? WHERE procedure_type_id = ?';
                sqlQuery($query_update_pt, array($ptid, $ptid));
            }
            //procedure_order
            $low_date = $value['reason_date_low'] ? date("Y-m-d H:i:s", $this->str_to_time($value['reason_date_low'])) : null;
            $high_date = $value['reason_date_high'] ? date("Y-m-d H:i:s", $this->str_to_time($value['reason_date_high'])) : null;

            $query_insert_po = 'INSERT INTO procedure_order(provider_id,patient_id,encounter_id,date_collected,date_ordered,order_priority,order_status,activity,lab_id,procedure_order_type)
                VALUES (?,?,?,NULL,?,?,?,?,?,?)';
            $result_po = $appTable->zQuery($query_insert_po, array('', $pid, $encounter_for_billing, $procedure_date_value, 'normal', ($value['status'] ?? 'completed'), 1, $pro_id, $value['procedure_type']));
            $po_id = $result_po->getGeneratedValue();

            //procedure_order_code
            $query_insert_poc = 'INSERT INTO procedure_order_code(procedure_order_id,procedure_order_seq,procedure_code,procedure_name,diagnoses,procedure_order_title,procedure_type, `date_end`, `reason_code`, `reason_description`, `reason_date_low`, `reason_date_high`, `reason_status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $result_poc = $appTable->zQuery($query_insert_poc, array($po_id, 1, $code, $value['code_text'], '', $value['procedure_type'], $value['procedure_type'], $end_date, $value['reason_code'], $value['reason_description'], $low_date, $high_date, $value['reason_status'] ?? null));

            $pro_name_enc = $pro_name . '-' . $value['procedure_type'];
            addForm($encounter_for_billing, $pro_name_enc, $po_id, 'procedure_order', $pid, $this->userauthorized);
        }
    }

    /**
     * @param                       $enc_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertEncounter($enc_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($enc_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($enc_array as $key => $value) {
            $encounter_id = $appTable->generateSequenceID();

            if (empty($value['provider_npi'])) {
                $value['provider_npi'] = CarecoordinationTable::NPI_SAMPLE;
            }
            if (!empty($value['provider_npi'])) {
                $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
                $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            }
            if (!empty($value['provider_npi']) && $res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $provider_id = $this->insertImportedUser($value, true);
            }

            //facility
            if (empty($value['represented_organization_name'])) {
                $value['represented_organization_name'] = CarecoordinationTable::ORGANIZATION_SAMPLE;
            }
            if (!empty($value['represented_organization_name'])) {
                $query_sel_fac = "SELECT *
                            FROM users
                            WHERE abook_type='external_org' AND organization=?";
                $res_query_sel_fac = $appTable->zQuery($query_sel_fac, array($value['represented_organization_name']));
            }
            if (!empty($value['represented_organization_name']) && $res_query_sel_fac->count() > 0) {
                foreach ($res_query_sel_fac as $value2) {
                    $facility_id = $value2['id'];
                }
            } else {
                $query_ins_fac = "INSERT INTO users
                              ( username,
                                organization,
                                phonecell,
                                street,
                                city,
                                state,
                                zip,
                                active,
                                abook_type
                              )
                              VALUES
                              (
                                ?,
                                ?,
                                ?,
                                ?,
                                ?,
                                ?,
                                ?,
                                1,
                                'external_org'
                              )";
                $res_query_ins_fac = $appTable->zQuery($query_ins_fac, array(
                    '',
                    $value['represented_organization_name'] ?? null,
                    $value['represented_organization_telecom'] ?? null,
                    $value['represented_organization_address'] ?? null,
                    $value['represented_organization_city'] ?? null,
                    $value['represented_organization_state'] ?? null,
                    $value['represented_organization_zip'] ?? null));
                $facility_id = $res_query_ins_fac->getGeneratedValue();
            }

            $encounter_date_value = null;
            $encounter_date_end = null;
            if (!empty($value['date']) && ($revapprove == 0 || $revapprove == 1)) {
                $encounter_date_value = date("Y-m-d H:i:s", $this->str_to_time($value['date']));
            }
            if (!empty($value['date_end']) && ($revapprove == 0 || $revapprove == 1)) {
                $encounter_date_end = date("Y-m-d H:i:s", $this->str_to_time($value['date_end']));
            }
            $diag_date = !empty($value['encounter_diagnosis_date']) ? date("Y-m-d H:i:s", $this->str_to_time($value['encounter_diagnosis_date'])) : null;

            if (!empty($value['extension'])) {
                $q_sel_encounter = "SELECT *
                               FROM form_encounter
                               WHERE external_id=? AND pid=?";
                $res_q_sel_encounter = $appTable->zQuery($q_sel_encounter, array($value['extension'], $pid));
            }
            if (empty($value['extension']) || $res_q_sel_encounter->count() === 0) {
                $query_insert1 = "INSERT INTO form_encounter
                           (
                            pid,
                            encounter,
                            date,
                            facility,
                            facility_id,
                            provider_id,
                            external_id,
                            reason,
                            discharge_disposition,
                            encounter_type_code,
                            encounter_type_description,
                            date_end
                           )
                           VALUES
                           (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                           )";
                $result = $appTable->zQuery(
                    $query_insert1,
                    array(
                        $pid,
                        $encounter_id,
                        $encounter_date_value,
                        $value['represented_organization_name'] ?? null,
                        $facility_id,
                        $provider_id,
                        $value['extension'] ?? null,
                        $value['code_text'] ?? null,
                        $value['encounter_discharge_code'] ?? null,
                        $value['code'] ?? null,
                        $value['code_text'] ?? null,
                        $encounter_date_end ?? null
                    )
                );
                $enc_id = $result->getGeneratedValue();
            } else {
                $q_upd_encounter = "UPDATE form_encounter
                            SET pid=?,
                                encounter=?,
                                date=?,
                                facility=?,
                                facility_id=?,
                                provider_id=?
                            WHERE external_id=? AND pid=?";
                $appTable->zQuery($q_upd_encounter, array($pid,
                    $encounter_id,
                    $encounter_date_value,
                    $value['represented_organization_name'],
                    $facility_id,
                    $provider_id,
                    $value['extension'],
                    $pid));
                $q_sel_enc = "SELECT id FROM form_encounter WHERE external_id=?";
                $res_q_sel_enc = $appTable->zQuery($q_sel_enc, array($value['extension']));
                $res_enc_cur = $res_q_sel_enc->current();
                $enc_id = $res_enc_cur['id'];
            }

            $q_ins_forms = "INSERT INTO forms (date,encounter,form_name,form_id,pid,user,groupname,deleted,formdir) VALUES (?,?,?,?,?,?,?,?,?)";
            $appTable->zQuery($q_ins_forms, array($encounter_date_value, $encounter_id, 'New Patient Encounter', $enc_id, $pid, ($_SESSION["authProvider"] ?? null), 'Default', 0, 'newpatient'));
            if (!empty($value['encounter_diagnosis_code'])) {
                $query_select = "SELECT * FROM lists WHERE begdate = ? AND title = ? AND pid = ?";
                $result = $appTable->zQuery($query_select, array($diag_date, $value['encounter_diagnosis_issue'], $pid));
                if ($result->count() > 0) {
                    foreach ($result as $value1) {
                        $list_id = $value1['id'];
                    }
                } else {
                    //to lists
                    $query_insert = "INSERT INTO lists(pid,type,begdate,activity,title,date, diagnosis, subtype) VALUES (?,?,?,?,?,?,?,?)";
                    $result = $appTable->zQuery($query_insert, array($pid, 'medical_problem', $diag_date, 1,
                        $value['encounter_diagnosis_issue'], date('Y-m-d H:i:s'), $value['encounter_diagnosis_code'], 'encounter_diagnosis'));
                    $list_id = $result->getGeneratedValue();
                }

                //Linking issue with encounter
                $q_sel_iss_enc = "SELECT * FROM issue_encounter WHERE pid=? and list_id=? and encounter=?";
                $res_sel_iss_enc = $appTable->zQuery($q_sel_iss_enc, array($pid, $list_id, $encounter_id));
                if ($res_sel_iss_enc->count() === 0) {
                    $insert = "INSERT INTO issue_encounter(pid,list_id,encounter,resolved) VALUES (?,?,?,?)";
                    $appTable->zQuery($insert, array($pid, $list_id, $encounter_id, 0));
                }
            }
            //to external_encounters
            $insertEX = "INSERT INTO external_encounters(ee_date,ee_pid,ee_provider_id,ee_facility_id,ee_encounter_diagnosis,ee_external_id) VALUES (?,?,?,?,?,?)";
            $appTable->zQuery($insertEX, array($encounter_date_value, $pid, $provider_id, $facility_id, ($value['encounter_diagnosis_issue'] ?? null), ($value['extension'] ?? null)));
        }
    }

    /**
     * @param                       $imm_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertImmunization($imm_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        // if we don't have any immunizations we aren't going to insert anything.
        if (empty($imm_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        $qc_select = "SELECT ct_id FROM code_types WHERE ct_key = ?";
        $c_result = $appTable->zQuery($qc_select, array('CVX'));
        foreach ($c_result as $val) {
            $ct_id = $val['ct_id'];
        }

        foreach ($imm_array as $key => $value) {
            //provider
            if (empty($value['provider_npi'])) {
                $value['provider_npi'] = CarecoordinationTable::NPI_SAMPLE;
            }
            if (!empty($value['provider_npi'])) {
                $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
                $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            }
            if (!empty($value['provider_npi']) && $res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $provider_id = $this->insertImportedUser($value, true);
            }

            //facility
            if (empty($value['represented_organization'])) {
                $value['represented_organization'] = CarecoordinationTable::ORGANIZATION_SAMPLE;
            }
            if (!empty($value['represented_organization'])) {
                $query_sel_fac = "SELECT *
                            FROM users
                            WHERE abook_type='external_org' AND organization=?";
                $res_query_sel_fac = $appTable->zQuery($query_sel_fac, array($value['represented_organization']));
            }
            if (!empty($value['represented_organization']) && $res_query_sel_fac->count() > 0) {
                foreach ($res_query_sel_fac as $value2) {
                    $facility_id = $value2['id'];
                }
            } else {
                $query_ins_fac = "INSERT INTO users
                              ( organization,
                                phonecell,
                                abook_type
                              )
                              VALUES
                              (
                                ?,
                                ?,
                                'external_org'
                              )";
                $res_query_ins_fac = $appTable->zQuery($query_ins_fac, array($value['represented_organization'],
                    $value['represented_organization_tele']));
                $facility_id = $res_query_ins_fac->getGeneratedValue();
            }

            if ($value['administered_date'] != 0 && $revapprove == 0) {
                $immunization_date = $carecoordinationTable->formatDate($value['administered_date'], 1);
                $immunization_date_value = fixDate($immunization_date);
            } elseif ($value['administered_date'] != 0 && $revapprove == 1) {
                $immunization_date_value = ApplicationTable::fixDate($value['administered_date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['administered_date'] != 0) {
                $immunization_date = $value['administered_date'];
                $immunization_date_value = fixDate($immunization_date);
            }

            $q_select = "SELECT * FROM codes WHERE code_text = ? AND code = ? AND code_type = ?";
            $res = $appTable->zQuery($q_select, array($value['cvx_code_text'], $value['cvx_code'], $ct_id));
            if ($res->count() == 0) {
                //codes
                $qc_insert = "INSERT INTO codes(code_text,code,code_type) VALUES (?,?,?)";
                $appTable->zQuery($qc_insert, array($value['cvx_code_text'], $value['cvx_code'], $ct_id));
            }

            $q1_unit = "SELECT * FROM list_options WHERE list_id='drug_units' AND title=?";
            $res_q1_unit = $appTable->zQuery($q1_unit, array($value['amount_administered_unit']));
            foreach ($res_q1_unit as $val) {
                $oid_unit = $val['option_id'];
            }
            if ($res_q1_unit->count() == 0) {
                $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('drug_units'));
                foreach ($lres as $lrow) {
                    $oid_unit = $lrow['option_id'];
                }
                $q_insert_route = "INSERT INTO list_options
                           (
                            list_id,
                            option_id,
                            title,
                            activity
                           )
                           VALUES
                           (
                            'drug_units',
                            ?,
                            ?,
                            1
                           )";
                $appTable->zQuery($q_insert_route, array($oid_unit, $value['amount_administered_unit']));
            }

            $value['completion_status'] = $value['reason_status'] ?: $value['completion_status'];
            $q1_completion_status = "SELECT *
                       FROM list_options
                       WHERE list_id='Immunization_Completion_Status' AND title=?";
            $res_q1_completion_status = $appTable->zQuery($q1_completion_status, array($value['completion_status']));

            if ($res_q1_completion_status->count() == 0) {
                $q_insert_completion_status = "INSERT INTO list_options
                           (
                            list_id,
                            option_id,
                            title,
                            activity
                           )
                           VALUES
                           (
                            'Immunization_Completion_Status',
                            ?,
                            ?,
                            1
                           )";
                $appTable->zQuery($q_insert_completion_status, array($value['completion_status'], $value['completion_status']));
            }

            $q1_manufacturer = "SELECT *
                       FROM list_options
                       WHERE list_id='Immunization_Manufacturer' AND title=?";
            $res_q1_manufacturer = $appTable->zQuery($q1_manufacturer, array($value['manufacturer']));

            if ($res_q1_manufacturer->count() == 0) {
                $q_insert_completion_status = "INSERT INTO list_options
                           (
                            list_id,
                            option_id,
                            title,
                            activity
                           )
                           VALUES
                           (
                            'Immunization_Manufacturer',
                            ?,
                            ?,
                            1
                           )";
                $appTable->zQuery($q_insert_completion_status, array($value['manufacturer'], $value['manufacturer']));
            }
            $option = '';
            if (!empty($value['reason_code'])) {
                // TODO: check with @sjpadgett why the codes are "SNOMED CT:<number>" instead of "SNOMED-CT" for the immunization reason code
                $reason_code = str_replace("SNOMED CT", "SNOMED-CT", $value['reason_code']);

                $listService = new ListService();
                $option = $listService->getOptionsByListName('immunization_refusal_reason', ['codes' => $reason_code])[0];
            }

            if (!empty($value['extension'])) {
                $q_sel_imm = "SELECT *
                        FROM immunizations
                        WHERE external_id=? AND patient_id=?";
                $res_q_sel_imm = $appTable->zQuery($q_sel_imm, array($value['extension'], $pid));
            }
            if (empty($value['extension']) || $res_q_sel_imm->count() == 0) {
                $query = "INSERT INTO immunizations
                  ( patient_id,
                    administered_date,
                    cvx_code,
                    route,
                    administered_by_id,
                    amount_administered,
                    amount_administered_unit,
                    manufacturer,
                    completion_status,
                    external_id,
                    refusal_reason
                  )
                  VALUES
                  (
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?,
                   ?
                  )";
                $appTable->zQuery(
                    $query,
                    array($pid,
                        $immunization_date_value,
                        $value['cvx_code'],
                        $value['route_code_text'],
                        $provider_id,
                        $value['amount_administered'],
                        $oid_unit,
                        $value['manufacturer'],
                        $value['completion_status'],
                        $value['extension'],
                        $option['option_id'] ?? '')
                );
            } else {
                $q_upd_imm = "UPDATE immunizations
                      SET patient_id=?,
                          administered_date=?,
                          cvx_code=?,
                          route=?,
                          administered_by_id=?,
                          amount_administered=?,
                          amount_administered_unit=?,
                          manufacturer=?,
                          completion_status=?
                      WHERE external_id=? AND patient_id=?";
                $appTable->zQuery($q_upd_imm, array($pid,
                    $immunization_date_value,
                    $value['cvx_code'],
                    $value['route_code_text'],
                    $provider_id,
                    $value['amount_administered'],
                    $oid_unit,
                    $value['manufacturer'],
                    $value['completion_status'],
                    $value['extension'],
                    $pid));
            }
        }
    }

    /**
     * @param                       $pres_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertPrescriptions($pres_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($pres_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        $oid_route = $unit_option_id = $oidu_unit = '';
        foreach ($pres_array as $key => $value) {
            $active = 1;
            if (empty($value['enddate'])) {
                $value['enddate'] = (null);
            }

            if ($revapprove == 1) {
                if ($value['discontinue'] == 1) {
                    $active = '-1';
                    if ($value['enddate'] == (null)) {
                        $value['enddate'] = date('Y-m-d');
                    }
                } else {
                    $active = '1';
                    if ($value['enddate']) {
                        $value['enddate'] = (null);
                    }
                }

                $value['begdate'] = ApplicationTable::fixDate($value['begdate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            }

            //provider
            if (empty($value['provider_npi'])) {
                $value['provider_npi'] = CarecoordinationTable::NPI_SAMPLE;
            }
            if (!empty($value['provider_npi'])) {
                $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
                $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            }
            if (!empty($value['provider_npi']) && $res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $provider_id = $this->insertImportedUser($value, true);
            }

            //unit
            if ($revapprove == 1) {
                $value['rate_unit'] = $carecoordinationTable->getListTitle($value['rate_unit'], 'drug_units', '');
            }

            $unit_option_id = $carecoordinationTable->getOptionId('drug_units', $value['rate_unit'], '');
            if ($unit_option_id == '' || $unit_option_id == null) {
                $q_max_option_id = "SELECT MAX(CAST(option_id AS SIGNED))+1 AS option_id
                              FROM list_options
                              WHERE list_id=?";
                $res_max_option_id = $appTable->zQuery($q_max_option_id, array('drug_units'));
                $res_max_option_id_cur = $res_max_option_id->current();
                $unit_option_id = $res_max_option_id_cur['option_id'];
                $q_insert_units_option = "INSERT INTO list_options
                           (
                            list_id,
                            option_id,
                            title,
                            activity
                           )
                           VALUES
                           (
                            'drug_units',
                            ?,
                            ?,
                            1
                           )";
                $appTable->zQuery($q_insert_units_option, array($unit_option_id, $value['rate_unit']));
            }

            //route
            $q1_route = "SELECT *
                       FROM list_options
                       WHERE list_id='drug_route' AND notes=?";
            $res_q1_route = $appTable->zQuery($q1_route, array($value['route']));
            foreach ($res_q1_route as $val) {
                $oid_route = $val['option_id'];
            }

            if ($res_q1_route->count() == 0) {
                $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('drug_route'));
                foreach ($lres as $lrow) {
                    $oid_route = $lrow['option_id'];
                }

                $q_insert_route = "INSERT INTO list_options
                           (
                            list_id,
                            option_id,
                            notes,
                            title,
                            activity
                           )
                           VALUES
                           (
                            'drug_route',
                            ?,
                            ?,
                            ?,
                            1
                           )";
                $appTable->zQuery($q_insert_route, array($oid_route, $value['route'],
                    $value['route_display']));
            }

            //drug form
            $query_select_form = "SELECT * FROM list_options WHERE list_id = ? AND title = ?";
            $result = $appTable->zQuery($query_select_form, array('drug_form', $value['dose_unit']));
            if ($result->count() > 0) {
                $q_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND title = ?";
                $appTable->zQuery($q_update, array('drug_form', $value['dose_unit']));
                foreach ($result as $value2) {
                    $oidu_unit = $value2['option_id'];
                }
            } else {
                $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('drug_form'));
                foreach ($lres as $lrow) {
                    $oidu_unit = $lrow['option_id'];
                }

                $q_insert = "INSERT INTO list_options (list_id,option_id,title,activity) VALUES (?,?,?,?)";
                $appTable->zQuery($q_insert, array('drug_form', $oidu_unit, $value['dose_unit'], 1));
            }

            $res_q_sel_pres_cnt = $res_q_sel_pres_r_cnt = null; // to avoid php8 warnings
            if (!empty($value['extension'])) {
                $q_sel_pres = "SELECT *
                         FROM prescriptions
                         WHERE patient_id = ? AND external_id = ?";
                $res_q_sel_pres = $appTable->zQuery($q_sel_pres, array($pid, $value['extension']));
                $res_q_sel_pres_cnt = $res_q_sel_pres->count();
            } else {
                // prevent bunch of duplicated prescriptions/medications
                $q_sel_pres_r = "SELECT *
                         FROM `prescriptions`
                         WHERE `patient_id` = ? AND `drug` = ?";
                $res_q_sel_pres_r = $appTable->zQuery($q_sel_pres_r, array($pid, $value['drug_text']));
                $res_q_sel_pres_r_cnt = $res_q_sel_pres_r->count();
            }

            if ((empty($value['extension']) && $res_q_sel_pres_r_cnt === 0) || ($res_q_sel_pres_cnt === 0)) {
                $query = "INSERT INTO prescriptions
                  ( patient_id,
                    date_added,
                    end_date,
                    active,
                    drug,
                    size,
                    form,
                    dosage,
                    route,
                    unit,
                    indication,
                    prn,
                    rxnorm_drugcode,
                    provider_id,
                    external_id,
                    medication,
                    request_intent
                 )
                 VALUES
                 (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                 )";
                $appTable->zQuery($query, array($pid,
                    $value['begdate'],
                    $value['enddate'],
                    $active,
                    $value['drug_text'],
                    $value['rate'],
                    $oidu_unit,
                    $value['dose'],
                    $oid_route,
                    $unit_option_id,
                    $value['indication'],
                    $value['prn'],
                    $value['drug_code'],
                    $provider_id,
                    $value['extension'],
                    0,
                    ($value['request_intent'] ?? null)));
            } else {
                $q_upd_pres = "UPDATE prescriptions
                       SET patient_id=?,
                           date_added=?,
                           end_date = ?,
                           active = ?,
                           drug=?,
                           size=?,
                           form=?,
                           dosage=?,
                           route=?,
                           unit=?,
                           note=?,
                           indication=?,
                           prn = ?,
                           rxnorm_drugcode=?,
                           provider_id=?,
                           medication=?,
                           request_intent=?
                       WHERE external_id=? AND patient_id=?";
                $appTable->zQuery($q_upd_pres, array($pid,
                    $value['begdate'],
                    $value['enddate'],
                    $active,
                    $value['drug_text'],
                    $value['rate'],
                    $oidu_unit,
                    $value['dose'],
                    $oid_route,
                    $unit_option_id,
                    $value['note'],
                    $value['indication'],
                    $value['prn'],
                    $value['drug_code'],
                    $provider_id,
                    $value['extension'],
                    $pid,
                    0,
                    ($value['request_intent'] ?? null)));
            }
        }
    }

    /**
     * @param                       $med_pblm_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertMedicalProblem($med_pblm_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($med_pblm_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($med_pblm_array as $key => $value) {
            $activity = 1;

            if (!empty($value['begdate']) && $revapprove == 0) {
                $med_pblm_begdate_value = date("Y-m-d H:i:s", $this->str_to_time($value['begdate']));
            } elseif ($value['begdate'] != 0 && $revapprove == 1) {
                $med_pblm_begdate_value = date("Y-m-d H:i:s", $this->str_to_time($value['begdate']));
            } elseif (empty($value['begdate'])) {
                $med_pblm_begdate_value = (null);
            }

            if (!empty($value['enddate']) && $revapprove == 0) {
                $med_pblm_enddate_value = date("Y-m-d H:i:s", $this->str_to_time($value['enddate']));
            } elseif (!empty($value['enddate']) && $revapprove == 1) {
                $med_pblm_enddate_value = date("Y-m-d H:i:s", $this->str_to_time($value['enddate']));
            } elseif (empty($value['enddate'])) {
                $med_pblm_enddate_value = (null);
            }

            if ($revapprove == 1) {
                if ($value['resolved'] == 1) {
                    if (!$med_pblm_enddate_value) {
                        $med_pblm_enddate_value = date('y-m-d');
                    }
                } else {
                    $med_pblm_enddate_value = (null);
                }
            }

            $query_select = "SELECT * FROM list_options WHERE list_id = ? AND title = ?";
            $result = $appTable->zQuery($query_select, array('outcome', $value['observation_text']));
            if ($result->count() > 0) {
                $q_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND title = ? AND codes = ?";
                $appTable->zQuery($q_update, array('outcome', $value['observation_text'], 'SNOMED-CT:' . ($value['observation'] ?? '')));
                foreach ($result as $value1) {
                    $o_id = $value1['option_id'];
                }
            } else {
                $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('outcome'));
                foreach ($lres as $lrow) {
                    $o_id = $lrow['option_id'];
                }

                $q_insert = "INSERT INTO list_options (list_id,option_id,title,codes,activity) VALUES (?,?,?,?,?)";
                $appTable->zQuery($q_insert, array('outcome', $o_id, $value['observation_text'], 'SNOMED-CT:' . ($value['observation'] ?? ''), 1));
            }

            if (!empty($value['extension'])) {
                $q_sel_med_pblm = "SELECT *
                             FROM lists
                             WHERE external_id=? AND type='medical_problem' AND begdate=? AND diagnosis=? AND pid=?";
                $res_q_sel_med_pblm = $appTable->zQuery($q_sel_med_pblm, array($value['extension'], $med_pblm_begdate_value, 'SNOMED-CT:' . $value['list_code'], $pid));
            }
            if (empty($value['extension']) || $res_q_sel_med_pblm->count() == 0) {
                $query = "INSERT INTO lists
                  ( pid,
                    date,
                    diagnosis,
                    activity,
                    title,
                    begdate,
                    enddate,
                    outcome,
                    type,
                    external_id,
                    subtype
                  )
                  VALUES
                  ( ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                  )";
                $result = $appTable->zQuery($query, array($pid,
                    date('y-m-d H:i:s'),
                    $value['list_code'],
                    $activity,
                    $value['list_code_text'],
                    $med_pblm_begdate_value,
                    $med_pblm_enddate_value,
                    $o_id,
                    'medical_problem',
                    $value['extension'],
                    $value['subtype']));

                $list_id = $result->getGeneratedValue();
            } else {
                $q_upd_med_pblm = "UPDATE lists
                           SET pid=?,
                               date=?,
                               diagnosis=?,
                               title=?,
                               begdate=?,
                               enddate=?,
                               outcome=?
                           WHERE external_id=? AND type='medical_problem' AND begdate=? AND diagnosis=? AND pid=?";
                $appTable->zQuery($q_upd_med_pblm, array($pid,
                    date('y-m-d H:i:s'),
                    $value['list_code'],
                    $value['list_code_text'],
                    $med_pblm_begdate_value,
                    $med_pblm_enddate_value,
                    $o_id,
                    $value['extension'],
                    $value['begdate'],
                    $value['list_code'],
                    $pid));
            }
        }
    }

    /**
     * @param $value
     * @param $create_user_name
     * @return mixed
     */
    public function insertImportedUser($value, $create_user_name = false)
    {
        $appTable = new ApplicationTable();
        $userName = "";

        if (!empty($value['provider_fname'])) {
            $value['provider_name'] = ($value['provider_fname'] ?? '') ?: 'External';
            $value['provider_family'] = ($value['provider_lname'] ?? '') ?: 'Provider';
        }

        if ($create_user_name) {
            $userName = (($value['provider_name'] ?? '') ?: 'External') . (($value['provider_family'] ?? '') ?: 'Provider');
        }
        $query_ins_users = "INSERT INTO users
        ( username, fname, lname, npi, authorized, organization, street, city, state, zip, active, abook_type)
        VALUES(?, ?, ?, ?, 1, ?, ?, ?, ?, ?, 1, 'external_provider')";
        $res_query_ins_users = $appTable->zQuery($query_ins_users, array(
            $userName,
            ($value['provider_name'] ?? '') ?: 'External',
            ($value['provider_family'] ?? '') ?: 'Provider',
            ($value['provider_npi'] ?? '') ?: CarecoordinationTable::NPI_SAMPLE,
            $value['represented_organization_name'] ?? null,
            $value['provider_address'] ?? null,
            $value['provider_city'] ?? null,
            $value['provider_state'] ?? null,
            $value['provider_postalCode'] ?? null));

        return $res_query_ins_users->getGeneratedValue();
    }

    /**
     * @param                       $lab_results
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @return void
     */
    public function InsertLabResults($lab_results, $pid, CarecoordinationTable $carecoordinationTable)
    {
        if (empty($lab_results)) {
            return;
        }

        $pro_name = xlt('External Lab');
        if ($carecoordinationTable->is_qrda_import) {
            $pro_name = xlt('Qrda Lab');
        }
        $appTable = new ApplicationTable();
        foreach ($lab_results as $key => $value) {
            $query_select_pro = "SELECT * FROM procedure_providers WHERE name = ?";
            $result_pro = $appTable->zQuery($query_select_pro, array($pro_name));
            if ($result_pro->count() == 0) {
                $query_insert_pro = "INSERT INTO procedure_providers(name) VALUES (?)";
                $result_pro = $appTable->zQuery($query_insert_pro, array($pro_name));
                $pro_id = $result_pro->getGeneratedValue();
            } else {
                foreach ($result_pro as $value1) {
                    $pro_id = $value1['ppid'];
                }
            }

            $enc = $appTable->zQuery("SELECT encounter
                                      FROM form_encounter
                                      WHERE pid=?
                                      ORDER BY id DESC LIMIT 1", array($pid));
            $enc_cur = $enc->current();
            $enc_id = $enc_cur['encounter'] ?: 0;

            $query_select_pt = 'SELECT * FROM procedure_type WHERE procedure_code = ? AND procedure_type = ? AND lab_id = ?';
            $result_pt = $appTable->zQuery($query_select_pt, array($value['proc_code'], 'ord', $pro_id));
            if ($result_pt->count() == 0) {
                //procedure_type
                $query_insert_pt = 'INSERT INTO procedure_type(name,lab_id,procedure_code,procedure_type,activity,procedure_type_name) VALUES (?,?,?,?,?,?)';
                $result_pt = $appTable->zQuery($query_insert_pt, array($value['proc_text'], $pro_id, $value['proc_code'], 'ord', 1, 'laboratory_test'));
                $res_pt_id = $result_pt->getGeneratedValue();
                $query_update_pt = 'UPDATE procedure_type SET parent = ? WHERE procedure_type_id = ?';
                $appTable->zQuery($query_update_pt, array($res_pt_id, $res_pt_id));
            }

            if (!empty($value['date'] ?? null)) {
                $date = ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'yyyy/mm/dd') ?? null;
            }
            if (!empty($value['result'][0]['result_date']) && empty($value['date'])) {
                // no order date so give result date
                $date = ApplicationTable::fixDate($value['result'][0]['result_date'], 'yyyy-mm-dd', 'yyyy/mm/dd');
                $value['date'] = $date;
            }
            if (empty($value['date'])) {
                // no order date make today
                $value['date'] = $carecoordinationTable->formatDate(date('Ymd'), 1);
                $date = ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'yyyy/mm/dd');
            }

            //procedure_order
            $query_insert_po = "INSERT INTO procedure_order(provider_id,patient_id,encounter_id,date_collected,date_ordered,order_priority,order_status,activity,lab_id,procedure_order_type) VALUES (?,?,?,?,?,?,?,?,?,'laboratory_test')";
            $result_po = $appTable->zQuery($query_insert_po, array('', $pid, $enc_id, ($date ?? null), ($date ?? null), 'normal', $value['status'] ?? 'complete', 1, $pro_id));
            $po_id = $result_po->getGeneratedValue();

            //procedure_order_code
            $query_insert_poc = 'INSERT INTO procedure_order_code(procedure_order_id,procedure_order_seq,procedure_code,procedure_name,diagnoses,procedure_order_title,procedure_type) VALUES (?,?,?,?,?,?,?)';

            $result_poc = $appTable->zQuery($query_insert_poc, array($po_id, 1, $value['proc_code'], $value['proc_text'], '', 'laboratory_test', 'laboratory_test'));
            addForm($enc_id, $pro_name . '-' . $po_id, $po_id, 'procedure_order', $pid, $this->userauthorized);

            //procedure_report
            $query_insert_pr = 'INSERT INTO procedure_report(procedure_order_id,date_collected,date_report,report_status,review_status) VALUES (?,?,?,?,?)';
            $result_pr = $appTable->zQuery($query_insert_pr, array($po_id, ($date ?? null), ($date ?? null), 'final', 'reviewed'));
            $res_id = $result_pr->getGeneratedValue();

            foreach ($value['result'] as $res) {
                //procedure_result
                $range = $res['result_range'] ?? '';
                $unit = $res['result_unit'] ?? '';
                $result_date = ApplicationTable::fixDate($res['result_date'], 'yyyy-mm-dd', 'yyyy/mm/dd');
                if (!empty($unit)) {
                    $qU_select = "SELECT * FROM list_options WHERE list_id = ? AND option_id = ?";
                    $Ures = $appTable->zQuery($qU_select, array('proc_unit', $unit));
                    if ($Ures->count() == 0) {
                        $qU_insert = "INSERT INTO list_options(list_id,option_id,title,activity) VALUES (?,?,?,?)";
                        $appTable->zQuery($qU_insert, array('proc_unit', $unit, $unit, 1));
                    } else {
                        $qU_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND option_id = ?";
                        $appTable->zQuery($qU_update, array('proc_unit', $unit));
                    }

                    $query_select_pt = 'SELECT * FROM procedure_type WHERE procedure_code = ? AND procedure_type = ? AND lab_id = ?';
                    $result_pt = $appTable->zQuery($query_select_pt, array($value['proc_code'], 'res', $pro_id));
                    if ($result_pt->count() == 0) {
                        //result_type
                        $query_insert_pt = 'INSERT INTO procedure_type(name,lab_id,procedure_code,procedure_type,activity,procedure_type_name) VALUES (?,?,?,?,?,?)';
                        $result_pt = $appTable->zQuery($query_insert_pt, array($res['result_text'], $pro_id, $res['result_code'], 'res', 1, 'laboratory_test'));
                        $res_pt_id_res = $result_pt->getGeneratedValue();
                        $query_update_pt = 'UPDATE procedure_type SET parent = ? WHERE procedure_type_id = ?';
                        $appTable->zQuery($query_update_pt, array(($res_pt_id ?? null), $res_pt_id_res));
                    }

                    if (!empty($res['result_code'])) {
                        $query_insert_prs = "INSERT INTO procedure_result(procedure_report_id,result_code,date,units,result,`range`,result_text,result_status) VALUES (?,?,?,?,?,?,?,?)";
                        $result_prs = $appTable->zQuery($query_insert_prs, array($res_id, $res['result_code'], $result_date, $unit, $res['result_value'], $range, $res['result_text'], 'final'));
                    }
                }
            }
        }
    }

    /**
     * @param                       $functional_cognitive_status_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertFunctionalCognitiveStatus($functional_cognitive_status_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($functional_cognitive_status_array)) {
            return;
        }
        $newid = '';
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery("SELECT MAX(id) as largestId FROM `form_functional_cognitive_status`");
        foreach ($res as $val) {
            if ($val['largestId']) {
                $newid = $val['largestId'] + 1;
            } else {
                $newid = 1;
            }
        }

        foreach ($functional_cognitive_status_array as $key => $value) {
            if ($value['date'] != '') {
                $date = $carecoordinationTable->formatDate($value['date']);
            } else {
                $date = date('Y-m-d');
            }

            $query_sel_enc = "SELECT encounter
                            FROM form_encounter
                            WHERE date=? AND pid=?";
            $res_query_sel_enc = $appTable->zQuery($query_sel_enc, array($date, $pid));

            if ($res_query_sel_enc->count() == 0) {
                $res_enc = $appTable->zQuery("SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1", array($pid));
                $res_enc_cur = $res_enc->current();
                $encounter_for_forms = $res_enc_cur['encounter'];
            } else {
                foreach ($res_query_sel_enc as $value2) {
                    $encounter_for_forms = $value2['encounter'];
                }
            }

            $query_insert = "INSERT INTO form_functional_cognitive_status(id,pid,groupname,user,encounter, activity,code,codetext,description,date)VALUES(?,?,?,?,?,?,?,?,?,?)";
            $res = $appTable->zQuery($query_insert, array($newid, $pid, $_SESSION["authProvider"], $_SESSION["authUser"], $encounter_for_forms, 1, $value['code'], $value['text'], $value['description'], $date));
        }

        if (count($functional_cognitive_status_array) > 0) {
            $query = "INSERT INTO forms(date,encounter,form_name,form_id,pid,user,groupname,formdir)VALUES(?,?,?,?,?,?,?,?)";
            $appTable->zQuery($query, array($date, $encounter_for_forms, 'Functional and Cognitive Status Form', $newid, $pid, $_SESSION["authUser"], $_SESSION["authProvider"], 'functional_cognitive_status'));
        }
    }

    /**
     * @param $arr_referral
     * @param $pid
     * @param $revapprove
     * @return void
     */
    public function InsertReferrals($arr_referral, $pid, $revapprove = 1)
    {
        if (empty($arr_referral)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($arr_referral as $key => $value) {
            $query_insert = "INSERT INTO transactions(date,title,pid,groupname,user,authorized)VALUES(?,?,?,?,?,?)";
            $res = $appTable->zQuery($query_insert, array(date('Y-m-d H:i:s'), 'LBTref', $pid, $_SESSION["authProvider"], $_SESSION["authUser"], $_SESSION["userauthorized"]));
            $trans_id = $res->getGeneratedValue();
            $appTable->zQuery("INSERT INTO lbt_data SET form_id = ?,field_id = ?,field_value = ?", array($trans_id, 'body', $value['body']));
        }
    }

    /**
     * @param $pid
     * @param $doc_id
     * @return void
     */
    public function InsertReconcilation($pid, $doc_id)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT encounter FROM documents d inner join form_encounter e on ( e.pid = d.foreign_id and e.date = d.docdate ) where d.id = ? and pid = ? and d.deleted = 0";
        $docEnc = $appTable->zQuery($query, array($doc_id, $pid));

        if ($docEnc->count() == 0) {
            $enc = $appTable->zQuery("SELECT encounter
                                      FROM form_encounter
                                      WHERE pid=?
                                      ORDER BY id DESC LIMIT 1", array($pid));
            $enc_cur = $enc->current();
            $enc_id = $enc_cur['encounter'] ? $enc_cur['encounter'] : 0;
        } else {
            foreach ($docEnc as $d_enc) {
                $enc_id = $d_enc['encounter'];
            }
        }

        $med_rec = $appTable->zQuery("select * from amc_misc_data where pid = ? and amc_id = 'med_reconc_amc' and map_category = 'form_encounter' and map_id = ?", array($pid, $enc_id));
        if ($med_rec->count() == 0) {
            $appTable->zQuery("INSERT INTO amc_misc_data (amc_id,pid,map_category,map_id,date_created,date_completed,soc_provided) values('med_reconc_amc',?,'form_encounter',?,NOW(),NOW(),NOW())", array($pid, $enc_id));
        } else {
            $appTable->zQuery("UPDATE amc_misc_data set date_completed = NOW() where pid = ? and amc_id = 'med_reconc_amc' and map_category ='form_encounter' and map_id = ?", array($pid, $enc_id));
        }
    }

    /**
     * @param                       $vitals_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertVitals($vitals_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($vitals_array)) {
            return;
        }
        $appTable = new ApplicationTable();
        foreach ($vitals_array as $key => $value) {
            if (!empty($value['date']) && $revapprove == 0) {
                $vitals_date_value = $value['date'] ? date("Y-m-d H:i:s", $this->str_to_time($value['date'])) : null;
            } elseif (!empty($value['date']) && $revapprove == 1) {
                $vitals_date_value = ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['date'] == 0) {
                $vitals_date = $value['date'];
                $vitals_date_value = fixDate($vitals_date);
            }

            if (!empty($value['extension'])) {
                $q_sel_vitals = "SELECT *
                           FROM form_vitals
                           WHERE external_id=?";
                $res_q_sel_vitals = $appTable->zQuery($q_sel_vitals, array($value['extension']));
            }
            if (empty($value['extension']) || $res_q_sel_vitals->count() == 0) {
                // TODO: @adunsulag we should move this into the vitals service.
                $query_insert = "INSERT INTO form_vitals
                         (
                          pid,
                          date,
                          bps,
                          bpd,
                          height,
                          weight,
                          temperature,
                          pulse,
                          respiration,
                          head_circ,
                          oxygen_saturation,
                          BMI,
                          activity,
                          external_id
                         )
                         VALUES
                         (
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          1,
                          ?
                         )";
                $res = $appTable->zQuery(
                    $query_insert,
                    array(
                        $pid,
                        $vitals_date_value,
                        $value['bps'],
                        $value['bpd'],
                        $value['height'],
                        $value['weight'],
                        $value['temperature'],
                        $value['pulse'],
                        $value['respiration'],
                        $value['head_circ'],
                        $value['oxygen_saturation'],
                        $value['BMI'],
                        $value['extension'])
                );
                $vitals_id = $res->getGeneratedValue();
            } else {
                $q_upd_vitals = "UPDATE form_vitals
                         SET pid=?,
                             date=?,
                             bps=?,
                             bpd=?,
                             height=?,
                             weight=?,
                             temperature=?,
                             pulse=?,
                             respiration=?,
                             head_circ=?,
                             oxygen_saturation=?,
                             BMI=?
                         WHERE external_id=?";
                $appTable->zQuery($q_upd_vitals, array($pid,
                    $vitals_date_value,
                    $value['bps'],
                    $value['bpd'],
                    $value['height'],
                    $value['weight'],
                    $value['temperature'],
                    $value['pulse'],
                    $value['respiration'],
                    $value['head_circ'],
                    $value['oxygen_saturation'],
                    $value['BMI'],
                    $value['extension']));
                foreach ($res_q_sel_vitals as $row_vitals) {
                    $vitals_id = $row_vitals['id'];
                }
            }

            $query_sel = "SELECT date FROM form_vitals WHERE id=?";
            $res_query_sel = $appTable->zQuery($query_sel, array($vitals_id));
            $res_cur = $res_query_sel->current();
            $vitals_date_forms = $res_cur['date'];

            $query_sel_enc = "SELECT encounter
                            FROM form_encounter
                            WHERE date=? AND pid=?";
            $res_query_sel_enc = $appTable->zQuery($query_sel_enc, array($vitals_date_forms, $pid));

            if ($res_query_sel_enc->count() == 0) {
                $res_enc = $appTable->zQuery("SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1", array($pid));
                if ($res_enc->count() == 0) {
                    // need to create a form_encounter for the patient to hold the vitals since the patient does not have any encounters
                    $data[0]['date'] = $value['date'];
                    $this->InsertEncounter($data, $pid, $carecoordinationTable, 0);
                }
                $res_enc = $appTable->zQuery("SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1", array($pid));
                $res_enc_cur = $res_enc->current();
                $encounter_for_forms = $res_enc_cur['encounter'] ?? null;
            } else {
                foreach ($res_query_sel_enc as $value2) {
                    $encounter_for_forms = $value2['encounter'];
                }
            }

            if (!empty($value['reason_code'] ?? null)) {
                $detail_query = "INSERT INTO `form_vital_details` (`form_id`, `vitals_column`, `reason_code`, `reason_description`, `reason_status`) VALUES (?,?,?,?,?)";
                $appTable->zQuery($detail_query, array(
                    $vitals_id,
                    $value['vital_column'] ?? '',
                    $value['reason_code'] ?? '',
                    $value['reason_code_text'] ?? null,
                    $value['reason_status'] ?? null
                ));
            }
            $query = "INSERT INTO forms
                (
                  date,
                  encounter,
                  form_name,
                  form_id,
                  pid,
                  user,
                  formdir
                )
                VALUES
                (
                  ?,
                  ?,
                  'Vitals',
                  ?,
                  ?,
                  ?,
                  'vitals'
                )";
            $appTable->zQuery($query, array($vitals_date_forms,
                $encounter_for_forms,
                $vitals_id,
                $pid,
                ($_SESSION['authUser'] ?? null)));
        }
    }

    /**
     * @param                       $observation_preformed_array
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertObservationPerformed($observation_preformed_array, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($observation_preformed_array)) {
            return;
        }
        $newid = '';
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery('SELECT MAX(id) as largestId FROM `form_observation`');
        foreach ($res as $val) {
            if ($val['largestId']) {
                $newid = $val['largestId'] + 1;
            } else {
                $newid = 1;
            }
        }

        foreach ($observation_preformed_array as $key => $value) {
            $date_end = null;
            if (!empty($value['date'])) {
                $enc_date = date("Y-m-d", $this->str_to_time($value['date']));
                $date = date("Y-m-d H:i:s", $this->str_to_time($value['date']));
                $date_end = $value['date_end'] ? date("Y-m-d H:i:s", $this->str_to_time($value['date_end'])) : null;
            } else {
                $date = date('Y-m-d');
            }

            $query_sel_enc = 'SELECT encounter
                            FROM form_encounter
                            WHERE date LIKE ? AND pid=?';
            $res_query_sel_enc = $appTable->zQuery($query_sel_enc, array('%' . $enc_date . '%', $pid));

            if ($res_query_sel_enc->count() == 0) {
                $res_enc = $appTable->zQuery('SELECT encounter
                                                 FROM form_encounter
                                                 WHERE pid=?
                                                 ORDER BY id DESC
                                                 LIMIT 1', array($pid));
                $res_enc_cur = $res_enc->current();
                $encounter_for_forms = $res_enc_cur['encounter'];
            } else {
                foreach ($res_query_sel_enc as $value2) {
                    $encounter_for_forms = $value2['encounter'];
                }
            }

            $res = $appTable->zQuery(
                'INSERT INTO form_observation(
                id,date,pid,groupname,user,encounter, activity,
                             code,
                             observation,
                             ob_value,
                             ob_unit,
                             description,
                             ob_status,
                             ob_code,
                             ob_type,
                             ob_reason_status,
                             ob_reason_code,
                             ob_reason_text,
                             date_end)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array(
                    $newid, $date, $pid, $_SESSION['authProvider'], $_SESSION['authUser'], $encounter_for_forms, 1,
                    $value['code'] ?: null,
                    $value['observation'],
                    $value['result_code_text'],
                    $value['result_code_unit'] ?? '',
                    $value['code_text'],
                    $value['observation_status'],
                    $value['result_code'],
                    $value['observation_type'],
                    $value['reason_status'],
                    $value['reason_code'],
                    $value['reason_code_text'],
                    $date_end
                )
            );
            // insert form for observation
            if (($observation_preformed_array[$key - 1]['date'] ?? null) == $value['date']) {
                continue;
            }
            if (count($observation_preformed_array) > 0) {
                $query = 'INSERT INTO forms(date,encounter,form_name,form_id,pid,user,groupname,formdir) VALUES(?,?,?,?,?,?,?,?)';
                $appTable->zQuery($query, array($date, $encounter_for_forms, 'Observation Form', $newid, $pid, $_SESSION['authUser'], $_SESSION['authProvider'], 'observation'));
            }
        }
    }

    /**
     * @param                       $payer
     * @param                       $pid
     * @param CarecoordinationTable $carecoordinationTable
     * @param                       $revapprove
     * @return void
     */
    public function InsertPayers($payer, $pid, CarecoordinationTable $carecoordinationTable, $revapprove = 1)
    {
        if (empty($payer)) {
            return;
        }
        $data = [];
        $payer = $payer[1]; // will only be one payer per patient.
        $insuranceData = new InsuranceService();

        $appTable = new ApplicationTable();
        $res_ins = $appTable->zQuery("SELECT `id`, `uuid` FROM `insurance_companies` WHERE `cqm_sop` = ? AND `inactive` = 0 ORDER BY `id` DESC LIMIT 1", array($payer['code'] ?? '1'));
        $res_ins_cur = $res_ins->current();
        if (empty($res_ins_cur['id'])) {
            $data["name"] = 'QRDA Insurance Company Payer ' . $payer['code'] ?? '1';
            $data["ins_type_code"] = $payer['code'] ?? '1';
            $data["city"] = 'QRDA City';
            $data["state"] = 'QRDA State';
            $data["zip"] = '33333';
            $data["cqm_sop"] = $payer['code'] ?? 1;
            $insuranceCompany = new InsuranceCompanyService();
            $ins_id = $insuranceCompany->insert($data);
        } else {
            $ins_id = $res_ins_cur['id'];
        }
        unset($data["name"]);
        unset($data["ins_type_code"]);
        $data["provider"] = $ins_id;
        $data["date"] = $payer['low_date'] ?? null;
        $data["type"] = 'primary';
        $data["plan_name"] = 'QRDA Payer';
        $data["policy_number"] = $payer['code'] ?? '1';

        $id_data = $insuranceData->insert($pid, 'primary', $data);
    }
}
