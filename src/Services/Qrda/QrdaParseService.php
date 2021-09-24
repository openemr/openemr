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

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\CodeTypesService;

class QrdaParseService
{
    private $qrdaData;

    public function __construct()
    {
        $this->qrdaData = [];
    }

    /**
     * parsePatientDataSection
     *
     * @param array $entryComponents An array of QRDA entry templates
     * @return array
     */
    public function parsePatientDataSection($entryComponents): array
    {
        $this->is_qrda_import = true;
        $qrda_oids = array(
            //'2.16.840.1.113883.10.20.24.3.90' => '', // cda Substance or Device Allergy - Intolerance Observation (V2)
            '2.16.840.1.113883.10.20.24.3.147' => 'fetchAllergyIntoleranceObservation',
            '2.16.840.1.113883.10.20.24.3.41' => 'fetchMedicationData', // @todo medication list? active medication
            //'2.16.840.1.113883.10.20.24.3.139' => 'fetchMedicationData', //Medication Dispensed Act
            //'2.16.840.1.113883.10.20.24.3.42' => 'fetchMedicationData', // prescribe QDM Datatype: Substance, Administered
            //'2.16.840.1.113883.10.20.24.3.47' => 'fetchMedicationData', // planned QDM Datatype: Substance, Order i.e prescribed
            //'2.16.840.1.113883.10.20.22.4.3' => '', //cda concern act
            '2.16.840.1.113883.10.20.24.3.137' => 'fetchMedicalProblemData', //qrda diagnosis
            '2.16.840.1.113883.10.20.24.3.140' => 'fetchImmunizationData', // qrda Immunization Administered (V3)
            '2.16.840.1.113883.10.20.24.3.143' => '', // qrda Immunization Order (V3)
            //'2.16.840.1.113883.10.20.22.4.14' => '', // C-CDA R2.1 Procedure Activity Procedure (V2)
            '2.16.840.1.113883.10.20.24.3.64' => 'fetchProcedureData', // qrda procedure performed
            '2.16.840.1.113883.10.20.24.3.7' => 'fetchProcedureData', // qrda procedure Device Applied (V5)
            //'2.16.840.1.113883.10.20.24.3.38' => 'lab_result', // lab test preformed wip
            //'2.16.840.1.113883.10.20.24.3.37' => 'lab_result', // lab test ordered wip
            '2.16.840.1.113883.10.20.24.3.133' => 'fetchEncounter',
        );
        foreach ($entryComponents['section']['entry'] as $i => $entry) {
            $key = array_keys($entry)[0]; // need the entry type i.e. observation, activity, substance etc.
            if (!empty($entry[$key]['templateId']['root'])) {
                if (!empty($qrda_oids[$entry[$key]['templateId']['root']])) {
                    $func_name = $qrda_oids[$entry[$key]['templateId']['root']];
                    $this->$func_name($entry);
                }
            } elseif (count($entry[$key]['templateId']) > 1) {
                foreach ($entry[$key]['templateId'] as $key_1 => $value_1) {
                    if (!empty($qrda_oids[$entry[$key]['templateId'][$key_1]['root']])) {
                        $func_name = $qrda_oids[$entry[$key]['templateId'][$key_1]['root']];
                        if (!empty($func_name)) {
                            $this->$func_name($entry);
                        }
                        break;
                    }
                }
            }
        }
        return $this->qrdaData;
    }

    /**
     * @param $entry
     */
    public function fetchEncounter($entry): void
    {
        $this->getEncounterData($entry['act']['entryRelationship']);
    }

