<?php
/**
 * Functions for documents.
 *
 * Copyright (C) 2013 Brady Miller <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

require_once($GLOBALS['fileroot']."/controllers/C_Document.class.php");

/**
 * Function to add a document via the C_Document class.
 *
 * @param  string         $name                            Name of the document
 * @param  string         $type                            Mime type of file
 * @param  string         $tmp_name                        Temporary file name
 * @param  string         $error                           Errors in file upload
 * @param  string         $size                            Size of file
 * @param  int            $owner                           Owner/user/service that imported the file
 * @param  string         $patient_id_or_simple_directory  Patient id or simple directory for storage when patient id not known (such as '00' or 'direct')
 * @param  int            $category_id                     Document category id
 * @param  string         $higher_level_path               Can set a higher level path here (and then place the path depth in $path_depth)
 * @param  int            $path_depth                      Path depth when using the $higher_level_path feature
 * @return array/boolean                                   Array(doc_id,url) of the file as stored in documents table, false = failure
 */
function addNewDocument($name,$type,$tmp_name,$error,$size,$owner='',$patient_id_or_simple_directory="00",$category_id='1',$higher_level_path='',$path_depth='1') {

    if (empty($owner)) {
      $owner = $_SESSION['authUserID'];
    }

    // Build the $_FILES array
    $TEMP_FILES = array();
    $TEMP_FILES['file']['name'][0]=$name;
    $TEMP_FILES['file']['type'][0]=$type;
    $TEMP_FILES['file']['tmp_name'][0]=$tmp_name;
    $TEMP_FILES['file']['error'][0]=$error;
    $TEMP_FILES['file']['size'][0]=$size;
    $_FILES = $TEMP_FILES;

    // Build the parameters
    $_GET['higher_level_path']=$higher_level_path;
    $_GET['patient_id']=$patient_id_or_simple_directory;
    $_POST['destination']='';
    $_POST['submit']='Upload';
    $_POST['path_depth']=$path_depth;
    $_POST['patient_id']=(is_numeric($patient_id_or_simple_directory) && $patient_id_or_simple_directory>0) ? $patient_id_or_simple_directory : "00";
    $_POST['category_id']=$category_id;
    $_POST['process']='true';

    // Add the Document and return the newly added document id
    $cd = new C_Document();
    $cd->upload_action_process($owner);
    $v = $cd->get_template_vars("file");
    if (!isset($v) || !$v) return false;
    return array ("doc_id" => $v[0]->id, "url" => $v[0]->url); 
}

/**
 * Function to return the category id of a category title.
 *
 * @param  string  $category_title  category title
 * @return int/boolean              category id (returns false if the category title does not exist)
 */
function document_category_to_id($category_title) {
  $ret = sqlQuery("SELECT `id` FROM `categories` WHERE `name`=?", array($category_title) );
  if ($ret['id']) {
    return $ret['id'];
  }
  else {
    return false;
  }
}
?>
