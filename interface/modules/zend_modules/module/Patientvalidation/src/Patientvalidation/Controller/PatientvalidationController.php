<?php


namespace Patientvalidation\Controller;

use Patientvalidation\Model\PatientData;
use Zend\Json\Server\Exception\ErrorException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Error;

class PatientvalidationController extends BaseController{


    /**
     * PatientvalidationController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        //todo add permission of admin

    }

    private function getAllRealatedPatients()
    {
        //Collect all of the data received from the new patient form
        $patientParams = $this->getRequestedParamsArray();

        //clean the mf_
        foreach ($patientParams as $key=>$item) {
                $keyArr=explode("mf_",$key);
                $patientParams[$keyArr[1]]="'".$item."'";
                unset($patientParams[$key]);


        }


        $patientData=$this->getPatientDataTable()->getPatients($patientParams);
        if(count($patientData)>0){
            return array("status"=>"failed","list"=>$patientData);
        }
        else{
            return array("status"=>"ok","list"=>$patientData);
        }
    }
    /**
     * @return \Zend\Stdlib\ResponseInterface the index action
     */

    public function indexAction()
    {

        $this->getJsFiles();
        $this->getCssFiles();
        $this->layout()->setVariable('jsFiles', $this->jsFiles);
        $this->layout()->setVariable('cssFiles', $this->cssFiles);
        $this->layout()->setVariable("title","Patient validation");

         $relatedPatients =  $this->getAllRealatedPatients();


        return array("related_patients"=>$relatedPatients['list'],"translate"=>$this->translate);
    }
    /**
     * get instance of Patientvalidation
     * @return array|object
     */
    private function getPatientDataTable()
    {
        if (!$this->PatientDataTable) {
            $sm = $this->getServiceLocator();
            $this->PatientDataTable = $sm->get('Patientvalidation\Model\PatientDataTable');
        }
        return $this->PatientDataTable;
    }



}