    /**
     * @param $entry
     */
    public function getEncounterData($entry): void
    {
        if ($entry['encounter']['effectiveTime']['value'] != 0 || $entry['encounter']['effectiveTime']['low']['value'] != 0) {
            $i = 1;
            if (!empty($this->qrdaData['field_name_value_array']['encounter'])) {
                $i += count($this->qrdaData['field_name_value_array']['encounter']);
            }
            $code_raw = $entry['encounter']['code']['code'];
            $code_type = $entry['encounter']['code']['codeSystemName'];
            $code_text = $entry['encounter']['code']['displayName'] ?? $entry['encounter']['text'];
            $code = (new CodeTypesService())->getCodeWithType($code_raw, $code_type, true);
            if (empty($code_text)) {
                $code_text = (new CodeTypesService())->lookup_code_description($code, 'code_text_short');
            }

            $this->qrdaData['field_name_value_array']['encounter'][$i]['extension'] = $entry['encounter']['id']['extension'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['root'] = $entry['encounter']['id']['root'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['date'] = $entry['encounter']['effectiveTime']['value'] ?: $entry['encounter']['effectiveTime']['low']['value'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['code'] = $code_raw;
            $this->qrdaData['field_name_value_array']['encounter'][$i]['code_text'] = $code_text;
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_npi'] = $entry['encounter']['performer']['assignedEntity']['id']['extension'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_name'] = $entry['encounter']['performer']['assignedEntity']['assignedPerson']['name']['given'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_address'] = $entry['encounter']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_city'] = $entry['encounter']['performer']['assignedEntity']['addr']['city'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_state'] = $entry['encounter']['performer']['assignedEntity']['addr']['state'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_postalCode'] = $entry['encounter']['performer']['assignedEntity']['addr']['postalCode'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['provider_country'] = $entry['encounter']['performer']['assignedEntity']['addr']['country'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_name'] = $entry['encounter']['participant']['participantRole']['playingEntity']['name'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_address'] = $entry['encounter']['participant']['participantRole']['addr']['streetAddressLine'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_city'] = $entry['encounter']['participant']['participantRole']['addr']['city'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_state'] = $entry['encounter']['participant']['participantRole']['addr']['state'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_zip'] = $entry['encounter']['participant']['participantRole']['addr']['postalCode'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_country'] = $entry['encounter']['participant']['participantRole']['addr']['country'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['represented_organization_telecom'] = $entry['encounter']['participant']['participantRole']['telecom'];

            $this->qrdaData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['effectiveTime']['low']['value'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_code'] = $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['code'];
            $this->qrdaData['field_name_value_array']['encounter'][$i]['encounter_diagnosis_issue'] = $entry['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['displayName'];
            $this->qrdaData['entry_identification_array']['encounter'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchMedicalProblemData($entry): void
    {
        if (!empty($entry['act']['entryRelationship']['observation']['value']['code'])) {
            $i = 1;
            if (!empty($this->qrdaData['field_name_value_array']['lists1'])) {
                $i += count($this->qrdaData['field_name_value_array']['lists1']);
            }
            $code = $entry['act']['entryRelationship']['observation']['value']['code'] ?? null;
            $code_text = $entry['act']['entryRelationship']['observation']['value']['displayName'] ?? null;
            $code_type = $entry['act']['entryRelationship']['observation']['value']['codeSystemName'] ?? null;
            $code = (new CodeTypesService())->getCodeWithType($code, $code_type, true);
            if (empty($code_text)) {
                $code_text = (new CodeTypesService())->lookup_code_description($code, 'code_text_short');
            }

            $this->qrdaData['field_name_value_array']['lists1'][$i]['type'] = 'medical_problem';
            $this->qrdaData['field_name_value_array']['lists1'][$i]['extension'] = $entry['act']['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['root'] = $entry['act']['id']['root'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['begdate'] = $entry['act']['effectiveTime']['low']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['enddate'] = $entry['act']['effectiveTime']['high']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['list_code'] = $code ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['list_code_text'] = $code_text ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['observation'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['observation_text'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists1'][$i]['status'] = $entry['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['displayName'] ?: $entry['act']['entryRelationship']['observation']['statusCode'];
            $this->qrdaData['field_name_value_array']['lists1'][$i]['modified_time'] = $entry['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->qrdaData['entry_identification_array']['lists1'][$i] = $i;
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
            if (!empty($this->qrdaData['field_name_value_array']['lists2'])) {
                $i += count($this->qrdaData['field_name_value_array']['lists2']);
            }

            $this->qrdaData['field_name_value_array']['lists2'][$i]['type'] = 'allergy';
            $this->qrdaData['field_name_value_array']['lists2'][$i]['extension'] = $entry['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['begdate'] = $entry['effectiveTime']['low']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['enddate'] = $entry['effectiveTime']['high']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['list_code'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['list_code_text'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['codeSystemName'] = $entry['observation']['participant']['participantRole']['playingEntity']['code']['codeSystemName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['outcome'] = $entry['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['severity_al_code'] = $entry['observation']['entryRelationship'][2]['observation']['value']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['severity_al'] = $entry['observation']['entryRelationship'][2]['observation']['value']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['status'] = $entry['observation']['entryRelationship'][0]['observation']['value']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['reaction'] = $entry['observation']['entryRelationship'][1]['observation']['value']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['reaction_text'] = $entry['observation']['entryRelationship'][1]['observation']['value']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists2'][$i]['modified_time'] = $entry['observation']['performer']['assignedEntity']['time']['value'] ?? null;
            $this->qrdaData['entry_identification_array']['lists2'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchMedicationData($entry): void
    {
        if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])) {
            $i = 1;
            if (!empty($this->qrdaData['field_name_value_array']['lists3'])) {
                $i += count($this->qrdaData['field_name_value_array']['lists3']);
            }

            $ctService = new CodeTypesService();
            $code_raw = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
            $code_type = 'RXCUI';
            $code = $ctService->getCodeWithType($code_raw, $code_type, true);
            $code_text = $ctService->lookup_code_description($code, 'code_text');

            $this->qrdaData['field_name_value_array']['lists3'][$i]['type'] = 'medication';
            $this->qrdaData['field_name_value_array']['lists3'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            if (empty($entry['substanceAdministration']['effectiveTime'][0]['low']['value'])) {
                $this->qrdaData['field_name_value_array']['lists3'][$i]['begdate'] = date('Y-m-d');
            } else {
                $this->qrdaData['field_name_value_array']['lists3'][$i]['begdate'] = $entry['substanceAdministration']['effectiveTime'][0]['low']['value'];
            }

            if (!empty($entry['substanceAdministration']['effectiveTime'][0]['high']['value'])) {
                $this->qrdaData['field_name_value_array']['lists3'][$i]['enddate'] = $entry['substanceAdministration']['effectiveTime'][0]['high']['value'];
            }

            $this->qrdaData['field_name_value_array']['lists3'][$i]['route'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['route_display'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['dose'] = $entry['substanceAdministration']['doseQuantity']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['dose_unit'] = $entry['substanceAdministration']['doseQuantity']['unit'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['rate'] = $entry['substanceAdministration']['rateQuantity']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['rate_unit'] = $entry['substanceAdministration']['rateQuantity']['unit'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['drug_code'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['drug_text'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'] ?? $code_text;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['note'] = $entry['substanceAdministration']['text']['reference']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['indication'] = $entry['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ?? ($entry['substanceAdministration']['entryRelationship']['observation']['value']['displayName'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['prn'] = $entry['substanceAdministration']['precondition']['criterion']['value']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['modified_time'] = $entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['time']['value'] ?? null;

            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_title'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['prefix'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['prefix'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_fname'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['given'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_lname'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['family'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['family'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_root'] = $entry['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['id']['root'] ?? null;
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_address'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_city'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['city'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['city'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_state'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['state'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['state'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_postalCode'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['postalCode'] ?? null);
            $this->qrdaData['field_name_value_array']['lists3'][$i]['provider_country'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['country']['value'] ?? ($entry['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['country'] ?? null);
            $this->qrdaData['entry_identification_array']['lists3'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchImmunizationData($entry): void
    {
        if (!empty($entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'])) {
            $i = 1;
            if (!empty($this->qrdaData['field_name_value_array']['immunization'])) {
                $i += count($this->qrdaData['field_name_value_array']['immunization']);
            }
            $this->qrdaData['field_name_value_array']['immunization'][$i]['extension'] = $entry['substanceAdministration']['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['root'] = $entry['substanceAdministration']['id']['root'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['administered_date'] = $entry['substanceAdministration']['effectiveTime']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['route_code'] = $entry['substanceAdministration']['routeCode']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['route_code_text'] = $entry['substanceAdministration']['routeCode']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['cvx_code'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['cvx_code_text'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['amount_administered'] = $entry['substanceAdministration']['doseQuantity']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['amount_administered_unit'] = $entry['substanceAdministration']['doseQuantity']['unit'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['completion_status'] = $entry['substanceAdministration']['statusCode']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['manufacturer'] = $entry['substanceAdministration']['consumable']['manufacturedProduct']['manufacturerOrganization']['name'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_npi'] = $entry['substanceAdministration']['performer']['assignedEntity']['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_name'] = $entry['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_address'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_city'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['city'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_state'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['state'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_postalCode'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_country'] = $entry['substanceAdministration']['performer']['assignedEntity']['addr']['country'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['provider_telecom'] = $entry['substanceAdministration']['performer']['assignedEntity']['telecom']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['represented_organization'] = $entry['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['name'] ?? null;
            $this->qrdaData['field_name_value_array']['immunization'][$i]['represented_organization_tele'] = $entry['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['telecom'] ?? null;
            $this->qrdaData['entry_identification_array']['immunization'][$i] = $i;
        }
    }

    /**
     * @param $entry
     */
    public function fetchProcedureData($entry): void
    {
        if (!empty($entry['procedure']['code']['code'])) {
            $i = 1;
            if (!empty($this->qrdaData['field_name_value_array']['procedure'])) {
                $i += count($this->qrdaData['field_name_value_array']['procedure']);
            }
            $this->qrdaData['field_name_value_array']['procedure'][$i]['extension'] = $entry['procedure']['id']['extension'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['root'] = $entry['procedure']['id']['root'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['code'] = $entry['procedure']['code']['code'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['code_text'] = $entry['procedure']['code']['displayName'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['codeSystemName'] = $entry['procedure']['code']['codeSystemName'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['date'] = $entry['procedure']['effectiveTime']['value'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization1'] = $entry['procedure']['performer']['assignedEntity']['representedOrganization']['name'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_address1'] = $entry['procedure']['performer']['assignedEntity']['addr']['streetAddressLine'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_city1'] = $entry['procedure']['performer']['assignedEntity']['addr']['city'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_state1'] = $entry['procedure']['performer']['assignedEntity']['addr']['state'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_postalcode1'] = $entry['procedure']['performer']['assignedEntity']['addr']['postalCode'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_country1'] = $entry['procedure']['performer']['assignedEntity']['addr']['country'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_telecom1'] = $entry['procedure']['performer']['assignedEntity']['telecom']['value'] ?? null;

            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization2'] = $entry['procedure']['participant']['participantRole']['playingEntity']['name'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_address2'] = $entry['procedure']['participant']['participantRole']['addr']['streetAddressLine'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_city2'] = $entry['procedure']['participant']['participantRole']['addr']['city'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_state2'] = $entry['procedure']['participant']['participantRole']['addr']['state'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_postalcode2'] = $entry['procedure']['participant']['participantRole']['addr']['postalCode'] ?? null;
            $this->qrdaData['field_name_value_array']['procedure'][$i]['represented_organization_country2'] = $entry['procedure']['participant']['participantRole']['addr']['country'] ?? null;
            $this->qrdaData['entry_identification_array']['procedure'][$i] = $i;
        }
    }
}
