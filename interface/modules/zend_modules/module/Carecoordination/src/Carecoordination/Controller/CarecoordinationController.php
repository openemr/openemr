<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Chandni Babu <chandnib@zhservices.com>
 * @author    Riju KP <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Controller;

use Application\Model\ApplicationTable;
use Application\Plugin\CommonPlugin;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Listener\Listener;
use Documents\Controller\DocumentsController;
use Carecoordination\Model\CarecoordinationTable;
use C_Document;
use Document;
use CouchDB;
use xmltoarray_parser_htmlfix;

class CarecoordinationController extends AbstractActionController
{
    /**
     * @var Carecoordination\Model\CarecoordinationTable
     */
    private $carecoordinationTable;

    /**
     * @var Documents\Controller\DocumentsController
     */
    private $documentsController;

    public function __construct(CarecoordinationTable $table, DocumentsController $documentsController)
    {
        $this->carecoordinationTable = $table;
        $this->listenerObject = new Listener();
        $this->date_format = ApplicationTable::dateFormat($GLOBALS['date_display_format']);
        $this->documentsController = $documentsController;
    }

    /**
     * Index Page
     *
     * @param int    $id   menu id
     *                     $param array $data   menu details
     * @param string $slug controller name
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->redirect()->toRoute('encountermanager', array('action' => 'index'));
    }

    /*
    * Upload CCDA file
    */
    public function uploadAction()
    {
        $request = $this->getRequest();
        $action = $request->getPost('action');
        $am_id = $request->getPost('am_id');
        $document_id = $request->getPost('document_id');

        if ($action == 'add_new_patient') {
            $this->getCarecoordinationTable()->insert_patient($am_id, $document_id);
        }

        $upload = $request->getPost('upload');
        $category_details = $this->getCarecoordinationTable()->fetch_cat_id('CCDA');

        if ($upload == 1) {
            $time_start = date('Y-m-d H:i:s');
            $obj_doc = $this->documentsController;
            $cdoc = $obj_doc->uploadAction($request);
            $uploaded_documents = array();
            $uploaded_documents = $this->getCarecoordinationTable()->fetch_uploaded_documents(array('user' => $_SESSION['authUserID'], 'time_start' => $time_start, 'time_end' => date('Y-m-d H:i:s')));
            if ($uploaded_documents[0]['id'] > 0) {
                $_REQUEST["document_id"] = $uploaded_documents[0]['id'];
                $_REQUEST["batch_import"] = 'YES';
                $this->importAction();
            }
        } else {
            $result = $this->Documents()->fetchXmlDocuments();
            foreach ($result as $row) {
                if ($row['doc_type'] == 'CCDA') {
                    $_REQUEST["document_id"] = $row['doc_id'];
                    $this->importAction();
                    $this->documentsController->getDocumentsTable()->updateDocumentCategoryUsingCatname($row['doc_type'], $row['doc_id']);
                }
            }
        }

        $records = $this->getCarecoordinationTable()->document_fetch(array('cat_title' => 'CCDA', 'type' => '12'));
        $view = new ViewModel(array(
            'records' => $records,
            'category_id' => $category_details[0]['id'],
            'file_location' => basename($_FILES['file']['name']),
            'patient_id' => '00',
            'listenerObject' => $this->listenerObject
        ));
        return $view;
    }

