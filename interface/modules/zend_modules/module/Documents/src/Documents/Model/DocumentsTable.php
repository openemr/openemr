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

use OpenEMR\Common\Database\QueryUtils;

class DocumentsTable
{
    /*
    * Save the category - document mapping
    * @param    $category_id    integer   Category ID
    * @param    $document_id    integer   Document ID
    */
    public function insertDocumentCategory($category_id, $document_id): void
    {
        $sql = "INSERT INTO categories_to_documents (category_id, document_id) VALUES (?, ?)";
        QueryUtils::sqlStatementThrowException($sql, [$category_id, $document_id]);
    }

    /*
    * Move the document to a different category
    * @param    $category_id    integer   Category ID
    * @param    $document_id    integer   Document ID
    */
    public function updateDocumentCategory($category_id, $document_id): void
    {
        $sql = "UPDATE categories_to_documents SET category_id = ? WHERE document_id = ?";
        QueryUtils::sqlStatementThrowException($sql, [$category_id, $document_id]);
    }

    /**
     * getCategories - Get Document Categories
     *
     * @param Integer $categoryParentId
     * @return array<int, array{category_id: mixed, category_name: mixed}>
     */
    public function getCategories($categoryParentId)
    {
        $sql = "SELECT * FROM `categories` WHERE `parent` = ? ORDER BY `order`";
        $result = QueryUtils::fetchRecords($sql, [$categoryParentId]);
        $category = [];
        foreach ($result as $row) {
            $category[$row['cat_id']] = [
                'category_id' => $row['id'],
                'category_name' => $row['name'],
            ];
        }

        return $category;
    }

    /**
     * getDocument - get Document Data by Id
     *
     * @param Integer $documentId Document Id
     * @return array<string, mixed>|false
     */
    public function getDocument($documentId)
    {
        $sql = "SELECT * FROM documents AS doc
              JOIN categories_to_documents AS cat_doc ON cat_doc.document_id = doc.id
              WHERE doc.id = ?";
        return QueryUtils::querySingleRow($sql, [$documentId]);
    }

    /**
     * getCategoryIDs - get Category Ids By Name
     *
     * @param array $categories - Category Lists
     * @return array<string, mixed>
     */
    public function getCategoryIDs($categories = []): array
    {
        $categories_count = count($categories);
        $cat_name = [];
        for ($i = 0; $i < $categories_count; $i++) {
            $cat_name[$i] = "?";
        }

        $sql = "SELECT `id`,`name` FROM `categories` " .
            "WHERE `name` IN (" . implode(",", $cat_name) . ")";
        $result = QueryUtils::fetchRecords($sql, $categories);
        $category = [];
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
            QueryUtils::sqlStatementThrowException($sql, [$values['doc_docdate'], $values['patientname'], $values['notes'], $values['issue'], $values['docname'], $values['doc_id']]);
            $this->updateDocumentCategory($values['category'], $values['doc_id']);
        }
    }

    /**
     * getCategory - get document categories
     *
     * @return list<array<string, mixed>>
     */
    public function getCategory(): array
    {
        $sql = "SELECT * FROM `categories`";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * deleteDocument - remove document from list
     *
     * @param Int $docid
     */
    public function deleteDocument($docid): void
    {
        $sql = "UPDATE
           `documents`
           SET
          `activity` = ?
          WHERE `id` = ?";
        QueryUtils::sqlStatementThrowException($sql, [0, $docid]);
    }

    /**
     *Update document category using category name
     *
     * @param $category_name - Name of the category to which the document has to be moved
     * @param $document_id   - Documents whose category has to be updated with $category_name
     */
    public function updateDocumentCategoryUsingCatname($category_name, $document_id): void
    {
        $sql = "UPDATE categories_to_documents
            JOIN categories ON `name` = ?
            SET category_id=id
            WHERE document_id = ?";
        QueryUtils::sqlStatementThrowException($sql, [$category_name, $document_id]);
    }
}
