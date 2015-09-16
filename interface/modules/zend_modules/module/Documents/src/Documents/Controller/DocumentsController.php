<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
*    @author  Basil PT <basil@zhservices.com>
*    @author  Chandni Babu <chandnib@zhservices.com> 
*    @author  Riju KP <rijukp@zhservices.com> 
* +------------------------------------------------------------------------------+
*/

namespace Documents\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Listener\Listener;
require_once($GLOBALS['fileroot'] . "/library/classes/Document.class.php");
use Document;

class DocumentsController extends AbstractActionController
{
  protected $documentsTable;
  protected $listenerObject;
  
  public function __construct()
  {
    $this->listenerObject	= new Listener;
  }
  
  public function getDocumentsTable()
  {
    if (!$this->documentsTable) {
      $sm = $this->getServiceLocator();
      $this ->documentsTable = $sm->get('Documents\Model\DocumentsTable');
    }
    return $this->documentsTable;
  }  
  
  /*
  * Upload document
  */
  public function uploadAction() {
    $request        = $this->getRequest();
    if($request->isPost()) {
      $error          = false;
      $files          = array();
      $uploaddir      = $GLOBALS['OE_SITE_DIR'].'/documents/'.$request->getPost('file_location');
      $pid            = $request->getPost('patient_id');
      $encounter      = $request->getPost('encounter_id');
      $batch_upload   = $request->getPost('batch_upload');
      $category_id    = $request->getPost('document_category');
      $encrypted_file = $request->getPost('encrypted_file');
      $encryption_key = $request->getPost('encryption_key');
      $storage_method = $GLOBALS['document_storage_method'];
      $documents = array();
      $i         = 0;
      foreach($_FILES as $file){
        $i++;
        $dateStamp      = date('Y-m-d-H-i-s');
        $file_name      = $dateStamp."_".basename($file["name"]);
        $file["name"]   = $file_name;
        
        $documents[$i]  = array(
          'name'        => $file_name,
          'type'        => $file['type'],
          'batch_upload'=> $batch_upload,
          'storage'     => $storage_method,
          'category_id' => $category_id,
          'pid'         => $pid,
        );
        
        // Read File Contents
        $tmpfile    = fopen($file['tmp_name'], "r");
        $filetext   = fread($tmpfile,$file['size']);
        
        // Decrypt Encryped Files
        if($encrypted_file == '1') {
          $plaintext  = \Documents\Plugin\Documents::decrypt($filetext,$encryption_key);
          fclose($tmpfile);
          unlink($file['tmp_name']);
          
          // Write new file contents
          $tmpfile = fopen($file['tmp_name'],"w+");
          fwrite($tmpfile,$plaintext);
          fclose($tmpfile);
          $file['size'] = filesize($file['tmp_name']);
        }
        
        $ob     = new \Document();
        $ret = $ob->createDocument($pid, $category_id, $file_name, $file['type'], $filetext,'', 1, 0);
      }
    }
  }
  
  /*
  * Retrieve document
  */
  public function retrieveAction() {
    
    // List of Preview Available File types
		$previewAvailableFiles = array(
			'application/pdf',
			'image/jpeg',
			'image/png',
			'image/gif',
			'text/plain',
			'text/html',
      'text/xml',
		);
    
    $request        = $this->getRequest();
    $documentId     = $this->params()->fromRoute('id');
    $doEncryption   = ($this->params()->fromRoute('doencryption') == '1') ? true : false;
    $encryptionKey  = $this->params()->fromRoute('key');
    $type           = ($this->params()->fromRoute('download') == '1') ? "attachment" : "inline";
    
    $result         = $this->getDocumentsTable()->getDocument($documentId);
    $skip_headers   = false;
    $contentType    = $result['mimetype'];
    
    $document       = \Documents\Plugin\Documents::getDocument($documentId,$doEncryption,$encryptionKey);
    $categoryIds    = $this->getDocumentsTable()->getCategoryIDs(array('CCD','CCR','CCDA'));
    if(in_array($result['category_id'],$categoryIds) && $contentType == 'text/xml'  && !$doEncryption) {
      $xml          = simplexml_load_string($document);
      $xsl          = new \DomDocument;
      
      switch($result['category_id']){
        case $categoryIds['CCD']:
          $style = "ccd.xsl";
          break;
        case $categoryIds['CCR']:
          $style = "ccr.xsl";
          break;
        case $categoryIds['CCDA']:
          $style = "ccda.xsl";
          break;
      };
      
      $xsl->load(__DIR__.'/../../../../../public/xsl/'.$style);
      $proc         = new \XSLTProcessor;
      $proc->importStyleSheet($xsl);
      $document     = $proc->transformToXML($xml);
    }
    
    if($type=="inline" && !$doEncryption) {
      if(in_array($result['mimetype'],$previewAvailableFiles)){
        if(in_array($result['category_id'],$categoryIds) && $contentType == 'text/xml') {
          $contentType  = 'text/html';
        }
      } else {
        $skip_headers = true;
      }
    } else {
      if($doEncryption) {
        $contentType  = "application/octet-stream";
      } else {
        $contentType  = $result['mimetype'];
      }  
    }
    
    if(!$skip_headers) {
      $response       = $this->getResponse();
      $response->setContent($document);
      $headers        = $response->getHeaders();
      $headers->clearHeaders()
              ->addHeaderLine('Content-Type',$contentType)
              ->addHeaderLine('Content-Disposition', $type . '; filename="' . $result['name'] . '"')
              ->addHeaderLine('Content-Length', strlen($document));
      $response->setHeaders($headers);
      return $this->response;
    }
  }
}