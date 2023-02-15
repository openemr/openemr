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

use Application\Model\ApplicationTable;
use Application\Plugin\CommonPlugin;
use Documents\Model\DocumentsTable;
use Documents\Plugin\Documents;
use Exception;
use Laminas\Config\Reader\ReaderInterface;
use Laminas\Config\Reader\Xml;
use Laminas\Db\TableGateway\AbstractTableGateway;
use OpenEMR\Services\Cda\CdaTemplateImportDispose;
use OpenEMR\Services\Cda\CdaTemplateParse;
use OpenEMR\Services\Cda\CdaValidateDocuments;
use OpenEMR\Services\Cda\XmlExtended;
use OpenEMR\Services\CodeTypesService;

class CarecoordinationTable extends AbstractTableGateway
{
    public const NPI_SAMPLE = "987654321";
    public const ORGANIZATION_SAMPLE = "External Physicians Practice";
    public const ORGANIZATION2_SAMPLE = "External Health and Hospitals";
    public $is_qrda_import = false;
    public $is_unstructured_import = false;
    public $validationIsDisabled = false;
    protected $documentData;
    protected $validateDocument;
    private $parseTemplates;
    private $codeService;
    private $importService;

    public function __construct()
    {
        $this->resetData();
        $this->codeService = new CodeTypesService();
        $this->importService = new CdaTemplateImportDispose();
        $this->validateDocument = new CdaValidateDocuments();
        $this->validationIsDisabled = $GLOBALS['ccda_validation_disable'] ?? false;
    }