    /*
    * Function to import the data CCDA file to audit tables.
    *
    * @param    document_id     integer value
    * @return \Laminas\View\Model\JsonModel
    */
    public function importAction()
    {
        $request = $this->getRequest();
        if ($request->getQuery('document_id')) {
            $_REQUEST["document_id"] = $request->getQuery('document_id');
            $category_details = $this->getCarecoordinationTable()->fetch_cat_id('CCDA');
            $this->documentsController->getDocumentsTable()->updateDocumentCategory($category_details[0]['id'], $_REQUEST["document_id"]);
        }

        $document_id = $_REQUEST["document_id"];
        $xml_content = $this->getCarecoordinationTable()->getDocument($document_id);
        $xml_content_new = preg_replace('#<br />#', '', $xml_content);
        $xml_content_new = preg_replace('#<br/>#', '', $xml_content_new);

// Note the behavior of this relies on PHP's XMLReader
// @see https://docs.zendframework.com/zend-config/reader/
// @see https://php.net/xmlreader
        $xmltoarray = new \Laminas\Config\Reader\Xml();
        $array = $xmltoarray->fromString((string)$xml_content_new);

        $patient_role = $array['recordTarget']['patientRole'];
        $patient_pub_pid = $patient_role['id'][0]['extension'];
        $patient_ssn = $patient_role['id'][1]['extension'];
        $patient_address = $patient_role['addr']['streetAddressLine'];
        $patient_city = $patient_role['addr']['city'];
        $patient_state = $patient_role['addr']['state'];
        $patient_postalcode = $patient_role['addr']['postalCode'];
        $patient_country = $patient_role['addr']['country'];
        $patient_phone_type = $patient_role['telecom']['use'];
        $patient_phone_no = $patient_role['telecom']['value'];
        $patient_fname = $patient_role['patient']['name']['given'][0];
        $patient_lname = $patient_role['patient']['name']['given'][1];
        $patient_family_name = $patient_role['patient']['name']['family'];
        $patient_gender_code = $patient_role['patient']['administrativeGenderCode']['code'];
        $patient_gender_name = $patient_role['patient']['administrativeGenderCode']['displayName'];
        $patient_dob = $patient_role['patient']['birthTime']['value'];
        $patient_marital_status = $patient_role['patient']['religiousAffiliationCode']['code'];
        $patient_marital_status_display = $patient_role['patient']['religiousAffiliationCode']['displayName'];
        $patient_race = $patient_role['patient']['raceCode']['code'];
        $patient_race_display = $patient_role['patient']['raceCode']['displayName'];
        $patient_ethnicity = $patient_role['patient']['ethnicGroupCode']['code'];
        $patient_ethnicity_display = $patient_role['patient']['ethnicGroupCode']['displayName'];
        $patient_language = $patient_role['patient']['languageCommunication']['languageCode']['code'];

        $author = $array['recordTarget']['author']['assignedAuthor'];
        $author_id = $author['id']['extension'];
        $author_address = $author['addr']['streetAddressLine'];
        $author_city = $author['addr']['city'];
        $author_state = $author['addr']['state'];
        $author_postalCode = $author['addr']['postalCode'];
        $author_country = $author['addr']['country'];
        $author_phone_use = $author['telecom']['use'];
        $author_phone = $author['telecom']['value'];
        $author_name_given = $author['assignedPerson']['name']['given'];
        $author_name_family = $author['assignedPerson']['name']['family'];

        $data_enterer = $array['recordTarget']['dataEnterer']['assignedEntity'];
        $data_enterer_id = $data_enterer['id']['extension'];
        $data_enterer_address = $data_enterer['addr']['streetAddressLine'];
        $data_enterer_city = $data_enterer['addr']['city'];
        $data_enterer_state = $data_enterer['addr']['state'];
        $data_enterer_postalCode = $data_enterer['addr']['postalCode'];
        $data_enterer_country = $data_enterer['addr']['country'];
        $data_enterer_phone_use = $data_enterer['telecom']['use'];
        $data_enterer_phone = $data_enterer['telecom']['value'];
        $data_enterer_name_given = $data_enterer['assignedPerson']['name']['given'];
        $data_enterer_name_family = $data_enterer['assignedPerson']['name']['family'];

        $informant = $array['recordTarget']['informant'][0]['assignedEntity'];
        $informant_id = $informant['id']['extension'];
        $informant_address = $informant['addr']['streetAddressLine'];
        $informant_city = $informant['addr']['city'];
        $informant_state = $informant['addr']['state'];
        $informant_postalCode = $informant['addr']['postalCode'];
        $informant_country = $informant['addr']['country'];
        $informant_phone_use = $informant['telecom']['use'];
        $informant_phone = $informant['telecom']['value'];
        $informant_name_given = $informant['assignedPerson']['name']['given'];
        $informant_name_family = $informant['assignedPerson']['name']['family'];

        $personal_informant = $array['recordTarget']['informant'][1]['relatedEntity'];
        $personal_informant_name = $personal_informant['relatedPerson']['name']['given'];
        $personal_informant_family = $personal_informant['relatedPerson']['name']['family'];

        $custodian = $array['recordTarget']['custodian']['assignedCustodian']['representedCustodianOrganization'];
        $custodian_name = $custodian['name'];
        $custodian_address = $custodian['addr']['streetAddressLine'];
        $custodian_city = $custodian['addr']['city'];
        $custodian_state = $custodian['addr']['state'];
        $custodian_postalCode = $custodian['addr']['postalCode'];
        $custodian_country = $custodian['addr']['country'];
        $custodian_phone = $custodian['telecom']['value'];
        $custodian_phone_use = $custodian['telecom']['use'];

        $informationRecipient = $array['recordTarget']['informationRecipient']['intendedRecipient'];
        $informationRecipient_name = $informationRecipient['informationRecipient']['name']['given'];
        $informationRecipient_name = $informationRecipient['informationRecipient']['name']['family'];
        $informationRecipient_org = $informationRecipient['receivedOrganization']['name'];

        $legalAuthenticator = $array['recordTarget']['legalAuthenticator'];
        $legalAuthenticator_signatureCode = $legalAuthenticator['signatureCode']['code'];
        $legalAuthenticator_id = $legalAuthenticator['assignedEntity']['id']['extension'];
        $legalAuthenticator_address = $legalAuthenticator['assignedEntity']['addr']['streetAddressLine'];
        $legalAuthenticator_city = $legalAuthenticator['assignedEntity']['addr']['city'];
        $legalAuthenticator_state = $legalAuthenticator['assignedEntity']['addr']['state'];
        $legalAuthenticator_postalCode = $legalAuthenticator['assignedEntity']['addr']['postalCode'];
        $legalAuthenticator_country = $legalAuthenticator['assignedEntity']['addr']['country'];
        $legalAuthenticator_phone = $legalAuthenticator['assignedEntity']['telecom']['value'];
        $legalAuthenticator_phone_use = $legalAuthenticator['assignedEntity']['telecom']['use'];
        $legalAuthenticator_name_given = $legalAuthenticator['assignedEntity']['assignedPerson']['name']['given'];
        $legalAuthenticator_name_family = $legalAuthenticator['assignedEntity']['assignedPerson']['name']['family'];

        $authenticator = $array['recordTarget']['authenticator'];
        $authenticator_signatureCode = $authenticator['signatureCode']['code'];
        $authenticator_id = $authenticator['assignedEntity']['id']['extension'];
        $authenticator_address = $authenticator['assignedEntity']['addr']['streetAddressLine'];
        $authenticator_city = $authenticator['assignedEntity']['addr']['city'];
        $authenticator_state = $authenticator['assignedEntity']['addr']['state'];
        $authenticator_postalCode = $authenticator['assignedEntity']['addr']['postalCode'];
        $authenticator_country = $authenticator['assignedEntity']['addr']['country'];
        $authenticator_phone = $authenticator['assignedEntity']['telecom']['value'];
        $authenticator_phone_use = $authenticator['assignedEntity']['telecom']['use'];
        $authenticator_name_given = $authenticator['assignedEntity']['assignedPerson']['name']['given'];
        $authenticator_name_family = $authenticator['assignedEntity']['assignedPerson']['name']['family'];

        $this->getCarecoordinationTable()->import($array, $document_id);

        $view = new \Laminas\View\Model\JsonModel();
        $view->setTerminal(true);
        return $view;
// $view = new ViewModel(array());
// $view->setTerminal(true);
// return $view;
    }

