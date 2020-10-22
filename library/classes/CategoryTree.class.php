<?php

/**
 * class CategoryTree
 * This is a class for storing document categories using the MPTT implementation
 */

class CategoryTree extends Tree
{

    /*
    *   This just sits on top of the parent constructor, only a shell so that the _table var gets set
    */
    function __construct($root, $root_type = ROOT_TYPE_ID)
    {
        $this->_table = "categories";
        parent::__construct($root, $root_type);
    }

    function _get_categories_array($patient_id, $user = '')
    {
        $categories = array();
        $sqlArray = array();
        $sql = "SELECT c.id, c.name, c.aco_spec, d.id AS document_id, d.name AS document_name, d.type, d.url, d.docdate"
            . " FROM categories AS c, documents AS d, categories_to_documents AS c2d"
            . " WHERE c.id = c2d.category_id"
            . " AND c2d.document_id = d.id AND d.deleted = 0";

        if (is_numeric($patient_id)) {
            if ($patient_id == "00") {
      // Collect documents that are not assigned to a patient
                $sql .= " AND (d.foreign_id = 0 OR d.foreign_id IS NULL) ";
            } else {
      // Collect documents for a specific patient
                $sql .= " AND d.foreign_id = ? ";
                array_push($sqlArray, $patient_id);
            }
        }

        $sql .= " ORDER BY c.id ASC, d.docdate DESC, d.url ASC";

        //echo $sql;
        $result = $this->_db->Execute($sql, $sqlArray);

        while ($result && !$result->EOF) {
            $categories[$result->fields['id']][$result->fields['document_id']] = $result->fields;
            $result->MoveNext();
        }

        return $categories;
    }
}
