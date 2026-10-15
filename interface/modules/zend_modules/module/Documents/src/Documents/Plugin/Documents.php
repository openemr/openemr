<?php

/**
 * interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Documents\Plugin;

use Documents\Model\DocumentsTable;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

require_once(OEGlobalsBag::getInstance()->getProjectDir() . "/controllers/C_Document.class.php");

class Documents extends AbstractPlugin
{
    protected $documentsTable;

  /**
   *
   * Documents Table Object
   **/
    public function __construct()
    {
        $this->documentsTable = new DocumentsTable();
    }

    /**
     * getDocument Retrieve Documents from Couch/HDD
     * @param int $documentId Document ID
     * @param bool $doEncryption Download Encrypted File
     * @param  String $encryption_key Key for Document Encryption
     * @return String File Content
     */
    public static function getDocument($documentId, $doEncryption = false, $encryption_key = '')
    {
        // Load the document's owning pid first so retrieve_action()'s
        // pid-vs-document check actually runs. An empty $patient_id string
        // trips an escape hatch in C_Document::retrieve_action() that
        // collapses the ownership assertion to 0===0 — defense in depth
        // against a controller that forgets its own ownership gate.
        $d = new \Document((int) $documentId);
        $foreignId = (int) $d->get_foreign_id();
        $obj = new \C_Document();
        $obj->onReturnRetrieveKey();
        return $obj->retrieve_action((string) $foreignId, $documentId, true, true, true);
    }

    public static function fetchXmlDocuments(): array
    {
        // The three batch-import loops that consume this list (CCDA, CCD,
        // CCR) each hand the id to importAction() under batchContext, which
        // skips the usual `foreign_id === 0` check that external callers
        // get. Enforce the unassigned-document precondition at the source
        // so an already-assigned XML can never be re-imported and
        // reclassified by a later batch pass.
        $query = "SELECT doc.id
	    FROM categories_to_documents AS cat_doc
	    JOIN documents AS doc
	        ON doc.imported = 0
	        AND doc.id = cat_doc.document_id
	        AND doc.mimetype = 'text/xml'
	        AND (doc.foreign_id = 0 OR doc.foreign_id IS NULL)
	    WHERE cat_doc.category_id = 1";
        $result = QueryUtils::fetchRecords($query);
        $count  = 0;
        $module = [];
        foreach ($result as $row) {
            $content = self::getDocument($row['id']);
            $module[$count]['doc_id']   = $row['id'];
            if (preg_match("/<ClinicalDocument/", $content)) {
                $module[$count]['doc_type'] = preg_match("/2.16.840.1.113883.3.88.11.32.1/", $content) ? 'CCD' : 'CCDA';
            } elseif (preg_match("/<ccr:ContinuityOfCareRecord/", $content)) {
                $module[$count]['doc_type'] = 'CCR';
            }

            $count++;
        }

        return $module;
    }
}
