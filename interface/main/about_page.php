<?php
/** 
 * OpenEMR About Page 
 *
 * This Displays an About page for OpenEMR Displaying Version Number, Support Phone Number
 * If it have been entered in Globals along with the Manual and On Line Support Links
 * 
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com> 
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
 * @author Terry Hill <terry@lilysystems.com> 
 * @link http://www.open-emr.org 
 *  
 * Please help the overall project by sending changes you make to the author and to the OpenEMR community.
 * 
 */ 
 
$fake_register_globals=false;
$sanitize_all_escapes=true;
  
require_once("../globals.php");
?>
 <html>
  <head>
  <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
 </head>
  <body class="body_top">
    <div style="text-align: center;">
    <table>
    <span class=text><?php  echo xlt('About OpenEMR'); ?> </span><br><br>  
	<span class=text><?php  echo xlt('Version Number'); ?>: <?php echo "v".text($openemr_version) ?></span><br><br>
    <?php if (!empty($GLOBALS['support_phone_number'])) { ?>
   	  <span class=text><?php  echo xlt('Support Phone Number'); ?>: <?php echo $GLOBALS['support_phone_number'] ?></span>
    <?php } ?>


   <div style="text-align: center;">
   <br><a href="http://open-emr.org/wiki/index.php/OpenEMR_4.2.1_Users_Guide" target="_blank" id="support_link" class='css_button' onClick="top.restoreSession()"><span><?php echo xlt('User Manual'); ?></span></a>
    <?php if (!empty($GLOBALS['online_support_link'])) { ?>
       <a href='<?php echo $GLOBALS["online_support_link"]; ?>' target="_blank" id="support_link" class='css_button' onClick="top.restoreSession()"><span><?php echo xlt('Online Support'); ?></span></a>
    <?php } ?>
   <br><br><a href="../../acknowledge_license_cert.html" target="_blank" id="support_link" class='css_button' onClick="top.restoreSession()"><span><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></span></a>
   </div>
    </table>
    </div>
  </body>
</html>
