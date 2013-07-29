<?php
/**
 * interface/patient_file/ccr_import.php Upload screen and parser for the CCR XML.
 *
 * Functions to upload the CCR XML and to parse and insert it into audit tables.
 *
 * Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
 * @author  Eldho Chacko <eldho@zhservices.com>
 * @author  Ajil P M <ajilpm@zhservices.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../globals.php");

?>
<html>
<head>
<title><?php echo xlt('Import');?></title>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
</head>
<body class="body_top" >
<center>
<p><b><?php echo xlt("Steps for uploading CCR XML");?></b></p>
<table style="width:85%;font-size:14px;" >
  <tr>
    <td>1.</td>
    <td><?php echo xlt('To upload CCR document of already existing patient use Patient Summary Screen->Documents. For CCR document of a new patient use Miscellanous->New Documents screen').'.'; ?></td>
  </tr>
  <tr>
    <td>2.</td>
    <td><?php echo xlt('Upload the xml file under the category CCR').'.'; ?></td>
  </tr>
  <tr>
    <td>3.</td>
    <td><?php echo xlt('After Uploading click the button "Import"').'.'; ?></td>
  </tr>
  <tr>
    <td>4.</td>
    <td><?php echo xlt('Approve the patient from Patient/Client->Import->Pending Approval').'.'; ?></td>
  </tr>
</table>
</center>
</form>
</body>
</html>