    /*
     * Fetch the category ID using category name
     *
     * @param       $title      String      Category Name
     * @return      $records    Array       Category ID
     */
    public function fetch_cat_id($title): array
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
    public function fetch_uploaded_documents($data): array
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
    public function document_fetch($data): array
    {
        $direction = $_REQUEST['sort_direction'] ?? 'DESC';
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
            d.document_data,
            am.is_qrda_document,
            am.is_unstructured_document,
            ad.field_value as ad_lname,
            ad1.field_value as ad_fname,
            ad2.field_value as dob_raw,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'encounter' AND field_name = 'date') as enc_count,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'lists1' AND field_name = 'type' AND field_value = 'medical_problem') as prb_count,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'form_care_plan' AND field_name = 'date') as cp_count,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'observation_preformed' AND field_name = 'date') as ob_count,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'procedure' AND field_name = 'date') as proc_count,
            (Select COUNT(field_name) From `audit_details` Where audit_master_id = am.id AND table_name = 'lists3' AND field_name = 'type' AND field_value = 'medication') as med_count,
            ad5.field_value as race,
            ad6.field_value as ethnicity,
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
        LEFT JOIN audit_details ad5 ON ad5.audit_master_id = am.id AND ad5.table_name = 'patient_data' AND ad5.field_name = 'race'
        LEFT JOIN audit_details ad6 ON ad6.audit_master_id = am.id AND ad6.table_name = 'patient_data' AND ad6.field_name = 'ethnicity'
        LEFT JOIN patient_data pd ON pd.lname = ad.field_value AND pd.fname = ad1.field_value AND pd.DOB = DATE(ad2.field_value)
        LEFT JOIN users AS u ON u.id = d.owner
        WHERE d.audit_master_approval_status = 1 AND am.id >= 0
        ORDER BY date " . escape_sort_order($direction); // DESC is default
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
     *  and directly import them into a new patient.
     *
     * @param   $document     Path to xml document
     */
    public function importNewPatient($document): void
    {
        if (!file_exists($document)) {
            error_log("OpenEMR CCDA import error: following file does not exist: " . $document);
            exit;
        }
        $xml_content = file_get_contents($document);
        $this->importCore($xml_content);
        $this->insert_patient(null, null);
    }

    /**
     * @param $xml_content
     * @param $doc_id
     * @return void
     * @throws Exception
     */
    public function importCore($xml_content, $doc_id = null): void
    {
        $xml_content_new = preg_replace('#<br />#', '', $xml_content);
        $xml_content_new = preg_replace('#<br/>#', '', $xml_content_new);
        $xml_content_new = (string)str_replace(array("\n    ", "\n", "\r"), '', $xml_content_new);

        // Note the behavior of this relies on PHP's XMLReader
        // @see https://docs.zendframework.com/zend-config/reader/
        // @see https://php.net/xmlreader
        // 10/27/2022 sjp Extended base reader Laminas XML class using provided interface class
        // Needed to add LIBXML_COMPACT | LIBXML_PARSEHUGE flags because large text node(>10MB) will fail.
        try {
            $xmltoarray = new XmlExtended();
            $xml = $xmltoarray->fromString($xml_content_new);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        // Document various sectional components
        $components = $xml['component']['structuredBody']['component'];
        $qrda_log['message'] = $validation_log = null;
        // test if a QRDA QDM CAT I document type from header OIDs
        $template_oid = $xml['templateId'][2]['root'] ?? null;
        if ($template_oid === '2.16.840.1.113883.10.20.24.1.2') {
            $this->is_qrda_import = 1;
            if (!empty($doc_id) && !$this->validationIsDisabled) {
                $validation_log = $this->validateDocument->validateDocument($xml_content_new, 'qrda1');
            }
            if (count($components[2]["section"]["entry"] ?? []) < 2) {
                $name = $xml["recordTarget"]["patientRole"]["patient"]["name"]["given"] . ' ' .
                    $xml["recordTarget"]["patientRole"]["patient"]["name"]["family"];
                error_log("No QDMs for patient: " . $name);
                $validation_log['xsd'][] = xl("QRDA is empty of content.") . ' ' . text($name);
                $this->is_qrda_import = 2;
            }
            // Offset to Patient Data section
            $this->documentData = $this->parseTemplates->parseQRDAPatientDataSection($components[2]);
        } else {
            if (!empty($doc_id) && !$this->validationIsDisabled) {
                $validation_log = $this->validateDocument->validateDocument($xml_content_new, 'ccda');
            }
            if ($template_oid === '2.16.840.1.113883.10.20.22.1.10') {
                $this->is_unstructured_import = true;
                $this->documentData = $this->parseTemplates->parseUnstructuredComponents($xml);
            } else {
                $this->documentData = $this->parseTemplates->parseCDAEntryComponents($components);
            }
        }

        $this->documentData['approval_status'] = 1;
        $this->documentData['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $this->documentData['type'] = '12';

        //Patient Details
        // Collect patient name (if more than one, then get the legal one)
        if (!empty($xml['recordTarget']['patientRole']['patient']['name'][0]['given'])) {
            $index = 0;
            foreach ($xml['recordTarget']['patientRole']['patient']['name'] as $i => $iValue) {
                if ($iValue['use'] === 'L') {
                    $index = $i;
                }
                if ($iValue['given'][0]['qualifier'] ?? '' === 'BR') {
                    $this->documentData['field_name_value_array']['patient_data'][1]['birth_fname'] = $iValue['given'][0]['_'] ?? null;
                    $this->documentData['field_name_value_array']['patient_data'][1]['birth_lname'] = $iValue['family'] ?? null;
                }
            }
            $name = $xml['recordTarget']['patientRole']['patient']['name'][$index];
        } else {
            $name = $xml['recordTarget']['patientRole']['patient']['name'];
        }
        $patient_role = $xml['recordTarget']['patientRole'];
        if (empty($xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'])) {
            if ($patient_role['patient']['administrativeGenderCode']['code'] == 'F') {
                $patient_role['patient']['administrativeGenderCode']['displayName'] = 'Female';
                $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'] = 'Female';
            } elseif ($patient_role['patient']['administrativeGenderCode']['code'] == 'M') {
                $patient_role['patient']['administrativeGenderCode']['displayName'] = 'Male';
                $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'] = 'Male';
            }
        }
        $this->documentData['field_name_value_array']['patient_data'][1]['fname'] = is_array($name['given']) ? $name['given'][0] : ($name['given'] ?? null);
        $this->documentData['field_name_value_array']['patient_data'][1]['mname'] = is_array($name['given']) ? $name['given'][1] ?? '' : '';
        $this->documentData['field_name_value_array']['patient_data'][1]['lname'] = $name['family'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['title'] = $name['prefix'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['suffix'] = $name['suffix'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['DOB'] = $xml['recordTarget']['patientRole']['patient']['birthTime']['value'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['sex'] = ucfirst(strtolower($xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'] ?? ''));
        if ($this->is_qrda_import ?? false) {
            $this->documentData['field_name_value_array']['patient_data'][1]['pubpid'] = $xml['recordTarget']['patientRole']['id']['extension'] ?? null;
        }
        $this->documentData['field_name_value_array']['patient_data'][1]['referrerID'] = $xml['recordTarget']['patientRole']['id']['extension'] ?? '';
        //$this->documentData['field_name_value_array']['patient_data'][1]['ss'] = $xml['recordTarget']['patientRole']['id'][1]['extension'] ?? null;
        if (is_array($xml['recordTarget']['patientRole']['addr']['streetAddressLine'] ?? null)) {
            $this->documentData['field_name_value_array']['patient_data'][1]['street'] = $xml['recordTarget']['patientRole']['addr']['streetAddressLine'][0] ?? null;
            $this->documentData['field_name_value_array']['patient_data'][1]['street_line_2'] = $xml['recordTarget']['patientRole']['addr']['streetAddressLine'][1] ?? null;
        } else {
            $this->documentData['field_name_value_array']['patient_data'][1]['street'] = $xml['recordTarget']['patientRole']['addr']['streetAddressLine'] ?? null;
        }
        $this->documentData['field_name_value_array']['patient_data'][1]['city'] = $xml['recordTarget']['patientRole']['addr']['city'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['state'] = $xml['recordTarget']['patientRole']['addr']['state'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['postal_code'] = $xml['recordTarget']['patientRole']['addr']['postalCode'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['country_code'] = $xml['recordTarget']['patientRole']['addr']['country'] ?? null;

        if (is_array($xml['recordTarget']['patientRole']['telecom'][0] ?? null)) {
            foreach ($xml['recordTarget']['patientRole']['telecom'] as $tel) {
                if ($tel['use'] == 'MC') {
                    $this->documentData['field_name_value_array']['patient_data'][1]['phone_cell'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
                } elseif ($tel['use'] == 'HP') {
                    $this->documentData['field_name_value_array']['patient_data'][1]['phone_home'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
                } elseif ($tel['use'] == 'WP') {
                    $this->documentData['field_name_value_array']['patient_data'][1]['phone_biz'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
                } elseif ($tel['use'] == 'EC') {
                    $this->documentData['field_name_value_array']['patient_data'][1]['phone_contact'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
                } elseif (stripos($tel['value'], 'mailto:') !== false) {
                    $regex = "/([a-z0-9_\-\.]+)" . "@" . "([a-z0-9-]{1,64})" . "\." . "([a-z]{2,10})/i";
                    $mail = explode('mailto:', ($tel['value'] ?? null));
                    $this->documentData['field_name_value_array']['patient_data'][1]['email'] = null;
                    if (!empty($mail[1])) {
                        $mailto = preg_replace($regex, '\\1@\\2.\\3', $mail[1]);
                        $this->documentData['field_name_value_array']['patient_data'][1]['email'] = $mailto;
                    }
                } else {
                    $this->documentData['field_name_value_array']['patient_data'][1]['phone_contact'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
                }
            }
        } else {
            $tel = $xml['recordTarget']['patientRole']['telecom'];
            if ($tel['use'] == 'MC') {
                $this->documentData['field_name_value_array']['patient_data'][1]['phone_cell'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
            } elseif ($tel['use'] == 'HP') {
                $this->documentData['field_name_value_array']['patient_data'][1]['phone_home'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
            } elseif ($tel['use'] == 'WP') {
                $this->documentData['field_name_value_array']['patient_data'][1]['phone_biz'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
            } elseif ($tel['use'] == 'EC') {
                $this->documentData['field_name_value_array']['patient_data'][1]['phone_contact'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
            } elseif (stripos($tel['value'], 'mailto:') !== false) {
                $regex = "/([a-z0-9_\-\.]+)" . "@" . "([a-z0-9-]{1,64})" . "\." . "([a-z]{2,10})/i";
                $mail = explode('mailto:', ($tel['value'] ?? null));
                $this->documentData['field_name_value_array']['patient_data'][1]['email'] = null;
                if (!empty($mail[1])) {
                    $mailto = preg_replace($regex, '\\1@\\2.\\3', $mail[1]);
                    $this->documentData['field_name_value_array']['patient_data'][1]['email'] = $mailto;
                }
            } else {
                $this->documentData['field_name_value_array']['patient_data'][1]['phone_contact'] = preg_replace('/[^0-9]+/i', '', ($tel['value'] ?? null));
            }
        }

        $this->documentData['field_name_value_array']['patient_data'][1]['status'] = strtolower($xml['recordTarget']['patientRole']['patient']['maritalStatusCode']['displayName']) ?? $xml['recordTarget']['patientRole']['patient']['maritalStatusCode']['code'] ?? null;
        $this->documentData['field_name_value_array']['patient_data'][1]['religion'] = $xml['recordTarget']['patientRole']['patient']['religiousAffiliationCode']['displayName'] ?? null;
        if (is_array($xml['recordTarget']['patientRole']['patient']['raceCode'][0])) {
            $this->documentData['field_name_value_array']['patient_data'][1]['race'] = $xml['recordTarget']['patientRole']['patient']['raceCode'][0]['displayName'] ?? $xml['recordTarget']['patientRole']['patient']['raceCode'][0]['code'] ?? null;
        } else {
            $this->documentData['field_name_value_array']['patient_data'][1]['race'] = $xml['recordTarget']['patientRole']['patient']['raceCode']['displayName'] ?? $xml['recordTarget']['patientRole']['patient']['raceCode']['code'] ?? null;
        }
        $ecode = $xml['recordTarget']['patientRole']['patient']['ethnicGroupCode']['code'] ?? null;
        switch ($ecode) {
            case '2135-2':
                $ecode = 'hisp_or_latin';
                break;
            case '2186-5':
                $ecode = 'not_hisp_or_latin';
                break;
        }
        $this->documentData['field_name_value_array']['patient_data'][1]['ethnicity'] = $ecode ?: $xml['recordTarget']['patientRole']['patient']['ethnicGroupCode']['displayName'] ?? null;

        $patient_language = substr(($xml['recordTarget']['patientRole']['patient']['languageCommunication']['languageCode']['code'] ?? null), 0, 2);
        $patient_language = sqlQuery("SELECT `option_id`  FROM `list_options` WHERE `list_id` = 'language' And `notes` = ?", [$patient_language])['option_id'];
        $this->documentData['field_name_value_array']['patient_data'][1]['language'] = $patient_language ?: '';

        //Author details
        $this->documentData['field_name_value_array']['author'][1]['extension'] = $xml['author']['assignedAuthor']['id']['extension'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['address'] = $xml['author']['assignedAuthor']['addr']['streetAddressLine'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['city'] = $xml['author']['assignedAuthor']['addr']['city'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['state'] = $xml['author']['assignedAuthor']['addr']['state'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['zip'] = $xml['author']['assignedAuthor']['addr']['postalCode'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['country'] = $xml['author']['assignedAuthor']['addr']['country'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['phone'] = $xml['author']['assignedAuthor']['telecom']['value'] ?? null;
        $this->documentData['field_name_value_array']['author'][1]['name'] = $xml['author']['assignedAuthor']['assignedPerson']['name']['given'] ?? null;

        //Data Enterer
        $this->documentData['field_name_value_array']['dataEnterer'][1]['extension'] = $xml['dataEnterer']['assignedEntity']['id']['extension'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['address'] = $xml['dataEnterer']['assignedEntity']['addr']['streetAddressLine'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['city'] = $xml['dataEnterer']['assignedEntity']['addr']['city'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['state'] = $xml['dataEnterer']['assignedEntity']['addr']['state'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['zip'] = $xml['dataEnterer']['assignedEntity']['addr']['postalCode'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['country'] = $xml['dataEnterer']['assignedEntity']['addr']['country'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['phone'] = $xml['dataEnterer']['assignedEntity']['telecom']['value'] ?? null;
        $this->documentData['field_name_value_array']['dataEnterer'][1]['name'] = $xml['dataEnterer']['assignedEntity']['assignedPerson']['name']['given'] ?? null;

        //Informant
        $this->documentData['field_name_value_array']['informant'][1]['extension'] = $xml['informant'][0]['assignedEntity']['id']['extension'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['street'] = $xml['informant'][0]['assignedEntity']['addr']['streetAddressLine'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['city'] = $xml['informant'][0]['assignedEntity']['addr']['city'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['state'] = $xml['informant'][0]['assignedEntity']['addr']['state'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['postalCode'] = $xml['informant'][0]['assignedEntity']['addr']['postalCode'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['country'] = $xml['informant'][0]['assignedEntity']['addr']['country'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['phone'] = $xml['informant'][0]['assignedEntity']['telecom']['value'] ?? null;
        $this->documentData['field_name_value_array']['informant'][1]['name'] = $xml['informant'][0]['assignedEntity']['assignedPerson']['name']['given'] ?? null;

        //Personal Informant
        $this->documentData['field_name_value_array']['custodian'][1]['extension'] = $xml['custodian']['assignedCustodian']['representedCustodianOrganization']['id']['extension'] ?? null;
        $this->documentData['field_name_value_array']['custodian'][1]['organisation'] = $xml['custodian']['assignedCustodian']['representedCustodianOrganization']['name'] ?? null;

        //documentationOf
        $doc_of_str = '';
        if (!empty($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['prefix']) && !is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['prefix'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['prefix'] . " ";
        }

        if (!empty($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['given']) && !is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['given'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['given'] . " ";
        }

        if (!empty($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['family']) && !is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['family'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['assignedPerson']['name']['family'] . " ";
        }

        if (!empty($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['representedOrganization']['name']) && !is_array($xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['representedOrganization']['name'])) {
            $doc_of_str .= $xml['documentationOf']['serviceEvent']['performer'][0]['assignedEntity']['representedOrganization']['name'] . " ";
        }

        $this->documentData['field_name_value_array']['documentationOf'][1]['assignedPerson'] = $doc_of_str;
        // test if patient is deceased
        if ($this->is_qrda_import) {
            foreach ($components[2]["section"]["entry"] as $entry_search) {
                if (($entry_search["observation"]["value"]["code"] ?? null) == '419099009') {
                    $deceased_date = $entry_search["observation"]["effectiveTime"]["low"]["value"];
                    $this->documentData['field_name_value_array']['patient_data'][1]['deceased_date'] = date('Y-m-d H:i:s', strtotime($deceased_date));
                    $this->documentData['field_name_value_array']['patient_data'][1]['deceased_reason'] = 'SNOMED-CT:419099009';
                    break;
                }
            }
        }
        if (!empty($doc_id)) {
            $this->validateDocument->saveValidationLog($doc_id, $validation_log);
        }
    }

    /*
     * Fetch the component values from the CCDA XML
     *
     * @param   $xml_content     The xml document
     */

    public function insert_patient($audit_master_id, $document_id)
    {
        require_once(__DIR__ . "/../../../../../../../../library/patient.inc.php");
        $pid = 0;
        $a = 1;
        $b = 1;
        $c = 1;
        $d = 1;
        $e = 1;
        $f = 1;
        $g = 1;
        $h = 1;
        $j = 1;
        $k = 1;
        $l = 1;
        $p = 1; // payer QRDA
        $q = 1;
        $y = 1;

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
        $arr_observation_preformed = array();

        $appTable = new ApplicationTable();

        $pres = $appTable->zQuery("SELECT IFNULL(MAX(pid)+1,1) AS pid FROM patient_data");
        foreach ($pres as $prow) {
            $pid = $prow['pid'];
        }
        if (!empty($audit_master_id)) {
            $res = $appTable->zQuery("SELECT DISTINCT am.is_qrda_document, am.is_unstructured_document, ad.table_name, entry_identification
                                     FROM audit_master as am,audit_details as ad
                                     WHERE am.id=ad.audit_master_id AND
                                     am.approval_status = '1' AND
                                     am.id=? AND am.type=12
                                     ORDER BY ad.id", array($audit_master_id));
        } else {
            // collect directly from $this->documentData (ie. no audit table middleman)
            $res = [];
            foreach ($this->documentData['field_name_value_array'] as $subKey => $subArray) {
                $tableName = $subKey;
                foreach ($subArray as $subsubKey => $subsubArray) {
                    $entryIdentification = $subsubKey;
                    $res[] = ['table_name' => trim($tableName), 'entry_identification' => trim($entryIdentification)];
                }
            }
        }
        foreach ($res as $row) {
            $this->is_qrda_import = $row['is_qrda_document'] ?? false;
            $this->is_unstructured_import = $row['is_unstructured_document'] ?? false;
            if (!empty($audit_master_id)) {
                $resfield = $appTable->zQuery(
                    "SELECT *
                             FROM audit_details
                             WHERE audit_master_id=? AND
                             table_name=? AND
                             entry_identification=?",
                    array($audit_master_id, $row['table_name'], $row['entry_identification'])
                );
            } else {
                // collect directly from $this->documentData (ie. no audit table middleman)
                $resfield = [];
                foreach ($this->documentData['field_name_value_array'][$row['table_name']][$row['entry_identification']] as $itemKey => $item) {
                    if (is_array($item)) {
                        if (!empty($item['status']) || !empty($item['enddate'])) {
                            $item = trim($item['value'] ?? '') . "|" . trim($item['status'] ?? '') . "|" . trim($item['begdate'] ?? '');
                        } else {
                            $item = trim($item['value'] ?? '');
                        }
                    } else {
                        $item = trim($item);
                    }
                    $resfield[] = ['table_name' => trim($row['table_name']), 'field_name' => trim($itemKey), 'field_value' => $item, 'entry_identification' => trim($row['entry_identification'])];
                }
            }
            $table = $row['table_name'];
            $newdata = array();
            foreach ($resfield as $rowfield) {
                if ($table == 'patient_data') {
                    if ($rowfield['field_name'] == 'DOB') {
                        $dob = $this->formatDate($rowfield['field_value'], 1);
                        $newdata['patient_data'][$rowfield['field_name']] = $dob;
                    } else {
                        if ($rowfield['field_name'] == 'religion') {
                            $religion_option_id = $this->getOptionId('religious_affiliation', $rowfield['field_value'], $rowfield['field_value']);
                            $newdata['patient_data'][$rowfield['field_name']] = $religion_option_id;
                        } elseif ($rowfield['field_name'] == 'race') {
                            $race_option_id = $this->getOptionId('race', $rowfield['field_value'], $rowfield['field_value']);
                            $newdata['patient_data'][$rowfield['field_name']] = $race_option_id;
                        } elseif ($rowfield['field_name'] == 'ethnicity') {
                            $newdata['patient_data'][$rowfield['field_name']] = $rowfield['field_value'];
                        } else {
                            $newdata['patient_data'][$rowfield['field_name']] = $rowfield['field_value'] ?? null;
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
                } elseif ($table == 'observation_preformed') {
                    $newdata['observation_preformed'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'payer') {
                    $newdata['payer'][$rowfield['field_name']] = $rowfield['field_value'];
                } elseif ($table == 'import_file') {
                    $newdata['import_file'][$rowfield['field_name']] = $rowfield['field_value'];
                }
            }
            if ($table == 'patient_data') {
                $createFlag = true;
                if (!empty($newdata['patient_data']['referrerID'])) {
                    // patient UUID from exported
                    $uuid = trim($newdata['patient_data']['referrerID']);
                    // have we already imported for this UUID?
                    $pid_exist = sqlQuery("SELECT pid FROM `patient_data` WHERE `referrerID` = ? ORDER BY `pid` DESC Limit 1", array($uuid))['pid'];
                    if (!empty($pid_exist) && is_numeric($pid_exist ?? null)) {
                        // We did so let check the type. If encounters then a CDA
                        $enc_exist = sqlQuery("SELECT COUNT(`encounter`) as `cnt` FROM `form_encounter` WHERE `pid` = ? AND `encounter` > 0", array((int)$pid_exist))['cnt'] ?? 0;
                        // If not CDA and not unstructured means unstructured already created a new PID
                        // otherwise merge one or the other to each other.
                        if ((!$this->is_unstructured_import && empty($enc_exist)) || $this->is_unstructured_import) {
                            $pid = $pid_exist;
                            $createFlag = false;
                        }
                    }
                }
                updatePatientData($pid, $newdata['patient_data'], $createFlag);
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
                // reason
                $arr_immunization['immunization'][$a]['reason_code'] = $newdata['immunization']['reason_code'] ?? null;
                $arr_immunization['immunization'][$a]['reason_description'] = $newdata['immunization']['reason_description'] ?? null;
                $arr_immunization['immunization'][$a]['reason_status'] = $newdata['immunization']['reason_status'] ?? null;

                $arr_immunization['immunization'][$a]['provider_npi'] = $newdata['immunization']['provider_npi'];
                $arr_immunization['immunization'][$a]['provider_name'] = $newdata['immunization']['provider_name'];
                $arr_immunization['immunization'][$a]['provider_family'] = $newdata['immunization']['provider_family'];
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
                $arr_prescriptions['lists3'][$b]['enddate'] = $newdata['lists3']['enddate'] ?? null;
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
            } elseif ($table == 'lists1' && !empty($newdata['lists1']['list_code'])) {
                $arr_med_pblm['lists1'][$d]['extension'] = $newdata['lists1']['extension'];
                $arr_med_pblm['lists1'][$d]['root'] = $newdata['lists1']['root'];
                $arr_med_pblm['lists1'][$d]['begdate'] = $newdata['lists1']['begdate'];
                $arr_med_pblm['lists1'][$d]['enddate'] = $newdata['lists1']['enddate'];
                $arr_med_pblm['lists1'][$d]['list_code'] = $newdata['lists1']['list_code'];
                $arr_med_pblm['lists1'][$d]['list_code_text'] = $newdata['lists1']['list_code_text'];
                $arr_med_pblm['lists1'][$d]['status'] = $newdata['lists1']['status'];
                $arr_med_pblm['lists1'][$d]['observation_text'] = $newdata['lists1']['observation_text'];
                $arr_med_pblm['lists1'][$d]['observation_code'] = $newdata['lists1']['observation'];
                $arr_med_pblm['lists1'][$d]['subtype'] = $newdata['lists1']['subtype'];
                $d++;
            } elseif ($table == 'lists2' && !empty($newdata['lists2']['list_code'])) {
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
                $arr_encounter['encounter'][$k]['date_end'] = $newdata['encounter']['date_end'] ?? null;

                $arr_encounter['encounter'][$k]['provider_npi'] = $newdata['encounter']['provider_npi'];
                $arr_encounter['encounter'][$k]['provider_name'] = $newdata['encounter']['provider_name'];
                $arr_encounter['encounter'][$k]['provider_family'] = $newdata['encounter']['provider_family'];
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

                $arr_encounter['encounter'][$k]['code'] = $newdata['encounter']['code'];
                $arr_encounter['encounter'][$k]['code_text'] = $newdata['encounter']['code_text'];
                $arr_encounter['encounter'][$k]['encounter_diagnosis_date'] = $newdata['encounter']['encounter_diagnosis_date'];
                $arr_encounter['encounter'][$k]['encounter_diagnosis_code'] = $newdata['encounter']['encounter_diagnosis_code'];
                $arr_encounter['encounter'][$k]['encounter_diagnosis_issue'] = $newdata['encounter']['encounter_diagnosis_issue'];
                $arr_encounter['encounter'][$k]['encounter_discharge_code'] = $newdata['encounter']['encounter_discharge_code'];
                $k++;
            } elseif ($table == 'vital_sign') {
                $arr_vitals['vitals'][$q]['extension'] = $newdata['vital_sign']['extension'];
                $arr_vitals['vitals'][$q]['date'] = $newdata['vital_sign']['date'];
                $arr_vitals['vitals'][$q]['temperature'] = $newdata['vital_sign']['temperature'] ?? null;
                $arr_vitals['vitals'][$q]['bpd'] = $newdata['vital_sign']['bpd'] ?? null;
                $arr_vitals['vitals'][$q]['bps'] = $newdata['vital_sign']['bps'] ?? null;
                $arr_vitals['vitals'][$q]['head_circ'] = $newdata['vital_sign']['head_circ'] ?? null;
                $arr_vitals['vitals'][$q]['pulse'] = $newdata['vital_sign']['pulse'] ?? null;
                $arr_vitals['vitals'][$q]['height'] = $newdata['vital_sign']['height'] ?? null;
                $arr_vitals['vitals'][$q]['oxygen_saturation'] = $newdata['vital_sign']['oxygen_saturation'] ?? null;
                $arr_vitals['vitals'][$q]['respiration'] = $newdata['vital_sign']['respiration'] ?? null;
                $arr_vitals['vitals'][$q]['weight'] = $newdata['vital_sign']['weight'] ?? null;
                $arr_vitals['vitals'][$q]['BMI'] = $newdata['vital_sign']['BMI'] ?? null;
                $arr_vitals['vitals'][$q]['vital_column'] = $newdata['vital_sign']['vital_column'] ?? '';
                $arr_vitals['vitals'][$q]['reason_code'] = $newdata['vital_sign']['reason_code'] ?? null;
                $arr_vitals['vitals'][$q]['reason_code_text'] = $newdata['vital_sign']['reason_code_text'] ?? null;
                $arr_vitals['vitals'][$q]['reason_description'] = $newdata['vital_sign']['reason_description'] ?? null;
                $arr_vitals['vitals'][$q]['reason_date_low'] = $newdata['vital_sign']['reason_date_low'] ?? null;
                $arr_vitals['vitals'][$q]['reason_date_high'] = $newdata['vital_sign']['reason_date_high'] ?? null;
                $arr_vitals['vitals'][$q]['reason_status'] = $newdata['vital_sign']['reason_status'] ?? null;
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
                $alcohol = explode("|", $newdata['social_history']['alcohol'] ?? '');
                if (!empty($alcohol[2])) {
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

                $tobacco = explode("|", $newdata['social_history']['smoking'] ?? '');
                if (!empty($tobacco[2])) {
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
                if (!empty($newdata['procedure_result']['date'])) {
                    $proc_date = $this->formatDate($newdata['procedure_result']['date'], 1);
                } else {
                    $proc_date = $newdata['procedure_result']['date'];
                }

                if (!empty($newdata['procedure_result']['results_date'])) {
                    $proc_result_date = $this->formatDate($newdata['procedure_result']['results_date'], 1);
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
                $arr_procedure_res['procedure_result'][$j]['results_unit'] = $newdata['procedure_result']['results_unit'];
                $arr_procedure_res['procedure_result'][$j]['results_value'] = $newdata['procedure_result']['results_value'];
                $arr_procedure_res['procedure_result'][$j]['results_date'] = $proc_result_date;
                $j++;
            } elseif ($table == 'procedure') {
                $arr_procedures['procedure'][$y]['extension'] = $newdata['procedure']['extension'];
                $arr_procedures['procedure'][$y]['root'] = $newdata['procedure']['root'];
                $arr_procedures['procedure'][$y]['codeSystemName'] = $newdata['procedure']['codeSystemName'] ?? '';
                $arr_procedures['procedure'][$y]['code'] = $newdata['procedure']['code'] ?? '';
                $arr_procedures['procedure'][$y]['code_text'] = $newdata['procedure']['code_text'] ?? '';
                $arr_procedures['procedure'][$y]['date'] = $newdata['procedure']['date'];

                $arr_procedures['procedure'][$y]['status'] = $newdata['procedure']['status'];
                $arr_procedures['procedure'][$y]['procedure_type'] = $newdata['procedure']['procedure_type'];

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
                // reason
                $arr_procedures['procedure'][$y]['reason_code'] = $newdata['procedure']['reason_code'] ?? null;
                $arr_procedures['procedure'][$y]['reason_code_text'] = $newdata['procedure']['reason_code_text'] ?? null;
                $arr_procedures['procedure'][$y]['reason_description'] = $newdata['procedure']['reason_description'] ?? null;
                $arr_procedures['procedure'][$y]['reason_date_low'] = $newdata['procedure']['reason_date_low'] ?? null;
                $arr_procedures['procedure'][$y]['reason_date_high'] = $newdata['procedure']['reason_date_high'] ?? null;
                $arr_procedures['procedure'][$y]['reason_status'] = $newdata['procedure']['reason_status'] ?? null;
                $y++;
            } elseif ($table == 'care_plan') {
                $arr_care_plan['care_plan'][$e]['extension'] = $newdata['care_plan']['extension'];
                $arr_care_plan['care_plan'][$e]['negate'] = $newdata['care_plan']['negate'] ?? null;
                $arr_care_plan['care_plan'][$e]['root'] = $newdata['care_plan']['root'];
                $arr_care_plan['care_plan'][$e]['text'] = $newdata['care_plan']['code_text'];
                $arr_care_plan['care_plan'][$e]['code'] = $newdata['care_plan']['code'];
                $arr_care_plan['care_plan'][$e]['description'] = $newdata['care_plan']['description'];
                $arr_care_plan['care_plan'][$e]['plan_type'] = $newdata['care_plan']['plan_type'];
                $arr_care_plan['care_plan'][$e]['date'] = $newdata['care_plan']['date'] ?? null;
                $arr_care_plan['care_plan'][$e]['end_date'] = $newdata['care_plan']['end_date'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_code'] = $newdata['care_plan']['reason_code'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_code_text'] = $newdata['care_plan']['reason_code_text'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_description'] = $newdata['care_plan']['reason_description'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_date_low'] = $newdata['care_plan']['reason_date_low'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_date_high'] = $newdata['care_plan']['reason_date_high'] ?? null;
                $arr_care_plan['care_plan'][$e]['reason_status'] = $newdata['care_plan']['reason_status'] ?? null;
                $e++;
            } elseif ($table == 'functional_cognitive_status') {
                $arr_functional_cognitive_status['functional_cognitive_status'][$f]['cognitive'] = $newdata['functional_cognitive_status']['cognitive'];
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
            } elseif ($table == 'observation_preformed') {
                $arr_observation_preformed['observation_preformed'][$h]['extension'] = $newdata['observation_preformed']['extension'];
                $arr_observation_preformed['observation_preformed'][$h]['root'] = $newdata['observation_preformed']['root'];
                $arr_observation_preformed['observation_preformed'][$h]['date'] = $newdata['observation_preformed']['date'];
                $arr_observation_preformed['observation_preformed'][$h]['date_end'] = $newdata['observation_preformed']['date_end'];

                $arr_observation_preformed['observation_preformed'][$h]['observation'] = $newdata['observation_preformed']['observation'];
                $arr_observation_preformed['observation_preformed'][$h]['observation_type'] = $newdata['observation_preformed']['observation_type'];
                $arr_observation_preformed['observation_preformed'][$h]['observation_status'] = $newdata['observation_preformed']['observation_status'];

                $arr_observation_preformed['observation_preformed'][$h]['code'] = $newdata['observation_preformed']['code'];
                $arr_observation_preformed['observation_preformed'][$h]['code_text'] = $newdata['observation_preformed']['code_text'];

                $arr_observation_preformed['observation_preformed'][$h]['result_status'] = $newdata['observation_preformed']['result_status'];
                $arr_observation_preformed['observation_preformed'][$h]['result_code'] = $newdata['observation_preformed']['result_code'];
                $arr_observation_preformed['observation_preformed'][$h]['result_code_text'] = $newdata['observation_preformed']['result_code_text'];
                $arr_observation_preformed['observation_preformed'][$h]['result_code_unit'] = $newdata['observation_preformed']['result_code_unit'];
                $arr_observation_preformed['observation_preformed'][$h]['reason_status'] = $newdata['observation_preformed']['reason_status'];
                $arr_observation_preformed['observation_preformed'][$h]['reason_code'] = $newdata['observation_preformed']['reason_code'];
                $arr_observation_preformed['observation_preformed'][$h]['reason_code_text'] = $newdata['observation_preformed']['reason_code_text'];
                $h++;
            } elseif ($table == 'payer') {
                $arr_payer['payer'][$p]['code'] = $newdata['payer']['code'];
                $arr_payer['payer'][$p]['status'] = $newdata['payer']['status'];
                $arr_payer['payer'][$p]['low_date'] = $newdata['payer']['low_date'];
                $arr_payer['payer'][$p]['high_date'] = $newdata['payer']['high_date'];
                $p++;
            } elseif ($table == 'import_file') {
                $arr_files['import_file'][$l]['uuid'] = $newdata['import_file']['uuid'];
                $arr_files['import_file'][$l]['hash'] = $newdata['import_file']['hash'];
                $arr_files['import_file'][$l]['mediaType'] = $newdata['import_file']['mediaType'] ?? '';
                $arr_files['import_file'][$l]['category'] = $newdata['import_file']['category'] ?? '';
                $arr_files['import_file'][$l]['file_name'] = $newdata['import_file']['file_name'] ?? '';
                $arr_files['import_file'][$l]['compression'] = $newdata['import_file']['compression'] ?? '';
                $arr_files['import_file'][$l]['content'] = $newdata['import_file']['content'] ?? '';
                $l++;
            }
        }

        $this->importService->InsertImmunization(($arr_immunization['immunization'] ?? null), $pid, $this, 0);
        $this->importService->InsertPrescriptions(($arr_prescriptions['lists3'] ?? null), $pid, $this, 0);
        $this->importService->InsertAllergies(($arr_allergies['lists2'] ?? null), $pid, $this, 0);
        $this->importService->InsertMedicalProblem(($arr_med_pblm['lists1'] ?? null), $pid, $this, 0);
        $this->importService->InsertEncounter(($arr_encounter['encounter'] ?? null), $pid, $this, 0);
        $this->importService->InsertVitals(($arr_vitals['vitals'] ?? null), $pid, $this, 0);
        $lab_results = $this->buildLabArray($arr_procedure_res['procedure_result'] ?? null);
        $this->importService->InsertProcedures(($arr_procedures['procedure'] ?? null), $pid, $this, 0);
        $this->importService->InsertLabResults($lab_results, $pid, $this);
        $this->importService->InsertCarePlan(($arr_care_plan['care_plan'] ?? null), $pid, $this, 0);
        $this->importService->InsertFunctionalCognitiveStatus(($arr_functional_cognitive_status['functional_cognitive_status'] ?? null), $pid, $this, 0);
        $this->importService->InsertReferrals(($arr_referral['referral'] ?? null), $pid, 0);
        $this->importService->InsertObservationPerformed(($arr_observation_preformed['observation_preformed'] ?? null), $pid, $this, 0);
        $this->importService->InsertPayers(($arr_payer['payer'] ?? null), $pid, $this, 0);
        $this->importService->InsertImportedFiles(($arr_files['import_file'] ?? null), $pid, $this, 0);

        if (!empty($audit_master_id)) {
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

    /*
     * Fetch a document from the database
     *
     * @param   $document_id        Integer     Document ID
     * @return  $content        String      File content
     */

    public function getOptionId($list_id, $title, $codes = null)
    {
        $appTable = new ApplicationTable();
        $res_cur = null;
        if ($title) {
            $query = "SELECT option_id
                FROM list_options
                WHERE list_id=? AND title=?";
            $result = $appTable->zQuery($query, array($list_id, $title));
            $res_cur = $result->current();
        }

        if (!empty($codes && empty($res_cur))) {
            $query = "SELECT option_id
                  FROM list_options
                  WHERE list_id=? AND (codes=? || notes=?)";
            $result = $appTable->zQuery($query, array($list_id, $codes, $codes));
            $res_cur = $result->current();
        }

        return ($res_cur['option_id'] ?? null);
    }

    public function getListTitle(string $option_id = null, $list_id, $codes = '')
    {
        $appTable = new ApplicationTable();
        $res_cur = null;
        if ($option_id) {
            $query = "SELECT title
                  FROM list_options
                  WHERE list_id=? AND option_id=? AND activity=?";
            $result = $appTable->zQuery($query, array($list_id, $option_id, 1));
            $res_cur = $result->current();
        }

        if (!empty($codes) && empty($res_cur)) {
            $query = "SELECT title
                  FROM list_options
                  WHERE list_id=? AND (codes=? OR option_id=?) AND activity=?";
            $result = $appTable->zQuery($query, array($list_id, $codes, $option_id, 1));
            $res_cur = $result->current();
        }

        return ($res_cur['title'] ?? null);
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
            // @todo fix below conditional to work for CCD.
            if (!empty($lab_results[$value['extension']]['result']) && is_countable($lab_results[$value['extension']]['result'])) {
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
                $lab_results[$value['extension']]['result'][$j]['result_unit'] = $value['results_unit'];
            } elseif (!empty($value['extension'])) {
                $j = 0;
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
                $lab_results[$value['extension']]['result'][$j]['result_unit'] = $value['results_unit'];
            }
        }

        return $lab_results;
    }

    public function import($document_id)
    {
        $this->resetData();
        $xml_content = $this->getDocument($document_id);
        $this->importCore($xml_content, $document_id);
        $audit_master_approval_status = 1;
        $documentationOf = $this->documentData['field_name_value_array']['documentationOf'][1]['assignedPerson'];
        $audit_master_id = CommonPlugin::insert_ccr_into_audit_data($this->documentData, $this->is_qrda_import, $this->is_unstructured_import);
        $this->update_document_table($document_id, $audit_master_id, $audit_master_approval_status, $documentationOf);
    }

    public static function getDocument($document_id): string
    {
        return Documents::getDocument($document_id);
    }

    public function update_document_table($document_id, $audit_master_id, $audit_master_approval_status, $documentationOf): void
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

    public function getCategory()
    {
        $doc_obj = new DocumentsTable();
        return $doc_obj->getCategory();
    }

    public function getIssues($pid)
    {
        // @todo Beware getIssues() doesn't exist in DocumentTable()! Method not used
        $doc_obj = new DocumentsTable();
        $issues = $doc_obj->getIssues($pid);
        return $issues;
    }

    public function getCategoryIDs(): string
    {
        $doc_obj = new DocumentsTable();
        return implode("|", $doc_obj->getCategoryIDs(array('CCD', 'CCR', 'CCDA')));
    }

    public function getDemographics($data): array
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

    public function getProblems($data): array
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

    public function getAllergies($data): array
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

    public function getMedications($data): array
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

    public function getImmunizations($data): array
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

    public function getLabResults($data): array
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

    public function getVitals($data): array
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

    public function getSocialHistory($data): array
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

    public function getEncounterData($data): array
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

    public function getProcedure($data): array
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

    public function getCarePlan($data): array
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

    public function getFunctionalCognitiveStatus($data): array
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

    public function createAuditArray($am_id, $table_name): array
    {
        $appTable = new ApplicationTable();
        if (strpos($table_name, ',')) {
            $tables = explode(',', $table_name);
            $arr = array($am_id);
            $table_qry = "";
            for ($i = 0, $iMax = count($tables); $i < $iMax; $i++) {
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

        $p1_arr = explode("||", $data['problem1check'] ?? '');
        $p2_arr = explode('||', $data['problem2check'] ?? '');
        $p3_arr = explode('||', $data['problem3check'] ?? '');
        $a1_arr = explode("||", $data['allergy1check'] ?? '');
        $a2_arr = explode('||', $data['allergy2check'] ?? '');
        $a3_arr = explode('||', $data['allergy3check'] ?? '');
        $m1_arr = explode("||", $data['med1check'] ?? '');
        $m2_arr = explode('||', $data['med2check'] ?? '');
        $m3_arr = explode('||', $data['med3check'] ?? '');

        foreach ($data as $key => $val) {
            if (substr($key, -4) == '-sel') {
                if (is_array($val)) {
                    for ($i = 0, $iMax = count($val); $i < $iMax; $i++) {
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
                                $arr_immunization['immunization'][$a]['provider_name'] = $data['immunization-provider_name'][$i] ?? '';
                                $arr_immunization['immunization'][$a]['provider_family'] = $data['immunization-provider_family'][$i] ?? '';
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
                                    ApplicationTable::fixDate($data['social_history-tobacco_date'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy') . "|" . $data['social_history-tobacco_snomed'][$i];
                                $alcohol = $data['social_history-alcohol_note'][$i] . "|" .
                                    $data['social_history-alcohol_status'][$i] . "|" .
                                    ApplicationTable::fixDate($data['social_history-alcohol_date'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy');
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
                                $arr_encounter['encounter'][$k]['encounter_discharge_code'] = $data['encounter-encounter_discharge_code'][$i] ?? '';
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
                                $arr_procedure_res['procedure_result'][$j]['results_unit'] = $data['procedure_result-result_unit'][$i];
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
                                $arr_care_plan['care_plan'][$e]['plan_type'] = $data['care_plan']['plan_type'][$i];
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
                                    ApplicationTable::fixDate($data['lists1-begdate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
                                    ApplicationTable::fixDate($data['lists1-enddate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
                                    $o_id,
                                    $data['pid'],
                                    $data['lists1-old-id-con'][$i]));

                                if ($p1_arr[$i] == 1) {
                                    $query7 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query7, array(date('Y-m-d'), $data['pid'], $data['lists1-old-id-con'][$i]));
                                } elseif ($p1_arr[$i] == 0) {
                                    $query7 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query7, array((null), $data['pid'], $data['lists1-old-id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists1_exist') {
                                if ($p2_arr[$i] == 1) {
                                    $query4 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query4, array(date('Y-m-d'), $data['pid'], $data['lists1_exist-list_id'][$i]));
                                } elseif ($p2_arr[$i] == 0) {
                                    $query4 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query4, array((null), $data['pid'], $data['lists1_exist-list_id'][$i]));
                                }
                            } elseif (substr($key, 0, -4) == 'lists2-con') {
                                if (!empty($data['lists2-begdate-con'][$i])) {
                                    $allergy_begdate_value = ApplicationTable::fixDate($data['lists2-begdate-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy');
                                } elseif (empty($data['lists2-begdate-con'][$i])) {
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
                                        $appTable->zQuery($q_insert_units_option, array($severity_option_id, $severity_text));
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
                                        $appTable->zQuery($q_insert_units_option, array($reaction_option_id, $data['lists2-reaction_text-con'][$i]));
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
                                    $appTable->zQuery($query5, array(date('Y-m-d'), $data['pid'], $data['lists2-list_id-con'][$i]));
                                } elseif ($a1_arr[$i] == 0) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array((null), $data['pid'], $data['lists2-list_id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists2_exist') {
                                if ($a2_arr[$i] == 1) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array(date('Y-m-d'), $data['pid'], $data['lists2_exist-list_id'][$i]));
                                } elseif ($a2_arr[$i] == 0) {
                                    $query5 = "UPDATE lists SET enddate = ? WHERE pid = ? AND id = ?";
                                    $appTable->zQuery($query5, array((null), $data['pid'], $data['lists2_exist-list_id'][$i]));
                                }
                            } elseif (substr($key, 0, -4) == 'lists3-con') {
                                $oid_route = $unit_option_id = $oidu_unit = '';
                                //provider
                                $query_sel_users = "SELECT *
                                                      FROM users
                                                      WHERE npi=?";// abook_type='external_provider' AND
                                $res_query_sel_users = $appTable->zQuery($query_sel_users, array($data['lists3-provider_npi-con'][$i]));
                                if ($res_query_sel_users->count() > 0) {
                                    foreach ($res_query_sel_users as $value1) {
                                        $provider_id = $value1['id'];
                                    }
                                } else {
                                    $value = [];
                                    $value['provider_name'] = $data['lists3-provider_fname-con'][$i] ?? null;
                                    $value['provider_family'] = $data['lists3-provider_lname-con'][$i] ?? null;
                                    $value['provider_address'] = $data['lists3-provider_address-con'][$i] ?? null;
                                    $value['provider_city'] = $data['lists3-provider_city-con'][$i] ?? null;
                                    $value['provider_state'] = $data['lists3-provider_state-con'][$i] ?? null;
                                    $value['provider_postalCode'] = $data['lists3-provider_postalCode-con'][$i] ?? null;

                                    $provider_id = $this->importService->insertImportedUser($value, true);
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

                                if (empty($data['lists3-enddate-con'][$i])) {
                                    $data['lists3-enddate-con'][$i] = (null);
                                }

                                // TODO: Note this is the only way right now to create / update prescriptions is via CCDA...
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
                                    ApplicationTable::fixDate($data['lists3-date_added-con'][$i], 'yyyy-mm-dd', 'dd/mm/yyyy'),
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
                                    $appTable->zQuery($query6, array(date('Y-m-d'), '-1', $data['pid'], $data['lists3-id-con'][$i]));
                                } elseif ($m1_arr[$i] == 0) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array((null), '1', $data['pid'], $data['lists3-id-con'][$i]));
                                }
                            }

                            if (substr($key, 0, -4) == 'lists3_exist') {
                                if ($m2_arr[$i] == 1) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array(date('Y-m-d'), '-1', $data['pid'], $data['lists3_exist-id'][$i]));
                                } elseif ($m2_arr[$i] == 0) {
                                    $query6 = "UPDATE prescriptions SET end_date = ?,active = ? WHERE patient_id = ? AND id = ?";
                                    $appTable->zQuery($query6, array((null), '1', $data['pid'], $data['lists3_exist-id'][$i]));
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
        $this->importService->InsertReconcilation($data['pid'], $data['document_id']);
        $this->importService->InsertImmunization($arr_immunization['immunization'], $data['pid'], $this, 1);
        $this->importService->InsertPrescriptions($arr_prescriptions['lists3'], $data['pid'], $this, 1);
        $this->importService->InsertAllergies($arr_allergies['lists2'], $data['pid'], $this, 1);
        $this->importService->InsertMedicalProblem($arr_med_pblm['lists1'], $data['pid'], $this, 1);
        $this->importService->InsertEncounter($arr_encounter['encounter'], $data['pid'], $this, 1);
        $this->importService->InsertVitals($arr_vitals['vitals'], $data['pid'], $this, 1);
        $this->importService->InsertProcedures($arr_procedures['procedure'], $data['pid'], $this, 1);
        $lab_results = $this->buildLabArray($arr_procedure_res['procedure_result']);
        $this->importService->InsertLabResults($lab_results, $data['pid'], $this);
        $this->importService->InsertCarePlan($arr_care_plan['care_plan'], $data['pid'], $this, 1);
        $this->importService->InsertFunctionalCognitiveStatus($arr_functional_cognitive_status['functional_cognitive_status'], $data['pid'], $this, 1);
        $this->importService->InsertReferrals($arr_referral['referral'], $data['pid'], 1);
    }

    /**
     * Method for review discard. Soft delete.
     *
     * @param $data
     * @return void
     */
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

    /**
     * Method hard delete audit data.
     *
     * @param $data
     * @return void
     */
    public function deleteImportAuditData($data)
    {
        $appTable = new ApplicationTable();
        $appTable->zQuery("DELETE FROM audit_details WHERE audit_master_id=?", array($data['audit_master_id']));
        $appTable->zQuery("DELETE FROM audit_master WHERE id=?", array($data['audit_master_id']));
        $result = $appTable->zQuery("SELECT url FROM documents WHERE audit_master_id=?", array($data['audit_master_id']));
        $res_cur = $result->current();
        if (is_file($res_cur['url'])) {
            unlink($res_cur['url']);
        }
        $file_c = pathinfo($res_cur['url']);
        if (is_dir($file_c['dirname'])) {
            rmdir($file_c['dirname']);
        }
        $appTable->zQuery("DELETE FROM documents WHERE audit_master_id=?", array($data['audit_master_id']));
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
        $query = "SELECT documentationOf FROM documents WHERE audit_master_id = ?";
        $result = $appTable->zQuery($query, array($audit_master_id));
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
        $components = array('schematron' => 'Errors');
        $query = "select * from ccda_components where ccda_type = ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($type));

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

        return $res_cur['codes'] ?? '';
    }

    /**
     * Initialize or reset our private member variables used for importing.
     */
    private function resetData()
    {
        $this->documentData = [];
        $this->is_qrda_import = false;
        $this->is_unstructured_import = false;
        $this->parseTemplates = new CdaTemplateParse();
    }
}
// Below was removed as couldn't find it used anywhere! Will keep for a minute or two...
// Maybe used to create methods in CdaTemplateParse class
/*
        $patient_role = $xml['recordTarget']['patientRole'] ?? null;
        $patient_pub_pid = $patient_role['id'][0]['extension'] ?? null;
        $patient_ssn = $patient_role['id'][1]['extension'] ?? null;
        $patient_address = $patient_role['addr']['streetAddressLine'] ?? null;
        $patient_city = $patient_role['addr']['city'] ?? null;
        $patient_state = $patient_role['addr']['state'] ?? null;
        $patient_postalcode = $patient_role['addr']['postalCode'] ?? null;
        $patient_country = $patient_role['addr']['country'] ?? null;
        $patient_phone_type = $patient_role['telecom']['use'] ?? null;
        $patient_phone_no = $patient_role['telecom']['value'] ?? null;
        $patient_fname = $patient_role['patient']['name']['given'][0] ?? null;
        $patient_lname = $patient_role['patient']['name']['given'][1] ?? null;
        $patient_family_name = $patient_role['patient']['name']['family'] ?? null;
        $patient_gender_code = $patient_role['patient']['administrativeGenderCode']['code'] ?? null;
        if (empty($patient_role['patient']['administrativeGenderCode']['displayName'])) {
            if ($patient_role['patient']['administrativeGenderCode']['code'] == 'F') {
                $patient_role['patient']['administrativeGenderCode']['displayName'] = 'Female';
                $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'] = 'Female';
            } elseif ($patient_role['patient']['administrativeGenderCode']['code'] == 'M') {
                $patient_role['patient']['administrativeGenderCode']['displayName'] = 'Male';
                $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'] = 'Male';
            }
        }
        $patient_gender_name = $patient_role['patient']['administrativeGenderCode']['displayName'] ?? null;
        $patient_dob = $patient_role['patient']['birthTime']['value'] ?? null;
        $patient_marital_status = $patient_role['patient']['religiousAffiliationCode']['code'] ?? null;
        $patient_marital_status_display = $patient_role['patient']['religiousAffiliationCode']['displayName'] ?? null;
        $patient_race = $patient_role['patient']['raceCode']['code'] ?? null;
        $patient_race_display = $patient_role['patient']['raceCode']['displayName'] ?? null;
        $patient_ethnicity = $patient_role['patient']['ethnicGroupCode']['code'] ?? null;
        $patient_ethnicity_display = $patient_role['patient']['ethnicGroupCode']['displayName'] ?? null;
        $patient_language = $patient_role['patient']['languageCommunication']['languageCode']['code'] ?? null;

        $author = $xml['recordTarget']['author']['assignedAuthor'] ?? null;
        $author_id = $author['id']['extension'] ?? null;
        $author_address = $author['addr']['streetAddressLine'] ?? null;
        $author_city = $author['addr']['city'] ?? null;
        $author_state = $author['addr']['state'] ?? null;
        $author_postalCode = $author['addr']['postalCode'] ?? null;
        $author_country = $author['addr']['country'] ?? null;
        $author_phone_use = $author['telecom']['use'] ?? null;
        $author_phone = $author['telecom']['value'] ?? null;
        $author_name_given = $author['assignedPerson']['name']['given'] ?? null;
        $author_name_family = $author['assignedPerson']['name']['family'] ?? null;

        $data_enterer = $xml['recordTarget']['dataEnterer']['assignedEntity'] ?? null;
        $data_enterer_id = $data_enterer['id']['extension'] ?? null;
        $data_enterer_address = $data_enterer['addr']['streetAddressLine'] ?? null;
        $data_enterer_city = $data_enterer['addr']['city'] ?? null;
        $data_enterer_state = $data_enterer['addr']['state'] ?? null;
        $data_enterer_postalCode = $data_enterer['addr']['postalCode'] ?? null;
        $data_enterer_country = $data_enterer['addr']['country'] ?? null;
        $data_enterer_phone_use = $data_enterer['telecom']['use'] ?? null;
        $data_enterer_phone = $data_enterer['telecom']['value'] ?? null;
        $data_enterer_name_given = $data_enterer['assignedPerson']['name']['given'] ?? null;
        $data_enterer_name_family = $data_enterer['assignedPerson']['name']['family'] ?? null;

        $informant = $xml['recordTarget']['informant'][0]['assignedEntity'] ?? null;
        $informant_id = $informant['id']['extension'] ?? null;
        $informant_address = $informant['addr']['streetAddressLine'] ?? null;
        $informant_city = $informant['addr']['city'] ?? null;
        $informant_state = $informant['addr']['state'] ?? null;
        $informant_postalCode = $informant['addr']['postalCode'] ?? null;
        $informant_country = $informant['addr']['country'] ?? null;
        $informant_phone_use = $informant['telecom']['use'] ?? null;
        $informant_phone = $informant['telecom']['value'] ?? null;
        $informant_name_given = $informant['assignedPerson']['name']['given'] ?? null;
        $informant_name_family = $informant['assignedPerson']['name']['family'] ?? null;

        $personal_informant = $xml['recordTarget']['informant'][1]['relatedEntity'] ?? null;
        $personal_informant_name = $personal_informant['relatedPerson']['name']['given'] ?? null;
        $personal_informant_family = $personal_informant['relatedPerson']['name']['family'] ?? null;

        $custodian = $xml['recordTarget']['custodian']['assignedCustodian']['representedCustodianOrganization'] ?? null;
        $custodian_name = $custodian['name'] ?? null;
        $custodian_address = $custodian['addr']['streetAddressLine'] ?? null;
        $custodian_city = $custodian['addr']['city'] ?? null;
        $custodian_state = $custodian['addr']['state'] ?? null;
        $custodian_postalCode = $custodian['addr']['postalCode'] ?? null;
        $custodian_country = $custodian['addr']['country'] ?? null;
        $custodian_phone = $custodian['telecom']['value'] ?? null;
        $custodian_phone_use = $custodian['telecom']['use'] ?? null;

        $informationRecipient = $xml['recordTarget']['informationRecipient']['intendedRecipient'] ?? null;
        $informationRecipient_name = $informationRecipient['informationRecipient']['name']['given'] ?? null;
        $informationRecipient_name = $informationRecipient['informationRecipient']['name']['family'] ?? null;
        $informationRecipient_org = $informationRecipient['receivedOrganization']['name'] ?? null;

        $legalAuthenticator = $xml['recordTarget']['legalAuthenticator'] ?? null;
        $legalAuthenticator_signatureCode = $legalAuthenticator['signatureCode']['code'] ?? null;
        $legalAuthenticator_id = $legalAuthenticator['assignedEntity']['id']['extension'] ?? null;
        $legalAuthenticator_address = $legalAuthenticator['assignedEntity']['addr']['streetAddressLine'] ?? null;
        $legalAuthenticator_city = $legalAuthenticator['assignedEntity']['addr']['city'] ?? null;
        $legalAuthenticator_state = $legalAuthenticator['assignedEntity']['addr']['state'] ?? null;
        $legalAuthenticator_postalCode = $legalAuthenticator['assignedEntity']['addr']['postalCode'] ?? null;
        $legalAuthenticator_country = $legalAuthenticator['assignedEntity']['addr']['country'] ?? null;
        $legalAuthenticator_phone = $legalAuthenticator['assignedEntity']['telecom']['value'] ?? null;
        $legalAuthenticator_phone_use = $legalAuthenticator['assignedEntity']['telecom']['use'] ?? null;
        $legalAuthenticator_name_given = $legalAuthenticator['assignedEntity']['assignedPerson']['name']['given'] ?? null;
        $legalAuthenticator_name_family = $legalAuthenticator['assignedEntity']['assignedPerson']['name']['family'] ?? null;

        $authenticator = $xml['recordTarget']['authenticator'] ?? null;
        $authenticator_signatureCode = $authenticator['signatureCode']['code'] ?? null;
        $authenticator_id = $authenticator['assignedEntity']['id']['extension'] ?? null;
        $authenticator_address = $authenticator['assignedEntity']['addr']['streetAddressLine'] ?? null;
        $authenticator_city = $authenticator['assignedEntity']['addr']['city'] ?? null;
        $authenticator_state = $authenticator['assignedEntity']['addr']['state'] ?? null;
        $authenticator_postalCode = $authenticator['assignedEntity']['addr']['postalCode'] ?? null;
        $authenticator_country = $authenticator['assignedEntity']['addr']['country'] ?? null;
        $authenticator_phone = $authenticator['assignedEntity']['telecom']['value'] ?? null;
        $authenticator_phone_use = $authenticator['assignedEntity']['telecom']['use'] ?? null;
        $authenticator_name_given = $authenticator['assignedEntity']['assignedPerson']['name']['given'] ?? null;
        $authenticator_name_family = $authenticator['assignedEntity']['assignedPerson']['name']['family'] ?? null;
*/
