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

namespace Documents\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use \Application\Model\ApplicationTable;


class DocumentsTable extends AbstractTableGateway
{ 
  /*
  * Save the category - document mapping
  * @param    $category_id    integer   Category ID
  * @param    $document_id    integer   Document ID
  */
  public function insertDocumentCategory($category_id, $document_id)
  {
    $obj  = new ApplicationTable();
    $sql = "INSERT INTO categories_to_documents (category_id, document_id) VALUES (?, ?)";
    $result = $obj->zQuery($sql, array($category_id, $document_id));
  }
  
  /*
  * Move the document to a different category
  * @param    $category_id    integer   Category ID
  * @param    $document_id    integer   Document ID
  */
  public function updateDocumentCategory($category_id, $document_id)
  {
    $obj  = new ApplicationTable();
    $sql = "UPDATE categories_to_documents SET category_id = ? WHERE document_id = ?";
    $result = $obj->zQuery($sql, array($category_id, $document_id));
  }
  
  /**
   * getCategories - Get Document Categories
   * @param Integer $categoryParentId
   * @return Array
   */
  public function getCategories($categoryParentId)
  {
    $obj      = new ApplicationTable();
    $sql      = "SELECT * FROM `categories` WHERE `parent` = ? ORDER BY `order`";
    $result   = $obj->zQuery($sql,array($categoryParentId));
    $category = array();
    foreach($result as $row) {
      $category[$row['cat_id']]= array(
        'category_id'   => $row['id'],
        'category_name' => $row['name'],
      );
    }
    return $category;
  }
  
  /**
   * getDocument - get Document Data by Id
   * @param Integer $documentId Document Id
   * @return Array
   */
  public function getDocument($documentId)
  {
    $obj    = new ApplicationTable();
    $sql    = "SELECT * FROM documents AS doc 
              JOIN categories_to_documents AS cat_doc ON cat_doc.document_id = doc.id
              WHERE doc.id = ?";
    $result = $obj->zQuery($sql,array($documentId));
    return $result->current();
  }
  
  /**
   * getCategoryIDs - get Category Ids By Name
   * @param Array $categories - Category Lists
   * @return Array
   */
  public function getCategoryIDs($categories = array())
  {
    $obj              = new ApplicationTable();
    $categories_count = count($categories);
    $cat_name         = array();
    for($i=0;$i<$categories_count;$i++){
      $cat_name[$i]   = "?";
    }
    $sql              = "SELECT `id`,`name` FROM `categories` ".
                        "WHERE `name` IN (". implode(",",$cat_name) .")";
    $result           = $obj->zQuery($sql,$categories);
    $category         = array();
    foreach($result as $row) {
      $category[$row['name']] = $row['id'];
    }
    return $category;
  }
  
  /**
   * saveDocumentdetails - save document details
   * @param Array $current_document - document details
   */
  public function saveDocumentdetails($current_document) {
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
      $result = $obj->zQuery($sql, array($values['doc_docdate'], $values['patientname'], $values['notes'], $values['issue'],$values['docname'],$values['doc_id']));
      $this->updateDocumentCategory($values['category'], $values['doc_id']);
    }
  }
  
  /**
   * getCategory - get document categories
   * @return Array
   */
  public function getCategory() {
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
   * @param Int $docid
   */
  public function deleteDocument($docid) {
    $obj = new ApplicationTable();
    $sql = "UPDATE 
           `documents` 
           SET
          `activity` = ? 
          WHERE `id` = ?";
    $obj->zQuery($sql,array(0,$docid));
  }
  
  /**
   *Update document category using category name
   *@param $category_name - Name of the category to which the document has to be moved
   *@param $document_id - Documents whose category has to be updated with $category_name
   */
  public function updateDocumentCategoryUsingCatname($category_name, $document_id)
  {
    $obj  = new ApplicationTable();
    $sql = "UPDATE categories_to_documents 
            JOIN categories ON `name` = ?
            SET category_id=id
            WHERE document_id = ?";
    $result = $obj->zQuery($sql, array($category_name, $document_id));
  }
}