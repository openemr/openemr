<?php
/**
 * Function to check and/or sanitize things for security such as
 * directories names, file names, etc.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2012-2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


// If the label contains any illegal characters, then the script will die.
function check_file_dir_name($label) {
  if (empty($label) || preg_match('/[^A-Za-z0-9_.-]/', $label)) {
    error_log("ERROR: The following variable contains invalid characters:" . $label);
    die(xlt("ERROR: The following variable contains invalid characters").": ". attr($label));
  }
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

?>
