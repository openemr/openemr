<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2012 NP Clinics <info@npclinics.com.au>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Scott Wakefield <scott@npclinics.com.au>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;


//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;


require_once("../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");

// Ensure authorized
if (!acl_check('admin', 'users')) {
  die(xlt("Unauthorized"));
}

$alertmsg = '';

if ( isset($_POST["mode"]) && $_POST["mode"] == "facility_user_id" && isset($_POST["user_id"]) && isset($_POST["fac_id"]) ) {
  // Inserting/Updating new facility specific user information
  $fres = sqlStatement("SELECT * FROM `layout_options` " .
                       "WHERE `form_id` = 'FACUSR' AND `uor` > 0 AND `field_id` != '' " .
                       "ORDER BY `group_name`, `seq`");
  while ($frow = sqlFetchArray($fres)) {
    $value = get_layout_form_value($frow);
    $entry_id = sqlQuery("SELECT `id` FROM `facility_user_ids` WHERE `uid` = ? AND `facility_id` = ? AND `field_id` =?", array($_POST["user_id"],$_POST["fac_id"],$frow['field_id']) );
    if (empty($entry_id)) {
      // Insert new entry
      sqlInsert("INSERT INTO `facility_user_ids` (`uid`, `facility_id`, `field_id`, `field_value`) VALUES (?,?,?,?)", array($_POST["user_id"],$_POST["fac_id"],$frow['field_id'], $value) );
    }
    else {
      // Update existing entry
      sqlStatement("UPDATE `facility_user_ids` SET `field_value` = ? WHERE `id` = ?", array($value,$entry_id['id']) );
    }
  }
}

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.easydrag.handler.beta2.js"></script>

<script type="text/javascript">

$(document).ready(function(){

    // fancy box
    enable_modals();
    
    // special size for
	$(".iframe_small").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 300,
		'frameWidth' : 500
	});
	
	$(function(){
		// add drag and drop functionality to fancybox
		$("#fancy_outer").easydrag();
	});
});

</script>

</head>
<body class="body_top">

<?php
// Collect all users
$u_res = sqlStatement("select * from `users` WHERE `username` != '' AND `active` = 1 order by `username`");

// Collect all facilities and store them in an array
$f_res = sqlStatement("select * from `facility` order by `name`");
$f_arr = array();
for($i=0; $row=sqlFetchArray($f_res); $i++) {
  $f_arr[$i]=$row;
}

// Collect layout information and store them in an array
$l_res = sqlStatement("SELECT * FROM layout_options " .
                      "WHERE form_id = 'FACUSR' AND uor > 0 AND field_id != '' " .
                      "ORDER BY group_name, seq");
$l_arr = array();
for($i=0; $row=sqlFetchArray($l_res); $i++) {
  $l_arr[$i]=$row;
}

?>

<div>
    <div>
       <table>
	  <tr >
		<td><b><?php echo xlt('Facility Specific User Information'); ?></b></td>
		<td><a href="usergroup_admin.php" class="css_button" onclick="top.restoreSession()"><span><?php echo xlt('Back to Users'); ?></span></a>
		</td>
	 </tr>
	</table>
    </div>
	
	<div style="width:400px;">
		<div>

			<table cellpadding="1" cellspacing="0" class="showborder">
				<tbody><tr height="22" class="showborder_head">
					<th width="180px"><b><?php echo xlt('Username'); ?></b></th>
					<th width="270px"><b><?php echo xlt('Full Name'); ?></b></th>
					<th width="190px"><b><span class="bold"><?php echo xlt('Facility'); ?></span></b></th>
                                        <?php
                                        foreach ($l_arr as $layout_entry) {
                                          echo "<th width='100px'><b><span class='bold'>" . text(xl_layout_label($layout_entry['title'])) . "&nbsp;</span></b></th>";
                                        }
                                        ?>
				</tr>
					<?php
					while ($user = sqlFetchArray($u_res)) {
						foreach ($f_arr as $facility) {
					?>
				<tr height="20"  class="text" style="border-bottom: 1px dashed;">
				   <td class="text"><b><a href="facility_user_admin.php?user_id=<?php echo attr($user['id']);?>&fac_id=<?php echo attr($facility['id']);?>" class="iframe_small" onclick="top.restoreSession()"><span><?php echo text($user['username']);?></span></a></b>&nbsp;</td>
				   <td><span class="text"><?php echo text($user['fname'] . " " . $user['lname']);?></span>&nbsp;</td>
				   <td><span class="text"><?php echo text($facility['name']);?>&nbsp;</td>
                                   <?php
                                   foreach ($l_arr as $layout_entry) {
                                     $entry_data = sqlQuery("SELECT `field_value` FROM `facility_user_ids` " .
                                                            "WHERE `uid` = ? AND `facility_id` = ? AND `field_id` = ?", array($user['id'],$facility['id'],$layout_entry['field_id']) );
                                     echo "<td><span class='text'>" . generate_display_field($layout_entry,$entry_data['field_value']) . "&nbsp;</td>";
                                   }
                                   ?>  
				</tr>
				<?php
				}}
				?>
				</tbody>
			</table>
		</div>
    </div>
</div>
</body>
</html>
