<?php
/**
 * Main info frame. 
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
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE>
Main Screen
</TITLE>
<!-- (CHEMED) -->
<!-- The DOCTYPE is set above to XHTML to put IE into Sttrict Mode so we can get a viewport width -->
<script type='text/javascript' language='JavaScript'>
function GetInnerX () {
	var x;
	if (self.innerHeight) // all except Explorer
	{
		x = self.innerWidth;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
		// Explorer 6 Strict Mode
	{
		x = document.documentElement.clientWidth;
	}
	else if (document.body) // other Explorers
	{
		x = document.body.clientWidth;
	}
	return x;
}

var x = GetInnerX();
var framesrc = '<frame ';

<?php

// this allows us to keep our viewtype between screens -- JRM
$viewtype = 'day';
if (isset($_SESSION['viewtype'])) { $viewtype = $_SESSION['viewtype']; }

// this allows us to keep our selected providers between screens -- JRM
$pcuStr = "pc_username=".$_SESSION['authUser'];
if (isset($_SESSION['pc_username'])) {
    $pcuStr = "";
    if (count($_SESSION['pc_username']) > 1) {
        // loop over the array of values in pc_username to build
        // a list of pc_username HTTP vars
        foreach ($_SESSION['pc_username'] as $pcu) {
            $pcuStr .= "&pc_username[]=".$pcu;
        }
    }
    else {
        // two possibilities here
        // 1) pc_username is an array with a single element
        // 2) pc_username is just a string, not an array
        if (is_string($_SESSION['pc_username'])) {
            $pcuStr .= "&pc_username[]=".$_SESSION['pc_username'];
        }
        else {
            $pcuStr .= "&pc_username[]=".$_SESSION['pc_username'][0];
        }
    }
}

// different frame source page depending on session vars
if ($_SESSION['userauthorized'] && $GLOBALS['docs_see_entire_calendar']) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=".$viewtype."&func=view";
}
else if ($_SESSION['userauthorized']) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=".$viewtype."&func=view&".$pcuStr;
}
else {
    $framesrc = "calendar/index.php?module=PostCalendar&func=view&viewtype=".$viewtype;
}
?>

framesrc += ' src="<?php echo $framesrc; ?>';
framesrc += '&framewidth='+x+'"   name="Calendar" scrolling="auto" frameborder="YES">';

</script>
<!-- END (CHEMED) -->
</HEAD>

<!-- (CHEMED) -->
<script type='text/javascript' language='JavaScript'>
    document.write('<frameset rows="*" cols="*" name="Main" frameborder="NO" border="0" framespacing="0"  <?php if ($_SESSION['cal_ui'] == 2) {echo 'onResize="window.location.href = window.location.href;"'; }?> >');
    document.write(framesrc);
    document.write('</frameset>');
    document.close();

</script>
<!-- END (CHEMED) -->


<noframes><body bgcolor="#FFFFFF">
<?php echo xlt('Frame support required'); ?>
</body></noframes>

</HTML>
