<?php
/**
 * Instructions for loading ICPC-2 Database
 *
 * Copyright (C) 2014 Medical Service Center, Pimmiq.
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
 * ICPC-2: Norwegian Centre for Informatics in Health and Social Care. 
 * File in ZIP: http://www.kith.no/upload/1785/ICPC_2e_v430.zip (Date 2014, ICPC-2e-v.4.3 15. September 2013)
 *
 * @package OpenEMR
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @author Pimm Blankevoort <pimmblankevoort@hotmail.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../interface/globals.php");

?>
<div class="dialog"><p>
<?php echo xlt("Steps to install the ICPC-2 database"); ?>:
<ol>
<li><?php echo xlt("The raw data feed release can be obtained from"); ?> 


<b><a href="http://www.kith.no/templates/kith_WebPage____1111.aspx"><?php echo xlt("this location"); ?></a></b>

 
 




<li><?php echo xlt("Place the downloaded ICPC-2 database zip file into the following directory"); ?>:  contrib/icpc2</li>
<li><?php echo xlt("Return to this page and you will be able to complete the ICPC-2 installation process by clicking on the ICPC-2 section header"); ?></li>
</ol>
</p>
</div>