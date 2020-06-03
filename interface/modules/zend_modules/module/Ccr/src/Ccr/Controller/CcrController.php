<?php

/**
 * interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju KP <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Ccr\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Listener\Listener;
use Documents\Controller\DocumentsController;
use Ccr\Model\CcrTable;

class CcrController extends AbstractActionController
{
    protected $ccrTable;
    protected $listenerObject;
    private $documentsController;

    public function __construct(CcrTable $ccrTable, DocumentsController $documentsController)
    {
        $this->ccrTable = $ccrTable;
        $this->listenerObject   = new Listener();
        $this->documentsController = $documentsController;
    }

    /*
    * Upload CCR XML file
    */
    public function indexAction()
    {
        $request = $this->getRequest();
        $action = $request->getPost('action');
        $am_id  = $request->getPost('am_id');
        if ($action == 'add_new_patient') {
            $this->getCcrTable()->insert_patient($am_id);
        }

        $category_details = $this->getCcrTable()->fetch_cat_id('CCR');

        $time_start     = date('Y-m-d H:i:s');
        $docid          = $this->documentsController->uploadAction($request);
        $uploaded_documents     = array();
        $uploaded_documents     = $this->getCcrTable()->fetch_uploaded_documents(array('user' => $_SESSION['authUserID'], 'time_start' => $time_start, 'time_end' => date('Y-m-d H:i:s')));

        if ($uploaded_documents[0]['id'] > 0) {
            $_REQUEST["document_id"]    = $uploaded_documents[0]['id'];
            $_REQUEST["batch_import"]   = 'YES';
            $this->importAction();
        } else {
            // TODO: change to $this->Documents()
            $result = \Documents\Plugin\Documents::fetchXmlDocuments();
            foreach ($result as $row) {
                if ($row['doc_type'] == 'CCR') {
                    $_REQUEST["document_id"] = $row['doc_id'];
                    $this->importAction();
                    // TODO: need to inject this dependency instead of the static...
                    \Documents\Model\DocumentsTable::updateDocumentCategoryUsingCatname($row['doc_type'], $row['doc_id']);
                }
            }
        }

        $records = $this->getCcrTable()->document_fetch(array('cat_title' => 'CCR'));
        $view = new ViewModel(array(
            'records'       => $records,
            'category_id'   => $category_details[0]['id'],
            'file_location' => basename($_FILES['file']['name']),
            'patient_id'    => '00',
            'listenerObject' => $this->listenerObject,
            'commonplugin'  => $this->CommonPlugin(),
        ));
        return $view;
    }

    /*
    * Import CCR data and update to audit tables
    *
    * @param    document_id     documents table ID to fetch the CCR XML file to import the data
    */
    public function importAction()
    {
        $request     = $this->getRequest();
        if ($request->getQuery('document_id')) {
            $_REQUEST["document_id"] = $request->getQuery('document_id');
            $category_details          = $this->getCcrTable()->fetch_cat_id('CCR');
            $this->documentsController->getDocumentsTable()->updateDocumentCategory($category_details[0]['id'], $_REQUEST["document_id"]);
        }

        $doc_id     = $_REQUEST["document_id"];
        $content    = $this->getCcrTable()->getDocument($doc_id);
        if ($request->getQuery('document_id')) {
            $replace    = array('<ccr:ContinuityOfCareRecord xsi:schemaLocation="urn:astm-org:CCR CCRV1.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ccr="urn:astm-org:CCR">','ccr:');
            $to_replace = array('<ContinuityOfCareRecord xmlns="urn:astm-org:CCR">','');
            $content    = str_replace($replace, $to_replace, $content);
            $content    = preg_replace('/BirthName/', 'CurrentName', $content, 2);
        }

        //fields to which the corresponding elements are to be inserted
        //format - level 1 key is the main tag in the XML eg:- //Problems or //Problems/Problem according to the content in the XML.
        //level 2 key is 'table name:field name' and level 2 value is the sub tag under the main tag given in level 1 key
        //eg:- 'Type/Text' if the XML format is '//Problems/Problem/Type/Text' or 'id/@extension' if it is an attribute
        //level 2 key can be 'table name:#some value' for checking whether a particular tag exits in the XML section
        $field_mapping = array(
          '//Problems/Problem'  => array(
            'lists1:diagnosis'  => 'Description/Code/Value',
            'lists1:comments'   => 'CommentID',
            'lists1:activity'   => 'Status/Text',
            'lists1:title'      => 'Description/Text',
            'lists1:date'       => 'DateTime/ExactDateTime',
          ),
          '//Alerts/Alert' => array(
            'lists2:type'       => 'Type/Text',
            'lists2:diagnosis'  => 'Description/Code/Value',
            'lists2:date'       => 'Agent/EnvironmentalAgents/EnvironmentalAgent/DateTime/ExactDateTime',
            'lists2:title'      => 'Agent/EnvironmentalAgents/EnvironmentalAgent/Description/Text',
            'lists2:reaction'   => 'Reaction/Description/Text',
          ),
          '//Medications/Medication'    => array(
            'prescriptions:date_added'  => 'DateTime/ExactDateTime',
            'prescriptions:active'      => 'Status/Text',
            'prescriptions:drug'        => 'Product/ProductName/Text',
            'prescriptions:size'        => 'Product/Strength/Value',
            'prescriptions:unit'        => 'Product/Strength/Units/Unit',
            'prescriptions:form'        => 'Product/Form/Text',
            'prescriptions:quantity'    => 'Quantity/Value',
            'prescriptions:note'        => 'PatientInstructions/Instruction/Text',
            'prescriptions:refills'     => 'Refills/Refill/Number',
          ),
          '//Immunizations/Immunization'        => array(
            'immunizations:administered_date'   => 'DateTime/ExactDateTime',
            'immunizations:note'                => 'Directions/Direction/Description/Text',
          ),
          '//Results/Result' => array(
            'procedure_result:date'     => 'DateTime/ExactDateTime',
            'procedure_type:name'       => 'Test/Description/Text',
            'procedure_result:result'   => 'Test/TestResult/Value',
            'procedure_result:range'    => 'Test/NormalResult/Normal/Value',
            'procedure_result:abnormal' => 'Test/Flag/Text',
          ),
          '//Actors/Actor' => array(
            'patient_data:fname'    => 'Person/Name/CurrentName/Given',
            'patient_data:lname'    => 'Person/Name/CurrentName/Family',
            'patient_data:DOB'      => 'Person/DateOfBirth/ExactDateTime',
            'patient_data:sex'      => 'Person/Gender/Text',
            'patient_data:abname'   => 'InformationSystem/Name',
            'patient_data:#Type'    => 'InformationSystem/Type',
            'patient_data:pubpid'   => 'IDs/ID',
            'patient_data:street'   => 'Address/Line1',
            'patient_data:city'     => 'Address/City',
            'patient_data:state'    => 'Address/State',
            'patient_data:postal_code'      => 'Address/PostalCode',
            'patient_data:phone_contact'    => 'Telephone/Value',
          ),
        );
        if (!empty($content)) {
            $var = array();
            $res = $this->getCcrTable()->parseXmlStream($content, $field_mapping);
            $var = array(
                'approval_status' => 1,
                'type' => 11,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            );
            foreach ($res as $sections => $details) {
                foreach ($details as $cnt => $vals) {
                    foreach ($vals as $key => $val) {
                        if (array_key_exists('#Type', $res[$sections][$cnt])) {
                            if ($key == 'postal_code') {
                                $var['field_name_value_array']['misc_address_book'][$cnt]['zip'] = $val;
                            } elseif ($key == 'phone_contact') {
                                $var['field_name_value_array']['misc_address_book'][$cnt]['phone'] = $val;
                            } elseif ($key == 'abname') {
                                $values = explode(' ', $val);
                                if ($values[0]) {
                                    $var['field_name_value_array']['misc_address_book'][$cnt]['lname'] = $values[0];
                                }

                                if ($values[1]) {
                                    $var['field_name_value_array']['misc_address_book'][$cnt]['fname'] = $values[1];
                                }
                            } else {
                                $var['field_name_value_array']['misc_address_book'][$cnt][$key] = $val;
                            }

                            $var['entry_identification_array']['misc_address_book'][$cnt] = $cnt;
                        } else {
                            if ($sections == 'lists1' && $key == 'activity') {
                                if ($val == 'Active') {
                                    $val = 1;
                                } else {
                                    $val = 0;
                                }
                            }

                            if ($sections == 'lists2' && $key == 'type') {
                                if (strpos($val, "-")) {
                                    $vals = explode("-", $val);
                                    $val = $vals[0];
                                } else {
                                    $val = "";
                                }
                            }

                            if ($sections == 'prescriptions' && $key == 'active') {
                                if ($val == 'Active') {
                                    $val = 1;
                                } else {
                                    $val = 0;
                                }
                            }

                            $var['field_name_value_array'][$sections][$cnt][$key] = $val;
                            $var['entry_identification_array'][$sections][$cnt] = $cnt;
                        }
                    }

                    if (array_key_exists('#Type', $var['field_name_value_array']['misc_address_book'][$cnt])) {
                        unset($var['field_name_value_array']['misc_address_book'][$cnt]['#Type']);
                    }
                }
            }

            $var['field_name_value_array']['documents'][0]['id'] = $doc_id;
            $audit_master_id = $this->getCcrTable()->insert_ccr_into_audit_data($var);
            $this->getCcrTable()->update_imported($doc_id);
            $this->getCcrTable()->update_document($doc_id, $audit_master_id);

            if ($_REQUEST["batch_import"]   == 'YES') {
                return;
            } else {
                //echo('Imported');
                //exit;
            }
        } else {
            //exit('Could not read the file');
        }
    }

    /*
    * Review the data imported from the CCR file
    * Approve/Discard the data imported
    *
    * @param    amid            Audit mater table ID
    * @param    pid             Patient ID to which the data has to be merged
    * @param    document_id     documents table ID
    */
    public function revandapproveAction()
    {
        $request            = $this->getRequest();
        $audit_master_id    = $request->getQuery('amid') ? $request->getQuery('amid') : $request->getPost('amid', null);
        $pid                = $request->getQuery('pid') ? $request->getQuery('pid') : $request->getPost('pid', null);
        $document_id        = $request->getQuery('document_id') ? $request->getQuery('document_id') : $request->getPost('document_id', null);

        if ($request->getPost('setval') == 'approve') {
            $this->getCcrTable()->insertApprovedData($_REQUEST);
            return $this->redirect()->toRoute('ccr', array('action' => 'index'));
        } elseif ($request->getPost('setval') == 'discard') {
            $this->getCcrTable()->discardCCRData(array('audit_master_id' => $audit_master_id));
            return $this->redirect()->toRoute('ccr', array('action' => 'index'));
        }

        $demographics       = $this->getCcrTable()->getDemographics(array('audit_master_id' => $audit_master_id));
        $demographics_old   = $this->getCcrTable()->getDemographicsOld(array('pid' => $pid));

        $problems           = $this->getCcrTable()->getProblems(array('pid' => $pid));
        $problems_audit     = $this->getCcrTable()->createAuditArray($audit_master_id, 'lists1');

        $allergies          = $this->getCcrTable()->getAllergies(array('pid' => $pid));
        $allergies_audit    = $this->getCcrTable()->createAuditArray($audit_master_id, 'lists2');

        $medications        = $this->getCcrTable()->getMedications(array('pid' => $pid));
        $medications_audit  = $this->getCcrTable()->createAuditArray($audit_master_id, 'prescriptions');

        $immunizations      = $this->getCcrTable()->getImmunizations(array('pid' => $pid));
        $immunizations_audit  = $this->getCcrTable()->createAuditArray($audit_master_id, 'immunizations');

        $lab_results        = $this->getCcrTable()->getLabResults(array('pid' => $pid));
        $lab_results_audit  = $this->getCcrTable()->createAuditArray($audit_master_id, 'procedure_result,procedure_type');

        $view = new ViewModel(array(
            'demographics'      => $demographics,
            'demographics_old'  => $demographics_old,
            'problems'          => $problems,
            'problems_audit'    => $problems_audit,
            'allergies'         => $allergies,
            'allergies_audit'   => $allergies_audit,
            'medications'       => $medications,
            'medications_audit' => $medications_audit,
            'immunizations'     => $immunizations,
            'immunizations_audit' => $immunizations_audit,
            'lab_results'       => $lab_results,
            'lab_results_audit' => $lab_results_audit,
            'amid'              => $audit_master_id,
            'pid'               => $pid,
            'document_id'       => $document_id,
            'listenerObject'    => $this->listenerObject,
            'commonplugin'      => $this->CommonPlugin(),

        ));
        return $view;
    }

    /**
    * Table Gateway
    *
    * @return type
    */
    public function getCcrTable()
    {
        return $this->ccrTable;
    }
}
