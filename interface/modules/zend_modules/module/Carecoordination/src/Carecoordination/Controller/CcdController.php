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

class CcdController extends AbstractActionController
{
    protected $ccdTable;
    public function __construct($sm)
    {
      $this->listenerObject	= new Listener;
    }
    
    /*
    * Upload CCD file
    */
    public function uploadAction()
    {
      $request          = $this->getRequest();
      $upload           = $request->getPost('upload');
      $category_details = \Carecoordination\Controller\CarecoordinationController::getCarecoordinationTable()->fetch_cat_id('CCD');
      
      if($upload == 1){
        $time_start         = date('Y-m-d H:i:s');
        $cdoc               = \Documents\Controller\DocumentsController::uploadAction();
        $uploaded_documents = array();
        $uploaded_documents = \Carecoordination\Controller\CarecoordinationController::getCarecoordinationTable()->fetch_uploaded_documents(array('user' => $_SESSION['authId'], 'time_start' => $time_start, 'time_end' => date('Y-m-d H:i:s')));
        if($uploaded_documents[0]['id'] > 0){
            $_REQUEST["document_id"]    = $uploaded_documents[0]['id'];
            $_REQUEST["batch_import"]   = 'YES';
            $this->importAction();
        }
      }
      else{
            $result = \Documents\Plugin\Documents::fetchXmlDocuments();
            foreach($result as $row){
                if($row['doc_type'] == 'CCD'){
                    $_REQUEST["document_id"] = $row['doc_id'];
                    $this->importAction();
                    \Documents\Model\DocumentsTable::updateDocumentCategoryUsingCatname($row['doc_type'], $row['doc_id']);
                }
            }
        }
      
      $records = \Carecoordination\Controller\CarecoordinationController::getCarecoordinationTable()->document_fetch(array('cat_title' => 'CCD','type' => '13'));
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
    * Function to import the data CCD file to audit tables.
    *
    * @param    document_id     integer value
    * @return   none
    */
    public function importAction()
    { 
        $request     = $this->getRequest();
        if($request->getQuery('document_id')) {
          $_REQUEST["document_id"] = $request->getQuery('document_id');
          $category_details  	     = \Carecoordination\Controller\CarecoordinationController::getCarecoordinationTable()->fetch_cat_id('CCD');
          \Documents\Controller\DocumentsController::getDocumentsTable()->updateDocumentCategory($category_details[0]['id'],$_REQUEST["document_id"]);
        }
        $document_id                      =    $_REQUEST["document_id"]; 
        $xml_content                      =    \Carecoordination\Controller\CarecoordinationController::getCarecoordinationTable()->getDocument($document_id);
        
        $xmltoarray                       =    new \Zend\Config\Reader\Xml();
        $array                            =    $xmltoarray->fromString((string) $xml_content);
        
        $this->getCcdTable()->import($array,$document_id);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
    /**
    * Table gateway
    * @return object
    */
    public function getCcdTable()
    {
        if (!$this->ccdTable) {
            $sm = $this->getServiceLocator();
            $this->ccdTable = $sm->get('Carecoordination\Model\CcdTable');
        }
        return $this->ccdTable;
    } 

}