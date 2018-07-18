<?php

namespace PrescriptionTemplates\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");

/**
 * Description of AlbumController
 *
 * @author suleymanmelikoglu
 */
class PdfTemplatesController extends AbstractActionController{

    public function defaultAction()
    {
        $id = $this->params()->fromQuery('id');
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ids as $id) {
            $p = new \Prescription($id);
        }


        $viewModel = new ViewModel();
        $viewModel->setTemplate("prescription-templates/default.phtml");

        return $viewModel;
    }

}