    public function revandapproveAction()
    {
        $request = $this->getRequest();
        $document_id = $request->getQuery('document_id') ? $request->getQuery('document_id') : $request->getPost('document_id', null);
        $audit_master_id = $request->getQuery('amid') ? $request->getQuery('amid') : $request->getPost('amid', null);
        $pid = $request->getQuery('pid') ? $request->getQuery('pid') : $request->getPost('pid', null);

        if ($request->getPost('setval') == 'approve') {
            $this->getCarecoordinationTable()->insertApprovedData($request->getPost());
            return $this->redirect()->toRoute('carecoordination', array(
                'controller' => 'Carecoordination',
                'action' => 'upload'));
        } elseif ($request->getPost('setval') == 'discard') {
            $this->getCarecoordinationTable()->discardCCDAData(array('audit_master_id' => $audit_master_id));
            return $this->redirect()->toRoute('carecoordination', array(
                'controller' => 'Carecoordination',
                'action' => 'upload'));
        }

        $documentationOf = $this->getCarecoordinationTable()->getdocumentationOf($audit_master_id);
        $demographics = $this->getCarecoordinationTable()->getDemographics(array('audit_master_id' => $audit_master_id));
        $demographics_old = $this->getCarecoordinationTable()->getDemographicsOld(array('pid' => $pid));

        $problems = $this->getCarecoordinationTable()->getProblems(array('pid' => $pid));
        $problems_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'lists1');

