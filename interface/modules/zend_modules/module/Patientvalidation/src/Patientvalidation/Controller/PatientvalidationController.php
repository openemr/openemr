<?php


namespace Patientvalidation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PatientvalidationController extends BaseController{

    /**
     * PatientvalidationController constructor.
     */
    public function __construct()
    {
        //todo add permission of admin

    }


    /**
     * @return \Zend\Stdlib\ResponseInterface the index action
     */
    public function indexAction()
    {
        //here we can conduct all the validation we need , on the patient we need to check
       return  $this->responseWithNoLayout(array("status"=>"ok"));
    }

}