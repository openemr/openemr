<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*    @author  Vinish K <vinish@zhservices.com>
*    @author  Chandni Babu <chandnib@zhservices.com> 
*    @author  Riju KP <rijukp@zhservices.com> 
* +------------------------------------------------------------------------------+
*/
namespace Carecoordination\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Listener\Listener;
use Documents\Controller\DocumentsController;

use C_Document;
use Document;
use CouchDB;
use xmltoarray_parser_htmlfix;

class CarecoordinationController extends AbstractActionController
{
    
    public function __construct($sm)
    {
      $this->listenerObject	= new Listener;
    }
    
    /**
    * Index Page
    * @param int   $id     menu id
    * $param array $data   menu details
    * @param string $slug  controller name
    * @return \Zend\View\Model\ViewModel
    */
    public function indexAction()
    {
        $this->redirect()->toRoute('encountermanager',array('action'=>'index'));
    }
    
    /*
    * Upload CCDA file
    */
    public function uploadAction()
    {
      $request     = $this->getRequest();
      $action      = $request->getPost('action');
      $am_id       = $request->getPost('am_id');
      $document_id = $request->getPost('document_id');
      
      if($action  == 'add_new_patient'){
          $this->getCarecoordinationTable()->insert_patient($am_id,$document_id);
      }
      
      $upload           = $request->getPost('upload');
      $category_details = $this->getCarecoordinationTable()->fetch_cat_id('CCDA');
      
      if($upload == 1){
        $time_start         = date('Y-m-d H:i:s');
        $cdoc               = \Documents\Controller\DocumentsController::uploadAction();
        $uploaded_documents = array();
        $uploaded_documents = $this->getCarecoordinationTable()->fetch_uploaded_documents(array('user' => $_SESSION['authId'], 'time_start' => $time_start, 'time_end' => date('Y-m-d H:i:s')));
        if($uploaded_documents[0]['id'] > 0){
            $_REQUEST["document_id"]    = $uploaded_documents[0]['id'];
            $_REQUEST["batch_import"]   = 'YES';
            $this->importAction();
        }
      } 
      
      $records = $this->getCarecoordinationTable()->document_fetch(array('cat_title' => 'CCDA','type' => '12'));
      $view = new ViewModel(array(
          'records'       => $records,
          'category_id'   => $category_details[0]['id'],
          'file_location' => basename($_FILES['file']['name']),
          'patient_id'    => '00',
          'listenerObject'=> $this->listenerObject
      ));
      return $view;
    }
    
