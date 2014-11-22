<?php
/**
 * Generic left frame
 *
 * Copyright (C) 2014 Ensoftek
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
 * @author  Hema Bandaru <hemab@drcloudemr.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");

$feature = $_GET["feature"];
$featureData['amendment']['title'] = xl("Amendments");
$featureData['amendment']['addLink'] = "add_edit_amendments.php";
$featureData['amendment']['listLink'] = "list_amendments.php";

?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<span class="title"><?php echo text($featureData[$feature]['title']); ?></span>
<table>
<tr height="20px">
<td>

<a href="<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/<?php echo attr($featureData[$feature]['listLink']); ?>?id=<?php echo attr($pid); ?>" target='rightFrame' class="css_button" onclick="top.restoreSession()">
<span><?php echo xlt('List');?></span></a>
<?php if ( acl_check('patients', 'trans') ) { ?>
	<a href="<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/<?php echo attr($featureData[$feature]['addLink']); ?>" target='rightFrame' class="css_button" onclick="top.restoreSession()">
	<span><?php echo xlt('Add');?></span></a>
<?php } ?>
</td>
</tr>
</table>
</body>
</html>
