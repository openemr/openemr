<?php
/**
* Function to check and/or sanitize things for security such as
* directories names, file names, etc.
*
* Copyright (C) 2012 by following Brady Miller <brady.g.miller@gmail.com>
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
* @author Brady Miller <brady.g.miller@gmail.com>
* @author Roberto Vasquez <robertogagliotta@gmail.com>
* @author Shachar Zilbershlag <shaharzi@matrix.co.il>
* @link http://www.open-emr.org
*/
// If the label contains any illegal characters, then the script will die.
function check_file_dir_name($label) {
  if (empty($label) || preg_match('/[^A-Za-z0-9_.-]/', $label))
    die(xlt("ERROR: The following variable contains invalid characters").": ". attr($label));
}

// Convert all illegal characters to _
function convert_safe_file_dir_name($label) {
  return preg_replace('/[^A-Za-z0-9_.-]/','_',$label);
}

//Basename functionality for nonenglish languages (without this, basename function ommits nonenglish characters).
function basename_international($path){
  $parts = preg_split('~[\\\\/]~', $path);
  foreach ($parts as $key => $value){
    $encoded = urlencode($value);
    $parts[$key] = $encoded;
  }
  $encoded_path = implode("/", $parts);
  $encoded_file_name = basename($encoded_path);
  $decoded_file_name = urldecode($encoded_file_name);

  return $decoded_file_name;
}


/**
 * This function detects a MIME type for a file and check if it in the white list of the allowed mime types.
 * @param string $file - file location.
 * @param array|null $whiteList - array of mime types that allowed to upload.
 */
// Regarding the variable below. In the case of multiple file upload the isWhiteList function will run multiple
// times, therefore, storing the white list in the variable below to prevent multiple requests from database.
$white_list = null;
function isWhiteFile($file){
    global $white_list;
    if(is_null($white_list)){
        $white_list = array();
        $lres = sqlStatement("SELECT option_id FROM list_options WHERE list_id = 'files_white_list' AND activity = 1");
        while ($lrow = sqlFetchArray($lres)) {
            $white_list[] = $lrow['option_id'];
        }
    }

    $mimetype  = mime_content_type($file);
    if(in_array($mimetype, $white_list)){
        return true;
    } else {
        $splitMimeType = explode('/', $mimetype);
        $categoryType = $splitMimeType[0];
        if(in_array($categoryType. '/*',  $white_list))return true;
    }
    return false;
}

?>
