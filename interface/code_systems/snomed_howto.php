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
<li><?php echo xlt("The first step is to download the SNOMED CT release. Access to SNOMED CT is provided by IHTSDO and their member countries. For more information see") . 
" <a href='http://www.ihtsdo.org/snomed-ct/get-snomed-ct'>http://www.ihtsdo.org/snomed-ct/get-snomed-ct</a>."; ?> 
</li>
<li><?php echo xlt("Place the downloaded Snomed database zip file into the following directory"); ?>: contrib/snomed 
</li>
<li><?php echo xlt("Return to this page and you will be able to complete the Snomed installation process by clicking on the SNOMED section header"); ?>
</li>
</ol>
<h5 class="error_msg"><?php echo xlt("NOTE: Only the Biannual International Snomed Release is currently supported"); ?></h5>
<h5 class="error_msg"><?php echo xlt("The following International Snomed Release languages are supported"); ?>: <?php echo xlt("English"); ?>, <?php echo xlt("Spanish"); ?></h5>
<h5 class="error_msg"><?php echo "(" . xlt("Additionally, the corresponding US Snomed Extension Package can be imported separately after installing the International (English version) Snomed Release") . ")"; ?></h5>
<h5 class="error_msg"><?php echo xlt("WARNING: If you are using the 2012-10-31 Spanish version, then note you need to unzip it once before placing it in the contrib/snomed (this is because the released file was \"double-zipped\")."); ?></h5>
</p>
</div>
