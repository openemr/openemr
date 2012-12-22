<?php
/**
 * This file implements the main jquery interface for loading external
 * database files into openEMR
 *
 * Copyright (C) 2012 Patient Healthcare Analytics, Inc.
 * Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
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
 * @author  Rohit Kumar <pandit.rohit@netsity.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
require_once("$srcdir/acl.inc");

// Control access
if (!acl_check('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';

// For now, only order by the revision_date. When have different formats of a code type (such as WHO vs CMS for ICD10 or different languages for SNOMED, then will incorporate this field)
$rez = sqlStatement("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision_date`, `revision_version`, `name` FROM `standardized_tables_track` WHERE upper(`name`) = ? ORDER BY `revision_date` DESC", array($db) );
for($iter=0; $row=sqlFetchArray($rez); $iter++) {
    $sqlReturn[$iter]=$row;
}

if (empty($sqlReturn)) {
?>
    <div class="stg"><?php echo xlt("Not installed"); ?></div>
<?php
} else {
    if ($sqlReturn[0]['name'] == 'SNOMED' && $sqlReturn[0]['revision_version'] == 'US Extension') {
        // If using the SNOMED US Extension package, then show the preceding SNOMED International Package information first
?>
        <div class="atr"><?php echo xlt("Name") . ": " . text($sqlReturn[1]['name']); ?> </div>
        <div class="atr"><?php echo xlt("Revision") . ": " . text($sqlReturn[1]['revision_version']); ?> </div>
        <div class="atr"><?php echo xlt("Release Date") . ": " . text($sqlReturn[1]['revision_date']); ?> </div>
        <br>
<?php
    }
    // Always show the first item of query results
?>
    <div class="atr"><?php echo xlt("Name") . ": " . text($sqlReturn[0]['name']); ?> </div>
    <div class="atr"><?php echo xlt("Revision") . ": " . text($sqlReturn[0]['revision_version']); ?> </div>
    <div class="atr"><?php echo xlt("Release Date") . ": " . text($sqlReturn[0]['revision_date']); ?> </div>
<?php
}
?>
