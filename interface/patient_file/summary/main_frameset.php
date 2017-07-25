<?php
/**
 * Generic main frameset
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




include_once("../../globals.php");

$feature = $_GET["feature"];
$id = $_GET["id"];

$featureData['amendment']['title'] = xl("Amendments");
$featureData['amendment']['addLink'] = "add_edit_amendments.php";
$featureData['amendment']['listLink'] = "list_amendments.php";
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo text($featureData[$feature]['title']); ?></title>
</head>

<frameset cols="18%,*" id="main_frame">
 <frame src="left_frame.php?feature=<?php echo attr($feature); ?>" name="leftFrame" scrolling="auto"/>
    <?php if ($id) { ?>
    <frame src="<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/summary/<?php echo attr($featureData[$feature]['addLink']); ?>?id=<?php echo attr($id) ?>"
        name="rightFrame" scrolling="auto"/>
    <?php } else { ?>
    <frame src="<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/summary/<?php echo attr($featureData[$feature]['listLink']); ?>?id=<?php echo attr($pid) ?>"
        name="rightFrame" scrolling="auto"/>
    <?php } ?>
</frameset>

</html>