        $allergies = $this->getCarecoordinationTable()->getAllergies(array('pid' => $pid));
        $allergies_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'lists2');

        $medications = $this->getCarecoordinationTable()->getMedications(array('pid' => $pid));
        $medications_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'lists3');

        $immunizations = $this->getCarecoordinationTable()->getImmunizations(array('pid' => $pid));
        $immunizations_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'immunization');

        $lab_results = $this->getCarecoordinationTable()->getLabResults(array('pid' => $pid));
        $lab_results_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'procedure_result');

        $vitals = $this->getCarecoordinationTable()->getVitals(array('pid' => $pid));
        $vitals_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'vital_sign');

        $social_history = $this->getCarecoordinationTable()->getSocialHistory(array('pid' => $pid));
        $social_history_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'social_history');

        $encounter = $this->getCarecoordinationTable()->getEncounterData(array('pid' => $pid));
        $encounter_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'encounter');

        $procedure = $this->getCarecoordinationTable()->getProcedure(array('pid' => $pid));
        $procedure_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'procedure');

        $care_plan = $this->getCarecoordinationTable()->getCarePlan(array('pid' => $pid));
        $care_plan_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'care_plan');

        $functional_cognitive_status = $this->getCarecoordinationTable()->getFunctionalCognitiveStatus(array('pid' => $pid));
        $functional_cognitive_status_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'functional_cognitive_status');

        $referral = $this->getCarecoordinationTable()->getReferralReason(array('pid' => $pid));
        $referral_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'referral');

        $discharge_medication_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'discharge_medication');

        $discharge_summary = array(); // TODO: stephen what happened here?? no discharge summary review?
        $discharge_summary_audit = $this->getRevAndApproveAuditArray($audit_master_id, 'discharge_summary');

        $gender_list = $this->getCarecoordinationTable()->getList('sex');
        $country_list = $this->getCarecoordinationTable()->getList('country');
        $marital_status_list = $this->getCarecoordinationTable()->getList('marital');
        $religion_list = $this->getCarecoordinationTable()->getList('religious_affiliation');
        $race_list = $this->getCarecoordinationTable()->getList('race');
        $ethnicity_list = $this->getCarecoordinationTable()->getList('ethnicity');
        $state_list = $this->getCarecoordinationTable()->getList('state');
        $tobacco = $this->getCarecoordinationTable()->getList('smoking_status');

        $demographics_old[0]['sex'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['sex'], 'sex', '');
        $demographics_old[0]['country_code'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['country_code'], 'country', '');
        $demographics_old[0]['status'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['status'], 'marital', '');
        $demographics_old[0]['religion'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['religion'], 'religious_affiliation', '');
        $demographics_old[0]['race'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['race'], 'race', '');
        $demographics_old[0]['ethnicity'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['ethnicity'], 'ethnicity', '');
        $demographics_old[0]['state'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['state'], 'state', '');

        $view = new ViewModel(array(
            'carecoordinationTable' => $this->getCarecoordinationTable(),
            'ApplicationTable' => $this->getApplicationTable(),
            'commonplugin' => $this->CommonPlugin(), // this comes from the Application Module
            'demographics' => $demographics,
            'demographics_old' => $demographics_old,
            'problems' => $problems,
            'problems_audit' => $problems_audit,
            'allergies' => $allergies,
            'allergies_audit' => $allergies_audit,
            'medications' => $medications,
            'medications_audit' => $medications_audit,
            'immunizations' => $immunizations,
            'immunizations_audit' => $immunizations_audit,
            'lab_results' => $lab_results,
            'lab_results_audit' => $lab_results_audit,
            'vitals' => $vitals,
            'vitals_audit' => $vitals_audit,
            'social_history' => $social_history,
            'social_history_audit' => $social_history_audit,
            'encounter' => $encounter,
            'encounter_audit' => $encounter_audit,
            'procedure' => $procedure,
            'procedure_audit' => $procedure_audit,
            'care_plan' => $care_plan,
            'care_plan_audit' => $care_plan_audit,
            'functional_cognitive_status' => $functional_cognitive_status,
            'functional_cognitive_status_audit' => $functional_cognitive_status_audit,
            'referral' => $referral,
            'referral_audit' => $referral_audit,
            'discharge_medication_audit' => $discharge_medication_audit,
            'discharge_summary' => $discharge_summary,
            'discharge_summary_audit' => $discharge_summary_audit,
            'amid' => $audit_master_id,
            'pid' => $pid,
            'document_id' => $document_id,
            'gender_list' => $gender_list,
            'country_list' => $country_list,
            'marital_status_list' => $marital_status_list,
            'religion_list' => $religion_list,
            'race_list' => $race_list,
            'ethnicity_list' => $ethnicity_list,
            'tobacco' => $tobacco,
            'state_list' => $state_list,
            'listenerObject' => $this->listenerObject,
            'documentationOf' => $documentationOf,
        ));
        return $view;
    }

    public function getCCDAComponentsAction()
    {
        $request = $this->getRequest();
        $id = $request->getQuery('id');
        $arr = explode("-", $id);
        $amid = $arr[0];
        $pid = $arr[1];
        $components = $this->getCarecoordinationTable()->getCCDAComponents(1);
        $discharge_medication_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'discharge_medication');
        $discharge_summary_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'discharge_summary');
        if (count($discharge_medication_audit) > 0) {
            $components['discharge_medication'] = 'Dishcharge Medications';
        }

        if (count($discharge_summary_audit) > 0) {
            $components['discharge_summary'] = 'Dishcharge Summary';
        }

        $components = array_diff($components, array('instructions' => 'Instructions'));

        $temp = '<table>';
        foreach ($components as $key => $value) {
            $temp .= '<tr class="se_in_9">
<th colspan="1" id="expandCompDetails-' . CommonPlugin::escape($key . $amid . $pid) . '" class="expandCompDetails se_in_23" component="' . CommonPlugin::escape($key) . '" amid="' . CommonPlugin::escape($amid) . '" style="padding: 0px 5px!important;"></th>
<th colspan="8" style="padding: 0px 0px!important;"><label>' . CommonPlugin::escape($value) . '</th>
</tr>
<tr>
<td colspan="9" id="hideComp-' . CommonPlugin::escape($key . $amid . $pid) . '" class="imported_ccdaComp_details" style="display: none;"></td>
</tr>';
        }

        $temp .= '</table>';
        echo $temp;
        exit;
    }

    public function getEachCCDAComponentDetailsAction()
    {
        $request = $this->getRequest();
        $id = $request->getQuery('id');
        $component = $request->getQuery('component');
        $amid = $request->getQuery('amid');
        $temp = '';

        switch ($component) {
            case 'allergies':
                $allergies_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'lists2');
                if (count($allergies_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%!important;">
 <thead><tr class="narr_tr">
        <th class="narr_th">' . Listener::z_xlt('Substance') . '</th>
        <th class="narr_th">' . Listener::z_xlt('Reaction') . '</th>
        <th class="narr_th">' . Listener::z_xlt('Severity') . '</th>
        <th class="narr_th">' . Listener::z_xlt('Status') . '</th>
    </tr></thead>
 <tbody>';
                    foreach ($allergies_audit['lists2'] as $key => $val) {
                        $severity_option_id = $this->getCarecoordinationTable()->getOptionId('severity_ccda', '', 'SNOMED-CT:' . $val['severity_al']);
                        $severity_text = $this->getCarecoordinationTable()->getListTitle($severity_option_id, 'severity_ccda', 'SNOMED-CT:' . $val['severity_al']);
                        if ($val['enddate'] != 0 && $val['enddate'] != '') {
                            $status = 'completed';
                        } else {
                            $status = 'active';
                        }

                        $temp .= '<tr class="narr_tr">
            <td>' . CommonPlugin::escape($val['list_code_text']) . '</td>
            <td>' . Listener::z_xlt($val['reaction_text']) . '</td>
            <td>' . Listener::z_xlt($severity_text) . '</td>
            <td>' . Listener::z_xlt($status) . '</td>
           </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Allergies');
                }
                break;
            case 'medications':
                $medications_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'lists3');
                if (count($medications_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Medication') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Directions') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Start Date') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Status') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Indications') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Fill Instructions') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($medications_audit['lists3'] as $key => $val) {
                        if ($val['enddate'] && $val['enddate'] != 0) {
                            $active = 'completed';
                        } else {
                            $active = 'active';
                        }

                        $temp .= '<tr class="narr_tr">
                <td>' . CommonPlugin::escape($val['drug_text']) . '</td>
                <td>' . CommonPlugin::escape($val['rate'] . " " . $val['rate_unit'] . " " . $val['route_display'] . " " . $val['dose'] . " " . $val['dose_unit']) . '</td>
                <td>' . ApplicationTable::fixDate(substr($val['begdate'], 0, 4) . "-" . substr($val['begdate'], 4, 2) . "-" . substr($val['begdate'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
                <td>' . Listener::z_xlt($active) . '</td>
                <td>' . CommonPlugin::escape($val['indication']) . '</td>
                <td>' . CommonPlugin::escape($val['note']) . '</td>
            </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Medications');
                }
                break;
            case 'problems':
                $problems_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'lists1');
                if (count($problems_audit) > 0) {
                    $temp .= '<div><ul>';
                    $i = 1;
                    foreach ($problems_audit['lists1'] as $key => $val) {
                        if ($val['enddate'] != 0 && $val['enddate'] != '') {
                            $status = 'Resolved';
                        } else {
                            $status = 'Active';
                        }

                        $temp .= '<li>' . $i . '. ' . CommonPlugin::escape($val['list_code_text']) . ',' . substr($val['begdate'], 0, 4) . "-" . substr($val['begdate'], 4, 2) . "-" . substr($val['begdate'], 6, 2) . ', ' . Listener::z_xlt('Status') . ' :' . Listener::z_xlt($status) . '</li>';
                        $i++;
                    }

                    $temp .= '</ul></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Problems');
                }
                break;
            case 'immunizations':
                $immunizations_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'immunization');
                if (count($immunizations_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Vaccine') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Date') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Status') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($immunizations_audit['immunization'] as $key => $val) {
                        $temp .= '<tr class="narr_tr">
        <td>' . CommonPlugin::escape($val['cvx_code_text']) . '</td>
        <td>' . $this->getCarecoordinationTable()->getMonthString(substr($val['administered_date'], 4, 2)) . ' ' . substr($val['administered_date'], 0, 4) . '</td>
        <td>' . Listener::z_xlt('Completed') . '</td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Immunizations');
                }
                break;
            case 'procedures':
                $procedure_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'procedure');
                if (count($procedure_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Procedure') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Date') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($procedure_audit['procedure'] as $key => $val) {
                        $temp .= '<tr class="narr_tr">
        <td>' . CommonPlugin::escape($val['code_text']) . '</td>
        <td>' . ApplicationTable::fixDate(substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Procedures');
                }
                break;
            case 'results':
                $lab_results_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'procedure_result');
                if (count($lab_results_audit) > 0) {
                    $temp .= '<div>
    <table class="narr_table" border="1">
        <thead><tr class="narr_tr">
                <th class="narr_th">' . Listener::z_xlt('Laboratory Information') . '</th>
                <th class="narr_th">' . Listener::z_xlt('Result') . '</th>
                <th class="narr_th">' . Listener::z_xlt('Date') . '</th>
            </tr></thead>
        <tbody>';
                    foreach ($lab_results_audit['procedure_result'] as $key => $val) {
                        if ($val['results_text']) {
                            $temp .= '<tr class="narr_tr">
        <td>' . CommonPlugin::escape($val['results_text']) . ($val['results_range'] != "-" ? "(" . CommonPlugin::escape($val['results_range']) . ")" : "") . '</td>
        <td>' . CommonPlugin::escape($val['results_value']) . " " . CommonPlugin::escape($val['results_unit']) . '</td>
        <td>' . ApplicationTable::fixDate(substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
     </tr>';
                        }
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Lab Results');
                }
                break;
            case 'plan_of_care':
                $care_plan_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'care_plan');
                if (count($care_plan_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <head><tr class="narr_tr">
    <th class="narr_th">' . Listener::z_xlt('Planned Activity') . '</th>
    <th class="narr_th">' . Listener::z_xlt('Planned Date') . '</th>
    </tr></thead>
    <tbody>';
                    foreach ($care_plan_audit['care_plan'] as $key => $val) {
                        $temp .= '<tr class="narr_tr">
    <td>' . CommonPlugin::escape($val['code_text']) . '</td>
    <td>' . ApplicationTable::fixDate(substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Plan of Care');
                }
                break;
            case 'vitals':
                $vitals_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'vital_sign');
                if (count($vitals_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
 <thead><tr class="narr_tr">
 <th class="narr_th" align="right">' . Listener::z_xlt('Date / Time') . ': </th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<th class="narr_th">' . ApplicationTable::fixDate(substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</th>';
                    }

                    $temp .= '</tr></thead><tbody>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Temperature') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['temperature']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Diastolic') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['bpd']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Systolic') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['bps']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Head Circumference') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['head_circ']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Pulse') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['pulse']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Height') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['height']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Oxygen Saturation') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['oxygen_saturation']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Breath') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['breath']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('Weight') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['weight']) . '</td>';
                    }

                    $temp .= '</tr>
 <tr class="narr_tr">
    <th class="narr_th" align="left">' . Listener::z_xlt('BMI') . '</th>';
                    foreach ($vitals_audit['vital_sign'] as $key => $val) {
                        $temp .= '<td>' . CommonPlugin::escape($val['BMI']) . '</td>';
                    }

                    $temp .= '</tr></tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Vitals');
                }
                break;
            case 'social_history':
                $social_history_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'social_history');
                if (count($social_history_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Social History Element') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Description') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Effective Dates') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($social_history_audit['social_history'] as $key => $val) {
                        $array_his_tobacco = explode("|", $val['smoking']);
                        if ($array_his_tobacco[2] != 0 && $array_his_tobacco[2] != '') {
                            $his_tob_date = substr($array_his_tobacco[2], 0, 4) . "-" . substr($array_his_tobacco[2], 4, 2) . "-" . substr($array_his_tobacco[2], 6, 2);
                        }

                        $temp .= '<tr class="narr_tr">
        <td>' . Listener::z_xlt('Smoking') . '</td>
        <td>' . CommonPlugin::escape($array_his_tobacco[0]) . '</td>
        <td>' . ApplicationTable::fixDate($his_tob_date, $this->date_format, 'yyyy-mm-dd') . '</td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Social History');
                }
                break;
            case 'encounters':
                $encounter_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'encounter');
                if (count($encounter_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Encounter') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Performer') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Location') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Date') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Encounter Diagnosis') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Status') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Reason for Visit') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($encounter_audit['encounter'] as $key => $val) {
                        if ($val['code_text'] != 'NULL') {
                            $encounter_activity = 'Active';
                        } else {
                            $encounter_activity = '';
                        }

                        $enc_date = substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2);
                        $temp .= '<tr class="narr_tr">
        <td>' . CommonPlugin::escape($val['pc_catname']) . '</td>
        <td>' . CommonPlugin::escape($val['provider_name']) . '</td>
        <td>' . CommonPlugin::escape($val['represented_organization_name']) . '</td>
        <td>' . ApplicationTable::fixDate($enc_date, $this->date_format, 'yyyy-mm-dd') . '</td>
        <td>' . ($val['code_text'] != 'NULL' ? CommonPlugin::escape($val['code_text']) : '') . '</td>
        <td>' . Listener::z_xlt($encounter_activity) . '</td>
        <td></td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Encounters');
                }
                break;
            case 'functional_status':
                $functional_cognitive_status_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'functional_cognitive_status');
                if (count($functional_cognitive_status_audit) > 0) {
                    $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Functional Condition') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Effective Dates') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Condition Status') . '</th>
        </tr></thead>
    <tbody>';
                    foreach ($functional_cognitive_status_audit['functional_cognitive_status'] as $key => $val) {
                        $temp .= '<tr class="narr_tr">
        <td>' . CommonPlugin::escape($val['description']) . '</td>
        <td>' . ApplicationTable::fixDate(substr($val['date'], 0, 4) . "-" . substr($val['date'], 4, 2) . "-" . substr($val['date'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
        <td>' . Listener::z_xlt('Active') . '</td>
    </tr>';
                    }

                    $temp .= '</tbody></table></div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Social Functional Status');
                }
                break;
            case 'referral':
                $referral_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'referral');
                if (count($referral_audit) > 0) {
                    $temp .= '<div>';
                    foreach ($referral_audit['referral'] as $key => $val) {
                        $referal_data = explode("#$%^&*", $val['body']);
                        foreach ($referal_data as $k => $v) {
                            $temp .= '<p>' . CommonPlugin::escape($v) . '</p>';
                        }
                    }

                    $temp .= '</div>';
                } else {
                    $temp .= Listener::z_xlt('No Known Referrals');
                }
                break;
            case 'instructions':
                $temp .= Listener::z_xlt('No Known Clinical Instructions');
                break;
            case 'discharge_medication':
                $discharge_medication_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'discharge_medication');
                $temp .= '<div><table class="narr_table" border="1" width="100%">
    <thead><tr class="narr_tr">
            <th class="narr_th">' . Listener::z_xlt('Medication') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Directions') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Start Date') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Status') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Indications') . '</th>
            <th class="narr_th">' . Listener::z_xlt('Fill Instructions') . '</th>
        </tr></thead>
    <tbody>';
                foreach ($discharge_medication_audit['discharge_medication'] as $key => $val) {
                    if ($val['enddate'] && $val['enddate'] != 0) {
                        $active = 'completed';
                    } else {
                        $active = 'active';
                    }

                    $temp .= '<tr class="narr_tr">
                <td>' . CommonPlugin::escape($val['drug_text']) . '</td>
                <td>' . CommonPlugin::escape($val['rate'] . " " . $val['rate_unit'] . " " . $val['route_display'] . " " . $val['dose'] . " " . $val['dose_unit']) . '</td>
                <td>' . ApplicationTable::fixDate(substr($val['begdate'], 0, 4) . "-" . substr($val['begdate'], 4, 2) . "-" . substr($val['begdate'], 6, 2), $this->date_format, 'yyyy-mm-dd') . '</td>
                <td>' . Listener::z_xlt($active) . '</td>
                <td>' . CommonPlugin::escape($val['indication']) . '</td>
                <td>' . CommonPlugin::escape($val['note']) . '</td>
            </tr>';
                }

                $temp .= '</tbody></table></div>';
                break;
            case 'discharge_summary':
                $discharge_summary_audit = $this->getCarecoordinationTable()->createAuditArray($amid, 'discharge_summary');
                $temp .= '<div>';
                foreach ($discharge_summary_audit['discharge_summary'] as $key => $val) {
                    $text = str_replace("#$%", "<br />", CommonPlugin::escape($val['text']));
                    $temp .= $text;
                }

                $temp .= '</div>';
                break;
        }

        echo $temp;
        exit;
    }

    /**
     * Table gateway
     *
     * @return \Carecoordination\Model\CarecoordinationTable
     */
    public function getCarecoordinationTable()
    {
        return $this->carecoordinationTable;
    }

    /**
     * Returns the application table.
     */
    public function getApplicationTable()
    {
        return $this->applicationTable;
    }

    /**
     * PHP 7 requires foreach iterables to not be null / undefined.  There's a ton of code in the revandapprove.phtml file that assumes
     * the arrays are not empty... so to skip over the foreach's we are giving them empty values.
     *
     * @param $audit_master_id
     * @param $table_name
     * @return array
     */
    private function getRevAndApproveAuditArray($audit_master_id, $table_name)
    {
        $audit = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, $table_name);
        if (empty($audit[$table_name])) {
            $audit[$table_name] = []; // leave it empty so we don't fail in the template
        }
        return $audit;
    }
}
