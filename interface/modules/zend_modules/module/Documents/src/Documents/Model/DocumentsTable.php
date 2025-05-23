<?php

/**
 * interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Documents\Model;

use Application\Model\ApplicationTable;
use Laminas\Db\TableGateway\AbstractTableGateway;

class DocumentsTable extends AbstractTableGateway
{
    /*
    * Save the category - document mapping
    * @param    $category_id    integer   Category ID
    * @param    $document_id    integer   Document ID
    */
    public function insertDocumentCategory($category_id, $document_id)
    {
        $obj = new ApplicationTable();
        $sql = "INSERT INTO categories_to_documents (category_id, document_id) VALUES (?, ?)";
        $result = $obj->zQuery($sql, array($category_id, $document_id));
    }

    /*
    * Move the document to a different category
    * @param    $category_id    integer   Category ID
    * @param    $document_id    integer   Document ID
    */
    public function updateDocumentCategory($category_id, $document_id): void
    {
        $obj = new ApplicationTable();
        $sql = "UPDATE categories_to_documents SET category_id = ? WHERE document_id = ?";
        $result = $obj->zQuery($sql, array($category_id, $document_id));
    }

    /**
     * getCategories - Get Document Categories
     *
     * @param Integer $categoryParentId
     * @return array
     */
    public function getCategories($categoryParentId)
    {
        $obj = new ApplicationTable();
        $sql = "SELECT * FROM `categories` WHERE `parent` = ? ORDER BY `order`";
        $result = $obj->zQuery($sql, array($categoryParentId));
        $category = array();
        foreach ($result as $row) {
            $category[$row['cat_id']] = array(
                'category_id' => $row['id'],
                'category_name' => $row['name'],
            );
        }

        return $category;
    }

    /**
     * getDocument - get Document Data by Id
     *
     * @param Integer $documentId Document Id
     * @return array
     */
    public function getDocument($documentId)
    {
        $obj = new ApplicationTable();
        $sql = "SELECT * FROM documents AS doc 
              JOIN categories_to_documents AS cat_doc ON cat_doc.document_id = doc.id
              WHERE doc.id = ?";
        $result = $obj->zQuery($sql, array($documentId));
        return $result->current();
    }

    /**
     * getCategoryIDs - get Category Ids By Name
     *
     * @param array $categories - Category Lists
     * @return
     */
    public function getCategoryIDs($categories = array()): array
    {
        $obj = new ApplicationTable();
        $categories_count = count($categories);
        $cat_name = array();
        for ($i = 0; $i < $categories_count; $i++) {
            $cat_name[$i] = "?";
        }

        $sql = "SELECT `id`,`name` FROM `categories` " .
            "WHERE `name` IN (" . implode(",", $cat_name) . ")";
        $result = $obj->zQuery($sql, $categories);
        $category = array();
        foreach ($result as $row) {
            $category[$row['name']] = $row['id'];
        }

        return $category;
    }

    /**
     * saveDocumentdetails - save document details
     *
     * @param array $current_document - document details
     */
    public function saveDocumentdetails($current_document): void
    {
        $obj = new ApplicationTable();
        foreach ($current_document as $values) {
            $sql = "UPDATE 
             `documents` 
              SET
              `docdate` = ?,
              `pid` = ?,
              `notes` = ?,
              `issues` = ?,
              `name`  = ?
               WHERE `id` = ?";
            $result = $obj->zQuery($sql, array($values['doc_docdate'], $values['patientname'], $values['notes'], $values['issue'], $values['docname'], $values['doc_id']));
            $this->updateDocumentCategory($values['category'], $values['doc_id']);
        }
    }

    /**
     * getCategory - get document categories
     *
     * @return array
     */
    public function getCategory(): array
    {
        $category = array();
        $obj = new ApplicationTable();
        $sql = "SELECT * FROM `categories`";
        $result = $obj->zQuery($sql);
        foreach ($result as $values) {
            $category[] = $values;
        }

        return $category;
    }

    /**
     * deleteDocument - remove document from list
     *
     * @param Int $docid
     */
    public function deleteDocument($docid): void
    {
        $obj = new ApplicationTable();
        $sql = "UPDATE 
           `documents` 
           SET
          `activity` = ? 
          WHERE `id` = ?";
        $obj->zQuery($sql, array(0, $docid));
    }

    /**
     *Update document category using category name
     *
     * @param $category_name - Name of the category to which the document has to be moved
     * @param $document_id   - Documents whose category has to be updated with $category_name
     */
    public function updateDocumentCategoryUsingCatname($category_name, $document_id): void
    {
        $obj = new ApplicationTable();
        $sql = "UPDATE categories_to_documents 
            JOIN categories ON `name` = ?
            SET category_id=id
            WHERE document_id = ?";
        $result = $obj->zQuery($sql, array($category_name, $document_id));
    }
}
