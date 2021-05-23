<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Chandni Babu <chandnib@zhservices.com>
 * @author    Riju KP <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\XmlRpc\Generator;
use DOMDocument;
use DOMXpath;
use Document;
use CouchDB;
use Documents\Model\DocumentsTable;

class CarecoordinationTable extends AbstractTableGateway
{

    protected $ccda_data_array;

    public function __construct()
    {
        $this->ccda_data_array = [];
    }

    /*
     * Fetch the category ID using category name
     *
     * @param       $title      String      Category Name
     * @return      $records    Array       Category ID
     */
    public function fetch_cat_id($title)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM categories
                   WHERE name = ?";
        $result = $appTable->zQuery($query, array($title));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the documents uploaded by a user
     *
     * @param  user          Integer   Uploaded user ID
     * @param  time_start    Date      Uploaded start time
     * @param  time_end      Date      Uploaded end time
     *
     * @return records       Array     List of documents uploaded by the user during a particular time
     */
    public function fetch_uploaded_documents($data)
    {
        $query = "SELECT *
                   FROM categories_to_documents AS cat_doc
                   JOIN documents AS doc
                   ON doc.id = cat_doc.document_id AND doc.owner = ? AND doc.date BETWEEN ? AND ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($data['user'], $data['time_start'], $data['time_end']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * List the documents uploaded by the user alogn with the matched data
     *
     * @param    cat_title   Text    Category Name
     * @return   records     Array   List of CCDA imported to the system, pending approval
     */
    public function document_fetch($data)
    {
        $query = "SELECT am.id as amid,
                        cat.name,
                        u.fname,
                        u.lname,
                        d.imported,
                        d.size,
                        d.date,
                        d.couch_docid,
                        d.couch_revid,
                        d.url AS file_url,
                        d.id AS document_id,
                        ad.field_value,
                        ad1.field_value,
                        ad2.field_value,
                        pd.pid,
                        CONCAT(ad.field_value,' ',ad1.field_value) as pat_name,
                        DATE(ad2.field_value) as dob,
                        CONCAT_WS(' ',pd.lname, pd.fname) as matched_patient
                     FROM documents AS d
                     JOIN categories AS cat ON cat.name = ?
                     JOIN categories_to_documents AS cd ON cd.document_id = d.id AND cd.category_id = cat.id
                     LEFT JOIN audit_master AS am ON am.type = ? AND am.approval_status = '1' AND d.audit_master_id = am.id
                     LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = 'patient_data' AND ad.field_name = 'lname'
                     LEFT JOIN audit_details ad1 ON ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND ad1.field_name = 'fname'
                     LEFT JOIN audit_details ad2 ON ad2.audit_master_id = am.id AND ad2.table_name = 'patient_data' AND ad2.field_name = 'DOB'
                     LEFT JOIN patient_data pd ON pd.lname = ad.field_value AND pd.fname = ad1.field_value AND pd.DOB = DATE(ad2.field_value)
                     LEFT JOIN users AS u ON u.id = d.owner
                     WHERE d.audit_master_approval_status = 1
                     ORDER BY date DESC";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($data['cat_title'], $data['type']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the component values from the CCDA XML
     *
     * @param   $components     Array of components
     */
    public function import($xml, $document_id)
    {
        $components = $xml['component']['structuredBody']['component'];
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
            '2.16.840.1.113883.10.20.22.2.3.1' => 'lab_result',
            '2.16.840.1.113883.10.20.22.2.3' => 'lab_result',
            '2.16.840.1.113883.10.20.22.2.4.1' => 'vital_sign',
            '2.16.840.1.113883.10.20.22.2.17' => 'social_history',
            '2.16.840.1.113883.3.88.11.83.127' => 'encounter',
            '2.16.840.1.113883.10.20.22.2.22.1' => 'encounter',
            '2.16.840.1.113883.10.20.22.2.10' => 'care_plan',
            '2.16.840.1.113883.10.20.22.2.14' => 'functional_cognitive_status',
            '1.3.6.1.4.1.19376.1.5.3.1.3.1' => 'referral',
            '2.16.840.1.113883.10.20.22.2.11.1' => 'discharge_medications',
            '2.16.840.1.113883.10.20.22.2.41'   => 'discharge_summary',
        );

        for ($i = 0; $i < count($components); $i++) {
            // the original code logic assumed there was more than one indexed array with a root key...
            // however the new Laminas XML reader returns a root value
            if (!empty($components[$i]['section']['templateId']['root'])) {
                if ($components_oids[$components[$i]['section']['templateId']['root']] != '') {
                    $func_name = $components_oids[$components[$i]['section']['templateId']['root']];
                    $this->$func_name($components[$i]);
                }
            } elseif (empty($components[$i]['section']['templateId'])) {
                // uncomment for debugging information.
                // error_log("section and template id empty for position $i " . var_export($components[$i], true));
            } elseif (count($components[$i]['section']['templateId']) > 1) {
                foreach ($components[$i]['section']['templateId'] as $key_1 => $value_1) {
                    if ($components_oids[$components[$i]['section']['templateId'][$key_1]['root']] != '') {
                        $func_name = $components_oids[$components[$i]['section']['templateId'][$key_1]['root']];
                        $this->$func_name($components[$i]);
                        break;
                    }
                }
            }
        }

        $audit_master_approval_status = $this->ccda_data_array['approval_status'] = 1;
        $this->ccda_data_array['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $this->ccda_data_array['type'] = '12';

        //Patient Details
        // Collect patient name (if more than one, then get the legal one)
        if (is_array($xml['recordTarget']['patientRole']['patient']['name'])) {
            $index = 0;
            for ($i = 0; $i < count($xml['recordTarget']['patientRole']['patient']['name']); $i++) {
                if ($xml['recordTarget']['patientRole']['patient']['name'][$i]['use'] == 'L') {
                    $index = $i;
                }
            }
            $name = $xml['recordTarget']['patientRole']['patient']['name'][$index];
        } else {
            $name = $xml['recordTarget']['patientRole']['patient']['name'];
        }
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['fname'] = is_array($name['given']) ? $name['given'][0] : $name['given'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['lname'] = $name['family'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['DOB'] = $xml['recordTarget']['patientRole']['patient']['birthTime']['value'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['sex'] = $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['pubpid'] = $xml['recordTarget']['patientRole']['id'][0]['extension'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['ss'] = $xml['recordTarget']['patientRole']['id'][1]['extension'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['street'] = $xml['recordTarget']['patientRole']['addr']['streetAddressLine'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['city'] = $xml['recordTarget']['patientRole']['addr']['city'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['state'] = $xml['recordTarget']['patientRole']['addr']['state'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['postal_code'] = $xml['recordTarget']['patientRole']['addr']['postalCode'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['country_code'] = $xml['recordTarget']['patientRole']['addr']['country'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['phone_home'] = preg_replace('/[^0-9]+/i', '', $xml['recordTarget']['patientRole']['telecom']['value']);
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['status'] = $xml['recordTarget']['patientRole']['patient']['maritalStatusCode']['displayName'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['religion'] = $xml['recordTarget']['patientRole']['patient']['religiousAffiliationCode']['displayName'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['race'] = $xml['recordTarget']['patientRole']['patient']['raceCode']['displayName'];
        $this->ccda_data_array['field_name_value_array']['patient_data'][1]['ethnicity'] = $xml['recordTarget']['patientRole']['patient']['ethnicGroupCode']['displayName'];

        //Author details
        $this->ccda_data_array['field_name_value_array']['author'][1]['extension'] = $xml['author']['assignedAuthor']['id']['extension'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['address'] = $xml['author']['assignedAuthor']['addr']['streetAddressLine'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['city'] = $xml['author']['assignedAuthor']['addr']['city'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['state'] = $xml['author']['assignedAuthor']['addr']['state'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['zip'] = $xml['author']['assignedAuthor']['addr']['postalCode'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['country'] = $xml['author']['assignedAuthor']['addr']['country'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['phone'] = $xml['author']['assignedAuthor']['telecom']['value'];
        $this->ccda_data_array['field_name_value_array']['author'][1]['name'] = $xml['author']['assignedAuthor']['assignedPerson']['name']['given'];

        //Data Enterer
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['extension'] = $xml['dataEnterer']['assignedEntity']['id']['extension'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['address'] = $xml['dataEnterer']['assignedEntity']['addr']['streetAddressLine'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['city'] = $xml['dataEnterer']['assignedEntity']['addr']['city'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['state'] = $xml['dataEnterer']['assignedEntity']['addr']['state'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['zip'] = $xml['dataEnterer']['assignedEntity']['addr']['postalCode'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['country'] = $xml['dataEnterer']['assignedEntity']['addr']['country'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['phone'] = $xml['dataEnterer']['assignedEntity']['telecom']['value'];
        $this->ccda_data_array['field_name_value_array']['dataEnterer'][1]['name'] = $xml['dataEnterer']['assignedEntity']['assignedPerson']['name']['given'];

        //Informant
        $this->ccda_data_array['field_name_value_array']['informant'][1]['extension'] = $xml['informant'][0]['assignedEntity']['id']['extension'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['street'] = $xml['informant'][0]['assignedEntity']['addr']['streetAddressLine'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['city'] = $xml['informant'][0]['assignedEntity']['addr']['city'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['state'] = $xml['informant'][0]['assignedEntity']['addr']['state'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['postalCode'] = $xml['informant'][0]['assignedEntity']['addr']['postalCode'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['country'] = $xml['informant'][0]['assignedEntity']['addr']['country'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['phone'] = $xml['informant'][0]['assignedEntity']['telecom']['value'];
        $this->ccda_data_array['field_name_value_array']['informant'][1]['name'] = $xml['informant'][0]['assignedEntity']['assignedPerson']['name']['given'];

        //Personal Informant
        $this->ccda_data_array['field_name_value_array']['custodian'][1]['extension'] = $xml['custodian']['assignedCustodian']['representedCustodianOrganization']['id']['extension'];
        $this->ccda_data_array['field_name_value_array']['custodian'][1]['organisation'] = $xml['custodian']['assignedCustodian']['representedCustodianOrganization']['name'];

        //documentationOf
        $doc_of_str = '';
        if (!is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['prefix'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['prefix'] . " ";
        }

        if (!is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['given'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['given'] . " ";
        }

        if (!is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['family'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['family'] . " ";
        }

        if (!is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['representedOrganization']['name'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['representedOrganization']['name'] . " ";
        }

        $this->ccda_data_array['field_name_value_array']['documentationOf'][1]['assignedPerson'] = $doc_of_str;

        $documentationOf = $this->ccda_data_array['field_name_value_array']['documentationOf'][1]['assignedPerson'];

        $audit_master_id = \Application\Plugin\CommonPlugin::insert_ccr_into_audit_data($this->ccda_data_array);
        $this->update_document_table($document_id, $audit_master_id, $audit_master_approval_status, $documentationOf);
    }

    public function update_document_table($document_id, $audit_master_id, $audit_master_approval_status, $documentationOf)
    {
        $appTable = new ApplicationTable();
        $query = "UPDATE documents
              SET audit_master_id = ?,
                  imported = ?,
                  audit_master_approval_status=?,
                  documentationOf=?
              WHERE id = ?";
        $appTable->zQuery($query, array($audit_master_id,
            1,
            $audit_master_approval_status,
            $documentationOf,
            $document_id));
    }

    public function allergy($component)
    {
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_allergy_value($value);
            }
        } else {
            $this->fetch_allergy_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_allergy_value($allergy_array)
    {
        if (
            $allergy_array['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['code'] != ''
            && $allergy_array['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['code'] != 0
        ) {
            $i = 1;
            // if there are already items here we want to add to them.
            if (!empty($this->ccda_data_array['field_name_value_array']['lists2'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['lists2']);
            }

            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['type'] = 'allergy';
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['extension'] = $allergy_array['act']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['begdate'] = $allergy_array['act']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['enddate'] = $allergy_array['act']['effectiveTime']['high']['value'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['list_code'] = $allergy_array['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['list_code_text'] = $allergy_array['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['codeSystemName'] = $allergy_array['act']['entryRelationship']['observation']['participant']['participantRole']['playingEntity']['code']['codeSystemName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['outcome'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['severity_al_code'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['severity_al'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['status'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][0]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['reaction'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['reaction_text'] = $allergy_array['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists2'][$i]['modified_time'] = $allergy_array['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'];
            $this->ccda_data_array['entry_identification_array']['lists2'][$i] = $i;
            unset($allergy_array);
            return;
        }
    }

    public function medication($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_medication_value($value);
            }
        } else {
            $this->fetch_medication_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_medication_value($medication_data)
    {
        if ($medication_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] != '' && $medication_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['lists3'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['lists3']);
            }

            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['type'] = 'medication';
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['extension'] = $medication_data['substanceAdministration']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['root'] = $medication_data['substanceAdministration']['id']['root'];
            if ($medication_data['substanceAdministration']['effectiveTime'][0]['low']['value'] == '') {
                $this->ccda_data_array['field_name_value_array']['lists3'][$i]['begdate'] = date('Y-m-d');
            } else {
                $this->ccda_data_array['field_name_value_array']['lists3'][$i]['begdate'] = $medication_data['substanceAdministration']['effectiveTime'][0]['low']['value'];
            }

            if ($medication_data['substanceAdministration']['effectiveTime'][0]['high']['value']) {
                $this->ccda_data_array['field_name_value_array']['lists3'][$i]['enddate'] = $medication_data['substanceAdministration']['effectiveTime'][0]['high']['value'];
            }

            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['route'] = $medication_data['substanceAdministration']['routeCode']['code'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['route_display'] = $medication_data['substanceAdministration']['routeCode']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['dose'] = $medication_data['substanceAdministration']['doseQuantity']['value'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['dose_unit'] = $medication_data['substanceAdministration']['doseQuantity']['unit'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['rate'] = $medication_data['substanceAdministration']['rateQuantity']['value'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['rate_unit'] = $medication_data['substanceAdministration']['rateQuantity']['unit'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['drug_code'] = $medication_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['drug_text'] = $medication_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['note'] = $medication_data['substanceAdministration']['text']['reference']['value'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['indication'] = $medication_data['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ? $medication_data['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] : $medication_data['substanceAdministration']['entryRelationship']['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['prn'] = $medication_data['substanceAdministration']['precondition']['criterion']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['modified_time'] = $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['author']['time']['value'];

            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_title'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['prefix'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['prefix'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['prefix'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_fname'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['given'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_lname'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['family'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['family'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['family'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_root'] = $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_address'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_city'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['city'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['city'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_state'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['state'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['state'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_postalCode'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['lists3'][$i]['provider_country'] = $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['country']['value'] ? $medication_data['substanceAdministration']['performer']['assignedEntity']['addr']['country']['value'] : $medication_data['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['country'];
            $this->ccda_data_array['entry_identification_array']['lists3'][$i] = $i;
            unset($medication_data);
            return;
        }
    }

    public function medical_problem($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_medical_problem_value($value);
            }
        } else {
            $this->fetch_medical_problem_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_medical_problem_value($medical_problem_data)
    {
        if ($medical_problem_data['act']['entryRelationship']['observation']['value']['code'] != '' && $medical_problem_data['act']['entryRelationship']['observation']['value']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['lists1'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['lists1']);
            }
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['type'] = 'medical_problem';
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['extension'] = $medical_problem_data['act']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['root'] = $medical_problem_data['act']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['begdate'] = $medical_problem_data['act']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['enddate'] = $medical_problem_data['act']['effectiveTime']['high']['value'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['list_code'] = $medical_problem_data['act']['entryRelationship']['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['list_code_text'] = $medical_problem_data['act']['entryRelationship']['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['observation'] = $medical_problem_data['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['observation_text'] = $medical_problem_data['act']['entryRelationship']['observation']['entryRelationship'][1]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['status'] = $medical_problem_data['act']['entryRelationship']['observation']['entryRelationship'][2]['observation']['value']['displayName'];
            $this->ccda_data_array['field_name_value_array']['lists1'][$i]['modified_time'] = $medical_problem_data['act']['entryRelationship']['observation']['performer']['assignedEntity']['time']['value'];
            $this->ccda_data_array['entry_identification_array']['lists1'][$i] = $i;
            unset($medical_problem_data);
            return;
        }
    }

    public function immunization($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_immunization_value($value);
            }
        } else {
            $this->fetch_immunization_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_immunization_value($immunization_data)
    {
        if ($immunization_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] != '' && $immunization_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['immunization'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['immunization']);
            }
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['extension'] = $immunization_data['substanceAdministration']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['root'] = $immunization_data['substanceAdministration']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['administered_date'] = $immunization_data['substanceAdministration']['effectiveTime']['value'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['route_code'] = $immunization_data['substanceAdministration']['routeCode']['code'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['route_code_text'] = $immunization_data['substanceAdministration']['routeCode']['displayName'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['cvx_code'] = $immunization_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['cvx_code_text'] = $immunization_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['amount_administered'] = $immunization_data['substanceAdministration']['doseQuantity']['value'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['amount_administered_unit'] = $immunization_data['substanceAdministration']['doseQuantity']['unit'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['completion_status'] = $immunization_data['substanceAdministration']['statusCode']['code'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['manufacturer'] = $immunization_data['substanceAdministration']['consumable']['manufacturedProduct']['manufacturerOrganization']['name'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_npi'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_name'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['assignedPerson']['name']['given'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_address'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_city'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_state'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_postalCode'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_country'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['addr']['country'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['provider_telecom'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['telecom']['value'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['represented_organization'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['name'];
            $this->ccda_data_array['field_name_value_array']['immunization'][$i]['represented_organization_tele'] = $immunization_data['substanceAdministration']['performer']['assignedEntity']['representedOrganization']['telecom'];
            $this->ccda_data_array['entry_identification_array']['immunization'][$i] = $i;
            unset($immunization_data);
            return;
        }
    }

    public function procedure($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                if ($key % 3 != 0) {
                    continue; //every third entry section has the procedure details
                }

                $this->fetch_procedure_value($value);
            }
        } else {
            $this->fetch_procedure_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_procedure_value($procedure_data)
    {
        if ($procedure_data['procedure']['code']['code'] != '' && $procedure_data['procedure']['code']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['procedure'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['procedure']);
            }
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['extension'] = $procedure_data['procedure']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['root'] = $procedure_data['procedure']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['code'] = $procedure_data['procedure']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['code_text'] = $procedure_data['procedure']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['codeSystemName'] = $procedure_data['procedure']['code']['codeSystemName'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['date'] = $procedure_data['procedure']['effectiveTime']['value'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization1'] = $procedure_data['procedure']['performer']['assignedEntity']['representedOrganization']['name'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_address1'] = $procedure_data['procedure']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_city1'] = $procedure_data['procedure']['performer']['assignedEntity']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_state1'] = $procedure_data['procedure']['performer']['assignedEntity']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_postalcode1'] = $procedure_data['procedure']['performer']['assignedEntity']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_country1'] = $procedure_data['procedure']['performer']['assignedEntity']['addr']['country'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_telecom1'] = $procedure_data['procedure']['performer']['assignedEntity']['telecom']['value'];

            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization2'] = $procedure_data['procedure']['participant']['participantRole']['playingEntity']['name'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_address2'] = $procedure_data['procedure']['participant']['participantRole']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_city2'] = $procedure_data['procedure']['participant']['participantRole']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_state2'] = $procedure_data['procedure']['participant']['participantRole']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_postalcode2'] = $procedure_data['procedure']['participant']['participantRole']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['procedure'][$i]['represented_organization_country2'] = $procedure_data['procedure']['participant']['participantRole']['addr']['country'];
            $this->ccda_data_array['entry_identification_array']['procedure'][$i] = $i;
            unset($procedure_data);
            return;
        }
    }

    public function lab_result($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_lab_result_value($value);
            }
        } else {
            $this->fetch_lab_result_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_lab_result_value($lab_result_data)
    {
        $i = 1;
        if (!empty($this->ccda_data_array['field_name_value_array']['procedure_result'])) {
            $i += count($this->ccda_data_array['field_name_value_array']['procedure_result']);
        }
        foreach ($lab_result_data['organizer']['component'] as $key => $value) {
            if ($value['observation']['code']['code']) {
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['extension'] = $lab_result_data['organizer']['id']['extension'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['root'] = $lab_result_data['organizer']['id']['root'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['proc_code'] = $lab_result_data['organizer']['code']['code'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['proc_text'] = $lab_result_data['organizer']['code']['displayName'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['date'] = $lab_result_data['organizer']['effectiveTime']['value'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['status'] = $lab_result_data['organizer']['statusCode']['code'];

                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_extension'] = $value['observation']['id']['extension'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_root'] = $value['observation']['id']['root'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_code'] = $value['observation']['code']['code'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_text'] = $value['observation']['code']['displayName'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_date'] = $value['observation']['effectiveTime']['value'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_value'] = $value['observation']['value']['value'];
                $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_unit'] = $value['observation']['value']['unit'];
                if ($value['observation']['referenceRange']['observationRange']['text']) {
                    $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_range'] = $value['observation']['referenceRange']['observationRange']['text'];
                } else {
                    $this->ccda_data_array['field_name_value_array']['procedure_result'][$i]['results_range'] = $value['observation']['referenceRange']['observationRange']['value']['low']['value'] . '-' . $value['observation']['referenceRange']['observationRange']['value']['high']['value'] . ' ' . $value['observation']['referenceRange']['observationRange']['value']['low']['unit'];
                }

                $this->ccda_data_array['entry_identification_array']['procedure_result'][$i] = $i;
                $i++;
            }
        }

            unset($lab_result_data);
            return;
    }

    public function vital_sign($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_vital_sign_value($value);
            }
        } else {
            $this->fetch_vital_sign_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_vital_sign_value($vital_sign_data)
    {
        if ($vital_sign_data['organizer']['component'][0]['observation']['effectiveTime']['value'] != '' && $vital_sign_data['organizer']['component'][0]['observation']['effectiveTime']['value'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['vital_sign'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['vital_sign']);
            }
            $this->ccda_data_array['field_name_value_array']['vital_sign'][$i]['extension'] = $vital_sign_data['organizer']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['vital_sign'][$i]['root'] = $vital_sign_data['organizer']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['vital_sign'][$i]['date'] = $vital_sign_data['organizer']['component'][0]['observation']['effectiveTime']['value'];
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
                $code = $vital_sign_data['organizer']['component'][$j]['observation']['code']['code'];
                if ($vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship']) {
                    $this->ccda_data_array['field_name_value_array']['vital_sign'][$i]['bps'] = $vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship'][0]['observation']['value']['value'];
                    $this->ccda_data_array['field_name_value_array']['vital_sign'][$i]['bpd'] = $vital_sign_data['organizer']['component'][$j]['observation']['entryRelationship'][1]['observation']['value']['value'];
                } else {
                    $this->ccda_data_array['field_name_value_array']['vital_sign'][$i][$vitals_array[$code]] = $vital_sign_data['organizer']['component'][$j]['observation']['value']['value'];
                }
            }

            $this->ccda_data_array['entry_identification_array']['vital_sign'][$i] = $i;
            unset($vital_sign_data);
            return;
        }
    }

    public function social_history($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_social_history_value($value);
            }
        } else {
            $this->fetch_social_history_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_social_history_value($social_history_data)
    {
        if ($social_history_data['observation']['value']['code'] != '' && $social_history_data['observation']['value']['code'] != 0) {
            $social_history_array = array(
            '2.16.840.1.113883.10.20.22.4.78' => 'smoking'
            );
            $i = 0;
            $code = $social_history_data['observation']['templateId']['root'];
            if (!empty($this->ccda_data_array['field_name_value_array']['social_history'])) {
                foreach ($this->ccda_data_array['field_name_value_array']['social_history'] as $key => $value) {
                    if (!array_key_exists($social_history_array[$code], $value)) {
                        $i = $key;
                    } else {
                        $i = count($this->ccda_data_array['field_name_value_array']['social_history']) + 1;
                    }
                }
            }

            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['extension'] = $social_history_data['observation']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['root'] = $social_history_data['observation']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['status'] = $social_history_data['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['begdate'] = $social_history_data['observation']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['enddate'] = $social_history_data['observation']['effectiveTime']['high']['value'];
            $this->ccda_data_array['field_name_value_array']['social_history'][$i][$social_history_array[$code]]['value'] = $social_history_data['observation']['value']['displayName'];
            $this->ccda_data_array['entry_identification_array']['social_history'][$i] = $i;
            unset($social_history_data);
            return;
        }
    }

    public function encounter($component)
    {
            $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_encounter_value($value);
            }
        } else {
            $this->fetch_encounter_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_encounter_value($encounter_data)
    {
        if ($encounter_data['encounter']['effectiveTime']['value'] != 0 || $encounter_data['encounter']['effectiveTime']['low']['value'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['encounter'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['encounter']);
            }
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['extension'] = $encounter_data['encounter']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['root'] = $encounter_data['encounter']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['date'] = $encounter_data['encounter']['effectiveTime']['value'] ? $encounter_data['encounter']['effectiveTime']['value'] : $encounter_data['encounter']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['code'] = $encounter_data['encounter']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['code_text'] = $encounter_data['encounter']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_npi'] = $encounter_data['encounter']['performer']['assignedEntity']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_name'] = $encounter_data['encounter']['performer']['assignedEntity']['assignedPerson']['name']['given'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_address'] = $encounter_data['encounter']['performer']['assignedEntity']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_city'] = $encounter_data['encounter']['performer']['assignedEntity']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_state'] = $encounter_data['encounter']['performer']['assignedEntity']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_postalCode'] = $encounter_data['encounter']['performer']['assignedEntity']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['provider_country'] = $encounter_data['encounter']['performer']['assignedEntity']['addr']['country'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_name'] = $encounter_data['encounter']['participant']['participantRole']['playingEntity']['name'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_address'] = $encounter_data['encounter']['participant']['participantRole']['addr']['streetAddressLine'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_city'] = $encounter_data['encounter']['participant']['participantRole']['addr']['city'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_state'] = $encounter_data['encounter']['participant']['participantRole']['addr']['state'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_zip'] = $encounter_data['encounter']['participant']['participantRole']['addr']['postalCode'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_country'] = $encounter_data['encounter']['participant']['participantRole']['addr']['country'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['represented_organization_telecom'] = $encounter_data['encounter']['participant']['participantRole']['telecom'];

            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['encounter_diagnosis_date'] = $encounter_data['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['encounter_diagnosis_code'] = $encounter_data['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['encounter'][$i]['encounter_diagnosis_issue'] = $encounter_data['encounter']['entryRelationship'][1]['act']['entryRelationship']['observation']['value']['displayName'];
            $this->ccda_data_array['entry_identification_array']['encounter'][$i] = $i;
            unset($encounter_data);
            return;
        }
    }

    public function care_plan($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_care_plan_value($value);
            }
        } else {
            $this->fetch_care_plan_value($component['section']['entry']);
        }

        unset($component);
        return;
    }

    public function fetch_care_plan_value($care_plan_data)
    {
        if ($care_plan_data['act']['code']['code'] != '' && $care_plan_data['act']['code']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['care_plan'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['care_plan']);
            }
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['extension'] = $care_plan_data['act']['templateId']['root'];
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['root'] = $care_plan_data['act']['templateId']['root'];
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['code'] = $care_plan_data['act']['code']['code'];
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['code_text'] = $care_plan_data['act']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['description'] = $care_plan_data['act']['text'];
            $this->ccda_data_array['field_name_value_array']['care_plan'][$i]['date'] = $care_plan_data['act']['effectiveTime']['center']['value'];
            $this->ccda_data_array['entry_identification_array']['care_plan'][$i] = $i;
            unset($care_plan_data);
            return;
        }
    }

    public function functional_cognitive_status($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_functional_cognitive_status_value($value);
            }
        } else {
            $this->fetch_functional_cognitive_status_value($component['section']['entry']);
        }

        unset($component);
        return;
    }

    public function fetch_functional_cognitive_status_value($functional_cognitive_status_data)
    {
        if ($functional_cognitive_status_data['observation']['value']['code'] != '' && $functional_cognitive_status_data['observation']['value']['code'] != 0) {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['functional_cognitive_status'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['functional_cognitive_status']);
            }
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['extension'] = $functional_cognitive_status_data['observation']['id']['extension'];
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['root'] = $functional_cognitive_status_data['observation']['id']['root'];
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['date'] = $functional_cognitive_status_data['observation']['effectiveTime']['low']['value'];
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['code'] = $functional_cognitive_status_data['observation']['value']['code'];
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['code_text'] = $functional_cognitive_status_data['observation']['code']['displayName'];
            $this->ccda_data_array['field_name_value_array']['functional_cognitive_status'][$i]['description'] = $functional_cognitive_status_data['observation']['value']['displayName'];
            $this->ccda_data_array['entry_identification_array']['functional_cognitive_status'][$i] = $i;
            unset($functional_cognitive_status_data);
            return;
        }
    }

    public function referral($component)
    {
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_referral_value($value);
            }
        } else {
            $this->fetch_referral_value($component['section']);
        }

        unset($component);
        return;
    }

    public function fetch_referral_value($referral_data)
    {
        if (!empty($referral_data['text']['paragraph']) && is_array($referral_data['text']['paragraph'])) {
              $i = 1;
            foreach ($referral_data['text']['paragraph'] as $key => $value) {
                if ($value) {
                    $this->ccda_data_array['field_name_value_array']['referral'][$i]['body'] = preg_replace("/\s+/", " ", $value);
                    $this->ccda_data_array['entry_identification_array']['referral'][$i]     = $i;
                    $i++;
                }
            }
        } else {
            $i = 1;
            if (!empty($this->ccda_data_array['field_name_value_array']['referral'])) {
                $i += count($this->ccda_data_array['field_name_value_array']['referral']);
            }
            $this->ccda_data_array['field_name_value_array']['referral'][$i]['root'] = $referral_data['templateId']['root'];
            $this->ccda_data_array['field_name_value_array']['referral'][$i]['body'] = (!empty($referral_data['text']['paragraph'])) ? preg_replace("/\s+/", " ", $referral_data['text']['paragraph']) : '';

            $this->ccda_data_array['entry_identification_array']['referral'][$i]          = $i;
            unset($referral_data);
        }

        return;
    }

    public function discharge_medications($component)
    {
        $component['section']['text'] = '';
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_discharge_medications_value($value);
            }
        } else {
            $this->fetch_discharge_medications_value($component['section']['entry']);
        }

            unset($component);
            return;
    }

    public function fetch_discharge_medications_value($discharge_medications_data)
    {
        $i = 1;
        if (!empty($this->ccda_data_array['field_name_value_array']['discharge_medication'])) {
            $i += count($this->ccda_data_array['field_name_value_array']['discharge_medication']);
        }
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['extension']           = $discharge_medications_data['act']['id']['extension'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['root']                = $discharge_medications_data['act']['id']['root'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['begdate']             = $discharge_medications_data['act']['effectiveTime']['low']['value'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['enddate']             = $discharge_medications_data['act']['effectiveTime']['high']['value'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['route']               = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['routeCode']['code'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['route_display']       = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['routeCode']['displayName'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['dose']                = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['doseQuantity']['value'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['dose_unit']           = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['doseQuantity']['unit'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['rate']                = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['rateQuantity']['value'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['rate_unit']           = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['rateQuantity']['unit'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['drug_code']           = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['code'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['drug_text']           = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['consumable']['manufacturedProduct']['manufacturedMaterial']['code']['displayName'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['note']                = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['text']['reference']['value'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['indication']          = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] ? $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][0]['observation']['value']['displayName'] : $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship']['observation']['value']['displayName'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['prn']                 = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['precondition']['criterion']['value']['displayName'];

                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_title']      = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['prefix'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_fname']      = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['given'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_lname']      = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['assignedPerson']['name']['family'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_root']       = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['author']['assignedAuthor']['id']['root'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_address']    = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['streetAddressLine'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_city']       = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['city'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_state']      = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['state'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_postalCode'] = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['postalCode'];
                $this->ccda_data_array['field_name_value_array']['discharge_medication'][$i]['provider_country']    = $discharge_medications_data['act']['entryRelationship']['substanceAdministration']['entryRelationship'][1]['supply']['performer']['assignedEntity']['addr']['country'];
                $this->ccda_data_array['entry_identification_array']['discharge_medication'][$i]                    = $i;
                unset($discharge_medications_data);
                return;
    }

    public function discharge_summary($component)
    {
        if ($component['section']['entry'][0]) {
            foreach ($component['section']['entry'] as $key => $value) {
                $this->fetch_discharge_summary_value($value);
            }
        } else {
            $this->fetch_discharge_summary_value($component['section']);
        }

        unset($component);
        return;
    }

    public function fetch_discharge_summary_value($discharge_summary_data)
    {
        $i = 1;
        if (!empty($this->ccda_data_array['field_name_value_array']['discharge_summary'])) {
            $i += count($this->ccda_data_array['field_name_value_array']['discharge_summary']);
        }
        $this->ccda_data_array['field_name_value_array']['discharge_summary'][$i]['root'] = $discharge_summary_data['templateId']['root'];
                $text =  preg_replace("/\s+/", " ", $discharge_summary_data['text']['content']);
        for ($j = 0; $j < count($discharge_summary_data['text']['list']['item']); $j++) {
            if (is_array($discharge_summary_data['text']['list']['item'][$j])) {
                for ($k = 0; $k < count($discharge_summary_data['text']['list']['item'][$j]['list']['item']); $k++) {
                    $text .= "#$%" . preg_replace("/\s+/", " ", $discharge_summary_data['text']['list']['item'][$j]['list']['item'][$k]);
                }
            } else {
                $text .= "#$%" . preg_replace("/\s+/", " ", $discharge_summary_data['text']['list']['item'][$j]);
            }
        }

        $this->ccda_data_array['field_name_value_array']['discharge_summary'][$i]['text'] = $text;

                $this->ccda_data_array['entry_identification_array']['discharge_summary'][$i]     = $i;
        unset($discharge_summary_data);
        return;
    }
    /*
     * Fetch a document from the database
     *
     * @param   $document_id        Integer     Document ID
     * @return  $content        String      File content
     */
    public function getDocument($document_id)
    {
        $content = \Documents\Plugin\Documents::getDocument($document_id);
        return $content;
    }

    public function getCategory()
    {
        $doc_obj = new DocumentsTable();
        $category = $doc_obj->getCategory();
        return $category;
    }

    public function getIssues($pid)
    {
        $doc_obj = new DocumentsTable();
        $issues = $doc_obj->getIssues($pid);
        return $issues;
    }

    public function getCategoryIDs()
    {
        $doc_obj = new DocumentsTable();
        $result = implode("|", $doc_obj->getCategoryIDs(array('CCD', 'CCR', 'CCDA')));
        return $result;
    }

    /*
     * Fetch the demographics data from audit tables
     *
     * @param    audit_master_id   Integer   ID from audit master table
     * @return   records           Array     Demographics data
     */
    public function getDemographics($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT ad.id as adid,
                          table_name,
                          field_name,
                          field_value
                   FROM audit_master am
                   JOIN audit_details ad ON ad.audit_master_id = am.id
                   WHERE am.id = ? AND ad.table_name = 'patient_data'
                   ORDER BY ad.id";
        $result = $appTable->zQuery($query, array($data['audit_master_id']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current demographics data of a patient from patient_data table
     *
     * @param    pid       Integer   Patient ID
     * @return   records   Array     current patient data
     */
    public function getDemographicsOld($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM patient_data
                   WHERE pid = ?";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Problems of a patient from lists table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of problems
     */
    public function getProblems($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'medical_problem'";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Allergies of a patient from lists table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of allergies
     */
    public function getAllergies($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'allergy'";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Medications of a patient from prescriptions table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of medications
     */
    public function getMedications($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM prescriptions
                   WHERE patient_id = ?";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Immunizations of a patient from immunizations table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of immunizations
     */
    public function getImmunizations($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM immunizations
                   WHERE patient_id = ?"; //removed the field 'added_erroneously' from where condition
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the currect Lab Results of a patient
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of lab results
     */
    public function getLabResults($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT CONCAT_WS('',po.procedure_order_id,poc.`procedure_order_seq`) AS tcode,
                          prs.result AS result_value,
                          prs.units, prs.range,
                          poc.procedure_name AS order_title,
                          prs.result_code as result_code,
                          prs.result_text as result_desc,
                          po.date_ordered,
                          prs.date AS result_time,
                          prs.abnormal AS abnormal_flag,
                          prs.procedure_result_id AS result_id
                   FROM procedure_order AS po
                   JOIN procedure_order_code AS poc ON poc.`procedure_order_id`=po.`procedure_order_id`
                   JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
                        AND pr.`procedure_order_seq`=poc.`procedure_order_seq`
                   JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id
                   WHERE po.patient_id = ? AND prs.result NOT IN ('DNR','TNP')";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Vitals of a patient from form_vitals table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of vitals
     */
    public function getVitals($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_vitals
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($data['pid'], 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the social history of a patient from history_data table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       history data
     */
    public function getSocialHistory($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM history_data
                   WHERE pid=?
                   ORDER BY id DESC LIMIT 1";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the encounter data of a patient from form_encounter table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       encounter data
     */
    public function getEncounterData($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT form_encounter.*,u.fname AS provider_name
                   FROM form_encounter
                   LEFT JOIN users AS u
                   ON form_encounter.provider_id=u.id
                   WHERE pid = ?";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the billing data of a patient from billing table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       billing data
     */
    public function getProcedure($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                 FROM billing
                 WHERE pid=? AND code_type=?";
        $result = $appTable->zQuery($query, array($data['pid'], 'CPT4'));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Care Plan of a patient from form_care_paln table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of Care Plans
     */
    public function getCarePlan($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_care_plan
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($data['pid'], 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Functional Cognitive Status of a patient from form_functional_cognitive_status table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of Functional Cognitive Status
     */
    public function getFunctionalCognitiveStatus($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_functional_cognitive_status
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($data['pid'], 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the data from audit tables
     *
     * @param    am_id         integer     audit master ID
     * @param    table_name    string      identifier inserted for each table (eg: prescriptions, list1 ...)
     */
    public function createAuditArray($am_id, $table_name)
    {
        $appTable = new ApplicationTable();
        if (strpos($table_name, ',')) {
            $tables = explode(',', $table_name);
            $arr = array($am_id);
            $table_qry = "";
            for ($i = 0; $i < count($tables); $i++) {
                $table_qry .= "?,";
                array_unshift($arr, $tables[$i]);
            }

            $table_qry = substr($table_qry, 0, -1);
            $query = "SELECT *
                     FROM audit_master am
                     JOIN audit_details ad
                     ON ad.audit_master_id = am.id
                     AND ad.table_name IN ($table_qry)
                     WHERE am.id = ? AND am.type = 12 AND am.approval_status = 1
                     ORDER BY ad.entry_identification,ad.field_name";
            $result = $appTable->zQuery($query, $arr);
        } else {
            $query = "SELECT *
                       FROM audit_master am
                       JOIN audit_details ad
                       ON ad.audit_master_id = am.id
                       AND ad.table_name = ?
                       WHERE am.id = ? AND am.type = 12 AND am.approval_status = 1
                       ORDER BY ad.entry_identification,ad.field_name";
            $result = $appTable->zQuery($query, array($table_name, $am_id));
        }

        $records = array();
        foreach ($result as $res) {
            $records[$table_name][$res['entry_identification']][$res['field_name']] = $res['field_value'];
        }

        return $records;
    }

    public function getListTitle(string $option_id = null, $list_id, $codes = '')
    {
        $appTable = new ApplicationTable();
        if ($option_id) {
            $query = "SELECT title
                  FROM list_options
                  WHERE list_id=? AND option_id=? AND activity=?";
            $result = $appTable->zQuery($query, array($list_id, $option_id, 1));
            $res_cur = $result->current();
        }

        if ($codes) {
            $query = "SELECT title
                  FROM list_options
                  WHERE list_id=? AND (codes=? OR option_id=?) AND activity=?";
            $result = $appTable->zQuery($query, array($list_id, $codes, $option_id, 1));
            $res_cur = $result->current();
        }

        return $res_cur['title'];
    }

    public function insertApprovedData($data)
    {
        $appTable = new ApplicationTable();
        $patient_data_fields = '';
        $patient_data_values = array();
        $j = 1;
        $y = 1;
        $k = 1;
        $q = 1;
        $a = 1;
        $b = 1;
        $c = 1;
        $d = 1;
        $e = 1;
        $f = 1;
        $g = 1;

        $arr_procedure_res = array();
        $arr_procedures = array();
        $arr_vitals = array();
        $arr_encounter = array();
        $arr_immunization = array();
        $arr_prescriptions = array();
        $arr_allergies = array();
        $arr_med_pblm = array();
        $arr_care_plan = array();
        $arr_functional_cognitive_status = array();
        $arr_referral = array();

        $p1_arr = explode("||", $data['problem1check']);
        $p2_arr = explode('||', $data['problem2check']);
        $p3_arr = explode('||', $data['problem3check']);
        $a1_arr = explode("||", $data['allergy1check']);
        $a2_arr = explode('||', $data['allergy2check']);
        $a3_arr = explode('||', $data['allergy3check']);
        $m1_arr = explode("||", $data['med1check']);
        $m2_arr = explode('||', $data['med2check']);
        $m3_arr = explode('||', $data['med3check']);

        foreach ($data as $key => $val) {
            if (substr($key, -4) == '-sel') {
                if (is_array($val)) {
                    for ($i = 0; $i < count($val); $i++) {
                        if ($val[$i] == 'insert') {
                            if (substr($key, 0, -4) == 'immunization') {
                                $arr_immunization['immunization'][$a]['extension'] = $data['immunization-extension'][$i];
                                $arr_immunization['immunization'][$a]['root'] = $data['immunization-root'][$i];
                                $arr_immunization['immunization'][$a]['administered_date'] = $data['immunization-administered_date'][$i];
                                $arr_immunization['immunization'][$a]['route_code'] = $data['immunization-route_code'][$i];
                                $arr_immunization['immunization'][$a]['route_code_text'] = $data['immunization-route_code_text'][$i];
                                $arr_immunization['immunization'][$a]['cvx_code'] = $data['immunization-cvx_code'][$i];
                                $arr_immunization['immunization'][$a]['cvx_code_text'] = $data['immunization-cvx_code_text'][$i];
                                $arr_immunization['immunization'][$a]['amount_administered'] = $data['immunization-amount_administered'][$i];
                                $arr_immunization['immunization'][$a]['amount_administered_unit'] = $data['immunization-amount_administered_unit'][$i];
                                $arr_immunization['immunization'][$a]['manufacturer'] = $data['immunization-manufacturer'][$i];
                                $arr_immunization['immunization'][$a]['completion_status'] = $data['immunization-completion_status'][$i];

                                $arr_immunization['immunization'][$a]['provider_npi'] = $data['immunization-provider_npi'][$i];
                                $arr_immunization['immunization'][$a]['provider_name'] = $data['immunization-provider_name'][$i];
                                $arr_immunization['immunization'][$a]['provider_address'] = $data['immunization-provider_address'][$i];
                                $arr_immunization['immunization'][$a]['provider_city'] = $data['immunization-provider_city'][$i];
                                $arr_immunization['immunization'][$a]['provider_state'] = $data['immunization-provider_state'][$i];
                                $arr_immunization['immunization'][$a]['provider_postalCode'] = $data['immunization-provider_postalCode'][$i];
                                $arr_immunization['immunization'][$a]['provider_country'] = $data['immunization-provider_country'][$i];
                                $arr_immunization['immunization'][$a]['provider_telecom'] = $data['immunization-provider_telecom'][$i];
                                $arr_immunization['immunization'][$a]['represented_organization'] = $data['immunization-represented_organization'][$i];
                                $arr_immunization['immunization'][$a]['represented_organization_tele'] = $data['immunization-represented_organization_tele'][$i];
                                $a++;
                            } elseif (substr($key, 0, -4) == 'lists3') {
                                $arr_prescriptions['lists3'][$b]['extension'] = $data['lists3-extension'][$i];
                                $arr_prescriptions['lists3'][$b]['root'] = $data['lists3-root'][$i];
                                $arr_prescriptions['lists3'][$b]['begdate'] = $data['lists3-date_added'][$i];
                                $arr_prescriptions['lists3'][$b]['enddate'] = $data['lists3-enddate'][$i];
                                $arr_prescriptions['lists3'][$b]['route'] = $data['lists3-route'][$i];
                                $arr_prescriptions['lists3'][$b]['note'] = $data['lists3-note'][$i];
                                $arr_prescriptions['lists3'][$b]['indication'] = $data['lists3-indication'][$i];
                                $arr_prescriptions['lists3'][$b]['route_display'] = $data['lists3-route_display'][$i];
                                $arr_prescriptions['lists3'][$b]['dose'] = $data['lists3-dose'][$i];
                                $arr_prescriptions['lists3'][$b]['rate'] = $data['lists3-size'][$i];
                                $arr_prescriptions['lists3'][$b]['dose_unit'] = $data['lists3-dose_unit'][$i];
                                $arr_prescriptions['lists3'][$b]['rate_unit'] = $data['lists3-rate_unit'][$i];
                                $arr_prescriptions['lists3'][$b]['drug_code'] = $data['lists3-drugcode'][$i];
                                $arr_prescriptions['lists3'][$b]['drug_text'] = $data['lists3-drug'][$i];
                                $arr_prescriptions['lists3'][$b]['prn'] = $data['lists3-prn'][$i];
                                $arr_prescriptions['lists3'][$b]['discontinue'] = $m3_arr[$i];

                                $arr_prescriptions['lists3'][$b]['provider_address'] = $data['lists3-provider_address'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_city'] = $data['lists3-provider_city'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_country'] = $data['lists3-provider_country'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_title'] = $data['lists3-provider_title'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_fname'] = $data['lists3-provider_fname'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_lname'] = $data['lists3-provider_lname'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_postalCode'] = $data['lists3-provider_postalCode'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_state'] = $data['lists3-provider_state'][$i];
                                $arr_prescriptions['lists3'][$b]['provider_root'] = $data['lists3-provider_root'][$i];
                                $b++;
                            } elseif (substr($key, 0, -4) == 'lists2') {
                                $arr_allergies['lists2'][$c]['extension'] = $data['lists2-extension'][$i];
                                $arr_allergies['lists2'][$c]['begdate'] = $data['lists2-begdate'][$i];
                                $arr_allergies['lists2'][$c]['enddate'] = $data['lists2-enddate'][$i];
                                $arr_allergies['lists2'][$c]['list_code'] = $data['lists2-diagnosis'][$i];
                                $arr_allergies['lists2'][$c]['list_code_text'] = $data['lists2-title'][$i];
                                $arr_allergies['lists2'][$c]['severity_al'] = $data['lists2-severity_al'][$i];
                                $arr_allergies['lists2'][$c]['status'] = $data['lists2-activity'][$i];
                                $arr_allergies['lists2'][$c]['reaction'] = $data['lists2-reaction'][$i];
                                $arr_allergies['lists2'][$c]['reaction_text'] = $data['lists2-reaction_text'][$i];
                                $arr_allergies['lists2'][$c]['codeSystemName'] = $data['lists2-codeSystemName'][$i];
                                $arr_allergies['lists2'][$c]['outcome'] = $data['lists2-outcome'][$i];
                                $arr_allergies['lists2'][$c]['resolved'] = $a3_arr[$i];
                                $c++;
                            } elseif (substr($key, 0, -4) == 'lists1') {
                                $arr_med_pblm['lists1'][$d]['extension'] = $data['lists1-extension'][$i];
                                $arr_med_pblm['lists1'][$d]['root'] = $data['lists1-root'][$i];
                                $arr_med_pblm['lists1'][$d]['begdate'] = $data['lists1-begdate'][$i];
                                $arr_med_pblm['lists1'][$d]['enddate'] = $data['lists1-enddate'][$i];
                                $arr_med_pblm['lists1'][$d]['list_code'] = $data['lists1-diagnosis'][$i];
                                $arr_med_pblm['lists1'][$d]['list_code_text'] = $data['lists1-title'][$i];
                                $arr_med_pblm['lists1'][$d]['status'] = $data['lists1-activity'][$i];
                                $arr_med_pblm['lists1'][$d]['observation_text'] = $data['lists1-observation_text'][$i];
                                $arr_med_pblm['lists1'][$d]['observation'] = $data['lists1-observation'][$i];
                                $arr_med_pblm['lists1'][$d]['resolved'] = $p3_arr[$i];
                                $d++;
                            } elseif (substr($key, 0, -4) == 'vital_sign') {
                                $arr_vitals['vitals'][$q]['extension'] = $data['vital_sign-extension'][$i];
                                $arr_vitals['vitals'][$q]['date'] = $data['vital_sign-date'][$i];
                                $arr_vitals['vitals'][$q]['temperature'] = $data['vital_sign-temp'][$i];
                                $arr_vitals['vitals'][$q]['bpd'] = $data['vital_sign-bpd'][$i];
                                $arr_vitals['vitals'][$q]['bps'] = $data['vital_sign-bps'][$i];
                                $arr_vitals['vitals'][$q]['head_circ'] = $data['vital_sign-head_circ'][$i];
                                $arr_vitals['vitals'][$q]['pulse'] = $data['vital_sign-pulse'][$i];
                                $arr_vitals['vitals'][$q]['height'] = $data['vital_sign-height'][$i];
                                $arr_vitals['vitals'][$q]['oxygen_saturation'] = $data['vital_sign-oxy_sat'][$i];
                                $arr_vitals['vitals'][$q]['respiration'] = $data['vital_sign-resp'][$i];
                                $arr_vitals['vitals'][$q]['weight'] = $data['vital_sign-weight'][$i];
                                $q++;
                            } elseif (substr($key, 0, -4) == 'social_history') {
                                $tobacco = $data['social_history-tobacco_note'][$i] . "|" .
                                                        $data['social_history-tobacco_status'][$i] . "|" .
                                                        \Application\Model\ApplicationTable::fixDate($data['social_history-tobacco_date'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy') . "|" . $data['social_history-tobacco_snomed'][$i];
                                                $alcohol = $data['social_history-alcohol_note'][$i] . "|" .
                                                        $data['social_history-alcohol_status'][$i] . "|" .
                                                        \Application\Model\ApplicationTable::fixDate($data['social_history-alcohol_date'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy');
                                                $query = "INSERT INTO history_data
                                            ( pid,
                                              tobacco,
                                              alcohol,
                                              date
                                            )
                                            VALUES
                                            (
                                              ?,
                                              ?,
                                              ?,
                                              ?
                                            )";
                                                $appTable->zQuery($query, array($data['pid'],
                                                    $tobacco,
                                                    $alcohol,
                                                    date('Y-m-d H:i:s')));
                            } elseif (substr($key, 0, -4) == 'encounter') {
                                $arr_encounter['encounter'][$k]['extension'] = $data['encounter-extension'][$i];
                                $arr_encounter['encounter'][$k]['root'] = $data['encounter-root'][$i];
                                $arr_encounter['encounter'][$k]['date'] = $data['encounter-date'][$i];

                                $arr_encounter['encounter'][$k]['provider_npi'] = $data['encounter-provider_npi'][$i];
                                $arr_encounter['encounter'][$k]['provider_name'] = $data['encounter-provider'][$i];
                                $arr_encounter['encounter'][$k]['provider_address'] = $data['encounter-provider_address'][$i];
                                $arr_encounter['encounter'][$k]['provider_city'] = $data['encounter-provider_city'][$i];
                                $arr_encounter['encounter'][$k]['provider_state'] = $data['encounter-provider_state'][$i];
                                $arr_encounter['encounter'][$k]['provider_postalCode'] = $data['encounter-provider_postalCode'][$i];
                                $arr_encounter['encounter'][$k]['provider_country'] = $data['encounter-provider_country'][$i];

                                $arr_encounter['encounter'][$k]['represented_organization_name'] = $data['encounter-facility'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_address'] = $data['encounter-represented_organization_address'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_city'] = $data['encounter-represented_organization_city'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_state'] = $data['encounter-represented_organization_state'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_zip'] = $data['encounter-represented_organization_zip'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_country'] = $data['encounter-represented_organization_country'][$i];
                                $arr_encounter['encounter'][$k]['represented_organization_telecom'] = $data['encounter-represented_organization_telecom'][$i];

                                $arr_encounter['encounter'][$k]['encounter_diagnosis_date'] = $data['encounter-encounter_diagnosis_date'][$i];
                                $arr_encounter['encounter'][$k]['encounter_diagnosis_code'] = $data['encounter-encounter_diagnosis_code'][$i];
                                $arr_encounter['encounter'][$k]['encounter_diagnosis_issue'] = $data['encounter-encounter_diagnosis_issue'][$i];
                                $k++;
                            } elseif (substr($key, 0, -4) == 'procedure_result') {
                                $arr_procedure_res['procedure_result'][$j]['proc_text'] = $data['procedure_result-proc_text'][$i];
                                $arr_procedure_res['procedure_result'][$j]['proc_code'] = $data['procedure_result-proc_code'][$i];
                                $arr_procedure_res['procedure_result'][$j]['extension'] = $data['procedure_result-extension'][$i];
                                $arr_procedure_res['procedure_result'][$j]['date'] = $data['procedure_result-date'][$i];
                                $arr_procedure_res['procedure_result'][$j]['status'] = $data['procedure_result-status'][$i];
                                $arr_procedure_res['procedure_result'][$j]['results_text'] = $data['procedure_result-result'][$i];
                                $arr_procedure_res['procedure_result'][$j]['results_code'] = $data['procedure_result-result_code'][$i];
                                $arr_procedure_res['procedure_result'][$j]['results_range'] = $data['procedure_result-result_range'][$i];
                                $arr_procedure_res['procedure_result'][$j]['results_value'] = $data['procedure_result-result_value'][$i];
                                $arr_procedure_res['procedure_result'][$j]['results_date'] = $data['procedure_result-result_date'][$i];
                                $j++;
                            } elseif (substr($key, 0, -4) == 'procedure') {
                                $arr_procedures['procedure'][$y]['extension'] = $data['procedures-extension'][$i];
                                $arr_procedures['procedure'][$y]['root'] = $data['procedures-root'][$i];
                                $arr_procedures['procedure'][$y]['codeSystemName'] = $data['procedures-codeSystemName'][$i];
                                $arr_procedures['procedure'][$y]['code'] = $data['procedures-code'][$i];
                                $arr_procedures['procedure'][$y]['code_text'] = $data['procedures-code_text'][$i];
                                $arr_procedures['procedure'][$y]['date'] = $data['procedures-date'][$i];

                                $arr_procedures['procedure'][$y]['represented_organization1'] = $data['procedures-represented_organization1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_address1'] = $data['procedures-represented_organization_address1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_city1'] = $data['procedures-represented_organization_city1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_state1'] = $data['procedures-represented_organization_state1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_postalcode1'] = $data['procedures-represented_organization_postalcode1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_country1'] = $data['procedures-represented_organization_country1'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_telecom1'] = $data['procedures-represented_organization_telecom1'][$i];

                                $arr_procedures['procedure'][$y]['represented_organization2'] = $data['procedures-represented_organization2'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_address2'] = $data['procedures-represented_organization_address2'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_city2'] = $data['procedures-represented_organization_city2'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_state2'] = $data['procedures-represented_organization_state2'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_postalcode2'] = $data['procedures-represented_organization_postalcode2'][$i];
                                $arr_procedures['procedure'][$y]['represented_organization_country2'] = $data['procedures-represented_organization_country2'][$i];
                                $y++;
                            } elseif (substr($key, 0, -4) == 'care_plan') {
                                $arr_care_plan['care_plan'][$e]['extension'] = $data['care_plan-extension'][$i];
                                $arr_care_plan['care_plan'][$e]['root'] = $data['care_plan-root'][$i];
                                $arr_care_plan['care_plan'][$e]['text'] = $data['care_plan-text'][$i];
                                $arr_care_plan['care_plan'][$e]['code'] = $data['care_plan-code'][$i];
                                $arr_care_plan['care_plan'][$e]['description'] = $data['care_plan-description'][$i];
                                $e++;
                            } elseif (substr($key, 0, -4) == 'functional_cognitive_status') {
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['extension'] = $data['functional_cognitive_status-extension'][$i];
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['root'] = $data['functional_cognitive_status-root'][$i];
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['text'] = $data['functional_cognitive_status-text'][$i];
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['code'] = $data['functional_cognitive_status-code'][$i];
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['date'] = $data['functional_cognitive_status-date'][$i];
                                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['description'] = $data['functional_cognitive_status-description'][$i];
                                $f++;
                            } elseif (substr($key, 0, -4) == 'referral') {
                                $arr_referral['referral'][$g]['body'] = $data['referral-body'][$i];
                                $arr_referral['referral'][$g]['root'] = $data['referral-root'][$i];
                                $g++;
                            }
                        } elseif ($val[$i] == 'update') {
                            if (substr($key, 0, -4) == 'lists1-con') {
                                if ($data['lists1-activity-con'][$i] == 'Active') {
                                    $activity = 1;
                                } elseif ($data['lists1-activity-con'][$i] == 'Inactive') {
                                    $activity = 0;
                                }

                                $query_select = "SELECT * FROM list_options WHERE list_id = ? AND title = ?";
                                $result = $appTable->zQuery($query_select, array('outcome', $data['lists1-observation_text-con'][$i]));
                                if ($result->count() > 0) {
                                    $q_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND title = ? AND codes = ?";
                                    $appTable->zQuery($q_update, array('outcome', $data['lists1-observation_text-con'][$i], 'SNOMED-CT:' . $data['lists1-observation-con'][$i]));
                                    foreach ($result as $value1) {
                                        $o_id = $value1['option_id'];
                                    }
                                } else {
                                    $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('outcome'));
                                    foreach ($lres as $lrow) {
                                        $o_id = $lrow['option_id'];
                                    }

                                    $q_insert = "INSERT INTO list_options (list_id,option_id,title,codes,activity) VALUES (?,?,?,?,?)";
                                    $appTable->zQuery($q_insert, array('outcome', $o_id, $data['lists1-observation_text-con'][$i], 'SNOMED-CT:' . $data['lists1-observation-con'][$i], 1));
                                }

                                $query = "UPDATE lists
                        SET title=?,
                            diagnosis=?,
                            begdate = ?,
                            enddate = ?,
                            outcome = ?
                        WHERE pid=? AND id=?";
                                $appTable->zQuery($query, array($data['lists1-title-con'][$i],
                                    'SNOMED-CT:' . $data['lists1-diagnosis-con'][$i],
                                    \Application\Model\ApplicationTable::fixDate($data['lists1-begdate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
                                    \Application\Model\ApplicationTable::fixDate($data['lists1-enddate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
                                    $o_id,
                                    $data['pid'],
                                    $data['lists1-old-id-con'][$i]));

                                if ($p1_arr[$i] == 1) {
                                    $query7 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query7, array(date('Y-m-d'),$data['pid'], $data['lists1-old-id-con'][$i]));
                                } elseif ($p1_arr[$i] == 0) {
                                    $query7 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query7, array((null),$data['pid'], $data['lists1-old-id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists1_exist') {
                                if ($p2_arr[$i] == 1) {
                                    $query4 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query4, array(date('Y-m-d'),$data['pid'], $data['lists1_exist-list_id'][$i]));
                                } elseif ($p2_arr[$i] == 0) {
                                    $query4 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query4, array((null),$data['pid'], $data['lists1_exist-list_id'][$i]));
                                }
                            } elseif (substr($key, 0, -4) == 'lists2-con') {
                                if ($data['lists2-begdate-con'][$i] != 0) {
                                    $allergy_begdate_value = \Application\Model\ApplicationTable::fixDate($data['lists2-begdate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy');
                                } elseif ($data['lists2-begdate-con'][$i] == 0) {
                                    $allergy_begdate = $data['lists2-begdate-con'][$i];
                                    $allergy_begdate_value = fixDate($allergy_begdate);
                                    $allergy_begdate_value = (null);
                                }

                                $severity_option_id = $this->getOptionId('severity_ccda', '', 'SNOMED-CT:' . $data['lists2-severity_al-con'][$i]);
                                $severity_text = $this->getListTitle($severity_option_id, 'severity_ccda', 'SNOMED-CT:' . $data['lists2-severity_al-con'][$i]);
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
                                        $appTable->zQuery($q_insert_units_option, array($severity_option_id,$severity_text));
                                    }
                                }

                                $reaction_option_id = $this->getOptionId('Reaction', $data['lists2-reaction_text-con'][$i], '');
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
                                        $appTable->zQuery($q_insert_units_option, array($reaction_option_id,$data['lists2-reaction_text-con'][$i]));
                                    }
                                }

                                $q_upd_allergies = "UPDATE lists
                                    SET date=?,
                                        begdate=?,
                                        title=?,
                                        diagnosis=?,
                                        severity_al=?,
                                        reaction=?
                                    WHERE pid = ? AND id=?";
                                $appTable->zQuery($q_upd_allergies, array(
                                date('y-m-d H:i:s'),
                                $allergy_begdate_value,
                                $data['lists2-title-con'][$i],
                                'RXNORM' . ':' . $data['lists2-diagnosis-con'][$i],
                                $severity_option_id,
                                $reaction_option_id ? $reaction_option_id : 0,
                                $data['pid'],
                                $data['lists2-list_id-con'][$i]));

                                if ($a1_arr[$i] == 1) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array(date('Y-m-d'),$data['pid'], $data['lists2-list_id-con'][$i]));
                                } elseif ($a1_arr[$i] == 0) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array((null),$data['pid'], $data['lists2-list_id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists2_exist') {
                                if ($a2_arr[$i] == 1) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array(date('Y-m-d'),$data['pid'], $data['lists2_exist-list_id'][$i]));
                                } elseif ($a2_arr[$i] == 0) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array((null),$data['pid'], $data['lists2_exist-list_id'][$i]));
                                }
                            } elseif (substr($key, 0, -4) == 'lists3-con') {
                                $oid_route = $unit_option_id = $oidu_unit = '';
                                //provider
                                    $query_sel_users = "SELECT *
                                                      FROM users
                                                      WHERE abook_type='external_provider' AND npi=?";
                                    $res_query_sel_users = $appTable->zQuery($query_sel_users, array($data['lists3-provider_npi-con'][$i]));
                                if ($res_query_sel_users->count() > 0) {
                                    foreach ($res_query_sel_users as $value1) {
                                        $provider_id = $value1['id'];
                                    }
                                } else {
                                        $query_ins_users = "INSERT INTO users
                                                        ( fname,
                                                          lname,
                                                          authorized,
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
                                                          1,
                                                          ?,
                                                          ?,
                                                          ?,
                                                          ?,
                                                          1,
                                                          'external_provider'
                                                        )";
                                        $res_query_ins_users = $appTable->zQuery($query_ins_users, array($data['lists3-provider_fname-con'][$i],
                                            $data['lists3-provider_lname-con'][$i],
                                            $data['lists3-provider_address-con'][$i],
                                            $data['lists3-provider_city-con'][$i],
                                            $data['lists3-provider_state-con'][$i],
                                            $data['lists3-provider_postalCode-con'][$i]
                                        ));
                                        $provider_id = $res_query_ins_users->getGeneratedValue();
                                }

                                    //route
                                    $q1_route = "SELECT *
                                               FROM list_options
                                               WHERE list_id='drug_route' AND notes=?";
                                    $res_q1_route = $appTable->zQuery($q1_route, array($data['lists3-route-con'][$i]));
                                foreach ($res_q1_route as $val1) {
                                    $oid_route = $val1['option_id'];
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
                                    $appTable->zQuery($q_insert_route, array($oid_route, $data['lists3-route-con'][$i],
                                    $data['lists3-route_display-con'][$i]));
                                }

                                    //drug form
                                    $query_select_form = "SELECT * FROM list_options WHERE list_id = ? AND title = ?";
                                    $result = $appTable->zQuery($query_select_form, array('drug_form', $data['lists3-dose_unit-con'][$i]));
                                if ($result->count() > 0) {
                                    $q_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND title = ?";
                                    $appTable->zQuery($q_update, array('drug_form', $data['lists3-dose_unit-con'][$i]));
                                    foreach ($result as $value2) {
                                        $oidu_unit = $value2['option_id'];
                                    }
                                } else {
                                        $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('drug_form'));
                                    foreach ($lres as $lrow) {
                                        $oidu_unit = $lrow['option_id'];
                                    }

                                        $q_insert = "INSERT INTO list_options (list_id,option_id,title,activity) VALUES (?,?,?,?)";
                                        $appTable->zQuery($q_insert, array('drug_form', $oidu_unit, $data['lists3-dose_unit-con'][$i], 1));
                                }

                                if ($data['lists3-enddate-con'][$i] == '' || $data['lists3-enddate-con'][$i] == 0) {
                                    $data['lists3-enddate-con'][$i] = (null);
                                }

                                $q_upd_pres = "UPDATE prescriptions
                                        SET date_added=?,
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
                                            provider_id=?
                                        WHERE id=? AND patient_id=?";
                                 $appTable->zQuery($q_upd_pres, array(
                                     \Application\Model\ApplicationTable::fixDate($data['lists3-date_added-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
                                     $data['lists3-drug-con'][$i],
                                     $data['lists3-size-con'][$i],
                                     $oidu_unit,
                                     $data['lists3-dose-con'][$i],
                                     $oid_route,
                                     $data['lists3-rate_unit-con'][$i],
                                     $data['lists3-note-con'][$i],
                                     $data['lists3-indication-con'][$i],
                                     $data['lists3-prn-con'][$i],
                                     $data['lists3-drugcode-con'][$i],
                                     $provider_id,
                                     $data['lists3-id-con'][$i],
                                     $data['pid']));
                                if ($m1_arr[$i] == 1) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array(date('Y-m-d'),'-1',$data['pid'], $data['lists3-id-con'][$i]));
                                } elseif ($m1_arr[$i] == 0) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array((null),'1',$data['pid'], $data['lists3-id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists3_exist') {
                                if ($m2_arr[$i] == 1) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array(date('Y-m-d'),'-1',$data['pid'], $data['lists3_exist-id'][$i]));
                                } elseif ($m2_arr[$i] == 0) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array((null),'1',$data['pid'], $data['lists3_exist-id'][$i]));
                                }
                            }
                        }
                    }
                } elseif (substr($key, 0, 12) == 'patient_data') {
                    if ($val == 'update') {
                        $var_name = substr($key, 0, -4);
                        $field_name = substr($var_name, 13);
                        $patient_data_fields .= $field_name . '=?,';
                        array_push($patient_data_values, $data[$var_name]);
                    }
                }
            }
        }

        if (count($patient_data_values) > 0) {
            array_push($patient_data_values, $data['pid']);
            $patient_data_fields = substr($patient_data_fields, 0, -1);
            $query = "UPDATE patient_data SET $patient_data_fields WHERE pid=?";
            $appTable->zQuery($query, $patient_data_values);
        }

        $appTable->zQuery("UPDATE documents
                       SET foreign_id = ?
                       WHERE id =? ", array($data['pid'],
            $data['document_id']));
        $appTable->zQuery("UPDATE audit_master
                       SET approval_status = '2'
                       WHERE id=?", array($data['amid']));
        $appTable->zQuery("UPDATE documents
                       SET audit_master_approval_status=2
                       WHERE audit_master_id=?", array($data['amid']));
        $this->InsertReconcilation($data['pid'], $data['document_id']);
        $this->InsertImmunization($arr_immunization['immunization'], $data['pid'], 1);
        $this->InsertPrescriptions($arr_prescriptions['lists3'], $data['pid'], 1);
        $this->InsertAllergies($arr_allergies['lists2'], $data['pid'], 1);
        $this->InsertMedicalProblem($arr_med_pblm['lists1'], $data['pid'], 1);
        $this->InsertEncounter($arr_encounter['encounter'], $data['pid'], 1);
        $this->InsertVitals($arr_vitals['vitals'], $data['pid'], 1);
        $this->InsertProcedures($arr_procedures['procedure'], $data['pid'], 1);
        $lab_results = $this->buildLabArray($arr_procedure_res['procedure_result']);
        $this->InsertLabResults($lab_results, $data['pid']);
        $this->InsertCarePlan($arr_care_plan['care_plan'], $data['pid'], 1);
        $this->InsertFunctionalCognitiveStatus($arr_functional_cognitive_status['functional_cognitive_status'], $data['pid'], 1);
        $this->InsertReferrals($arr_referral['referral'], $data['pid'], 1);
    }

    public function InsertReconcilation($pid, $doc_id)
    {
        $appTable = new ApplicationTable();
        $query    = "SELECT encounter FROM documents d inner join form_encounter e on ( e.pid = d.foreign_id and e.date = d.docdate ) where d.id = ? and pid = ? and d.deleted = 0";
        $docEnc   = $appTable->zQuery($query, array($doc_id,$pid));

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

        $med_rec = $appTable->zQuery("select * from amc_misc_data where pid = ? and amc_id = 'med_reconc_amc' and map_category = 'form_encounter' and map_id = ?", array($pid,$enc_id));
        if ($med_rec->count() == 0) {
            $appTable->zQuery("INSERT INTO amc_misc_data (amc_id,pid,map_category,map_id,date_created,date_completed,soc_provided) values('med_reconc_amc',?,'form_encounter',?,NOW(),NOW(),NOW())", array($pid,$enc_id));
        } else {
            $appTable->zQuery("UPDATE amc_misc_data set date_completed = NOW() where pid = ? and amc_id = 'med_reconc_amc' and map_category ='form_encounter' and map_id = ?", array($pid,$enc_id));
        }
    }
    public function discardCCDAData($data)
    {
        $appTable = new ApplicationTable();
        $query = "UPDATE audit_master
                   SET approval_status = '3'
                   WHERE id=?";
        $appTable->zQuery($query, array($data['audit_master_id']));
        $appTable->zQuery("UPDATE documents
                      SET audit_master_approval_status='3'
                      WHERE audit_master_id=?", array($data['audit_master_id']));
    }

    public function buildLabArray($lab_array)
    {
        // nothing to build if we are empty here.
        if (empty($lab_array)) {
            return [];
        }

        $lab_results = array();
        $j = 0;
        foreach ($lab_array as $key => $value) {
            $j = count($lab_results[$value['extension']]['result']) + 1;
            $lab_results[$value['extension']]['proc_text'] = $value['proc_text'];
            $lab_results[$value['extension']]['date'] = $value['date'];
            $lab_results[$value['extension']]['proc_code'] = $value['proc_code'];
            $lab_results[$value['extension']]['extension'] = $value['extension'];
            $lab_results[$value['extension']]['status'] = $value['status'];
            $lab_results[$value['extension']]['result'][$j]['result_date'] = $value['results_date'];
            $lab_results[$value['extension']]['result'][$j]['result_text'] = $value['results_text'];
            $lab_results[$value['extension']]['result'][$j]['result_value'] = $value['results_value'];
            $lab_results[$value['extension']]['result'][$j]['result_range'] = $value['results_range'];
            $lab_results[$value['extension']]['result'][$j]['result_code'] = $value['results_code'];
        }

        return $lab_results;
    }

    public function InsertLabResults($lab_results, $pid)
    {
        if (empty($lab_results)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($lab_results as $key => $value) {
            $query_select_pro = "SELECT * FROM procedure_providers WHERE name = ?";
            $result_pro = $appTable->zQuery($query_select_pro, array('External Lab'));
            if ($result_pro->count() == 0) {
                $query_insert_pro = "INSERT INTO procedure_providers(name) VALUES (?)";
                $result_pro = $appTable->zQuery($query_insert_pro, array('External Lab'));
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
            $enc_id = $enc_cur['encounter'] ? $enc_cur['encounter'] : 0;

            foreach ($value['result'] as $res) {
                $query_select_pt = "SELECT * FROM procedure_type WHERE procedure_code = ? AND lab_id = ?";
                $result_pt = $appTable->zQuery($query_select_pt, array($res['result_code'], $pro_id));
                if ($result_pt->count() == 0) {
                    //procedure_type
                    $query_insert_pt = "INSERT INTO procedure_type(name,lab_id,procedure_code,procedure_type,activity) VALUES (?,?,?,?,?)";
                    $result_pt = $appTable->zQuery($query_insert_pt, array($res['result_text'], $pro_id, $res['result_code'], 'ord', 1));
                    $res_pt_id = $result_pt->getGeneratedValue();
                    $query_update_pt = "UPDATE procedure_type SET parent = ? WHERE procedure_type_id = ?";
                    $appTable->zQuery($query_update_pt, array($res_pt_id, $res_pt_id));
                }

                //procedure_order
                $query_insert_po = "INSERT INTO procedure_order(provider_id,patient_id,encounter_id,date_collected,date_ordered,order_priority,order_status,activity,lab_id) VALUES (?,?,?,?,?,?,?,?,?)";
                $result_po = $appTable->zQuery($query_insert_po, array('', $pid, $enc_id, \Application\Model\ApplicationTable::fixDate($res['result_date'], 'yyyy-mm-dd', 'dd/mm/yyyy'), \Application\Model\ApplicationTable::fixDate($res['result_date'], 'yyyy-mm-dd', 'dd/mm/yyyy'), 'normal', 'completed', 1, $pro_id));
                $po_id = $result_po->getGeneratedValue();
                //procedure_order_code
                $query_insert_poc = "INSERT INTO procedure_order_code(procedure_order_id,procedure_order_seq,procedure_code,procedure_name,diagnoses) VALUES (?,?,?,?,?)";
                $result_poc = $appTable->zQuery($query_insert_poc, array($po_id, 1, $res['result_code'], $res['result_text'], 'LOINC:' . $res['result_code']));
                //procedure_report
                $query_insert_pr = "INSERT INTO procedure_report(procedure_order_id,date_collected,report_status,review_status) VALUES (?,?,?,?)";
                $result_pr = $appTable->zQuery($query_insert_pr, array($po_id, \Application\Model\ApplicationTable::fixDate($res['result_date'], 'yyyy-mm-dd', 'dd/mm/yyyy'), 'final', 'reviewed'));
                $res_id = $result_pr->getGeneratedValue();
                //procedure_result
                $range_unit = explode(' ', $res['result_range']);
                $range = $range_unit[0];
                $unit = $range_unit[1];
                if ($unit != '') {
                    $qU_select = "SELECT * FROM list_options WHERE list_id = ? AND option_id = ?";
                    $Ures = $appTable->zQuery($qU_select, array('proc_unit', $unit));
                    if ($Ures->count() == 0) {
                        $qU_insert = "INSERT INTO list_options(list_id,option_id,title,activity) VALUES (?,?,?,?)";
                        $appTable->zQuery($qU_insert, array('proc_unit', $unit, $unit, 1));
                    } else {
                        $qU_update = "UPDATE list_options SET activity = 1 WHERE list_id = ? AND option_id = ?";
                        $appTable->zQuery($qU_update, array('proc_unit', $unit));
                    }

                    $query_insert_prs = "INSERT INTO procedure_result(procedure_report_id,result_code,date,units,result,`range`,result_text,result_status) VALUES (?,?,?,?,?,?,?,?)";
                    $result_prs = $appTable->zQuery($query_insert_prs, array($res_id, $res['result_code'], \Application\Model\ApplicationTable::fixDate($res['result_date'], 'yyyy-mm-dd', 'dd/mm/yyyy'), $unit, $res['result_value'], $range, $res['result_text'], 'final'));
                }
            }
        }
    }

    public function insert_patient($audit_master_id, $document_id)
    {
        require_once(dirname(__FILE__) . "/../../../../../../../../library/patient.inc");
        $pid = 0;
        $j = 1;
        $k = 1;
        $q = 1;
        $y = 1;
        $a = 1;
        $b = 1;
        $c = 1;
        $d = 1;
        $e = 1;
        $f = 1;
        $g = 1;

        $arr_procedure_res = array();
        $arr_encounter = array();
        $arr_vitals = array();
        $arr_procedures = array();
        $arr_immunization = array();
        $arr_prescriptions = array();
        $arr_allergies = array();
        $arr_med_pblm = array();
        $arr_care_plan = array();
        $arr_functional_cognitive_status = array();
        $arr_referral = array();
        $appTable = new ApplicationTable();

        $pres = $appTable->zQuery("SELECT IFNULL(MAX(pid)+1,1) AS pid
                                     FROM patient_data");
        foreach ($pres as $prow) {
            $pid = $prow['pid'];
        }

        $res = $appTable->zQuery("SELECT DISTINCT ad.table_name,
                                            entry_identification
                                     FROM audit_master as am,audit_details as ad
                                     WHERE am.id=ad.audit_master_id AND
                                     am.approval_status = '1' AND
                                     am.id=? AND am.type=12
                                     ORDER BY ad.id", array($audit_master_id));
        $tablecnt = $res->count();
        foreach ($res as $row) {
            $resfield = $appTable->zQuery("SELECT *
                                     FROM audit_details
                                     WHERE audit_master_id=? AND
                                     table_name=? AND
                                     entry_identification=?", array($audit_master_id,
                $row['table_name'],
                $row['entry_identification']));
            $table = $row['table_name'];
            $newdata = array();
            foreach ($resfield as $rowfield) {
                if ($table == 'patient_data') {
                    if ($rowfield['field_name'] == 'DOB') {
                        $dob = $this->formatDate($rowfield['field_value'], 1);
                        $newdata['patient_data'][$rowfield['field_name']] = $dob;
                    } else {
                        if ($rowfield['field_name'] == 'religion') {
                            $religion_option_id = $this->getOptionId('religious_affiliation', $rowfield['field_value'], '');
                            $newdata['patient_data'][$rowfield['field_name']] = $religion_option_id;
                        } elseif ($rowfield['field_name'] == 'race') {
                            $race_option_id = $this->getOptionId('race', $rowfield['field_value'], '');
                            $newdata['patient_data'][$rowfield['field_name']] = $race_option_id;
                        } elseif ($rowfield['field_name'] == 'ethnicity') {
                              $ethnicity_option_id = $this->getOptionId('ethnicity', $rowfield['field_value'], '');
                              $newdata['patient_data'][$rowfield['field_name']] = $ethnicity_option_id;
                        } else {
                            $newdata['patient_data'][$rowfield['field_name']] = $rowfield['field_value'];
                        }
                    }
                } elseif ($table == 'immunization') {
                    $newdata['immunization'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'lists3') {
                    $newdata['lists3'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'lists1') {
                    $newdata['lists1'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'lists2') {
                    $newdata['lists2'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'vital_sign') {
                    $newdata['vital_sign'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'social_history') {
                    $newdata['social_history'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'encounter') {
                    $newdata['encounter'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'procedure_result') {
                    $newdata['procedure_result'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'procedure') {
                    $newdata['procedure'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'care_plan') {
                    $newdata['care_plan'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'functional_cognitive_status') {
                    $newdata['functional_cognitive_status'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'referral') {
                    $newdata['referral'][$rowfield['field_name']] = $rowfield['field_value'];
                }
            }

            if ($table == 'patient_data') {
                updatePatientData($pid, $newdata['patient_data'], true);
            } elseif ($table == 'immunization') {
                $arr_immunization['immunization'][$a]['extension'] = $newdata['immunization']['extension'];
                $arr_immunization['immunization'][$a]['root'] = $newdata['immunization']['root'];
                $arr_immunization['immunization'][$a]['administered_date'] = $newdata['immunization']['administered_date'];
                $arr_immunization['immunization'][$a]['route_code'] = $newdata['immunization']['route_code'];
                $arr_immunization['immunization'][$a]['route_code_text'] = $newdata['immunization']['route_code_text'];
                $arr_immunization['immunization'][$a]['cvx_code_text'] = $newdata['immunization']['cvx_code_text'];
                $arr_immunization['immunization'][$a]['cvx_code'] = $newdata['immunization']['cvx_code'];
                $arr_immunization['immunization'][$a]['amount_administered'] = $newdata['immunization']['amount_administered'];
                $arr_immunization['immunization'][$a]['amount_administered_unit'] = $newdata['immunization']['amount_administered_unit'];
                $arr_immunization['immunization'][$a]['manufacturer'] = $newdata['immunization']['manufacturer'];
                $arr_immunization['immunization'][$a]['completion_status'] = $newdata['immunization']['completion_status'];

                $arr_immunization['immunization'][$a]['provider_npi'] = $newdata['immunization']['provider_npi'];
                $arr_immunization['immunization'][$a]['provider_name'] = $newdata['immunization']['provider_name'];
                $arr_immunization['immunization'][$a]['provider_address'] = $newdata['immunization']['provider_address'];
                $arr_immunization['immunization'][$a]['provider_city'] = $newdata['immunization']['provider_city'];
                $arr_immunization['immunization'][$a]['provider_state'] = $newdata['immunization']['provider_state'];
                $arr_immunization['immunization'][$a]['provider_postalCode'] = $newdata['immunization']['provider_postalCode'];
                $arr_immunization['immunization'][$a]['provider_country'] = $newdata['immunization']['provider_country'];
                $arr_immunization['immunization'][$a]['provider_telecom'] = $newdata['immunization']['provider_telecom'];
                $arr_immunization['immunization'][$a]['represented_organization'] = $newdata['immunization']['represented_organization'];
                $arr_immunization['immunization'][$a]['represented_organization_tele'] = $newdata['immunization']['represented_organization_tele'];
                $a++;
            } elseif ($table == 'lists3') {
                $arr_prescriptions['lists3'][$b]['extension'] = $newdata['lists3']['extension'];
                $arr_prescriptions['lists3'][$b]['root'] = $newdata['lists3']['root'];
                $arr_prescriptions['lists3'][$b]['begdate'] = $newdata['lists3']['begdate'];
                $arr_prescriptions['lists3'][$b]['enddate'] = $newdata['lists3']['enddate'];
                $arr_prescriptions['lists3'][$b]['route'] = $newdata['lists3']['route'];
                $arr_prescriptions['lists3'][$b]['note'] = $newdata['lists3']['note'];
                $arr_prescriptions['lists3'][$b]['indication'] = $newdata['lists3']['indication'];
                $arr_prescriptions['lists3'][$b]['route_display'] = $newdata['lists3']['route_display'];
                $arr_prescriptions['lists3'][$b]['dose'] = $newdata['lists3']['dose'];
                $arr_prescriptions['lists3'][$b]['dose_unit'] = $newdata['lists3']['dose_unit'];
                $arr_prescriptions['lists3'][$b]['rate'] = $newdata['lists3']['rate'];
                $arr_prescriptions['lists3'][$b]['rate_unit'] = $newdata['lists3']['rate_unit'];
                $arr_prescriptions['lists3'][$b]['drug_code'] = $newdata['lists3']['drug_code'];
                $arr_prescriptions['lists3'][$b]['drug_text'] = $newdata['lists3']['drug_text'];
                $arr_prescriptions['lists3'][$b]['prn'] = $newdata['lists3']['prn'];

                $arr_prescriptions['lists3'][$b]['provider_address'] = $newdata['lists3']['provider_address'];
                $arr_prescriptions['lists3'][$b]['provider_city'] = $newdata['lists3']['provider_city'];
                $arr_prescriptions['lists3'][$b]['provider_country'] = $newdata['lists3']['provider_country'];
                $arr_prescriptions['lists3'][$b]['provider_title'] = $newdata['lists3']['provider_title'];
                $arr_prescriptions['lists3'][$b]['provider_fname'] = $newdata['lists3']['provider_fname'];
                $arr_prescriptions['lists3'][$b]['provider_lname'] = $newdata['lists3']['provider_lname'];
                $arr_prescriptions['lists3'][$b]['provider_postalCode'] = $newdata['lists3']['provider_postalCode'];
                $arr_prescriptions['lists3'][$b]['provider_state'] = $newdata['lists3']['provider_state'];
                $arr_prescriptions['lists3'][$b]['provider_root'] = $newdata['lists3']['provider_root'];
                $b++;
            } elseif ($table == 'lists1' && $newdata['lists1']['list_code'] != 0) {
                $arr_med_pblm['lists1'][$d]['extension'] = $newdata['lists1']['extension'];
                $arr_med_pblm['lists1'][$d]['root'] = $newdata['lists1']['root'];
                $arr_med_pblm['lists1'][$d]['begdate'] = $newdata['lists1']['begdate'];
                $arr_med_pblm['lists1'][$d]['enddate'] = $newdata['lists1']['enddate'];
                $arr_med_pblm['lists1'][$d]['list_code'] = $newdata['lists1']['list_code'];
                $arr_med_pblm['lists1'][$d]['list_code_text'] = $newdata['lists1']['list_code_text'];
                $arr_med_pblm['lists1'][$d]['status'] = $newdata['lists1']['status'];
                $arr_med_pblm['lists1'][$d]['observation_text'] = $newdata['lists1']['observation_text'];
                $arr_med_pblm['lists1'][$d]['observation_code'] = $newdata['lists1']['observation'];
                $d++;
            } elseif ($table == 'lists2' && $newdata['lists2']['list_code'] != 0) {
                $arr_allergies['lists2'][$c]['extension'] = $newdata['lists2']['extension'];
                $arr_allergies['lists2'][$c]['begdate'] = $newdata['lists2']['begdate'];
                $arr_allergies['lists2'][$c]['enddate'] = $newdata['lists2']['enddate'];
                $arr_allergies['lists2'][$c]['list_code'] = $newdata['lists2']['list_code'];
                $arr_allergies['lists2'][$c]['list_code_text'] = $newdata['lists2']['list_code_text'];
                $arr_allergies['lists2'][$c]['severity_al'] = $newdata['lists2']['severity_al'];
                $arr_allergies['lists2'][$c]['status'] = $newdata['lists2']['status'];
                $arr_allergies['lists2'][$c]['reaction'] = $newdata['lists2']['reaction'];
                $arr_allergies['lists2'][$c]['reaction_text'] = $newdata['lists2']['reaction_text'];
                $arr_allergies['lists2'][$c]['codeSystemName'] = $newdata['lists2']['codeSystemName'];
                $arr_allergies['lists2'][$c]['outcome'] = $newdata['lists2']['outcome'];
                $c++;
            } elseif ($table == 'encounter') {
                $arr_encounter['encounter'][$k]['extension'] = $newdata['encounter']['extension'];
                $arr_encounter['encounter'][$k]['root'] = $newdata['encounter']['root'];
                $arr_encounter['encounter'][$k]['date'] = $newdata['encounter']['date'];

                $arr_encounter['encounter'][$k]['provider_npi'] = $newdata['encounter']['provider_npi'];
                $arr_encounter['encounter'][$k]['provider_name'] = $newdata['encounter']['provider_name'];
                $arr_encounter['encounter'][$k]['provider_address'] = $newdata['encounter']['provider_address'];
                $arr_encounter['encounter'][$k]['provider_city'] = $newdata['encounter']['provider_city'];
                $arr_encounter['encounter'][$k]['provider_state'] = $newdata['encounter']['provider_state'];
                $arr_encounter['encounter'][$k]['provider_postalCode'] = $newdata['encounter']['provider_postalCode'];
                $arr_encounter['encounter'][$k]['provider_country'] = $newdata['encounter']['provider_country'];

                $arr_encounter['encounter'][$k]['represented_organization_name'] = $newdata['encounter']['represented_organization_name'];
                $arr_encounter['encounter'][$k]['represented_organization_address'] = $newdata['encounter']['represented_organization_address'];
                $arr_encounter['encounter'][$k]['represented_organization_city'] = $newdata['encounter']['represented_organization_city'];
                $arr_encounter['encounter'][$k]['represented_organization_state'] = $newdata['encounter']['represented_organization_state'];
                $arr_encounter['encounter'][$k]['represented_organization_zip'] = $newdata['encounter']['represented_organization_zip'];
                $arr_encounter['encounter'][$k]['represented_organization_country'] = $newdata['encounter']['represented_organization_country'];
                $arr_encounter['encounter'][$k]['represented_organization_telecom'] = $newdata['encounter']['represented_organization_telecom'];

                $arr_encounter['encounter'][$k]['encounter_diagnosis_date'] = $newdata['encounter']['encounter_diagnosis_date'];
                $arr_encounter['encounter'][$k]['encounter_diagnosis_code'] = $newdata['encounter']['encounter_diagnosis_code'];
                $arr_encounter['encounter'][$k]['encounter_diagnosis_issue'] = $newdata['encounter']['encounter_diagnosis_issue'];
                $k++;
            } elseif ($table == 'vital_sign') {
                $arr_vitals['vitals'][$q]['extension'] = $newdata['vital_sign']['extension'];
                $arr_vitals['vitals'][$q]['date'] = $newdata['vital_sign']['date'];
                $arr_vitals['vitals'][$q]['temperature'] = $newdata['vital_sign']['temperature'];
                $arr_vitals['vitals'][$q]['bpd'] = $newdata['vital_sign']['bpd'];
                $arr_vitals['vitals'][$q]['bps'] = $newdata['vital_sign']['bps'];
                $arr_vitals['vitals'][$q]['head_circ'] = $newdata['vital_sign']['head_circ'];
                $arr_vitals['vitals'][$q]['pulse'] = $newdata['vital_sign']['pulse'];
                $arr_vitals['vitals'][$q]['height'] = $newdata['vital_sign']['height'];
                $arr_vitals['vitals'][$q]['oxygen_saturation'] = $newdata['vital_sign']['oxygen_saturation'];
                $arr_vitals['vitals'][$q]['respiration'] = $newdata['vital_sign']['respiration'];
                $arr_vitals['vitals'][$q]['weight'] = $newdata['vital_sign']['weight'];
                $q++;
            } elseif ($table == 'social_history') {
                $tobacco_status = array(
                    '449868002' => 'Current',
                    '8517006' => 'Quit',
                    '266919005' => 'Never'
                      );
                      $alcohol_status = array(
                          '219006' => 'Current',
                          '82581004' => 'Quit',
                          '228274009' => 'Never'
                      );
                      $alcohol = explode("|", $newdata['social_history']['alcohol']);
                      if ($alcohol[2] != 0) {
                          $alcohol_date = $this->formatDate($alcohol[2], 1);
                      } else {
                                $alcohol_date = $alcohol[2];
                      }

                      $alcohol_date_value = fixDate($alcohol_date);
                      foreach ($alcohol_status as $key => $value) {
                          if ($alcohol[1] == $key) {
                              $alcohol[1] = strtolower($value) . "alcohol";
                          }
                      }

                      $alcohol_value = $alcohol[0] . "|" . $alcohol[1] . "|" . $alcohol_date_value;

                      $tobacco = explode("|", $newdata['social_history']['smoking']);
                      if ($tobacco[2] != 0) {
                            $smoking_date = $this->formatDate($tobacco[2], 1);
                      } else {
                                $smoking_date = $tobacco[2];
                      }

                      $smoking_date_value = fixDate($smoking_date);
                      foreach ($tobacco_status as $key => $value2) {
                          if ($tobacco[1] == $key) {
                              $tobacco[1] = strtolower($value2) . "tobacco";
                          }
                      }

                      $smoking_value = $tobacco[0] . "|" . $tobacco[1] . "|" . $smoking_date_value;

                      $query_insert = "INSERT INTO history_data
                         (
                          pid,
                          alcohol,
                          tobacco,
                          date
                         )
                         VALUES
                         (
                          ?,
                          ?,
                          ?,
                          ?
                         )";
                      $appTable->zQuery($query_insert, array($pid,
                          $alcohol_value,
                          $smoking_value,
                          date('Y-m-d H:i:s')));
            } elseif ($table == 'procedure_result') {
                if ($newdata['procedure_result']['date'] != 0) {
                    $proc_date = $this->formatDate($newdata['procedure_result']['date'], 0);
                } else {
                    $proc_date = $newdata['procedure_result']['date'];
                }

                if ($newdata['procedure_result']['results_date'] != 0) {
                    $proc_result_date = $this->formatDate($newdata['procedure_result']['results_date'], 0);
                } else {
                    $proc_result_date = $newdata['procedure_result']['results_date'];
                }

                $arr_procedure_res['procedure_result'][$j]['proc_text'] = $newdata['procedure_result']['proc_text'];
                $arr_procedure_res['procedure_result'][$j]['proc_code'] = $newdata['procedure_result']['proc_code'];
                $arr_procedure_res['procedure_result'][$j]['extension'] = $newdata['procedure_result']['extension'];
                $arr_procedure_res['procedure_result'][$j]['date'] = $proc_date;
                $arr_procedure_res['procedure_result'][$j]['status'] = $newdata['procedure_result']['status'];
                $arr_procedure_res['procedure_result'][$j]['results_text'] = $newdata['procedure_result']['results_text'];
                $arr_procedure_res['procedure_result'][$j]['results_code'] = $newdata['procedure_result']['results_code'];
                $arr_procedure_res['procedure_result'][$j]['results_range'] = $newdata['procedure_result']['results_range'];
                $arr_procedure_res['procedure_result'][$j]['results_value'] = $newdata['procedure_result']['results_value'];
                $arr_procedure_res['procedure_result'][$j]['results_date'] = $proc_result_date;
                $j++;
            } elseif ($table == 'procedure') {
                $arr_procedures['procedure'][$y]['extension'] = $newdata['procedure']['extension'];
                $arr_procedures['procedure'][$y]['root'] = $newdata['procedure']['root'];
                $arr_procedures['procedure'][$y]['codeSystemName'] = $newdata['procedure']['codeSystemName'];
                $arr_procedures['procedure'][$y]['code'] = $newdata['procedure']['code'];
                $arr_procedures['procedure'][$y]['code_text'] = $newdata['procedure']['code_text'];
                $arr_procedures['procedure'][$y]['date'] = $newdata['procedure']['date'];

                $arr_procedures['procedure'][$y]['represented_organization1'] = $newdata['procedure']['represented_organization1'];
                $arr_procedures['procedure'][$y]['represented_organization_address1'] = $newdata['procedure']['represented_organization_address1'];
                $arr_procedures['procedure'][$y]['represented_organization_city1'] = $newdata['procedure']['represented_organization_city1'];
                $arr_procedures['procedure'][$y]['represented_organization_state1'] = $newdata['procedure']['represented_organization_state1'];
                $arr_procedures['procedure'][$y]['represented_organization_postalcode1'] = $newdata['procedure']['represented_organization_postalcode1'];
                $arr_procedures['procedure'][$y]['represented_organization_country1'] = $newdata['procedure']['represented_organization_country1'];
                $arr_procedures['procedure'][$y]['represented_organization_telecom1'] = $newdata['procedure']['represented_organization_telecom1'];

                $arr_procedures['procedure'][$y]['represented_organization2'] = $newdata['procedure']['represented_organization2'];
                $arr_procedures['procedure'][$y]['represented_organization_address2'] = $newdata['procedure']['represented_organization_address2'];
                $arr_procedures['procedure'][$y]['represented_organization_city2'] = $newdata['procedure']['represented_organization_city2'];
                $arr_procedures['procedure'][$y]['represented_organization_state2'] = $newdata['procedure']['represented_organization_state2'];
                $arr_procedures['procedure'][$y]['represented_organization_postalcode2'] = $newdata['procedure']['represented_organization_postalcode2'];
                $arr_procedures['procedure'][$y]['represented_organization_country2'] = $newdata['procedure']['represented_organization_country2'];
                $y++;
            } elseif ($table == 'care_plan') {
                $arr_care_plan['care_plan'][$e]['extension'] = $newdata['care_plan']['extension'];
                $arr_care_plan['care_plan'][$e]['root'] = $newdata['care_plan']['root'];
                $arr_care_plan['care_plan'][$e]['text'] = $newdata['care_plan']['code_text'];
                $arr_care_plan['care_plan'][$e]['code'] = $newdata['care_plan']['code'];
                $arr_care_plan['care_plan'][$e]['description'] = $newdata['care_plan']['description'];
                $e++;
            } elseif ($table == 'functional_cognitive_status') {
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['extension'] = $newdata['functional_cognitive_status']['extension'];
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['root'] = $newdata['functional_cognitive_status']['root'];
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['text'] = $newdata['functional_cognitive_status']['code_text'];
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['code'] = $newdata['functional_cognitive_status']['code'];
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['date'] = $newdata['functional_cognitive_status']['date'];
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['description'] = $newdata['functional_cognitive_status']['description'];
                $f++;
            } elseif ($table == 'referral') {
                $arr_referral['referral'][$g]['body'] = $newdata['referral']['body'];
                $arr_referral['referral'][$g]['root'] = $newdata['referral']['root'];
                $g++;
            }
        }

        $this->InsertImmunization($arr_immunization['immunization'], $pid, 0);
        $this->InsertPrescriptions($arr_prescriptions['lists3'], $pid, 0);
        $this->InsertAllergies($arr_allergies['lists2'], $pid, 0);
        $this->InsertMedicalProblem($arr_med_pblm['lists1'], $pid, 0);
        $this->InsertEncounter($arr_encounter['encounter'], $pid, 0);
        $this->InsertVitals($arr_vitals['vitals'], $pid, 0);
        $lab_results = $this->buildLabArray($arr_procedure_res['procedure_result']);
        $this->InsertProcedures($arr_procedures['procedure'], $pid, 0);
        $this->InsertLabResults($lab_results, $pid);
        $this->InsertCarePlan($arr_care_plan['care_plan'], $pid, 0);
        $this->InsertFunctionalCognitiveStatus($arr_functional_cognitive_status['functional_cognitive_status'], $pid, 0);
        $this->InsertReferrals($arr_referral['referral'], $pid, 0);

        $appTable->zQuery("UPDATE audit_master
                       SET approval_status=2
                       WHERE id=?", array($audit_master_id));
        $appTable->zQuery("UPDATE documents
                       SET audit_master_approval_status=2
                       WHERE audit_master_id=?", array($audit_master_id));
        $appTable->zQuery("UPDATE documents
                       SET foreign_id = ?
                       WHERE id =? ", array($pid,
            $document_id));
    }

    public function formatDate($unformatted_date, $ymd = 1)
    {
        $day = substr($unformatted_date, 6, 2);
        $month = substr($unformatted_date, 4, 2);
        $year = substr($unformatted_date, 0, 4);
        if ($ymd == 1) {
            $formatted_date = $year . "/" . $month . "/" . $day;
        } else {
            $formatted_date = $day . "/" . $month . "/" . $year;
        }

        return $formatted_date;
    }

    public function getOptionId($list_id, $title, $codes = null)
    {
        $appTable = new ApplicationTable();
        if ($title) {
            $query = "SELECT option_id
                FROM list_options
                WHERE list_id=? AND title=?";
            $result = $appTable->zQuery($query, array($list_id, $title));
            $res_cur = $result->current();
        }

        if ($codes !== null) {
            $query = "SELECT option_id
                  FROM list_options
                  WHERE list_id=? AND codes=?";
            $result = $appTable->zQuery($query, array($list_id, $codes));
            $res_cur = $result->current();
        }

        return $res_cur['option_id'];
    }

    public function InsertEncounter($enc_array, $pid, $revapprove = 1)
    {
        if (empty($enc_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($enc_array as $key => $value) {
            $encounter_id = $appTable->generateSequenceID();
            $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
            $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            if ($res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $query_ins_users = "INSERT INTO users
                                ( username,
                                  fname,
                                  npi,
                                  organization,
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
                                  ?,
                                  1,
                                  'external_provider'
                                )";
                $res_query_ins_users = $appTable->zQuery($query_ins_users, array('',
                    $value['provider_name'],
                    $value['provider_npi'],
                    $value['represented_organization_name'],
                    $value['provider_address'],
                    $value['provider_city'],
                    $value['provider_state'],
                    $value['provider_postalCode']));
                      $provider_id = $res_query_ins_users->getGeneratedValue();
            }

            //facility
            $query_sel_fac = "SELECT *
                            FROM users
                            WHERE abook_type='external_org' AND organization=?";
            $res_query_sel_fac = $appTable->zQuery($query_sel_fac, array($value['represented_organization_name']));
            if ($res_query_sel_fac->count() > 0) {
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
                $res_query_ins_fac = $appTable->zQuery($query_ins_fac, array('',
                    $value['represented_organization_name'],
                    $value['represented_organization_telecom'],
                    $value['represented_organization_address'],
                    $value['represented_organization_city'],
                    $value['represented_organization_state'],
                    $value['represented_organization_zip']));
                      $facility_id = $res_query_ins_fac->getGeneratedValue();
            }

            if ($value['date'] != 0 && $revapprove == 0) {
                $encounter_date = $this->formatDate($value['date'], 1);
                $encounter_date_value = fixDate($encounter_date);
            } elseif ($value['date'] != 0 && $revapprove == 1) {
                $encounter_date_value = \Application\Model\ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['date'] == 0) {
                $encounter_date = $value['date'];
                $encounter_date_value = fixDate($encounter_date);
            }

            $q_sel_encounter = "SELECT *
                               FROM form_encounter
                               WHERE external_id=? AND pid=?";
            $res_q_sel_encounter = $appTable->zQuery($q_sel_encounter, array($value['extension'], $pid));
            if ($res_q_sel_encounter->count() == 0) {
                $query_insert1 = "INSERT INTO form_encounter
                           (
                            pid,
                            encounter,
                            date,
                            facility,
                            facility_id,
                            provider_id,
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
                            ?
                           )";
                $result = $appTable->zQuery($query_insert1, array($pid,
                    $encounter_id,
                    $encounter_date_value,
                    $value['represented_organization_name'],
                    $facility_id,
                    $provider_id,
                    $value['extension']));
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
            $appTable->zQuery($q_ins_forms, array($encounter_date_value, $encounter_id, 'New Patient Encounter', $enc_id, $pid, $_SESSION["authProvider"], 'Default', 0, 'newpatient'));
            if ($value['encounter_diagnosis_issue'] != '') {
                $query_select = "SELECT * FROM lists WHERE begdate = ? AND title = ? AND pid = ?";
                $result = $appTable->zQuery($query_select, array($value['encounter_diagnosis_date'], $value['encounter_diagnosis_issue'], $pid));
                if ($result->count() > 0) {
                    foreach ($result as $value1) {
                        $list_id = $value1['id'];
                    }
                } else {
                    //to lists
                    $query_insert = "INSERT INTO lists(pid,type,begdate,activity,title,date, diagnosis) VALUES (?,?,?,?,?,?,?)";
                    $result = $appTable->zQuery($query_insert, array($pid, 'medical_problem', $value['encounter_diagnosis_date'], 1,
                        $value['encounter_diagnosis_issue'], date('Y-m-d H:i:s'), 'SNOMED-CT:' . $value['encounter_diagnosis_code']));
                            $list_id = $result->getGeneratedValue();
                }

                //Linking issue with encounter
                $q_sel_iss_enc = "SELECT * FROM issue_encounter WHERE pid=? and list_id=? and encounter=?";
                $res_sel_iss_enc = $appTable->zQuery($q_sel_iss_enc, array($pid, $list_id, $encounter_id));
                if ($res_sel_iss_enc->count() == 0) {
                    $insert = "INSERT INTO issue_encounter(pid,list_id,encounter,resolved) VALUES (?,?,?,?)";
                    $appTable->zQuery($insert, array($pid, $list_id, $encounter_id, 0));
                }
            }

            //to external_encounters
            $insertEX = "INSERT INTO external_encounters(ee_date,ee_pid,ee_provider_id,ee_facility_id,ee_encounter_diagnosis,ee_external_id) VALUES (?,?,?,?,?,?)";
            $appTable->zQuery($insertEX, array($encounter_date_value, $pid, $provider_id, $facility_id, $value['encounter_diagnosis_issue'], $value['extension']));
        }
    }

    public function InsertVitals($vitals_array, $pid, $revapprove = 1)
    {
        if (empty($vitals_array)) {
            return;
        }
        $appTable = new ApplicationTable();
        foreach ($vitals_array as $key => $value) {
            if ($value['date'] != 0 && $revapprove == 0) {
                $vitals_date = $this->formatDate($value['date'], 1);
                $vitals_date_value = fixDate($vitals_date);
            } elseif ($value['date'] != 0 && $revapprove == 1) {
                $vitals_date_value = \Application\Model\ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['date'] == 0) {
                $vitals_date = $value['date'];
                $vitals_date_value = fixDate($vitals_date);
            }

            $q_sel_vitals = "SELECT *
                           FROM form_vitals
                           WHERE external_id=?";
            $res_q_sel_vitals = $appTable->zQuery($q_sel_vitals, array($value['extension']));
            if ($res_q_sel_vitals->count() == 0) {
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
                          1,
                          ?
                         )";
                $res = $appTable->zQuery($query_insert, array($pid,
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
                    $value['extension']));
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
                             oxygen_saturation=?
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
                $res_enc_cur = $res_enc->current();
                $encounter_for_forms = $res_enc_cur['encounter'];
            } else {
                foreach ($res_query_sel_enc as $value2) {
                    $encounter_for_forms = $value2['encounter'];
                }
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
                $_SESSION[authUser]));
        }
    }

    public function InsertProcedures($proc_array, $pid, $revapprove = 1)
    {
        if (empty($proc_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($proc_array as $key => $value) {
            if ($value['date'] != 0 && $revapprove == 0) {
                $procedure_date = $this->formatDate($value['date'], 1);
                $procedure_date_value = fixDate($procedure_date);
            } elseif ($value['date'] != 0 && $revapprove == 1) {
                $procedure_date_value = \Application\Model\ApplicationTable::fixDate($value['date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['date'] == 0) {
                $procedure_date = $value['date'];
                $procedure_date_value = fixDate($procedure_date);
            }

            //facility1
            $query3 = "SELECT *
                 FROM users
                 WHERE abook_type='external_org' AND organization=?";
            $res3 = $appTable->zQuery($query3, array($value['represented_organization1']));
            if ($res3->count() > 0) {
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
                $res4 = $appTable->zQuery($query4, array('',
                    $value['represented_organization1'],
                    $value['represented_organization_address1'],
                    $value['represented_organization_city1'],
                    $value['represented_organization_state1'],
                    $value['represented_organization_postalcode1']));
                      $facility_id = $res4->getGeneratedValue();
            }

            //facility2
            $query6 = "SELECT *
                 FROM users
                 WHERE abook_type='external_org' AND organization=?";
            $res6 = $appTable->zQuery($query6, array($value['represented_organization2']));
            if ($res6->count() > 0) {
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
                $res_enc_cur = $res_enc->current();
                $encounter_for_billing = $res_enc_cur['encounter'];
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

            $q_select = "SELECT * FROM codes WHERE code_text = ? AND code = ? AND active = ?";
            $res = $appTable->zQuery($q_select, array($value['code_text'], $value['code'], 1));
            if (count($res) == 0) {
                //codes
                $qc_insert = "INSERT INTO codes(code_text,code_text_short,code,code_type,active) VALUES (?,?,?,?,?)";
                $appTable->zQuery($qc_insert, array($value['code_text'], $value['code_text'], $value['code'], $ct_id, 1));
            }

            $query_selectB = "SELECT * FROM external_procedures WHERE ep_code = ? AND ep_code_type = ? AND ep_encounter = ? AND ep_pid = ?";
            $result_selectB = $appTable->zQuery($query_selectB, array($value['code'], $value['codeSystemName'], $encounter_for_billing, $pid));
            if ($result_selectB->count() == 0) {
                //external_procedures
                $qB_insert = "INSERT INTO external_procedures(ep_date,ep_code,ep_code_type,ep_code_text,ep_pid,ep_encounter,ep_facility_id,ep_external_id) VALUES (?,?,?,?,?,?,?,?)";
                $appTable->zQuery($qB_insert, array($procedure_date_value, $value['code'], $value['codeSystemName'], $value['code_text'], $pid, $encounter_for_billing, $facility_id2, $value['extension']));
            }
        }
    }

    public function InsertImmunization($imm_array, $pid, $revapprove = 1)
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
            $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
            $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            if ($res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $query_ins_users = "INSERT INTO users
                            ( fname,
                              npi,
                              organization,
                              street,
                              city,
                              state,
                              zip,
                              phone,
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
                              ?,
                              'external_provider')";
                $res_query_ins_users = $appTable->zQuery($query_ins_users, array($value['provider_name'],
                    $value['provider_npi'],
                    $value['represented_organization'],
                    $value['provider_address'],
                    $value['provider_city'],
                    $value['provider_state'],
                    $value['provider_postalCode'],
                    $value['provider_telecom']));
                      $provider_id = $res_query_ins_users->getGeneratedValue();
            }

            //facility
            $query_sel_fac = "SELECT *
                            FROM users
                            WHERE abook_type='external_org' AND organization=?";
            $res_query_sel_fac = $appTable->zQuery($query_sel_fac, array($value['represented_organization']));
            if ($res_query_sel_fac->count() > 0) {
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
                $immunization_date = $this->formatDate($value['administered_date'], 1);
                $immunization_date_value = fixDate($immunization_date);
            } elseif ($value['administered_date'] != 0 && $revapprove == 1) {
                $immunization_date_value = \Application\Model\ApplicationTable::fixDate($value['administered_date'], 'yyyy-mm-dd', 'dd/mm/yyyy');
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

            $q1_unit = "SELECT *
                       FROM list_options
                       WHERE list_id='drug_units' AND title=?";
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

            $q_sel_imm = "SELECT *
                        FROM immunizations
                        WHERE external_id=? AND patient_id=?";
            $res_q_sel_imm = $appTable->zQuery($q_sel_imm, array($value['extension'], $pid));
            if ($res_q_sel_imm->count() == 0) {
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
                   ?
                  )";
                $appTable->zQuery($query, array($pid,
                    $immunization_date_value,
                    $value['cvx_code'],
                    $value['route_code_text'],
                    $provider_id,
                    $value['amount_administered'],
                    $oid_unit,
                    $value['manufacturer'],
                    $value['completion_status'],
                    $value['extension']));
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

    public function InsertPrescriptions($pres_array, $pid, $revapprove = 1)
    {
        if (empty($pres_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        $oid_route = $unit_option_id = $oidu_unit = '';
        foreach ($pres_array as $key => $value) {
            $active = 1;
            if ($value['enddate'] == '' || $value['enddate'] == 0) {
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

                $value['begdate'] = \Application\Model\ApplicationTable::fixDate($value['begdate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            }

            //provider
            $query_sel_users = "SELECT *
                              FROM users
                              WHERE abook_type='external_provider' AND npi=?";
            $res_query_sel_users = $appTable->zQuery($query_sel_users, array($value['provider_npi']));
            if ($res_query_sel_users->count() > 0) {
                foreach ($res_query_sel_users as $value1) {
                    $provider_id = $value1['id'];
                }
            } else {
                $query_ins_users = "INSERT INTO users
                                ( fname,
                                  lname,
                                  authorized,
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
                                  1,
                                  ?,
                                  ?,
                                  ?,
                                  ?,
                                  1,
                                  'external_provider'
                                )";
                $res_query_ins_users = $appTable->zQuery($query_ins_users, array($value['provider_fname'],
                    $value['provider_lname'],
                    $value['provider_address'],
                    $value['provider_city'],
                    $value['provider_state'],
                    $value['provider_postalCode']
                ));
                      $provider_id = $res_query_ins_users->getGeneratedValue();
            }

            //unit
            if ($revapprove == 1) {
                $value['rate_unit'] = $this->getListTitle($value['rate_unit'], 'drug_units', '');
            }

            $unit_option_id = $this->getOptionId('drug_units', $value['rate_unit'], '');
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
                $appTable->zQuery($q_insert_units_option, array($unit_option_id,$value['rate_unit']));
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

            $q_sel_pres = "SELECT *
                         FROM prescriptions
                         WHERE patient_id = ? AND external_id = ?";
            $res_q_sel_pres = $appTable->zQuery($q_sel_pres, array($pid, $value['extension']));

            if ($res_q_sel_pres->count() == 0) {
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
                    $value['extension']));
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
                           provider_id=?
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
                    $pid));
            }
        }
    }

    public function InsertAllergies($allergy_array, $pid, $revapprove = 1)
    {
        if (empty($allergy_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($allergy_array as $key => $value) {
            $active = 1;

            if ($value['begdate'] != 0 && $revapprove == 0) {
                $allergy_begdate = $this->formatDate($value['begdate'], 1);
                $allergy_begdate_value = fixDate($allergy_begdate);
            } elseif ($value['begdate'] != 0 && $revapprove == 1) {
                $allergy_begdate_value = \Application\Model\ApplicationTable::fixDate($value['begdate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['begdate'] == 0) {
                $allergy_begdate = $value['begdate'];
                $allergy_begdate_value = fixDate($allergy_begdate);
                $allergy_begdate_value = (null);
            }

            if ($value['enddate'] != 0 && $revapprove == 0) {
                $allergy_enddate = $this->formatDate($value['enddate'], 1);
                $allergy_enddate_value = fixDate($allergy_enddate);
            } elseif ($value['enddate'] != 0 && $revapprove == 1) {
                $allergy_enddate_value = \Application\Model\ApplicationTable::fixDate($value['enddate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['enddate'] == 0 || $value['enddate'] == '') {
                $allergy_enddate = $value['enddate'];
                $allergy_enddate_value = fixDate($allergy_enddate);
                $allergy_enddate_value = (null);
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

            $q_sel_allergies = "SELECT *
                              FROM lists
                              WHERE external_id=? AND type='allergy' AND pid=?";
            $res_q_sel_allergies = $appTable->zQuery($q_sel_allergies, array($value['extension'], $pid));

            $severity_option_id = $this->getOptionId('severity_ccda', '', 'SNOMED-CT:' . $value['severity_al']);
            $severity_text = $this->getListTitle($severity_option_id, 'severity_ccda', 'SNOMED-CT:' . $value['severity_al']);
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
                    $appTable->zQuery($q_insert_units_option, array($severity_option_id,$severity_text));
                }
            }

            $reaction_option_id = $this->getOptionId('Reaction', $value['reaction_text'], '');
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
                    $appTable->zQuery($q_insert_units_option, array($reaction_option_id,$value['reaction_text']));
                }
            }

            if ($res_q_sel_allergies->count() == 0) {
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
                    $reaction_option_id ? $reaction_option_id : 0,
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

    public function InsertMedicalProblem($med_pblm_array, $pid, $revapprove = 1)
    {
        if (empty($med_pblm_array)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($med_pblm_array as $key => $value) {
            $activity = 1;

            if ($value['begdate'] != 0 && $revapprove == 0) {
                $med_pblm_begdate = $this->formatDate($value['begdate'], 1);
                $med_pblm_begdate_value = fixDate($med_pblm_begdate);
            } elseif ($value['begdate'] != 0 && $revapprove == 1) {
                $med_pblm_begdate_value = \Application\Model\ApplicationTable::fixDate($value['begdate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['begdate'] == 0) {
                $med_pblm_begdate = $value['begdate'];
                $med_pblm_begdate_value = fixDate($med_pblm_begdate);
                $med_pblm_begdate_value = (null);
            }

            if ($value['enddate'] != 0 && $revapprove == 0) {
                $med_pblm_enddate = $this->formatDate($value['enddate'], 1);
                $med_pblm_enddate_value = fixDate($med_pblm_enddate);
            } elseif ($value['enddate'] != 0 && $revapprove == 1) {
                $med_pblm_enddate_value = \Application\Model\ApplicationTable::fixDate($value['enddate'], 'yyyy-mm-dd', 'dd/mm/yyyy');
            } elseif ($value['enddate'] == 0 || $value['enddate'] == '') {
                $med_pblm_enddate = $value['enddate'];
                $med_pblm_enddate_value = fixDate($med_pblm_enddate);
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
                $appTable->zQuery($q_update, array('outcome', $value['observation_text'], 'SNOMED-CT:' . $value['observation']));
                foreach ($result as $value1) {
                    $o_id = $value1['option_id'];
                }
            } else {
                $lres = $appTable->zQuery("SELECT IFNULL(MAX(CONVERT(SUBSTRING_INDEX(option_id,'-',-1),UNSIGNED INTEGER))+1,1) AS option_id FROM list_options WHERE list_id = ?", array('outcome'));
                foreach ($lres as $lrow) {
                    $o_id = $lrow['option_id'];
                }

                $q_insert = "INSERT INTO list_options (list_id,option_id,title,codes,activity) VALUES (?,?,?,?,?)";
                $appTable->zQuery($q_insert, array('outcome', $o_id, $value['observation_text'], 'SNOMED-CT:' . $value['observation'], 1));
            }

            $q_sel_med_pblm = "SELECT *
                             FROM lists
                             WHERE external_id=? AND type='medical_problem' AND begdate=? AND diagnosis=? AND pid=?";
            $res_q_sel_med_pblm = $appTable->zQuery($q_sel_med_pblm, array($value['extension'], $med_pblm_begdate_value, 'SNOMED-CT:' . $value['list_code'], $pid));
            if ($res_q_sel_med_pblm->count() == 0) {
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
                    external_id
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
                    ?
                  )";
                $result = $appTable->zQuery($query, array($pid,
                    date('y-m-d H:i:s'),
                    'SNOMED-CT:' . $value['list_code'],
                    $activity,
                    $value['list_code_text'],
                    $med_pblm_begdate_value,
                    $med_pblm_enddate_value,
                    $o_id,
                    'medical_problem',
                    $value['extension']));

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
                    'SNOMED-CT:' . $value['list_code'],
                    $value['list_code_text'],
                    $med_pblm_begdate_value,
                    $med_pblm_enddate_value,
                    $o_id,
                    $value['extension'],
                    $value['begdate'],
                    'SNOMED-CT:' . $value['list_code'],
                    $pid));
            }
        }
    }
    public function InsertCarePlan($care_plan_array, $pid, $revapprove = 1)
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

            $query_insert = "INSERT INTO form_care_plan(id,pid,groupname,user,encounter, activity,code,codetext,description,date)VALUES(?,?,?,?,?,?,?,?,?,?)";
            $res = $appTable->zQuery($query_insert, array($newid, $pid, $_SESSION["authProvider"], $_SESSION["authUser"], $encounter_for_forms, 1, $value['code'], $value['text'], $value['description'], date('Y-m-d')));
        }

        if (count($care_plan_array) > 0) {
            $query = "INSERT INTO forms(date,encounter,form_name,form_id,pid,user,groupname,formdir)VALUES(?,?,?,?,?,?,?,?)";
            $appTable->zQuery($query, array(date('Y-m-d'), $encounter_for_forms, 'Care Plan Form', $newid, $pid, $_SESSION["authUser"], $_SESSION["authProvider"], 'care_plan'));
        }
    }

    public function InsertFunctionalCognitiveStatus($functional_cognitive_status_array, $pid, $revapprove = 1)
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
                $date = $this->formatDate($value['date']);
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

    public function InsertReferrals($arr_referral, $pid, $revapprove = 1)
    {
        if (empty($arr_referral)) {
            return;
        }

        $appTable = new ApplicationTable();
        foreach ($arr_referral as $key => $value) {
            $query_insert = "INSERT INTO transactions(date,title,pid,groupname,user,authorized)VALUES(?,?,?,?,?,?)";
            $res = $appTable->zQuery($query_insert, array(date('Y-m-d H:i:s'), 'LBTref', $pid, $_SESSION["authProvider"], $_SESSION["authUser"],$_SESSION["userauthorized"]));
            $trans_id = $res->getGeneratedValue();
            $appTable->zQuery("INSERT INTO lbt_data SET form_id = ?,field_id = ?,field_value = ?", array($trans_id, 'body', $value['body']));
        }
    }

    public function getCodes($option_id, $list_id)
    {
        $appTable = new ApplicationTable();
        if ($option_id) {
            $query = "SELECT codes
                  FROM list_options
                  WHERE list_id=? AND option_id=?";
            $result = $appTable->zQuery($query, array($list_id, $option_id));
            $res_cur = $result->current();
        }

        return $res_cur['codes'];
    }
    /*
     * Fetch list details
     *
     * @param    list_id  string
     * @return   records   Array  list of list details
     */
    public function getList($list)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT title,option_id,notes,codes FROM list_options WHERE list_id = ?";
        $result = $appTable->zQuery($query, array($list));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Referral values of a patient from transactions table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of Referral values
     */

    public function getReferralReason($data)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM transactions
                   WHERE pid = ?";
        $result = $appTable->zQuery($query, array($data['pid']));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }
      /*
   * fetch documentationOf and returns
   *
   * @param audit_master_id   Integer  ID from audi_master table
   */
    public function getdocumentationOf($audit_master_id)
    {
        $appTable = new ApplicationTable();
        $query    = "SELECT documentationOf FROM documents WHERE audit_master_id = ?";
        $result   = $appTable->zQuery($query, array($audit_master_id));
        foreach ($result as $row) {
            $documentationOf = $row['documentationOf'];
        }

        return $documentationOf;
    }

   /*
    * Return the list of CCDA components
    *
    * @param    $type
    * @return   Array       $components
    */
    public function getCCDAComponents($type)
    {
        $components = array();
        $query      = "select * from ccda_components where ccda_type = ?";
        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query, array($type));

        foreach ($result as $row) {
            $components[$row['ccda_components_field']] = $row['ccda_components_name'];
        }

        return $components;
    }

    public function getMonthString($m)
    {
        $m = trim($m);
        if ($m == '01') {
            return "Jan";
        } elseif ($m == '02') {
            return "Feb";
        } elseif ($m == '03') {
            return "March";
        } elseif ($m == '04') {
            return "April";
        } elseif ($m == '05') {
            return "May";
        } elseif ($m == '06') {
            return "June";
        } elseif ($m == '07') {
            return "July";
        } elseif ($m == '08') {
            return "Aug";
        } elseif ($m == '09') {
            return "Sep";
        } elseif ($m == '10') {
            return "Oct";
        } elseif ($m == '11') {
            return "Nov";
        } elseif ($m == '12') {
            return "Dec";
        }
    }

    public function getListCodes($option_id, $list_id)
    {
        $appTable = new ApplicationTable();
        if ($option_id) {
            $query = "SELECT codes
                  FROM list_options
                  WHERE list_id=? AND option_id=?";
            $result = $appTable->zQuery($query, array($list_id, $option_id));
            $res_cur = $result->current();
        }

        return $res_cur['codes'];
    }
}
