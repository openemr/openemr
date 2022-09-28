<?php

/**
 * interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @author    Chandni Babu <chandnib@zhservices.com>
 * @author    Riju KP <rijukp@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Documents\Controller;

use DOMDocument;
use OpenEMR\Common\Crypto\CryptoGen;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Listener\Listener;
use Documents\Model\DocumentsTable;
use Document;
use XSLTProcessor;

class DocumentsController extends AbstractActionController
{
    protected $documentsTable;
    protected $listenerObject;

    public function __construct(DocumentsTable $table)
    {
        $this->listenerObject = new Listener();
        $this->documentsTable = $table;
    }

    public function getDocumentsTable()
    {
        return $this->documentsTable;
    }

    public function getDocumentsPlugin()
    {
        return $this->Documents();
    }

    public function isZipUpload($request = null)
    {
        if (!$request) {
            $request = $this->getRequest();
        }
        if ($request->isPost() && count($_FILES) > 0) {
            // we only deal with the first uploaded file... with zip files only one file at a time should be sent
            $filePtr = reset($_FILES);
            // we don't rely on the mime type sent from the client and grab it from the operating system
            if (is_uploaded_file($filePtr['tmp_name'])) {
                $mime_type = mime_content_type($filePtr['tmp_name']);
                return $mime_type == 'application/zip';
            }
        }
        return false;
    }

    /*
    * Upload document
    */
    public function uploadAction($request = null)
    {
        if (!$request) {
            $request = $this->getRequest();
        }
        if ($request->isPost()) {
            $error = false;
            $files = array();
            $uploaddir = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $request->getPost('file_location');
            $pid = $request->getPost('patient_id');
            $encounter = $request->getPost('encounter_id');
            $batch_upload = $request->getPost('batch_upload');
            $category_id = $request->getPost('document_category');
            $encrypted_file = $request->getPost('encrypted_file');
            $encryption_key = $request->getPost('encryption_key');
            $storage_method = $GLOBALS['document_storage_method'];
            $documents = array();
            $i = 0;
            foreach ($_FILES as $file) {
                $i++;
                $dateStamp = date('Y-m-d-H-i-s');
                $file_name = $dateStamp . "_" . basename($file["name"]);
                $file["name"] = $file_name;

                $documents[$i] = array(
                    'name' => $file_name,
                    'type' => $file['type'],
                    'batch_upload' => $batch_upload,
                    'storage' => $storage_method,
                    'category_id' => $category_id,
                    'pid' => $pid,
                );

                // Read File Contents
                $tmpfile = fopen($file['tmp_name'], "r");
                $filetext = fread($tmpfile, $file['size']);

                // Decrypt Encrypted File
                if ($encrypted_file == '1') {
                    $cryptoGen = new CryptoGen();
                    $plaintext = $cryptoGen->decryptStandard($filetext, $encryption_key);
                    if ($plaintext === false) {
                        error_log("OpenEMR Error: Unable to decrypt a document since decryption failed.");
                        $plaintext = "";
                    }
                    fclose($tmpfile);
                    unlink($file['tmp_name']);

                    // Write new file contents
                    $tmpfile = fopen($file['tmp_name'], "w+");
                    fwrite($tmpfile, $plaintext);
                    fclose($tmpfile);
                    $file['size'] = filesize($file['tmp_name']);
                }

                $ob = new Document();
                $ret = $ob->createDocument($pid, $category_id, $file_name, $file['type'], $filetext, '', 1, 0);
            }
        }
    }

    /*
    * Retrieve document
    */
    public function retrieveAction()
    {

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

        $request = $this->getRequest();
        $documentId = $this->params()->fromRoute('id');
        $doEncryption = ($this->params()->fromRoute('doencryption') == '1') ? true : false;
        $encryptionKey = $this->params()->fromRoute('key');
        $type = ($this->params()->fromRoute('download') == '1') ? "attachment" : "inline";

        $result = $this->getDocumentsTable()->getDocument($documentId);
        $skip_headers = false;
        $contentType = $result['mimetype'];

        // @see Documents/Plugin/Documents
        $document = $this->Documents()->getDocument($documentId, $doEncryption, $encryptionKey);
        $categoryIds = $this->getDocumentsTable()->getCategoryIDs(array('CCD', 'CCR', 'CCDA'));
        if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml' && !$doEncryption) {
            $xml = simplexml_load_string($document);
            $xsl = new DomDocument();
            $qrda = $xml->templateId[2]['root'];
            switch ($result['category_id']) {
                case $categoryIds['CCD']:
                    $style = "ccd.xsl";
                    break;
                case $categoryIds['CCR']:
                    $style = "ccr.xsl";
                    break;
                case $categoryIds['CCDA']:
                    $style = "cda.xsl";
                    break;
            }

            if ($qrda == '2.16.840.1.113883.10.20.24.1.2') {
                // a QRDA QDM CAT I document
                $style = 'qrda.xsl';
            }
            $xsl->load(__DIR__ . '/../../../../../public/xsl/' . $style);
            $proc = new XSLTProcessor();
            $proc->importStyleSheet($xsl);
            $document = $proc->transformToXML($xml);
        }

        if ($type == "inline" && !$doEncryption) {
            if (in_array($result['mimetype'], $previewAvailableFiles)) {
                if (in_array($result['category_id'], $categoryIds) && $contentType == 'text/xml') {
                    $contentType = 'text/html';
                }
            } else {
                $skip_headers = true;
            }
        } else {
            if ($doEncryption) {
                $contentType = "application/octet-stream";
            } else {
                $contentType = $result['mimetype'];
            }
        }

        if (!$skip_headers) {
            $response = $this->getResponse();
            $response->setContent($document);
            $headers = $response->getHeaders();
            $headers->clearHeaders()
                ->addHeaderLine('Content-Type', $contentType)
                ->addHeaderLine('Content-Disposition', $type . '; filename="' . $result['name'] . '"')
                ->addHeaderLine('Content-Length', strlen($document));
            $response->setHeaders($headers);
            return $this->response;
        }
    }
}
