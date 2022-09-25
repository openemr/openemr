<?php

/**
 * Qrda and Cda ParseService Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use DOMDocument;
use OpenEMR\Services\CodeTypesService;

class CdaTemplateParse
{
    private $templateData;
    private $codeService;
    private $currentOid;
    protected $is_qrda_import;

    public function __construct()
    {
        $this->templateData = [];
        $this->is_qrda_import = false;
        $this->codeService = new CodeTypesService();
    }

    public function parseCDAEntryComponents($components): array
    {

        $components_oids = array(
            '2.16.840.1.113883.10.20.22.4.7' => 'allergy',
            '2.16.840.1.113883.10.20.22.2.6.1' => 'allergy',
            '2.16.840.1.113883.10.20.22.2.1' => 'medication',
            '2.16.840.1.113883.10.20.22.2.1.1' => 'medication',
            '2.16.840.1.113883.10.20.22.2.5.1' => 'medical_problem',
            '2.16.840.1.113883.10.20.22.2.5' => 'medical_problem',
            '2.16.840.1.113883.10.20.22.2.2' => 'immunization',
            '2.16.840.1.113883.10.20.22.2.2.1' => 'immunization',
            '2.16.840.1.113883.3.88.11.83.145' => 'procedure',
            '2.16.840.1.113883.10.20.22.2.7.1' => 'procedure',
            '2.16.840.1.113883.10.20.22.2.3.1' => 'labResult',
            '2.16.840.1.113883.10.20.22.2.3' => 'labResult',
            '2.16.840.1.113883.10.20.22.2.4.1' => 'VitalSign',
            '2.16.840.1.113883.10.20.22.2.17' => 'socialHistory',
            '2.16.840.1.113883.3.88.11.83.127' => 'encounter',
            '2.16.840.1.113883.10.20.22.2.22.1' => 'encounter',
            '2.16.840.1.113883.10.20.22.2.10' => 'carePlan',
            '2.16.840.1.113883.10.20.22.2.14' => 'functionalCognitiveStatus',
            '1.3.6.1.4.1.19376.1.5.3.1.3.1' => 'referral',
            '2.16.840.1.113883.10.20.22.2.11.1' => 'dischargeMedications',
            '2.16.840.1.113883.10.20.22.2.41' => 'dischargeSummary'
        );

        foreach ($components as $component) {
            if (!empty($component['section']['templateId']['root'])) {
                if (!empty($components_oids[$component['section']['templateId']['root']])) {
                    $this->currentOid = '';
                    $func_name = $components_oids[$component['section']['templateId']['root']];
                    $this->$func_name($component);
                }
            } elseif (empty($component['section']['templateId'])) {
                // uncomment for debugging information.
                error_log("section and template id empty for " . var_export($component, true));
            } elseif (count($component['section']['templateId']) > 1) {
                $this->currentOid = '';
                foreach ($component['section']['templateId'] as $key_1 => $value_1) {
                    if (!empty($components_oids[$component['section']['templateId'][$key_1]['root']])) {
                        $func_name = $components_oids[$component['section']['templateId'][$key_1]['root']];
                        $this->$func_name($component);
                        break;
                    }
                }
            }
        }
        return $this->templateData;
    }

    /**
     * parsePatientDataSection
     *
     * @param $entryComponents //An array of QRDA entry templates
     * @return array
     */
    public function parseQRDAPatientDataSection($entryComponents): array
    {
        $this->is_qrda_import = true;
        $qrda_oids = array(
            '2.16.840.1.113883.10.20.24.3.147' => 'fetchAllergyIntoleranceObservation',
            '2.16.840.1.113883.10.20.24.3.41' => 'fetchMedicationData',  // active medication @todo verify status all meds
            '2.16.840.1.113883.10.20.24.3.42' => 'fetchMedicationData',  // Medication Administered Act @todo honor end dates
            '2.16.840.1.113883.10.20.24.3.139' => 'fetchMedicationData', // Medication Dispensed Act @todo set med type
            '2.16.840.1.113883.10.20.24.3.105' => 'fetchMedicationData', // Medication Discharge Act
            '2.16.840.1.113883.10.20.24.3.137' => 'fetchMedicalProblemData',// diagnosis
            '2.16.840.1.113883.10.20.24.3.138' => 'fetchMedicalProblemData',// concern symtom
            '2.16.840.1.113883.10.20.24.3.140' => 'fetchImmunizationData',  // Immunization Administered (V3)
            '2.16.840.1.113883.10.20.22.4.14' => 'fetchProcedureActivityData', // procedure activity-performed 2.16.840.1.113883.10.20.24.3.64
            '2.16.840.1.113883.10.20.24.3.7' => 'fetchProcedureDeviceData', // procedure preformed Device Applied
            '2.16.840.1.113883.10.20.24.3.32' => 'fetchProcedurePreformedActivity',// procedure activity-intervention
            '2.16.840.1.113883.10.20.24.3.38' => 'fetchQrdaLabResultData',  // lab test preformed
            '2.16.840.1.113883.10.20.24.3.133' => 'fetchEncounterPerformed',
            '2.16.840.1.113883.10.20.24.3.143' => 'fetchCarePlanData',  // Immunization Order Substance Order @todo this is planned or goal MOVE
            '2.16.840.1.113883.10.20.24.3.47' => 'fetchCarePlanData', // Plan of Care Medication Substance Observation Activity Ordered
            '2.16.840.1.113883.10.20.24.3.130' => 'fetchCarePlanData', // Plan of Care Activity Supply CDA 2.16.840.1.113883.10.20.24.3.43
            '2.16.840.1.113883.10.20.24.3.31' => 'fetchCarePlanData', // Plan of Care Activity (act) Intervention Order
            '2.16.840.1.113883.10.20.24.3.37' => 'fetchCarePlanData', // Plan of Care Activity Observation Lab order
            '2.16.840.1.113883.10.20.24.3.17' => 'fetchCarePlanData', // Plan of Care Activity Observation Diagnostic Study, Order
            /**
             * CCDA 2.16.840.1.113883.10.20.22.4.13 - Procedure Activity Observation.
             * QRDA 2.16.840.1.113883.10.20.24.3.18 Diagnostic Study, Performed,
             * QRDA 2.16.840.1.113883.10.20.24.3.59 Physical Exam, Performed
             * QRDA '2.16.840.1.113883.10.20.24.3.54' Deceased Observation (V3) is handled in patient data parse.
             * */
            '2.16.840.1.113883.10.20.24.3.59' => 'fetchPhysicalExamPerformedData', // Physical Exam, Performed observation Vitals
            '2.16.840.1.113883.10.20.24.3.18' => 'fetchObservationPerformedData', //
            '2.16.840.1.113883.10.20.24.3.144' => 'fetchObservationPerformedData', // Assessment Performed
            '2.16.840.1.113883.10.20.24.3.54' => 'fetchDeceasedObservationData', // Deceased Observation (V3)
            '2.16.840.1.113883.10.20.24.3.55' => 'fetchPaymentSourceData', // Patient Characteristic Payer
        );
        foreach ($entryComponents['section']['entry'] as $entry) {
            $key = array_keys($entry)[0]; // need the entry template type i.e. observation, activity, substance etc.
            if (!empty($entry[$key]['templateId']['root'])) {
                if (!empty($qrda_oids[$entry[$key]['templateId']['root']])) {
                    $this->currentOid = $entry[$key]['templateId']['root'];
                    $func_name = $qrda_oids[$entry[$key]['templateId']['root']] ?? null;
                    if (!empty($func_name)) {
                        $this->$func_name($entry);
                    }
                } else {
                    $text = $entry[$key]['templateId']['root'] . ' ' . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                    error_log('Root Missing QDM: ' . $text);
                }
            } elseif (count($entry[$key]['templateId'] ?? []) > 1) {
                $key_1 = 1;
                if (!empty($qrda_oids[$entry[$key]['templateId'][$key_1]['root']])) {
                    $this->currentOid = $entry[$key]['templateId'][$key_1]['root'];
                    $func_name = $qrda_oids[$entry[$key]['templateId'][$key_1]['root']] ?? null;
                    if (!empty($func_name)) {
                        $this->$func_name($entry);
                    } else {
                        $text = $entry[$key]['templateId'][$key_1]['root'] . " Key: $key_1 " . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                        error_log('Missing Function for: ' . $text);
                    }
                } elseif (count($entry[$key]['templateId'] ?? []) > 1) {
                    $key_1 = 0;
                    if (!empty($qrda_oids[$entry[$key]['templateId'][$key_1]['root']])) {
                        $this->currentOid = $entry[$key]['templateId'][$key_1]['root'];
                        $func_name = $qrda_oids[$entry[$key]['templateId'][$key_1]['root']] ?? null;
                        if (!empty($func_name)) {
                            $this->$func_name($entry);
                        } else {
                            $text = $entry[$key]['templateId'][$key_1]['root'] . " Key: $key_1 " . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                            error_log('Missing  Function for: ' . $text);
                        }
                    } else {
                        $text = $entry[$key]['templateId'][$key_1]['root'] . " Key: $key_1 " . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                        error_log('Missing QDM: ' . $text);
                    }
                } else {
                    $text = $entry[$key]['templateId'][$key_1]['root'] . ' ' . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                    error_log('Missing QDM: ' . $text);
                }
            } elseif (!empty($entry[$key]['root'])) {
                if (!empty($qrda_oids[$entry[$key]['root']])) {
                    $this->currentOid = $entry[$key]['root'];
                    $func_name = $qrda_oids[$entry[$key]['root']] ?? null;
                    if (!empty($func_name)) {
                        $this->$func_name($entry);
                    }
                } else {
                    $text = $entry[$key]['root'] . ' ' . ($entry[$key]['text'] ?: $entry[$key]['code']['displayName']);
                    error_log('Root Missing QDM: ' . $text);
                }
            }
        }
        if (empty($this->templateData)) {
            error_log('Could not find any QDMs in document!');
        }
        return $this->templateData ?? [];
    }

    public function fetchDeceasedObservationData($entry)
    {
        // handled in patient data parse.
        // leave this function to prevent parse errors.
    }

    public function fetchPaymentSourceData($entry)
    {
        if (!empty($entry['observation']['effectiveTime']['low']['value'] ?? null)) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['payer'])) {
                $i += count($this->templateData['field_name_value_array']['payer']);
            }
            $this->templateData['field_name_value_array']['payer'][$i]['status'] = $entry['observation']['statusCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['payer'][$i]['code'] = $entry['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['payer'][$i]['low_date'] = $entry['observation']['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['payer'][$i]['high_date'] = $entry['observation']['effectiveTime']['high']['value'] ?? null;

            $this->templateData['entry_identification_array']['payer'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchObservationPerformedData($entry): void
    {
        // was also called from fetchPhysicalExamPerformedData()
        if (
            !empty($entry['observation']['value']['code'] ?? null)
            || !empty($entry['observation']['code']['code'] ?? null)
            || (!empty($entry['observation']['value']['nullFlavor'] ?? null) && empty($entry['observation']['value']['code'] ?? null))
            || (!empty($entry['observation']['entryRelationship']['observation']['value']['code'] ?? null) && ($entry['observation']['entryRelationship']['typeCode'] ?? null) === 'RSON')
        ) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['observation_preformed'])) {
                $i += count($this->templateData['field_name_value_array']['observation_preformed']);
            }
            $is_negated = !empty($entry['observation']['negationInd'] ?? false);
            $ob_type = 'assessment'; // default and 2.16.840.1.113883.10.20.24.3.144
            if ($this->currentOid === '2.16.840.1.113883.10.20.24.3.18') {
                $ob_type = 'procedure_diagnostic';
            } elseif ($this->currentOid === '2.16.840.1.113883.10.20.24.3.59') {
                $ob_type = 'physical_exam_performed';
            }

            $ob_code = $entry['observation']['code']['code'];
            $ob_system = $entry['observation']['code']['codeSystemName'] ?: ($entry['observation']['code']['codeSystem'] ?? '');
            $ob_code_text = $entry['observation']['code']['displayName'] ?? '';
            if (($entry['observation']['code']['codeSystemName'] ?? null) == 'CPT4' && !empty($entry['observation']['code']['translation']['code'])) {
                $ob_code = $entry['observation']['code']['translation']['code'];
                $ob_system = $entry['observation']['code']['translation']['codeSystemName'] ?: $entry['observation']['value']['codeSystem'] ?? '';
                $ob_code_text = $entry['observation']['code']['translation']['displayName'] ?? '';
            }
            $code = $this->codeService->resolveCode($ob_code, $ob_system, $ob_code_text);

            $result_code = [];
            $result_status = $entry['observation']['statusCode']['code'] ?? '';
            $reason_code = [];
            $reason_status = '';
            if (!empty($entry['observation']['value']['nullFlavor'])) {
                $result_code['formatted_code'] = null;
                $result_code['code_text'] = null;
                $result_status = $entry['observation']['statusCode']['code'] ?? '';
            } elseif (($entry['observation']['value']['type'] ?? '') === 'CD') {
                $ob_code = $entry['observation']['value']['code'];
                $ob_system = $entry['observation']['value']['codeSystemName'] ?: $entry['observation']['value']['codeSystem'] ?? '';
                $ob_code_text = $entry['observation']['value']['displayName'] ?? '';
                $result_code = $this->codeService->resolveCode($ob_code, $ob_system, $ob_code_text);
                $result_status = $entry['observation']['statusCode']['code'] ?? '';
            } elseif (($entry['observation']['value']['type'] ?? '') === 'PQ') {
                $result_code['formatted_code'] = 'PQ';
                $result_code['code_text'] = $entry['observation']['value']['value'] ?? '';
                $result_unit = $entry['observation']['value']['unit'] ?? '';
                $result_status = $entry['observation']['statusCode']['code'] ?? '';
            }
            if (!empty($entry['observation']['entryRelationship']['observation']['value']['code'] ?? null)) {
                // @todo inter to this moodcode RSON in full template!
                $ob_code = $entry['observation']['entryRelationship']['observation']['value']['code'];
                $ob_system = $entry['observation']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['observation']['entryRelationship']['observation']['value']['codeSystem'] ?? '';
                $ob_code_text = $entry['observation']['entryRelationship']['observation']['value']['displayName'] ?? '';
                $reason_code = $this->codeService->resolveCode($ob_code, $ob_system, $ob_code_text);
                $reason_status = $entry['observation']['entryRelationship']['observation']['statusCode']['code'] ?? '';
            }

            $this->templateData['field_name_value_array']['observation_preformed'][$i]['observation_type'] = $ob_type;
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['extension'] = $entry['observation']['id']['extension'] ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['root'] = $entry['observation']['id']['root'] ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['date'] = $entry['observation']['effectiveTime']['value'] ?? '';
            if (!empty($entry['observation']['effectiveTime']['low'])) {
                $this->templateData['field_name_value_array']['observation_preformed'][$i]['date'] = $entry['observation']['effectiveTime']['low'];
                $this->templateData['field_name_value_array']['observation_preformed'][$i]['date_end'] = $entry['observation']['effectiveTime']['high'] ?? null;
            }

            if (!empty($entry['observation']['code']["nullFlavor"]) && !empty($entry['observation']['code']["valueSet"])) {
                $code['code'] = $entry['observation']['code']["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $entry['observation']['code']["valueSet"] ?? null;
                $code['code_text'] = $entry['observation']['text'] ?? '';
            }
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['observation_status'] = $result_status ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['observation'] = $entry['observation']['text'] ?: $code['code_text'] ?? null;
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['code'] = $code['formatted_code'] ?? null;
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['code_text'] = $code['code_text'] ?? '';

            $this->templateData['field_name_value_array']['observation_preformed'][$i]['result_status'] = $result_status ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['result_code'] = $result_code['formatted_code'] ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['result_code_text'] = $result_code['code_text'] ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['result_code_unit'] = $result_unit ?? '';

            $this->templateData['field_name_value_array']['observation_preformed'][$i]['reason_status'] = $is_negated ? 'negated' : ($reason_status ?? '');
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['reason_code'] = $reason_code['formatted_code'] ?? '';
            $this->templateData['field_name_value_array']['observation_preformed'][$i]['reason_code_text'] = $reason_code['code_text'] ?? '';

            $this->templateData['entry_identification_array']['observation_preformed'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchEncounterPerformed($entry): void
    {
        if ($this->is_qrda_import) {
            $entry = $entry['act']['entryRelationship'];
        }
        if (!empty($entry['encounter']['effectiveTime']['value']) || !empty($entry['encounter']['effectiveTime']['low']['value'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['encounter'])) {
                $i += count($this->templateData['field_name_value_array']['encounter']);
            }

            $this->templateData['field_name_value_array']['encounter'][$i]['extension'] = $entry['encounter']['id']['extension'];
            $this->templateData['field_name_value_array']['encounter'][$i]['root'] = $entry['encounter']['id']['root'];
            $this->templateData['field_name_value_array']['encounter'][$i]['date'] = ($entry['encounter']['effectiveTime']['value'] ?? null) ?: $entry['encounter']['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['encounter'][$i]['date_end'] = $entry['encounter']['effectiveTime']['high']['value'] ?? null;

            $code_type = $entry['encounter']['code']['codeSystemName'] ?: $entry['encounter']['code']['codeSystem'] ?? '';
            $code_text = $entry['encounter']['code']['displayName'] ?? '';
            $code = $this->codeService->resolveCode($entry['encounter']['code']['code'], $code_type, $code_text);
            $this->templateData['field_name_value_array']['encounter'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['encounter'][$i]['code_text'] = $code['code_text'];

            $this->templateData['field_name_value_array']['encounter'][$i]['provider_npi'] = $entry['encounter']['performer']['assignedEntity']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_name'] = $entry['encounter']['performer']['assignedEntity']['assignedPerson']['name']['given'] ?? ''; // first
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_family'] = $entry['encounter']['performer']['assignedEntity']['assignedPerson']['name']['family'] ?? ''; // last
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_address'] = $entry['encounter']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_city'] = $entry['encounter']['performer']['assignedEntity']['addr']['city'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_state'] = $entry['encounter']['performer']['assignedEntity']['addr']['state'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_postalCode'] = $entry['encounter']['performer']['assignedEntity']['addr']['postalCode'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_country'] = $entry['encounter']['performer']['assignedEntity']['addr']['country'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_name'] = $entry['encounter']['participant']['participantRole']['playingEntity']['name'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_address'] = $entry['encounter']['participant']['participantRole']['addr']['streetAddressLine'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_city'] = $entry['encounter']['participant']['participantRole']['addr']['city'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_state'] = $entry['encounter']['participant']['participantRole']['addr']['state'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_zip'] = $entry['encounter']['participant']['participantRole']['addr']['postalCode'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_country'] = $entry['encounter']['participant']['participantRole']['addr']['country'] ?? '';
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_telecom'] = $entry['encounter']['participant']['participantRole']['telecom'] ?? '';
            // encounter diagnosis to issues list
            $code = $this->codeService->resolveCode(
                $entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['value']['code'] ?? '',
                ($entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['value']['codeSystemName'] ?? '') ?: $entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                $entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['value']['displayName'] ?? ''
            );
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? $this->templateData['field_name_value_array']['encounter'][$i]['date'] ?? null;
            if (empty($code['code'])) {
                $code = $this->codeService->resolveCode(
                    $entry["encounter"]["entryRelationship"]["observation"]["value"]["code"] ?? '',
                    ($entry["encounter"]["entryRelationship"]["observation"]["value"]['codeSystemName'] ?? '') ?: $entry["encounter"]["entryRelationship"]["observation"]["value"]['codeSystem'] ?? '',
                    $entry["encounter"]["entryRelationship"]["observation"]["value"]['displayName'] ?? ''
                );
                $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $this->templateData['field_name_value_array']['encounter'][$i]['date'] ?? null;
            }
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_issue'] = $code['code_text'];

            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $entry['encounter']['entryRelationship']['act']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? null;

            $discharge = $entry['encounter']['sdtc:dischargeDispositionCode'] ?? null;
            $code = '';
            if (!empty($discharge)) {
                $code = $this->codeService->getCodeWithType(($discharge['code'] ?? ''), ($discharge['codeSystemName'] ?? ''), true) ?? '';
                $option = $this->codeService->dischargeOptionIdFromCode($code) ?? '';
                if (empty($option)) {
                    $code = str_replace(" ", "-", $code); // Because "SNOMED CT" is "SNOMED-CT" in list options
                    $option = $this->codeService->dischargeOptionIdFromCode($code) ?? '';
                }
            }
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_discharge_code'] = $option ?? '';

            $this->templateData['entry_identification_array']['encounter'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchMedicalProblemData($entry): void
    {
        if (!empty($entry['act']['entryRelationship']['observation']['value']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['lists1'])) {
                $i += count($this->templateData['field_name_value_array']['lists1']);
            }
            $classification = 'diagnosis';
            if ($this->currentOid == '2.16.840.1.113883.10.20.24.3.138') {
                $classification = 'concern';
            }
            $this->templateData['field_name_value_array']['lists1'][$i]['subtype'] = $classification;
            $code = $this->codeService->resolveCode(
                $entry['act']['entryRelationship']['observation']['value']['code'],
                ($entry['act']['entryRelationship']['observation']['value']['codeSystemName'] ?? '') ?: $entry['act']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                $entry['act']['entryRelationship']['observation']['value']['displayName']
            );
            $this->templateData['field_name_value_array']['lists1'][$i]['list_code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['lists1'][$i]['list_code_text'] = $code['code_text'];

            $this->templateData['field_name_value_array']['lists1'][$i]['type'] = 'medical_problem';
            $this->templateData['field_name_value_array']['lists1'][$i]['extension'] = $entry['act']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['root'] = $entry['act']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['begdate'] = $entry['act']['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['enddate'] = $entry['act']['effectiveTime']['high']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['observation'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['observation_text'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['status'] = ($entry['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['displayName'] ?? '') ?: $entry['act']['entryRelationship']['observation']['statusCode'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['modified_time'] = $entry['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->templateData['entry_identification_array']['lists1'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchAllergyIntoleranceObservation($entry)
    {
        if (!empty($entry['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['code'])) {
            $i = 1;
            // if there are already items here we want to add to them.
            if (!empty($this->ccda_data_array['field_name_value_array']['lists2'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['lists2']);
            }

            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['type'] = 'allergy';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['extension'] = $entry['act']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['begdate'] = $entry['act']['effectiveTime']['low']['value'] ?? null;
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['enddate'] = $entry['act']['effectiveTime']['high']['value'] ?? null;
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['list_code'] = $entry['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['code'] ?? '';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['list_code_text'] = $entry['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['codeSystemName'] = $entry['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['codeSystemName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['outcome'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'] ?? '';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['severity_al_code'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['code'] ?? '';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['severity_al'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['code'] ?? '';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['status'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][0]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['reaction'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'] ?? '';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['reaction_text'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['modified_time'] = $entry['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->ccda_data_array['entry_identification_array']['lists2'][$i] = $i;
        } elseif (!empty($entry['observation']['participant']['participantRole']['playingEntity']['code']['code'])) {
            $i = 1;
            // if there are already items here we want to add to them.
            if (!empty($this->templateData['field_name_value_array']['lists2'])) {
                $i += count($this->templateData['field_name_value_array']['lists2']);
            }

            $this->templateData['field_name_value_array']['lists2'][$i]['type'] = 'allergy';
            $this->templateData['field_name_value_array']['lists2'][$i]['extension'] = $entry['observation']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['begdate'] = $entry['observation']['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['enddate'] = $entry['observation']['effectiveTime']['high']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['status'] = $entry['observation']['statusCode']['code'] ?? null;

            $code = $this->codeService->resolveCode(
                $entry['observation']['participant']['participantRole']['playingEntity']['code']['code'] ?? null,
                $entry['observation']['participant']['participantRole']['playingEntity']['code']['codeSystemName'] ?? null,
                $entry['observation']['participant']['participantRole']['playingEntity']['code']['displayName'] ?: $entry['observation']['participant']['participantRole']['playingEntity']['name'] ?? null
            );
            $this->templateData['field_name_value_array']['lists2'][$i]['list_code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['lists2'][$i]['list_code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['lists2'][$i]['codeSystemName'] = $code['formatted_code_type'];

            $this->templateData['field_name_value_array']['lists2'][$i]['outcome'] = $entry['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['severity_al_code'] = $entry['observation']['entryRelationship'][2]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['severity_al'] = $entry['observation']['entryRelationship'][2]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['status'] = $entry['observation']['entryRelationship'][0]['observation']['value']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['reaction'] = $entry['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['reaction_text'] = $entry['observation']['entryRelationship'][1]['observation']['value']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['modified_time'] = $entry['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->templateData['entry_identification_array']['lists2'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchMedicationData($entry): void
    {
        if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['lists3'])) {
                $i += count($this->templateData['field_name_value_array']['lists3']);
            }

            $substanceAdministration_oids = array(
                '2.16.840.1.113883.10.20.24.3.41' => 'active',
                '2.16.840.1.113883.10.20.24.3.42' => 'administered',
                '2.16.840.1.113883.10.20.24.3.139' => 'dispensed',
                '2.16.840.1.113883.10.20.24.3.105' => 'discharge',
            );
            $request_type = '';
            if ($this->is_qrda_import) {
                if (!empty($entry['substanceAdministration']['templateId']['root'])) {
                    $request_type = $substanceAdministration_oids[$entry['substanceAdministration']['templateId']['root']];
                } elseif (!empty($entry['substanceAdministration']['templateId'][1]['root'])) {
                    $request_type = $substanceAdministration_oids[$entry['substanceAdministration']['templateId'][1]['root']];
                }
            }

            $code = $this->codeService->resolveCode(
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'],
                'RXNORM',
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName']
            );

            $this->templateData['field_name_value_array']['lists3'][$i]['type'] = 'medication';
            $this->templateData['field_name_value_array']['lists3'][$i]['request_type'] = $request_type;
            $this->templateData['field_name_value_array']['lists3'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;

            $this->templateData['field_name_value_array']['lists3'][$i]['begdate'] = date('Y-m-d');
            if (!empty($entry['substanceAdministration']['effectiveTime'][0]['low']['value'])) {
                $this->templateData['field_name_value_array']['lists3'][$i]['begdate'] = $entry['substanceAdministration']['effectiveTime'][0]['low']['value'];
                $this->templateData['field_name_value_array']['lists3'][$i]['enddate'] = $entry['substanceAdministration']['effectiveTime'][0]['high']['value'] ?? null;
            } elseif (!empty($entry['substanceAdministration']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['lists3'][$i]['begdate'] = $entry['substanceAdministration']['effectiveTime']['low']['value'];
                $this->templateData['field_name_value_array']['lists3'][$i]['enddate'] = $entry['substanceAdministration']['effectiveTime']['high']['value'] ?? null;
            }

            $this->templateData['field_name_value_array']['lists3'][$i]['route'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['route_display'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['dose'] = $entry['substanceAdministration']['doseQuantity']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['dose_unit'] = $entry['substanceAdministration']['doseQuantity']['unit'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['rate'] = $entry['substanceAdministration']['rateQuantity']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['rate_unit'] = $entry['substanceAdministration']['rateQuantity']['unit'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['drug_code'] = $code['code'];
            $this->templateData['field_name_value_array']['lists3'][$i]['drug_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['lists3'][$i]['note'] = $entry['substanceAdministration']['text']['reference']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['indication'] = $entry['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ?? ($entry['substanceAdministration']['entryRelationship']['observation']['value']['displayName'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['prn'] = $entry['substanceAdministration']['precondition']['criterion']['value']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['modified_time'] = $entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['time']['value'] ?? null;

            $this->templateData['field_name_value_array']['lists3'][$i]['provider_title'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['prefix'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['prefix'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_fname'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['given'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_lname'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['family'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['family'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_root'] = $entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_address'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_city'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['city'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['city'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_state'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['state'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['state'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_postalCode'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['postalCode'] ?? null);
            $this->templateData['field_name_value_array']['lists3'][$i]['provider_country'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['country']['value'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['country'] ?? null);
            $this->templateData['entry_identification_array']['lists3'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchImmunizationData($entry): void
    {
        if (
            !empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])
            || !empty($entry['substanceAdministration']['negationInd']) == "true"
        ) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['immunization'])) {
                $i += count($this->templateData['field_name_value_array']['immunization']);
            }
            $this->templateData['field_name_value_array']['immunization'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['administered_date'] = $entry['substanceAdministration']['effectiveTime']['value'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['route_code'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['route_code_text'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;

            $code = $this->codeService->resolveCode(
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? null,
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['codeSystemName'] ?? null,
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName']
            );
            $code = $this->codeService->resolveCode(
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'],
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['codeSystemName'] ?? $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['codeSystem'],
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName']
            );
            if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']["nullFlavor"])) {
                $code['code'] = 'OID:' . $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $code['code'];
                $code['code_text'] = $entry['substanceAdministration']['text'] ?? '';
            }
            $this->templateData['field_name_value_array']['immunization'][$i]['cvx_code'] = $code['code'];
            $this->templateData['field_name_value_array']['immunization'][$i]['cvx_code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['immunization'][$i]['amount_administered'] = $entry['substanceAdministration']['doseQuantity']['value'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['amount_administered_unit'] = $entry['substanceAdministration']['doseQuantity']['unit'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['completion_status'] = $entry['substanceAdministration']['statusCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['manufacturer'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturerOrganization']['name'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_npi'] = $entry['substanceAdministration']['performer']['assignedEntity']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_name'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_address'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_city'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['city'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_state'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['state'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_postalCode'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_country'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['country'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['provider_telecom'] = $entry['substanceAdministration']['performer']['assignedEntity']['telecom']['value'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['represented_organization'] = $entry['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['name'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['represented_organization_tele'] = $entry['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['telecom'] ?? null;

            if ($entry['substanceAdministration']['entryRelationship']['observation']['value']['code']) {
                $code = $this->codeService->resolveCode(
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['code'],
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['substanceAdministration']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['displayName']
                );
                $this->templateData['field_name_value_array']['immunization'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['immunization'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['immunization'][$i]['reason_description'] = $code['code_text'] ?? $entry['observation']['text'];
                $date_low = $entry['substanceAdministration']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry['substanceAdministration']['entryRelationship']['observation']['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['immunization'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['immunization'][$i]['reason_date_high'] = $date_high;
            }

            $this->templateData['field_name_value_array']['immunization'][$i]['reason_status'] = (($entry['substanceAdministration']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['immunization'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchProcedureActivityData($entry): void
    {
        if (!empty($entry['procedure']['code']['code']) || !empty($entry['procedure']['code']["nullFlavor"])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['procedure'])) {
                $i += count($this->templateData['field_name_value_array']['procedure']);
            }

            $code = $this->codeService->resolveCode(
                $entry['procedure']['code']['code'] ?? '',
                ($entry['procedure']['code']['codeSystemName'] ?? '') ?: $entry['procedure']['code']['codeSystem'] ?? null,
                $entry['procedure']['code']['displayName'] ?? ''
            );
            if (!empty($entry['procedure']['code']["nullFlavor"]) && !empty($entry['procedure']['code']["valueSet"])) {
                $code['code'] = $entry['procedure']['code']["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $entry['procedure']['code']["valueSet"] ?? null;
                $code['code_text'] = $entry['procedure']['text'] ?? '';
            }

            $procedure_type = 'order';

            $this->templateData['field_name_value_array']['procedure'][$i]['extension'] = $entry['procedure']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['root'] = $entry['procedure']['id']['root'] ?? null;

            $this->templateData['field_name_value_array']['procedure'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['procedure'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['procedure'][$i]['codeSystemName'] = $code['formatted_code_type'];

            $this->templateData['field_name_value_array']['procedure'][$i]['procedure_type'] = $procedure_type;
            $this->templateData['field_name_value_array']['procedure'][$i]['status'] = $entry['procedure']['statusCode']['code'] ?? '';

            if (!empty($entry['procedure']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['low']['value'] ?? null;
                $this->templateData['field_name_value_array']['procedure'][$i]['date_end'] = $entry['procedure']['effectiveTime']['high']['value'] ?? null;
            } else {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['value'] ?? null;
            }

            // check for a reason code if observation.
            if (is_array($entry['procedure']['entryRelationship'])) {
                $entryRelationship = $entry['procedure']['entryRelationship'][1];
            } else {
                $entryRelationship = $entry['procedure']['entryRelationship'];
            }
            if ($entryRelationship['observation']['value']['code']) {
                $code = $this->codeService->resolveCode(
                    $entryRelationship['observation']['value']['code'],
                    $entryRelationship['observation']['value']['codeSystemName'] ?: $entryRelationship['observation']['value']['codeSystem'] ?? '',
                    $entryRelationship['observation']['value']['displayName']
                );
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_description'] = $code['code_text'] ?? $entry['observation']['text'];
                $date_low = $entryRelationship['observation']['effectiveTime']['low']['value'] ?? null;
                $date_high = $entryRelationship['observation']['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_date_high'] = $date_high;
            }

            $this->templateData['field_name_value_array']['procedure'][$i]['reason_status'] = (($entry['procedure']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;

            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization1'] = $entry['procedure']['performer']['assignedEntity']['representedOrganization']['name'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_address1'] = $entry['procedure']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_city1'] = $entry['procedure']['performer']['assignedEntity']['addr']['city'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_state1'] = $entry['procedure']['performer']['assignedEntity']['addr']['state'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_postalcode1'] = $entry['procedure']['performer']['assignedEntity']['addr']['postalCode'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_country1'] = $entry['procedure']['performer']['assignedEntity']['addr']['country'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_telecom1'] = $entry['procedure']['performer']['assignedEntity']['telecom']['value'] ?? null;

            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization2'] = $entry['procedure']['participant']['participantRole']['playingEntity']['name'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_address2'] = $entry['procedure']['participant']['participantRole']['addr']['streetAddressLine'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_city2'] = $entry['procedure']['participant']['participantRole']['addr']['city'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_state2'] = $entry['procedure']['participant']['participantRole']['addr']['state'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_postalcode2'] = $entry['procedure']['participant']['participantRole']['addr']['postalCode'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['represented_organization_country2'] = $entry['procedure']['participant']['participantRole']['addr']['country'] ?? null;

            $this->templateData['entry_identification_array']['procedure'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchProcedureDeviceData($entry): void
    {
        if (!empty($entry['procedure']['code']['code']) && (($entry['procedure']['negationInd'] ?? 'false') != 'true')) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['procedure'])) {
                $i += count($this->templateData['field_name_value_array']['procedure']);
            }

            // future may need device status code
            /*$code_proc_type = $this->codeService->resolveCode(
                $entry['procedure']['code']['code'] ?? '',
                $entry['procedure']['code']['codeSystemName'] ?: $entry['procedure']['code']['codeSystem'] ?? null,
                $entry['procedure']['code']['displayName'] ?? ''
            );*/

            $code = $this->codeService->resolveCode(
                $entry['procedure']['participant']['participantRole']['playingDevice']['code']['code'] ?? '',
                $entry['procedure']['participant']['participantRole']['playingDevice']['code']['codeSystem'] ?? null,
                $entry['procedure']['participant']['participantRole']['playingDevice']['code']['displayName'] ?? ''
            );

            $this->templateData['field_name_value_array']['procedure'][$i]['procedure_type'] = 'device';

            $this->templateData['field_name_value_array']['procedure'][$i]['status'] = $entry['procedure']['statusCode']['code'] ?? '';

            $this->templateData['field_name_value_array']['procedure'][$i]['extension'] = $entry['procedure']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['root'] = $entry['procedure']['id']['root'] ?? null;

            $this->templateData['field_name_value_array']['procedure'][$i]['code'] = $code['code'];
            $this->templateData['field_name_value_array']['procedure'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['procedure'][$i]['codeSystemName'] = $code['formatted_code_type'];

            if (!empty($entry['procedure']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['low']['value'] ?? null;
            } else {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['value'] ?? null;
            }

            $this->templateData['entry_identification_array']['procedure'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchProcedurePreformedActivity($entry): void
    {
        if (!empty($entry['act']['code']['code']) || $entry['act']['negationInd'] ?? 'false' == 'true') {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['procedure'])) {
                $i += count($this->templateData['field_name_value_array']['procedure']);
            }

            $procedure_type = 'intervention';
            if (!empty($entry['act']['templateId'][1]['root']) && $entry['act']['templateId'][1]['root'] == '2.16.840.1.113883.10.20.24.3.32') {
                $procedure_type = 'intervention';
            }

            $code = $this->codeService->resolveCode(
                $entry['act']['code']['code'] ?? '',
                $entry['act']['code']['codeSystemName'] ?: $entry['act']['code']['codeSystem'] ?? null,
                $entry['act']['code']['displayName'] ?? $entry['act']['text']
            );
            // negated oid
            if (!empty($entry["act"]["code"]["nullFlavor"]) && !empty($entry['act']['code']["valueSet"])) {
                $code['code'] = 'OID:' . $entry["act"]["code"]["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $entry["act"]["code"]["valueSet"] ?? null;
                $code['code_text'] = $entry['act']['text'] ?? '';
            }

            $this->templateData['field_name_value_array']['procedure'][$i]['procedure_type'] = $procedure_type;

            $this->templateData['field_name_value_array']['procedure'][$i]['status'] = $entry['act']['statusCode']['code'] ?? '';

            $this->templateData['field_name_value_array']['procedure'][$i]['extension'] = $entry['act']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['root'] = $entry['act']['id']['root'] ?? null;

            $this->templateData['field_name_value_array']['procedure'][$i]['code'] = $code['code'] ?? '';
            $this->templateData['field_name_value_array']['procedure'][$i]['code_text'] = $entry['act']['text'] ?? $code['code_text'] ?? '';
            $this->templateData['field_name_value_array']['procedure'][$i]['codeSystemName'] = $code['formatted_code_type'] ?? '';

            if (!empty($entry['act']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['act']['effectiveTime']['low']['value'] ?? null;
                $this->templateData['field_name_value_array']['procedure'][$i]['date_end'] = $entry['act']['effectiveTime']['high']['value'] ?? null;
            } else {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['act']['effectiveTime']['value'] ?? null;
            }

            $reason_code = $entry["act"]["entryRelationship"]["observation"]["value"]["code"] ?? null;
            if ($reason_code) {
                $reason_system = $entry["act"]["entryRelationship"]["observation"]["value"]["codeSystem"];
                $reason_name = $entry["act"]["entryRelationship"]["observation"]["value"]["codeSystemName"];
                $code = $this->codeService->resolveCode(
                    $reason_code,
                    $reason_name ?: $reason_system ?? '',
                    ''
                );
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_description'] = $code['code_text'] ?? $entry['act']['text'];
                $date_low = $entry["act"]["entryRelationship"]["observation"]['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry["act"]["entryRelationship"]["observation"]['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['procedure'][$i]['reason_date_high'] = $date_high;
            }
            $this->templateData['field_name_value_array']['procedure'][$i]['reason_status'] = (($entry['act']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['procedure'][$i] = $i;
        }
    }

    public function allergy($component)
    {
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchAllergyIntoleranceObservation($value);
            }
        } else {
            $this->fetchAllergyIntoleranceObservation($component['section']['entry'] ?? null);
        }
    }

    public function medication($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchMedicationData($value);
            }
        } else {
            $this->fetchMedicationData($component['section']['entry'] ?? null);
        }
    }

    public function medical_problem($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchMedicalProblemData($value);
            }
        } else {
            $this->fetchMedicalProblemData($component['section']['entry'] ?? null);
        }
    }

    public function immunization($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchImmunizationData($value);
            }
        } else {
            $this->fetchImmunizationData($component['section']['entry']);
        }
    }

    public function procedure($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                if ($key % 3 != 0) {
                    continue; //every third entry section has the procedure details
                }

                $this->fetchProcedureActivityData($value);
            }
        } else {
            $this->fetchProcedureActivityData($component['section']['entry'] ?? null);
        }
    }

    public function labResult($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchLabResultData($value);
            }
        } else {
            $this->fetchLabResultData($component['section']['entry'] ?? null);
        }
    }

    public function fetchLabResultData($lab_result_data)
    {
        $i = 1;
        if (!empty($this->templateData['field_name_value_array']['procedure_result'])) {
            $i += count($this->templateData['field_name_value_array']['procedure_result']);
        }
        if (!empty($lab_result_data['organizer']['component'])) {
            foreach ($lab_result_data['organizer']['component'] as $key => $value) {
                if (!empty($value['observation']['code']['code'])) {
                    $code = $this->codeService->resolveCode(
                        $lab_result_data['organizer']['code']['code'] ?? '',
                        ($lab_result_data['organizer']['code']['codeSystemName'] ?? '') ?: $lab_result_data['organizer']['code']['codeSystem'] ?? '',
                        $lab_result_data['organizer']['code']['displayName'] ?? ''
                    );
                    if (empty($lab_result_data['organizer']['id']['extension'])) {
                        $lab_result_data['organizer']['id']['extension'] = $lab_result_data['organizer']['id']['root'] ?? null;
                    }
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['extension'] = $lab_result_data['organizer']['id']['extension'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['root'] = $lab_result_data['organizer']['id']['root'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_code'] = $code['formatted_code'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_text'] = $code['code_text'] ?? null;
                    if (!empty($lab_result_data['organizer']['effectiveTime']['low']['value'])) {
                        $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['organizer']['effectiveTime']['low']['value'];
                    } else {
                        $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['organizer']['effectiveTime']['value'] ?? null;
                    }

                    $this->templateData['field_name_value_array']['procedure_result'][$i]['status'] = $lab_result_data['organizer']['statusCode']['code'] ?? null;

                    if (empty($value['observation']['id']['extension'])) {
                        $value['observation']['id']['extension'] = $value['observation']['id']['root'] ?? null;
                    }
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_extension'] = $value['observation']['id']['extension'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_root'] = $value['observation']['id']['root'] ?? null;
                    // @TODO code lookup here
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_code'] = $value['observation']['code']['code'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_text'] = $value['observation']['code']['displayName'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_date'] = $value['observation']['effectiveTime']['value'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['value'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_unit'] = $value['observation']['value']['unit'] ?? 'UNK';
                    if (!empty($value['observation']['referenceRange']['observationRange']['text'])) {
                        $this->templateData['field_name_value_array']['procedure_result'][$i]['results_range'] = $value['observation']['referenceRange']['observationRange']['text'];
                    } else {
                        $this->templateData['field_name_value_array']['procedure_result'][$i]['results_range'] = ($value['observation']['referenceRange']['observationRange']['value']['low']['value'] ?? '') . '-' . ($value['observation']['referenceRange']['observationRange']['value']['high']['value'] ?? '') . ' ' . ($value['observation']['referenceRange']['observationRange']['value']['low']['unit'] ?? '');
                    }

                    $this->templateData['entry_identification_array']['procedure_result'][$i] = $i;
                    $i++;
                }
            }
        }
    }

    public function fetchQrdaLabResultData($entry)
    {
        $i = 1;
        if (!empty($this->templateData['field_name_value_array']['procedure_result'])) {
            $i += count($this->templateData['field_name_value_array']['procedure_result']);
        }
        if (!empty($entry['observation']['code']['code'])) {
            $code = $this->codeService->resolveCode(
                $entry['observation']['code']['code'] ?? '',
                $entry['observation']['code']['codeSystemName'] ?: $entry['observation']['code']['codeSystem'] ?? '',
                $entry['observation']['code']['displayName'] ?? ''
            );

            $this->templateData['field_name_value_array']['procedure_result'][$i]['extension'] = $entry['observation']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['root'] = $entry['observation']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_text'] = $code['code_text'];

            if (!empty($entry['observation']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $entry['observation']['effectiveTime']['low']['value'];
            } else {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $entry['observation']['effectiveTime']['value'] ?? null;
            }
            $this->templateData['field_name_value_array']['procedure_result'][$i]['status'] = $entry['observation']['statusCode']['code'] ?? null;

            $value = $entry['observation']['entryRelationship'] ?? null;
            if (!empty($value)) {
                // find the result template
                foreach ($entry['observation'] as $key => $find_value) {
                    // check if a ccda result template
                    $flag = false;
                    foreach ($find_value as $key1 => $val) {
                        if (is_array($val)) {
                            if ($val['templateId'][0]['root'] == '2.16.840.1.113883.10.20.22.4.2') {
                                $flag = true;
                                break;
                            }
                            if ($val['observation']['templateId'][0]['root'] == '2.16.840.1.113883.10.20.22.4.2') {
                                $flag = true;
                                $value = $val;
                                break;
                            }
                        }
                    }
                    if ($flag) {
                        break;
                    }
                }
            }
            if (empty($value)) {
                $this->templateData['entry_identification_array']['procedure_result'][$i] = $i;
                return;
            }
            $code = $this->codeService->resolveCode(
                $value['observation']['code']['code'] ?? null,
                $value['observation']['code']['codeSystemName'] ?? $value['observation']['code']['codeSystem'] ?? '',
                $value['observation']['code']['displayName'] ?? ''
            );
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_text'] = $code['code_text'];

            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_extension'] = $value['observation']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_root'] = $value['observation']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_date'] = $value['observation']['effectiveTime']['value'] ?? null;
            if ($value['observation']['value']['type'] == 'ST') {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['_'] ?? null;
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_unit'] = 'UNK'; // must be set to save
            } else {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['value'] ?? null;
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_unit'] = $value['observation']['value']['unit'] ?: 'UNK';
            }

            if (!empty($value['observation']['referenceRange']['observationRange']['text'])) {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_range'] = $value['observation']['referenceRange']['observationRange']['text'];
            } else {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['results_range'] = ($value['observation']['referenceRange']['observationRange']['value']['low']['value'] ?? '') . '-' . ($value['observation']['referenceRange']['observationRange']['value']['high']['value'] ?? '') . ' ' . ($value['observation']['referenceRange']['observationRange']['value']['low']['unit'] ?? '');
            }

            $this->templateData['entry_identification_array']['procedure_result'][$i] = $i;
        }
    }

    public function VitalSign($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchVitalSignData($value);
            }
        } else {
            $this->fetchVitalSignData($component['section']['entry'] ?? null);
        }
    }

    public function fetchVitalSignData($vital_sign_data)
    {
        if (!empty($vital_sign_data['organizer']['component'][0]['observation']['effectiveTime']['value'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['vital_sign'])) {
                $i += count($this->templateData['field_name_value_array']['vital_sign']);
            }
            $this->templateData['field_name_value_array']['vital_sign'][$i]['extension'] = $vital_sign_data['organizer']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['vital_sign'][$i]['root'] = $vital_sign_data['organizer']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['vital_sign'][$i]['date'] = $vital_sign_data['organizer']['component'][0]['observation']['effectiveTime']['value'] ?? null;
            $vitals_array = array(
                '8310-5' => 'temperature',
                '8462-4' => 'bpd',
                '8480-6' => 'bps',
                '8287-5' => 'head_circ',
                '8867-4' => 'pulse',
                '8302-2' => 'height',
                '2710-2' => 'oxygen_saturation',
                '9279-1' => 'respiration',
                '3141-9' => 'weight',
                '39156-5' => 'BMI'
            );

            for ($j = 0; $j < 9; $j++) {
                $code = $vital_sign_data['organizer']['component'][$j]['observation']['code']['code'] ?? null;
                if (!empty($vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship'])) {
                    $this->templateData['field_name_value_array']['vital_sign'][$i]['bps'] = $vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship'][0]['observation']['value']['value'] ?? null;
                    $this->templateData['field_name_value_array']['vital_sign'][$i]['bpd'] = $vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship'][1]['observation']['value']['value'] ?? null;
                } else {
                    if (array_key_exists($code, $vitals_array)) {
                        $this->templateData['field_name_value_array']['vital_sign'][$i][$vitals_array[$code]] = $vital_sign_data['organizer']['component'][$j]['observation']['value']['value'] ?? null;
                    }
                }
            }

            $this->templateData['entry_identification_array']['vital_sign'][$i] = $i;
        }
    }

    public function fetchPhysicalExamPerformedData($entry)
    {
        // create an observation for this exam.
        $this->fetchObservationPerformedData($entry);
        // and a vital in vital forms.
        if (
            (!empty($entry['observation']['effectiveTime']['value']) && !empty($entry['observation']['value']['value']))
            || (!empty($entry['observation']['entryRelationship']['observation']['value']['code'] ?? null) && ($entry['observation']['entryRelationship']['typeCode'] ?? null) === 'RSON')
        ) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['vital_sign'])) {
                $cnt = count($this->templateData['field_name_value_array']['vital_sign'] ?? []);
                $v_date = $entry['observation']['effectiveTime']['value'] ?? null;
                for ($c = 1; $c <= $cnt; $c++) {
                    if ($this->templateData['field_name_value_array']['vital_sign'][$c]['date'] == $v_date) {
                        $i = 0;
                        $cnt = $c;
                        break;
                    }
                }
                $i += $cnt;
            }
            $this->templateData['field_name_value_array']['vital_sign'][$i]['extension'] = $entry['organizer']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['vital_sign'][$i]['root'] = $entry['organizer']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['vital_sign'][$i]['date'] = $entry['observation']['effectiveTime']['value'] ?? null;
            $vitals_array = array(
                '8310-5' => 'temperature',
                '8462-4' => 'bpd',
                '8480-6' => 'bps',
                '8287-5' => 'head_circ',
                '8867-4' => 'pulse',
                '8302-2' => 'height',
                '2710-2' => 'oxygen_saturation', // deprecated code
                '59408-5' => 'oxygen_saturation',
                '9279-1' => 'respiration',
                '3141-9' => 'weight',
                '29463-7' => 'weight', // with clothes
                '39156-5' => 'BMI'
            );
            $is_negated = !empty($entry['observation']['negationInd'] ?? false);
            $code = $entry['observation']['code']['code'] ?? null;
            if (array_key_exists($code, $vitals_array)) {
                $this->templateData['field_name_value_array']['vital_sign'][$i][$vitals_array[$code]] = $entry['observation']['value']['value'] ?? null;
                $this->templateData['field_name_value_array']['vital_sign'][$i]['vital_column'] = $vitals_array[$code] ?? '';
            } else {
                // log missed exam
                error_log('Missed Physical Exam code (likely vital): ' . $code);
            }

            if (!empty($entry['observation']['entryRelationship']['observation']['value']['code'] ?? null)) {
                // @todo inter to this moodcode RSON in full template!
                $ob_code = $entry['observation']['entryRelationship']['observation']['value']['code'];
                $ob_system = $entry['observation']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['observation']['entryRelationship']['observation']['value']['codeSystem'] ?? '';
                $ob_code_text = $entry['observation']['entryRelationship']['observation']['value']['displayName'] ?? '';
                $reason_code = $this->codeService->resolveCode($ob_code, $ob_system, $ob_code_text);
                $reason_status = $entry['observation']['entryRelationship']['observation']['statusCode']['code'] ?? '';
            }

            $this->templateData['field_name_value_array']['vital_sign'][$i]['reason_status'] = $is_negated ? 'negated' : ($reason_status ?? '');
            $this->templateData['field_name_value_array']['vital_sign'][$i]['reason_code'] = $reason_code['formatted_code'] ?? '';
            $this->templateData['field_name_value_array']['vital_sign'][$i]['reason_code_text'] = $reason_code['code_text'] ?? '';

            $this->templateData['entry_identification_array']['vital_sign'][$i] = $i;
        }
    }

    public function socialHistory($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchSocialHistoryData($value);
            }
        } else {
            $this->fetchSocialHistoryData($component['section']['entry'] ?? null);
        }
    }

    public function fetchSocialHistoryData($social_history_data)
    {
        if (!empty($social_history_data['observation']['value']['code'])) {
            $social_history_array = array(
                '2.16.840.1.113883.10.20.22.4.78' => 'smoking'
            );
            $i = 0;
            $code = $social_history_data['observation']['templateId']['root'];
            if (!empty($this->templateData['field_name_value_array']['social_history'])) {
                foreach ($this->templateData['field_name_value_array']['social_history'] as $key => $value) {
                    if (!array_key_exists($social_history_array[$code], $value)) {
                        $i = $key;
                    } else {
                        $i = count($this->templateData['field_name_value_array']['social_history']) + 1;
                    }
                }
            }

            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['extension'] = $social_history_data['observation']['id']['extension'];
            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['root'] = $social_history_data['observation']['id']['root'];
            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['status'] = $social_history_data['observation']['value']['code'];
            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['begdate'] = $social_history_data['observation']['effectiveTime']['low']['value'];
            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['enddate'] = $social_history_data['observation']['effectiveTime']['high']['value'];
            $this->templateData['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['value'] = $social_history_data['observation']['value']['displayName'];
            $this->templateData['entry_identification_array']['social_history'][$i] = $i;
        }
    }

    public function encounter($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchEncounterPerformed($value);
            }
        } else {
            $this->fetchEncounterPerformed($component['section']['entry']);
        }

        unset($component);
        return;
    }

    public function carePlan($component)
    {
        $component['section']['text'] = '';
        if (!empty($component['section']['entry'][0])) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchCarePlanData($value);
            }
        } else {
            $this->fetchCarePlanData($component['section']['entry'] ?? null);
        }
    }

    public function fetchCarePlanData($entry)
    {
        $plan_type = 'plan_of_care';
        if ($this->currentOid == '2.16.840.1.113883.10.20.24.3.31') {
            $plan_type = 'intervention';
        } elseif ($this->currentOid == '2.16.840.1.113883.10.20.24.3.37') {
            $plan_type = 'test_or_order';
        } elseif ($this->currentOid == '2.16.840.1.113883.10.20.24.3.143' || $this->currentOid == '2.16.840.1.113883.10.20.24.3.47') {
            $plan_type = 'planned_medication_activity';
        } elseif ($this->currentOid == '2.16.840.1.113883.10.20.24.3.130') {
            $plan_type = 'supply_order';
            if (($entry["act"]["entryRelationship"]["supply"]["templateId"][1]["root"] ?? null) == '2.16.840.1.113883.10.20.24.3.9') {
                $plan_type = 'device_order';
            }
        }

        $i = 1;

        if (
            (!empty($entry['act']['code']['code']) && $plan_type == 'device_order')
            || (($entry['act']['negationInd'] ?? 'false') == 'true' && $plan_type == 'device_order')
        ) {
            if (!empty($this->templateData['field_name_value_array']['care_plan'])) {
                $i += count($this->templateData['field_name_value_array']['care_plan']);
            }

            $device_code = $entry["act"]["entryRelationship"]["supply"]["participant"]["participantRole"]["playingDevice"]["code"]["code"];
            $device_system = $entry["act"]["entryRelationship"]["supply"]["participant"]["participantRole"]["playingDevice"]["code"]["codeSystem"];
            $device_name = $entry["act"]["entryRelationship"]["supply"]["participant"]["participantRole"]["playingDevice"]["code"]["codeSystemName"];

            $code = $this->codeService->resolveCode(
                $device_code,
                $device_name ?: $device_system ?? null,
                ''
            );

            $this->templateData['field_name_value_array']['care_plan'][$i]['plan_type'] = $plan_type;
            $this->templateData['field_name_value_array']['care_plan'][$i]['extension'] = $entry['act']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['root'] = $entry['act']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['description'] = $entry["act"]["entryRelationship"]["supply"]['text'] ?? $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['act']['effectiveTime']['center']['value'] ?? $entry["act"]["entryRelationship"]["supply"]['author']['time']['value'] ?? null;

            // reason
            $reason_code = $entry["act"]["entryRelationship"]["supply"]["entryRelationship"]["observation"]["value"]["code"] ?? null;
            if ($reason_code) {
                $reason_system = $entry["act"]["entryRelationship"]["supply"]["entryRelationship"]["observation"]["value"]["codeSystem"];
                $reason_name = $entry["act"]["entryRelationship"]["supply"]["entryRelationship"]["observation"]["value"]["codeSystemName"];
                $code = $this->codeService->resolveCode(
                    $reason_code,
                    $reason_name ?: $reason_system ?? '',
                    ''
                );
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_description'] = $code['code_text'] ?? $entry['act']['text'];
                $date_low = $entry["act"]["entryRelationship"]["supply"]["entryRelationship"]["observation"]['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry["act"]["entryRelationship"]["supply"]["entryRelationship"]["observation"]['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_high'] = $date_high;
            }
            $this->templateData['field_name_value_array']['care_plan'][$i]['reason_status'] = (($entry['act']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['care_plan'][$i] = $i;
        } elseif (!empty($entry['act']['code']['code']) || ($entry['act']['negationInd'] ?? 'false') == 'true') {
            if (!empty($this->templateData['field_name_value_array']['care_plan'])) {
                $i += count($this->templateData['field_name_value_array']['care_plan']);
            }

            $code = $this->codeService->resolveCode(
                $entry['act']['code']['code'] ?? '',
                ($entry['act']['code']['codeSystemName'] ?? null) ?: $entry['act']['code']['codeSystem'] ?? null,
                $entry['act']['code']['displayName'] ?? $entry['act']['text']
            );
            if (!empty($entry["act"]["code"]["nullFlavor"]) && !empty($entry['act']['code']["valueSet"])) {
                $code['code'] = $entry["act"]["code"]["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $entry["act"]["code"]["valueSet"] ?? null;
                $code['code_text'] = $entry['act']['text'] ?? '';
            }
            $this->templateData['field_name_value_array']['care_plan'][$i]['plan_type'] = $plan_type;
            $this->templateData['field_name_value_array']['care_plan'][$i]['extension'] = $entry['act']['templateId']['root'] ?? '';
            $this->templateData['field_name_value_array']['care_plan'][$i]['root'] = $entry['act']['templateId']['root'] ?? '';
            $this->templateData['field_name_value_array']['care_plan'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['description'] = $entry['act']['text'] ?? $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['act']['effectiveTime']['center']['value'] ?? $entry['act']['author']['time']['value'] ?? null;

            // negate
            if ($entry['act']['entryRelationship']['observation']['value']['code']) {
                $code = $this->codeService->resolveCode(
                    $entry['act']['entryRelationship']['observation']['value']['code'],
                    $entry['act']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['act']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                    $entry['act']['entryRelationship']['observation']['value']['displayName'] ?? ''
                );
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_description'] = $code['code_text'] ?? $entry['act']['text'];
                $date_low = $entry['act']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry['act']['entryRelationship']['observation']['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_high'] = $date_high;
            }
            $this->templateData['field_name_value_array']['care_plan'][$i]['reason_status'] = (($entry['act']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['care_plan'][$i] = $i;
        } elseif (!empty($entry['observation']['code']['code']) || ($entry['observation']['negationInd'] ?? 'false') == 'true') { // it's an observation template
            if (!empty($this->templateData['field_name_value_array']['care_plan'])) {
                $i += count($this->templateData['field_name_value_array']['care_plan']);
            }

            $code = $this->codeService->resolveCode(
                $entry['observation']['code']['code'] ?? null,
                $entry['observation']['code']['codeSystemName'] ?? $value['observation']['code']['codeSystem'] ?? '',
                $entry['observation']['code']['displayName'] ?? ''
            );
            if (!empty($entry['observation']['code']["nullFlavor"]) && !empty($entry['observation']['code']["valueSet"])) {
                $code['code'] = $entry['observation']['code']["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $entry['observation']['code']["valueSet"] ?? null;
                $code['code_text'] = $entry['observation']['text'] ?? '';
            }
            $this->templateData['field_name_value_array']['care_plan'][$i]['plan_type'] = $plan_type;
            $this->templateData['field_name_value_array']['care_plan'][$i]['extension'] = $entry['observation']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['root'] = $entry['observation']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['description'] = $entry['observation']['text'] ?? $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['observation']['author']['time']['value'] ?? null;

            // check for a reason code if observation.
            if ($entry['observation']['entryRelationship']['observation']['value']['code']) {
                $code = $this->codeService->resolveCode(
                    $entry['observation']['entryRelationship']['observation']['value']['code'],
                    $entry['observation']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['observation']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                    $entry['observation']['entryRelationship']['observation']['value']['displayName']
                );
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_description'] = $code['code_text'] ?? $entry['observation']['text'];
                $date_low = $entry['observation']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry['observation']['entryRelationship']['observation']['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_high'] = $date_high;
            }

            $this->templateData['field_name_value_array']['care_plan'][$i]['reason_status'] = (($entry['observation']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['care_plan'][$i] = $i;
        } elseif (
            !empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])
            || ($entry['substanceAdministration']['negationInd'] ?? 'false') == 'true'
        ) {
            if (!empty($this->templateData['field_name_value_array']['care_plan'])) {
                $i += count($this->templateData['field_name_value_array']['care_plan']);
            }

            //$plan_type = 'substance_medication_order';
            $code = $this->codeService->resolveCode(
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? '',
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['codeSystemName'] ?? ($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['codeSystem'] ?? ''),
                $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'] ?? ''
            );
            if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']["nullFlavor"])) {
                $code['code'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']["valueSet"] ?? null;
                $code['formatted_code'] = 'OID:' . $code['code'];
                $code['code_text'] = $entry['substanceAdministration']['text'] ?? '';
            }

            $this->templateData['field_name_value_array']['care_plan'][$i]['plan_type'] = $plan_type;
            $this->templateData['field_name_value_array']['care_plan'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            ;
            $this->templateData['field_name_value_array']['care_plan'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['care_plan'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['description'] = $entry['substanceAdministration']['text'] ?? $code['code_text'];

            $this->templateData['field_name_value_array']['care_plan'][$i]['end_date'] = null;
            if (!empty($entry['substanceAdministration']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['substanceAdministration']['effectiveTime']['low']['value'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['end_date'] = $entry['substanceAdministration']['effectiveTime']['high']['value'] ?? null;
            } elseif (!empty($entry['substanceAdministration']['effectiveTime'][0]['low']['value'])) {
                $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['substanceAdministration']['effectiveTime'][0]['low']['value'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['end_date'] = $entry['substanceAdministration']['effectiveTime'][0]['high']['value'] ?? null;
            } elseif (!empty($entry['substanceAdministration']['effectiveTime']['value'])) {
                $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $entry['substanceAdministration']['effectiveTime']['value'];
            } else {
                $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = date('Y-m-d');
            }

            if ($entry['substanceAdministration']['entryRelationship']['observation']['value']['code']) {
                $code = $this->codeService->resolveCode(
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['code'],
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['codeSystemName'] ?: $entry['substanceAdministration']['entryRelationship']['observation']['value']['codeSystem'] ?? '',
                    $entry['substanceAdministration']['entryRelationship']['observation']['value']['displayName'] ?? ''
                );
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code'] = $code['formatted_code'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_code_text'] = $code['code_text'];
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_description'] = $code['code_text'] ?? $entry['observation']['text'];
                $date_low = $entry['substanceAdministration']['entryRelationship']['observation']['effectiveTime']['low']['value'] ?? null;
                $date_high = $entry['substanceAdministration']['entryRelationship']['observation']['effectiveTime']['high']['value'] ?? null;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_low'] = $date_low;
                $this->templateData['field_name_value_array']['care_plan'][$i]['reason_date_high'] = $date_high;
            }

            $this->templateData['field_name_value_array']['care_plan'][$i]['reason_status'] = (($entry['substanceAdministration']['negationInd'] ?? 'false') == 'true') ? 'negated' : null;
            $this->templateData['entry_identification_array']['care_plan'][$i] = $i;
        }
    }

    public function functionalCognitiveStatus($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchFunctionalCognitiveStatusData($value);
            }
        } else {
            $this->fetchFunctionalCognitiveStatusData($component['section']['entry']);
        }
    }

    public function fetchFunctionalCognitiveStatusData($entry)
    {
        if (!empty($entry['observation']['value']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['functional_cognitive_status'])) {
                $i += count($this->templateData['field_name_value_array']['functional_cognitive_status']);
            }

            $code = $this->codeService->resolveCode(
                $entry['observation']['value']['code'] ?? null,
                $value['observation']['value']['codeSystemName'] ?? $value['observation']['code']['codeSystem'] ?? '',
                $value['observation']['value']['displayName'] ?? ''
            );

            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['extension'] = $entry['observation']['id']['extension'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['root'] = $entry['observation']['id']['root'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['date'] = $entry['observation']['effectiveTime']['low']['value'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['code'] = $code['formatted_code'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['code_text'] = $code['code_text'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['description'] = $code['code_text'];
            $this->templateData['entry_identification_array']['functional_cognitive_status'][$i] = $i;
        }
    }

    public function referral($component)
    {
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchReferralData($value);
            }
        } else {
            $this->fetchReferralData($component['section']);
        }
    }

    public function fetchReferralData($referral_data)
    {
        if (!empty($referral_data['text']['paragraph']) && is_array($referral_data['text']['paragraph'])) {
            $i = 1;
            foreach ($referral_data['text']['paragraph'] as $key => $value) {
                if ($value) {
                    $this->templateData['field_name_value_array']['referral'][$i]['body'] = preg_replace('/\s+/', ' ', $value);
                    $this->templateData['entry_identification_array']['referral'][$i] = $i;
                    $i++;
                }
            }
        } else {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['referral'])) {
                $i += count($this->templateData['field_name_value_array']['referral']);
            }
            $this->templateData['field_name_value_array']['referral'][$i]['root'] = $referral_data['templateId']['root'];
            $this->templateData['field_name_value_array']['referral'][$i]['body'] = (!empty($referral_data['text']['paragraph'])) ? preg_replace('/\s+/', ' ', $referral_data['text']['paragraph']) : '';

            $this->templateData['entry_identification_array']['referral'][$i] = $i;
        }

        return;
    }

    public function dischargeMedications($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchDischargeMedicationsData($value);
            }
        } else {
            $this->fetchDischargeMedicationsData($component['section']['entry']);
        }
    }

    public function fetchDischargeMedicationsData($discharge_medications_data)
    {
        $i = 1;
        if (!empty($this->templateData['field_name_value_array']['discharge_medication'])) {
            $i += count($this->templateData['field_name_value_array']['discharge_medication']);
        }
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['extension'] = $discharge_medications_data['act']['id']['extension'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['root'] = $discharge_medications_data['act']['id']['root'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['begdate'] = $discharge_medications_data['act']['effectiveTime']['low']['value'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['enddate'] = $discharge_medications_data['act']['effectiveTime']['high']['value'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['route'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['routeCode']['code'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['route_display'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['routeCode']['displayName'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['dose'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['doseQuantity']['value'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['dose_unit'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['doseQuantity']['unit'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['rate'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['rateQuantity']['value'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['rate_unit'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['rateQuantity']['unit'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['drug_code'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['drug_text'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['note'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['text']['reference']['value'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['indication'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ?: $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship']['observation']['value']['displayName'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['prn'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['precondition']['criterion']['value']['displayName'];

        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_title'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['prefix'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_fname'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['given'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_lname'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['family'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_root'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['id']['root'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_address'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['streetAddressLine'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_city'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['city'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_state'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['state'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_postalCode'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['postalCode'];
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['provider_country'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['country'];
        $this->templateData['entry_identification_array']['discharge_medication'][$i] = $i;
        unset($discharge_medications_data);
        return;
    }

    public function dischargeSummary($component)
    {
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetchDischargeSummaryData($value);
            }
        } else {
            $this->fetchDischargeSummaryData($component['section']);
        }

        unset($component);
        return;
    }

    public function fetchDischargeSummaryData($discharge_summary_data)
    {
        $i = 1;
        if (!empty($this->templateData['field_name_value_array']['discharge_summary'])) {
            $i += count($this->templateData['field_name_value_array']['discharge_summary']);
        }
        $this->templateData['field_name_value_array']['discharge_summary'][$i]['root'] = $discharge_summary_data['templateId']['root'];
        $text = preg_replace('/\s+/', ' ', $discharge_summary_data['text']['content']);
        for ($j = 0, $jMax = count($discharge_summary_data['text']['list']['item']); $j < $jMax; $j++) {
            if (is_array($discharge_summary_data['text']['list']['item'][$j])) {
                for ($k = 0, $kMax = count($discharge_summary_data['text']['list']['item'][$j]['list']['item']); $k < $kMax; $k++) {
                    $text .= '#$%' . preg_replace('/\s+/', ' ', $discharge_summary_data['text']['list']['item'][$j]['list']['item'][$k]);
                }
            } else {
                $text .= '#$%' . preg_replace('/\s+/', ' ', $discharge_summary_data['text']['list']['item'][$j]);
            }
        }

        $this->templateData['field_name_value_array']['discharge_summary'][$i]['text'] = $text;

        $this->templateData['entry_identification_array']['discharge_summary'][$i] = $i;
    }
}
