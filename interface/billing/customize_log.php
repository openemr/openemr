<?php
/** 
* interface/billing/customize_log.php - starting point for customization of billing log
*
* Copyright (C) 2014 Stephen Waite <stephen.waite@cmsvt.com>
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
* @author Stephen Waite <stephen.waite@cmsvt.com> 
* @link http://www.open-emr.org 
*/     

$fake_register_globals=false;
$sanitize_all_escapes=true;
     
require_once("../globals.php");

$filename = $GLOBALS['OE_SITE_DIR'] . '/edi/process_bills.log';


$fh = fopen($filename,'r');

while ($line = fgets($fh)) {
    echo(text($line));
    echo("<br />");
    }
    fclose($fh);
    
?>