    /*
    * Function to import the data CCDA file to audit tables.
    *
    * @param    document_id     integer value
    * @return   none
    */
    public function importAction()
    { 
        $request     = $this->getRequest();
        if($request->getQuery('document_id')) {
          $_REQUEST["document_id"] = $request->getQuery('document_id');
          $category_details  	     = $this->getCarecoordinationTable()->fetch_cat_id('CCDA');
          \Documents\Controller\DocumentsController::getDocumentsTable()->updateDocumentCategory($category_details[0]['id'],$_REQUEST["document_id"]);
        }
        $document_id                      =    $_REQUEST["document_id"]; 
        $xml_content                      =    $this->getCarecoordinationTable()->getDocument($document_id);
        
        $xmltoarray                       =    new \Zend\Config\Reader\Xml();
        $array                            =    $xmltoarray->fromString((string) $xml_content);
        
        $patient_role                     =    $array['recordTarget']['patientRole']; 
        $patient_pub_pid                  =    $patient_role['id'][0]['extension'];
        $patient_ssn                      =    $patient_role['id'][1]['extension'];
        $patient_address                  =    $patient_role['addr']['streetAddressLine'];
        $patient_city                     =    $patient_role['addr']['city'];
        $patient_state                    =    $patient_role['addr']['state'];
        $patient_postalcode               =    $patient_role['addr']['postalCode'];
        $patient_country                  =    $patient_role['addr']['country'];        
        $patient_phone_type               =    $patient_role['telecom']['use'];
        $patient_phone_no                 =    $patient_role['telecom']['value'];        
        $patient_fname                    =    $patient_role['patient']['name']['given'][0];
        $patient_lname                    =    $patient_role['patient']['name']['given'][1];
        $patient_family_name              =    $patient_role['patient']['name']['family'];        
        $patient_gender_code              =    $patient_role['patient']['administrativeGenderCode']['code'];
        $patient_gender_name              =    $patient_role['patient']['administrativeGenderCode']['displayName'];        
        $patient_dob                      =    $patient_role['patient']['birthTime']['value'];
        $patient_marital_status           =    $patient_role['patient']['religiousAffiliationCode']['code'];
        $patient_marital_status_display   =    $patient_role['patient']['religiousAffiliationCode']['displayName'];        
        $patient_race                     =    $patient_role['patient']['raceCode']['code'];
        $patient_race_display             =    $patient_role['patient']['raceCode']['displayName'];        
        $patient_ethnicity                =    $patient_role['patient']['ethnicGroupCode']['code'];
        $patient_ethnicity_display        =    $patient_role['patient']['ethnicGroupCode']['displayName'];        
        $patient_language                 =    $patient_role['patient']['languageCommunication']['languageCode']['code'];
        
        $author                           =    $array['recordTarget']['author']['assignedAuthor'];
        $author_id                        =    $author['id']['extension'];
        $author_address                   =    $author['addr']['streetAddressLine'];
        $author_city                      =    $author['addr']['city'];
        $author_state                     =    $author['addr']['state'];
        $author_postalCode                =    $author['addr']['postalCode'];
        $author_country                   =    $author['addr']['country'];
        $author_phone_use                 =    $author['telecom']['use'];
        $author_phone                     =    $author['telecom']['value'];
        $author_name_given                =    $author['assignedPerson']['name']['given'];
        $author_name_family               =    $author['assignedPerson']['name']['family'];
        
        $data_enterer                     =    $array['recordTarget']['dataEnterer']['assignedEntity'];
        $data_enterer_id                  =    $data_enterer['id']['extension'];
        $data_enterer_address             =    $data_enterer['addr']['streetAddressLine'];
        $data_enterer_city                =    $data_enterer['addr']['city'];
        $data_enterer_state               =    $data_enterer['addr']['state'];
        $data_enterer_postalCode          =    $data_enterer['addr']['postalCode'];
        $data_enterer_country             =    $data_enterer['addr']['country'];
        $data_enterer_phone_use           =    $data_enterer['telecom']['use'];
        $data_enterer_phone               =    $data_enterer['telecom']['value'];
        $data_enterer_name_given          =    $data_enterer['assignedPerson']['name']['given'];
        $data_enterer_name_family         =    $data_enterer['assignedPerson']['name']['family'];
        
        $informant                        =    $array['recordTarget']['informant'][0]['assignedEntity'];
        $informant_id                     =    $informant['id']['extension'];
        $informant_address                =    $informant['addr']['streetAddressLine'];
        $informant_city                   =    $informant['addr']['city'];
        $informant_state                  =    $informant['addr']['state'];
        $informant_postalCode             =    $informant['addr']['postalCode'];
        $informant_country                =    $informant['addr']['country'];
        $informant_phone_use              =    $informant['telecom']['use'];
        $informant_phone                  =    $informant['telecom']['value'];
        $informant_name_given             =    $informant['assignedPerson']['name']['given'];
        $informant_name_family            =    $informant['assignedPerson']['name']['family'];
        
        $personal_informant               =    $array['recordTarget']['informant'][1]['relatedEntity'];
        $personal_informant_name          =    $personal_informant['relatedPerson']['name']['given'];
        $personal_informant_family        =    $personal_informant['relatedPerson']['name']['family'];
        
        $custodian                        =    $array['recordTarget']['custodian']['assignedCustodian']['representedCustodianOrganization'];
        $custodian_name                   =    $custodian['name'];
        $custodian_address                =    $custodian['addr']['streetAddressLine'];
        $custodian_city                   =    $custodian['addr']['city'];
        $custodian_state                  =    $custodian['addr']['state'];
        $custodian_postalCode             =    $custodian['addr']['postalCode'];
        $custodian_country                =    $custodian['addr']['country'];
        $custodian_phone                  =    $custodian['telecom']['value'];
        $custodian_phone_use              =    $custodian['telecom']['use'];
        
        $informationRecipient             =    $array['recordTarget']['informationRecipient']['intendedRecipient'];
        $informationRecipient_name        =    $informationRecipient['informationRecipient']['name']['given'];
        $informationRecipient_name        =    $informationRecipient['informationRecipient']['name']['family'];
        $informationRecipient_org         =    $informationRecipient['receivedOrganization']['name'];
        
        $legalAuthenticator               =    $array['recordTarget']['legalAuthenticator'];
        $legalAuthenticator_signatureCode =    $legalAuthenticator['signatureCode']['code'];
        $legalAuthenticator_id            =    $legalAuthenticator['assignedEntity']['id']['extension'];
        $legalAuthenticator_address       =    $legalAuthenticator['assignedEntity']['addr']['streetAddressLine'];
        $legalAuthenticator_city          =    $legalAuthenticator['assignedEntity']['addr']['city'];
        $legalAuthenticator_state         =    $legalAuthenticator['assignedEntity']['addr']['state'];
        $legalAuthenticator_postalCode    =    $legalAuthenticator['assignedEntity']['addr']['postalCode'];
        $legalAuthenticator_country       =    $legalAuthenticator['assignedEntity']['addr']['country'];
        $legalAuthenticator_phone         =    $legalAuthenticator['assignedEntity']['telecom']['value'];
        $legalAuthenticator_phone_use     =    $legalAuthenticator['assignedEntity']['telecom']['use'];
        $legalAuthenticator_name_given    =    $legalAuthenticator['assignedEntity']['assignedPerson']['name']['given'];
        $legalAuthenticator_name_family   =    $legalAuthenticator['assignedEntity']['assignedPerson']['name']['family'];
        
        $authenticator                    =    $array['recordTarget']['authenticator'];
        $authenticator_signatureCode      =    $authenticator['signatureCode']['code'];
        $authenticator_id                 =    $authenticator['assignedEntity']['id']['extension'];
        $authenticator_address            =    $authenticator['assignedEntity']['addr']['streetAddressLine'];
        $authenticator_city               =    $authenticator['assignedEntity']['addr']['city'];
        $authenticator_state              =    $authenticator['assignedEntity']['addr']['state'];
        $authenticator_postalCode         =    $authenticator['assignedEntity']['addr']['postalCode'];
        $authenticator_country            =    $authenticator['assignedEntity']['addr']['country'];
        $authenticator_phone              =    $authenticator['assignedEntity']['telecom']['value'];
        $authenticator_phone_use          =    $authenticator['assignedEntity']['telecom']['use'];
        $authenticator_name_given         =    $authenticator['assignedEntity']['assignedPerson']['name']['given'];
        $authenticator_name_family        =    $authenticator['assignedEntity']['assignedPerson']['name']['family'];
        
        $this->getCarecoordinationTable()->import($array,$document_id);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
    
    public function revandapproveAction()
    {
      $request         = $this->getRequest();    
      $document_id     = $request->getQuery('document_id') ? $request->getQuery('document_id') : $request->getPost('document_id',null);
      $audit_master_id = $request->getQuery('amid') ? $request->getQuery('amid') : $request->getPost('amid',null);
      $pid             = $request->getQuery('pid') ? $request->getQuery('pid') : $request->getPost('pid',null);
      
      if($request->getPost('setval') == 'approve'){
        $this->getCarecoordinationTable()->insertApprovedData($request->getPost());
        return $this->redirect()->toRoute('carecoordination',array(
                                                                'controller'=>'Carecoordination',
                                                                'action'    =>'upload'));
      }
      else if($request->getPost('setval') == 'discard'){
        $this->getCarecoordinationTable()->discardCCDAData(array('audit_master_id' => $audit_master_id));
        return $this->redirect()->toRoute('carecoordination',array(
                                                                'controller'=>'Carecoordination',
                                                                'action'    =>'upload'));
      }       
      
      $demographics                        = $this->getCarecoordinationTable()->getDemographics(array('audit_master_id' => $audit_master_id));
      $demographics_old                    = $this->getCarecoordinationTable()->getDemographicsOld(array('pid' => $pid));

      $problems                            = $this->getCarecoordinationTable()->getProblems(array('pid' => $pid));
      $problems_audit                      = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'lists1');

      $allergies                           = $this->getCarecoordinationTable()->getAllergies(array('pid' => $pid));
      $allergies_audit                     = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'lists2');

