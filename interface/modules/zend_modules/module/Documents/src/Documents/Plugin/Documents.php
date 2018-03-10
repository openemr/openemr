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
* +------------------------------------------------------------------------------+
*/
namespace Documents\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Documents\Model\DocumentsTable;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;
require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");
use C_Document;

class Documents extends AbstractPlugin
{
    protected $documentsTable;

  /**
   *
   * Documents Table Object
   * @param type $sm Service Manager
   **/
    public function __construct($sm)
    {
        $sm->get('Zend\Db\Adapter\Adapter');
        $this->documentsTable = new DocumentsTable();
    }

    /**
     * encrypt - Encrypts a plain text
     * Supports AES-256-CBC encryption
     * @param String $plain_text Plain Text to be encrypted
     * @param String $key Encryption Key
     * @return String
     */
    public function encrypt($plaintext, $key)
    {
                $obj = new \C_Document();
                $obj->encrypt($plaintext, $key);
    }

    /**
     * decrypt  - Decrypts an Encrypted String
     * @param String $crypttext Encrypted String
     * @param String $key Decryption Key
     * @return String
     */
    public function decrypt($crypttext, $key)
    {
                $obj = new \C_Document();
                $obj->decrypt($crypttext, $key);
    }

    /**
     * couchDB - Couch DB Connection
     *               - Uses Doctrine  CouchDBClient
     * @return Object $connection
     */
    public function couchDB()
    {
        $host       = $GLOBALS['couchdb_host'];
        $port       = $GLOBALS['couchdb_port'];
        $usename    = $GLOBALS['couchdb_user'];
        $password   = $GLOBALS['couchdb_pass'];
        $database   = $GLOBALS['couchdb_dbase'];
        $enable_log = ($GLOBALS['couchdb_log'] == 1) ? true : false;

        $options = array(
            'host'        => $host,
            'port'        => $port,
            'user'        => $usename,
            'password'    => $password,
            'logging'     => $enable_log,
            'dbname'      => $database
        );
        $connection = \Doctrine\CouchDB\CouchDBClient::create($options);
        return $connection;
    }

    /**
     * saveCouchDocument - Save Document to Couch DB
     * @param Object $connection Couch DB Connection Object
     * @param Json Encoded Data
     * @return Array
     */
    public function saveCouchDocument($connection, $data)
    {
        $couch  = $connection->postDocument($data);
        $id         = $couch[0];
        $rev        = $couch[1];
        if ($id && $rev) {
            $connection->putDocument($data, $id, $rev);
            return $couch;
        } else {
            return false;
        }
    }

    /**
     * getDocument Retieve Documents from Couch/HDD
     * @param Integer $documentId Document ID
     * @param Boolean $doEncryption Download Encrypted File
     * @param  String $encryption_key Key for Document Encryption
     * @return String File Content
     */
    public function getDocument($documentId, $doEncryption = false, $encryption_key = '')
    {
                $obj = new \C_Document();
                $document = $obj->retrieve_action("", $documentId, true, true, true);
        return $document;
    }

    public function fetchXmlDocuments()
    {
        $obj = new ApplicationTable();
        $query = "SELECT doc.id 
	    FROM categories_to_documents AS cat_doc
	    JOIN documents AS doc ON doc.imported = 0 AND doc.id = cat_doc.document_id AND doc.mimetype = 'text/xml'
	    WHERE cat_doc.category_id = 1";
        $result = $obj->zQuery($query);
        $count  = 0;
        $module = array();
        foreach ($result as $row) {
            $content = \Documents\Plugin\Documents::getDocument($row['id']);
            $module[$count]['doc_id']   = $row['id'];
            if (preg_match("/<ClinicalDocument/", $content)) {
                if (preg_match("/2.16.840.1.113883.3.88.11.32.1/", $content)) {
                    $module[$count]['doc_type'] = 'CCD';
                } else {
                    $module[$count]['doc_type'] = 'CCDA';
                }
            } elseif (preg_match("/<ccr:ContinuityOfCareRecord/", $content)) {
                $module[$count]['doc_type'] = 'CCR';
            }

            $count++;
        }

        return $module;
    }
}
