<?php
/**
* Drag and Drop file uploader.
*
* Copyright (C) 2017 Sherwin Gaddis sherwingaddis@gmail.com
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Sherwin Gaddis sherwingaddis@gmail.com
*/

$patient_id = filter_input(INPUT_GET, 'patient_id');
$category_id = filter_input(INPUT_GET, 'parent_id');


$sanitize_all_escapes=true;
$fake_register_globals=false;

require_once("../../../interface/globals.php");
require_once(dirname(__FILE__) . "/../../../library/documents.php");
 
if (!empty($_FILES)) {

    $name     = $_FILES['file']['name'];
    $type     = $_FILES['file']['type'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $size     = $_FILES['file']['size'];
    $owner    = $GLOBALS['userauthorized'];


addNewDocument($name,$type,$tmp_name,$error,$size,$owner,$patient_id_or_simple_directory=$patient_id,$category_id,
	                      $higher_level_path='',$path_depth='1');
	
}