      $medications                         = $this->getCarecoordinationTable()->getMedications(array('pid' => $pid));
      $medications_audit                   = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'lists3');

      $immunizations                       = $this->getCarecoordinationTable()->getImmunizations(array('pid' => $pid));
      $immunizations_audit                 = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'immunization');

      $lab_results                         = $this->getCarecoordinationTable()->getLabResults(array('pid' => $pid));
      $lab_results_audit                   = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'procedure_result');
      
      $vitals                              = $this->getCarecoordinationTable()->getVitals(array('pid' => $pid));
      $vitals_audit                        = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'vital_sign');
      
      $social_history                      = $this->getCarecoordinationTable()->getSocialHistory(array('pid' => $pid));
      $social_history_audit                = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'social_history');
      
      $encounter                           = $this->getCarecoordinationTable()->getEncounterData(array('pid' => $pid));
      $encounter_audit                     = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'encounter');
      
      $procedure                           = $this->getCarecoordinationTable()->getProcedure(array('pid' => $pid));
      $procedure_audit                     = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'procedure');
      
      $care_plan                           = $this->getCarecoordinationTable()->getCarePlan(array('pid' => $pid));
      $care_plan_audit                     = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'care_plan');
      
      $functional_cognitive_status         = $this->getCarecoordinationTable()->getFunctionalCognitiveStatus(array('pid' => $pid));
      $functional_cognitive_status_audit   = $this->getCarecoordinationTable()->createAuditArray($audit_master_id, 'functional_cognitive_status');
           
      $gender_list                         = $this->getCarecoordinationTable()->getList('sex');
      $country_list                        = $this->getCarecoordinationTable()->getList('country');
      $marital_status_list                 = $this->getCarecoordinationTable()->getList('marital');
      $religion_list                       = $this->getCarecoordinationTable()->getList('religious_affiliation');
      $race_list                           = $this->getCarecoordinationTable()->getList('race');
      $ethnicity_list                      = $this->getCarecoordinationTable()->getList('ethnicity');
      $state_list                          = $this->getCarecoordinationTable()->getList('state');
      $tobacco                             = $this->getCarecoordinationTable()->getList('smoking_status');
      
      $demographics_old[0]['sex']          = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['sex'],'sex','');
      $demographics_old[0]['country_code'] = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['country_code'],'country','');
      $demographics_old[0]['status']       = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['status'],'marital','');
      $demographics_old[0]['religion']     = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['religion'],'religious_affiliation','');
      $demographics_old[0]['race']         = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['race'],'race','');
      $demographics_old[0]['ethnicity']    = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['ethnicity'],'ethnicity','');
      $demographics_old[0]['state']        = $this->getCarecoordinationTable()->getListTitle($demographics_old[0]['state'],'state','');
          
      $view = new ViewModel(array(
          'demographics'        => $demographics,
          'demographics_old'    => $demographics_old,
          'problems'            => $problems,
          'problems_audit'      => $problems_audit,
          'allergies'           => $allergies,
          'allergies_audit'     => $allergies_audit,
          'medications'         => $medications,
          'medications_audit'   => $medications_audit,
          'immunizations'       => $immunizations,
          'immunizations_audit' => $immunizations_audit,
          'lab_results'         => $lab_results,
          'lab_results_audit'   => $lab_results_audit,
          'vitals'              => $vitals,
          'vitals_audit'        => $vitals_audit,
          'social_history'      => $social_history,
          'social_history_audit'=> $social_history_audit,
          'encounter'           => $encounter,
          'encounter_audit'     => $encounter_audit,
          'care_plan'           => $care_plan,
          'care_plan_audit'     => $care_plan_audit,
          'functional_cognitive_status' => $functional_cognitive_status,
          'functional_cognitive_status_audit' => $functional_cognitive_status_audit,
          'amid'                => $audit_master_id,
          'pid'                 => $pid,
          'document_id'         => $document_id,
          'gender_list'         => $gender_list,
          'country_list'        => $country_list,
          'marital_status_list' => $marital_status_list,
          'religion_list'       => $religion_list,
          'race_list'           => $race_list,
          'ethnicity_list'      => $ethnicity_list,
          'tobacco'             => $tobacco,
          'state_list'          => $state_list,
          'listenerObject'      => $this->listenerObject
      ));
      return $view;   
    }
    
    /**
    * Table gateway
    * @return object
    */
    public function getCarecoordinationTable()
    {
        if (!$this->carecoordinationTable) {
            $sm = $this->getServiceLocator();
            $this->carecoordinationTable = $sm->get('Carecoordination\Model\CarecoordinationTable');
        }
        return $this->carecoordinationTable;
    } 

}