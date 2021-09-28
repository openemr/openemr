<?php

/**
 * QrdaParseService Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use OpenEMR\Services\CodeTypesService;

class CdaTemplateParse
{
    private $templateData;
    private $codeService;
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
                    $func_name = $components_oids[$component['section']['templateId']['root']];
                    $this->$func_name($component);
                }
            } elseif (empty($component['section']['templateId'])) {
                // uncomment for debugging information.
                error_log("section and template id empty for " . var_export($component, true));
            } elseif (count($component['section']['templateId']) > 1) {
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
            '2.16.840.1.113883.10.20.24.3.41' => 'fetchMedicationData', // active medication @todo verify status all meds
            '2.16.840.1.113883.10.20.24.3.42' => 'fetchMedicationData', // Medication Administered Act @todo honor end dates
            '2.16.840.1.113883.10.20.24.3.139' => 'fetchMedicationData', // Medication Dispensed Act
            '2.16.840.1.113883.10.20.24.3.105' => 'fetchMedicationData', // Medication Discharge Act
            //'2.16.840.1.113883.10.20.24.3.42' => '', // QDM Datatype: Substance, Administered
            //'2.16.840.1.113883.10.20.24.3.47' => '', // QDM Datatype: Substance, Order @todo figure out substance i.e water, breast milk ...? what to do!
            '2.16.840.1.113883.10.20.24.3.137' => 'fetchMedicalProblemData', // diagnosis
            '2.16.840.1.113883.10.20.24.3.140' => 'fetchImmunizationData', // Immunization Administered (V3)
            '2.16.840.1.113883.10.20.24.3.143' => 'fetchImmunizationData', // Immunization Order (V3) @todo verify status
            '2.16.840.1.113883.10.20.24.3.64' => 'fetchProcedureData', // procedure performed
            '2.16.840.1.113883.10.20.24.3.7' => 'fetchProcedureData', // procedure Device Applied (V5)
            '2.16.840.1.113883.10.20.24.3.37' => 'fetchQrdaLabResultData', // lab test ordered
            '2.16.840.1.113883.10.20.24.3.38' => 'fetchQrdaLabResultData', // lab test preformed
            //'2.16.840.1.113883.10.20.24.3.39' => 'fetchQrdaLabResultData', // lab test recommend. @todo maybe care plan
            '2.16.840.1.113883.10.20.24.3.133' => 'fetchEncounterPerformed',
        );
        foreach ($entryComponents['section']['entry'] as $entry) {
            $key = array_keys($entry)[0]; // need the entry type i.e. observation, activity, substance etc.
            if (!empty($entry[$key]['templateId']['root'])) {
                if (!empty($qrda_oids[$entry[$key]['templateId']['root']])) {
                    $func_name = $qrda_oids[$entry[$key]['templateId']['root']];
                    $this->$func_name($entry);
                } else {
                    $text = $entry[$key]['templateId']['root'] . ' ' . ($entry[$key]['text'] ?? $entry[$key]['code']['displayName']);
                    error_log('Missing QDM: ' . $text);
                }
            } elseif (count($entry[$key]['templateId']) > 1) {
                foreach ($entry[$key]['templateId'] as $key_1 => $value_1) {
                    if (!empty($qrda_oids[$entry[$key]['templateId'][$key_1]['root']])) {
                        $func_name = $qrda_oids[$entry[$key]['templateId'][$key_1]['root']];
                        if (!empty($func_name)) {
                            $this->$func_name($entry);
                        }
                        break;
                    } else {
                        $text = $entry[$key]['templateId'][1]['root'] . ' ' . ($entry[$key]['text'] ?? $entry[$key]['code']['displayName']);
                        error_log('Missing QDM: ' . $text);
                    }
                }
            }
        }
        return $this->templateData;
    }

    /**
     * @param $entry
     */
    public function fetchEncounterPerformed($entry): void
    {
        $entry = $entry['act']['entryRelationship'];
        if ($entry['encounter']['effectiveTime']['value'] != 0 || $entry['encounter']['effectiveTime']['low']['value'] != 0) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['encounter'])) {
                $i += count($this->templateData['field_name_value_array']['encounter']);
            }

            $this->templateData['field_name_value_array']['encounter'][$i]['extension'] = $entry['encounter']['id']['extension'];
            $this->templateData['field_name_value_array']['encounter'][$i]['root'] = $entry['encounter']['id']['root'];
            $this->templateData['field_name_value_array']['encounter'][$i]['date'] = $entry['encounter']['effectiveTime']['value'] ?: $entry['encounter']['effectiveTime']['low']['value'];

            $code_type = $entry['encounter']['code']['codeSystemName'];
            $code_text = $entry['encounter']['code']['displayName'] ?? $entry['encounter']['text'];
            $code = $this->codeService->getCodeWithType($entry['encounter']['code']['code'], $code_type ?: 'SNOMED-CT', true);
            $code_text = $code_text ?: $this->codeService->lookup_code_description($code, 'code_text_short');
            $this->templateData['field_name_value_array']['encounter'][$i]['code'] = $code;
            $this->templateData['field_name_value_array']['encounter'][$i]['code_text'] = $code_text;

            $this->templateData['field_name_value_array']['encounter'][$i]['provider_npi'] = $entry['encounter']['performer']['assignedEntity']['id']['extension'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_name'] = $entry['encounter']['performer']['assignedEntity']['assignedPerson']['name']['given'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_address'] = $entry['encounter']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_city'] = $entry['encounter']['performer']['assignedEntity']['addr']['city'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_state'] = $entry['encounter']['performer']['assignedEntity']['addr']['state'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_postalCode'] = $entry['encounter']['performer']['assignedEntity']['addr']['postalCode'];
            $this->templateData['field_name_value_array']['encounter'][$i]['provider_country'] = $entry['encounter']['performer']['assignedEntity']['addr']['country'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_name'] = $entry['encounter']['participant']['participantRole']['playingEntity']['name'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_address'] = $entry['encounter']['participant']['participantRole']['addr']['streetAddressLine'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_city'] = $entry['encounter']['participant']['participantRole']['addr']['city'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_state'] = $entry['encounter']['participant']['participantRole']['addr']['state'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_zip'] = $entry['encounter']['participant']['participantRole']['addr']['postalCode'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_country'] = $entry['encounter']['participant']['participantRole']['addr']['country'];
            $this->templateData['field_name_value_array']['encounter'][$i]['represented_organization_telecom'] = $entry['encounter']['participant']['participantRole']['telecom'];

            // encounter diagnosis to issues list
            $code = $this->codeService->getCodeWithType(
                $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['code'],
                $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['codeSystemName'],
                true
            );
            $code_text = $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['displayName'] ?: $this->codeService->lookup_code_description($code, 'code_text_short');

            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['effectiveTime']['low']['value'];
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_code'] = $code;
            $this->templateData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_issue'] = $code_text;
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
            $code = $entry['act']['entryRelationship']['observation']['value']['code'] ?? null;
            $code_text = $entry['act']['entryRelationship']['observation']['value']['displayName'] ?? null;
            $code_type = $entry['act']['entryRelationship']['observation']['value']['codeSystemName'] ?? null;
            $code = $this->codeService->getCodeWithType($code, $code_type, true);
            if (empty($code_text)) {
                $code_text = $this->codeService->lookup_code_description($code, 'code_text_short');
            }

            $this->templateData['field_name_value_array']['lists1'][$i]['type'] = 'medical_problem';
            $this->templateData['field_name_value_array']['lists1'][$i]['extension'] = $entry['act']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['root'] = $entry['act']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['begdate'] = $entry['act']['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['enddate'] = $entry['act']['effectiveTime']['high']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['list_code'] = $code ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['list_code_text'] = $code_text ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['observation'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['observation_text'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists1'][$i]['status'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['displayName'] ?: $entry['act']['entryRelationship']['observation']['statusCode'];
            $this->templateData['field_name_value_array']['lists1'][$i]['modified_time'] = $entry['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->templateData['entry_identification_array']['lists1'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchAllergyIntoleranceObservation($entry)
    {
        if (!empty($entry['observation']['participant']['participantRole']['playingEntity']['code']['code'])) {
            $i = 1;
            // if there are already items here we want to add to them.
            if (!empty($this->templateData['field_name_value_array']['lists2'])) {
                $i += count($this->templateData['field_name_value_array']['lists2']);
            }

            $this->templateData['field_name_value_array']['lists2'][$i]['type'] = 'allergy';
            $this->templateData['field_name_value_array']['lists2'][$i]['extension'] = $entry['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['begdate'] = $entry['effectiveTime']['low']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['enddate'] = $entry['effectiveTime']['high']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['list_code'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['list_code_text'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists2'][$i]['codeSystemName'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['codeSystemName'] ?? null;
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
            $request_type = 'active';
            if (!empty($entry['substanceAdministration']['templateId']['root'])) {
                $request_type = $substanceAdministration_oids[$entry['substanceAdministration']['templateId']['root']];
            } elseif (!empty($entry['substanceAdministration']['templateId'][1]['root'])) {
                $request_type = $substanceAdministration_oids[$entry['substanceAdministration']['templateId'][1]['root']];
            }

            $ctService = new CodeTypesService();
            $code_raw = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
            $code_type = 'RXCUI';
            $code = $ctService->getCodeWithType($code_raw, $code_type, true);
            $code_text = $ctService->lookup_code_description($code, 'code_text');

            $this->templateData['field_name_value_array']['lists3'][$i]['type'] = 'medication';
            $this->templateData['field_name_value_array']['lists3'][$i]['request_type'] = $request_type;
            $this->templateData['field_name_value_array']['lists3'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            if (empty($entry['substanceAdministration']['effectiveTime'][0]['low']['value'])) {
                $this->templateData['field_name_value_array']['lists3'][$i]['begdate'] = date('Y-m-d');
            } else {
                $this->templateData['field_name_value_array']['lists3'][$i]['begdate'] = $entry['substanceAdministration']['effectiveTime'][0]['low']['value'];
            }

            if (!empty($entry['substanceAdministration']['effectiveTime'][0]['high']['value'])) {
                $this->templateData['field_name_value_array']['lists3'][$i]['enddate'] = $entry['substanceAdministration']['effectiveTime'][0]['high']['value'];
            }

            $this->templateData['field_name_value_array']['lists3'][$i]['route'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['route_display'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['dose'] = $entry['substanceAdministration']['doseQuantity']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['dose_unit'] = $entry['substanceAdministration']['doseQuantity']['unit'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['rate'] = $entry['substanceAdministration']['rateQuantity']['value'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['rate_unit'] = $entry['substanceAdministration']['rateQuantity']['unit'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['drug_code'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? null;
            $this->templateData['field_name_value_array']['lists3'][$i]['drug_text'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'] ?? $code_text;
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
        if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['immunization'])) {
                $i += count($this->templateData['field_name_value_array']['immunization']);
            }
            $this->templateData['field_name_value_array']['immunization'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['administered_date'] = $entry['substanceAdministration']['effectiveTime']['value'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['route_code'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['route_code_text'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['cvx_code'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? null;
            $this->templateData['field_name_value_array']['immunization'][$i]['cvx_code_text'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'] ?? null;
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
            $this->templateData['entry_identification_array']['immunization'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchProcedureData($entry): void
    {
        if (!empty($entry['procedure']['code']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['procedure'])) {
                $i += count($this->templateData['field_name_value_array']['procedure']);
            }
            $code = $this->codeService->getCodeWithType(
                $entry['procedure']['code']['code'] ?? null,
                $entry['procedure']['code']['codeSystemName'] ?? null,
                true
            );
            $code_text = $entry['procedure']['code']['displayName'] ?? null ?: $this->codeService->lookup_code_description($code);

            $this->templateData['field_name_value_array']['procedure'][$i]['extension'] = $entry['procedure']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['root'] = $entry['procedure']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['code'] = $entry['procedure']['code']['code'] ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['code_text'] = $code_text ?? null;
            $this->templateData['field_name_value_array']['procedure'][$i]['codeSystemName'] = $this->codeService->formatCodeType($entry['procedure']['code']['codeSystemName']);

            if (!empty($entry['procedure']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['low']['value'] ?? null;
            } else {
                $this->templateData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['value'] ?? null;
            }

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

                $this->fetchProcedureData($value);
            }
        } else {
            $this->fetchProcedureData($component['section']['entry'] ?? null);
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
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['extension'] = $lab_result_data['organizer']['id']['extension'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['root'] = $lab_result_data['organizer']['id']['root'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_code'] = $lab_result_data['organizer']['code']['code'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_text'] = $lab_result_data['organizer']['code']['displayName'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['organizer']['effectiveTime']['value'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['status'] = $lab_result_data['organizer']['statusCode']['code'] ?? null;

                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_extension'] = $value['observation']['id']['extension'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_root'] = $value['observation']['id']['root'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_code'] = $value['observation']['code']['code'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_text'] = $value['observation']['code']['displayName'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_date'] = $value['observation']['effectiveTime']['value'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['value'] ?? null;
                    $this->templateData['field_name_value_array']['procedure_result'][$i]['results_unit'] = $value['observation']['value']['unit'] ?? null;
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

    public function fetchQrdaLabResultData($lab_result_data)
    {
        $i = 1;
        if (!empty($this->templateData['field_name_value_array']['procedure_result'])) {
            $i += count($this->templateData['field_name_value_array']['procedure_result']);
        }
        if (!empty($lab_result_data['observation']['code']['code'])) {
            $code = $this->codeService->getCodeWithType(
                $lab_result_data['observation']['code']['code'] ?? null,
                $lab_result_data['observation']['code']['codeSystemName'] ?? null,
                true
            );
            $code_text = $lab_result_data['observation']['code']['displayName'] ?? null ?: $this->codeService->lookup_code_description($code);

            $this->templateData['field_name_value_array']['procedure_result'][$i]['extension'] = $lab_result_data['observation']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['root'] = $lab_result_data['observation']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_code'] = $code ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['proc_text'] = $code_text ?? null;

            if (!empty($lab_result_data['observation']['effectiveTime']['low']['value'])) {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['observation']['effectiveTime']['low']['value'];
            } else {
                $this->templateData['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['observation']['effectiveTime']['value'] ?? null;
            }

            $this->templateData['field_name_value_array']['procedure_result'][$i]['status'] = $lab_result_data['observation']['statusCode']['code'] ?? null;

            $value = $lab_result_data['observation']['entryRelationship'];

            $code = $this->codeService->getCodeWithType(
                $value['observation']['code']['code'] ?? null,
                $value['observation']['code']['codeSystemName'] ?? null,
                true
            );
            $code_text = $value['observation']['code']['displayName'] ?? null ?: $this->codeService->lookup_code_description($code);

            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_extension'] = $value['observation']['id']['extension'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_root'] = $value['observation']['id']['root'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_code'] = $code ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_text'] = $code_text ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_date'] = $value['observation']['effectiveTime']['value'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['value'] ?? null;
            $this->templateData['field_name_value_array']['procedure_result'][$i]['results_unit'] = $value['observation']['value']['unit'] ?? null;
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
            $this->fetchVitalSignData($component['section']['entry']);
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
                '3141-9' => 'weight'
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

    public function fetchCarePlanData($care_plan_data)
    {
        if (!empty($care_plan_data['act']['code']['code'])) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['care_plan'])) {
                $i += count($this->templateData['field_name_value_array']['care_plan']);
            }
            $this->templateData['field_name_value_array']['care_plan'][$i]['extension'] = $care_plan_data['act']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['root'] = $care_plan_data['act']['templateId']['root'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code'] = $care_plan_data['act']['code']['code'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['code_text'] = $care_plan_data['act']['code']['displayName'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['description'] = $care_plan_data['act']['text'];
            $this->templateData['field_name_value_array']['care_plan'][$i]['date'] = $care_plan_data['act']['effectiveTime']['center']['value'];
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

    public function fetchFunctionalCognitiveStatusData($functional_cognitive_status_data)
    {
        if ($functional_cognitive_status_data['observation']['value']['code'] != '' && $functional_cognitive_status_data['observation']['value']['code'] != 0) {
            $i = 1;
            if (!empty($this->templateData['field_name_value_array']['functional_cognitive_status'])) {
                $i += count($this->templateData['field_name_value_array']['functional_cognitive_status']);
            }
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['extension'] = $functional_cognitive_status_data['observation']['id']['extension'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['root'] = $functional_cognitive_status_data['observation']['id']['root'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['date'] = $functional_cognitive_status_data['observation']['effectiveTime']['low']['value'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['code'] = $functional_cognitive_status_data['observation']['value']['code'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['code_text'] = $functional_cognitive_status_data['observation']['code']['displayName'];
            $this->templateData['field_name_value_array']['functional_cognitive_status'][$i]['description'] = $functional_cognitive_status_data['observation']['value']['displayName'];
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
        $this->templateData['field_name_value_array']['discharge_medication'][$i]['indication'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ? $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] : $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship']['observation']['value']['displayName'];
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
