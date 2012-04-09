<?php
/**
 * Instructions for loading SNOMED Database
 *
 * Copyright (C) 2012 Patient Healthcare Analytics, Inc.
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
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../interface/globals.php");

?>
<div class="dialog"><p>
<?php echo xlt("Steps to install the Snomed database"); ?>:
<ol>
<li><?php echo xlt("The first step is to open an account with the Unified Medical Language System web site"); ?> <b><a href="https://utslogin.nlm.nih.gov/cas/login"><?php echo xlt("here"); ?></a></b></li>
<li><?php echo xlt("Then the raw data feed release can be obtained from"); ?> <b><a href="http://www.nlm.nih.gov/research/umls/licensedcontent/snomedctfiles.html"><?php echo xlt("this location"); ?></a></b>
<li><?php echo xlt("Place the downloaded Snomed database zip file into the following directory"); ?>: contrib/snomed 
</li>
<li><?php echo xlt("Return to this page and you will be able to complete the RxNorm installation process by clicking on the SNOMED section header"); ?>
</li>
</ol>
<h5 class="error_msg"><?php echo xlt("NOTE: Only the Biannual International Snomed Release is currently supported"); ?></h5>
</p>
</div>
