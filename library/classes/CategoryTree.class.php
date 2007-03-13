<?php

require_once("Tree.class.php");

/**
 * class CategoryTree
 * This is a class for storing document categories using the MPTT implementation
 */

class CategoryTree extends Tree {

	
	/*
	*	This just sits on top of the parent constructor, only a shell so that the _table var gets set
	*/
	function CategoryTree($root,$root_type = ROOT_TYPE_ID) {
		$this->_table = "categories";
		parent::Tree($root,$root_type);
	}
	
	function _get_categories_array($patient_id) {
		$categories = array();
		$sql = "SELECT c.id, c.name, d.id AS document_id, d.type, d.url, d.docdate"
			. " FROM categories AS c, documents AS d, categories_to_documents AS c2d"
			. " WHERE c.id = c2d.category_id"
			. " AND c2d.document_id = d.id";

		if (is_numeric($patient_id)) {
			$sql .= " AND d.foreign_id = '" . $patient_id . "'";
		}
		$sql .= " ORDER BY c.id ASC, d.docdate DESC, d.url ASC";

		//echo $sql;
		$result = $this->_db->Execute($sql);

	  while ($result && !$result->EOF) {
	  	$categories[$result->fields['id']][$result->fields['document_id']] = $result->fields;
	  	$result->MoveNext();
	  }
	  
	  return $categories;
		
	}
}
?>
