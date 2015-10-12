<?php
/**
* Function to check and/or sanitize things for security such as
* directories names, file names, etc.
*
* Copyright (C) 2012 by following Brady Miller <brady@sparmy.com>
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
* @author Brady Miller <brady@sparmy.com>
* @author Roberto Vasquez <robertogagliotta@gmail.com>
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

?>
