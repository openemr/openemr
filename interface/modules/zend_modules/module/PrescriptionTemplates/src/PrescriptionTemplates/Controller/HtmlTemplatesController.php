<?php

namespace PrescriptionTemplates\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");


class HtmlTemplatesController extends AbstractActionController{


    public function defaultAction()
    {
        $id = $this->params()->fromQuery('id');
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
        $prescriptions = array();
        foreach ($ids as $id) {
            $p = new \Prescription($id);
            $prescriptions[] = $p;
        }
        $patient = $p->patient;

        $viewModel = new ViewModel(array('patient' => $patient, 'prescriptions' => $prescriptions, 'user' => array()));
        $viewModel->setTemplate("prescription-templates/default.phtml");

        return $viewModel;
    }

}
