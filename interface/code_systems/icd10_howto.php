<?php
/**
 * Instructions for loading ICD10 Database
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
<?php echo xlt("Steps to install the ICD 10 database"); ?>:
<ol>

-<br>

-<h5><?php echo xlt("These are the 2012 links"); ?></h5>

-<ul>

-        <li><a href="https://www.cms.gov/Medicare/Coding/ICD10/Downloads/DiagnosisGEMs_2012.zip">DiagnosisGEMs_2012</a></li>

-        <li><a href="https://www.cms.gov/Medicare/Coding/ICD10/Downloads/ProcedureGEMs_2012.zip">ProcedureGEMs_2012</a></li>

-        <li><a href="https://www.cms.gov/Medicare/Coding/ICD10/Downloads/ReimbursementMapping_2012.zip">ReimbursementMapping_2012</a></li>

-        <li><a href="https://www.cms.gov/Medicare/Coding/ICD10/Downloads/2012_PCS_long_and_abbreviated_titles.zip">2012_PCS_long_and_abbreviated_titles</a></li>

-        <li><a href="https://www.cms.gov/Medicare/Coding/ICD10/Downloads/ICD10OrderFiles_2012.zip">ICD10OrderFiles_2012</a></li>

-</ul>


<li><?php echo xlt("The raw data feed release can be obtained from"); ?> <b><a href="https://www.cms.gov/Medicare/Coding/ICD10"><?php echo xlt("this location"); ?></a></b>
<li><?php echo xlt("Place the downloaded ICD 10 database zip files into the following directory"); ?>: contrib/icd10
</li>
<li><?php echo xlt("Return to this page and you will be able to complete the ICD10 installation process by clicking on the ICD10 section header"); ?>
</li>
</ol>
</p>
</div>