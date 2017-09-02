<?php

/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
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
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */



require_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<span class="title"><?php echo xlt('Prescriptions'); ?></span>
<table>
<tr height="20px">
<td>
    <a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo attr($pid); ?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('List');?></span></a>
    <a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo attr($pid); ?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('Add');?></span></a>
</td>
</tr>
<tr>
<td>
<?php if ($GLOBALS['rx_show_drug-drug']) { ?>
    <a href="<?php echo $GLOBALS['webroot']?>/interface/weno/drug-drug.php"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('Drug-Drug');?></span></a>
<?php } ?>
</td>
</tr>
</table>

</body>
</html>
