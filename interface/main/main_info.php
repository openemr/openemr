<?php
/**
 * Main info frame.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE><?php echo xlt('Calendar'); ?></TITLE>
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

// this allows us to keep our viewtype between screens -- JRM calendar_view_type
$viewtype = $GLOBALS['calendar_view_type'];
if (isset($_SESSION['viewtype'])) {
    $viewtype = $_SESSION['viewtype'];
}

// this allows us to keep our selected providers between screens -- JRM
$pcuStr = "pc_username=".$_SESSION['authUser'];
if (isset($_SESSION['pc_username'])) {
    $pcuStr = "";
    if (!empty($_SESSION['pc_username']) && is_array($_SESSION['pc_username']) && count($_SESSION['pc_username']) > 1) {
        // loop over the array of values in pc_username to build
        // a list of pc_username HTTP vars
        foreach ($_SESSION['pc_username'] as $pcu) {
            $pcuStr .= "&pc_username[]=".$pcu;
        }
    } else {
        // two possibilities here
        // 1) pc_username is an array with a single element
        // 2) pc_username is just a string, not an array
        if (is_string($_SESSION['pc_username'])) {
            $pcuStr .= "&pc_username[]=".$_SESSION['pc_username'];
        } else {
            $pcuStr .= "&pc_username[]=".$_SESSION['pc_username'][0];
        }
    }
}

// different frame source page depending on session vars
if ($_SESSION['userauthorized'] && $GLOBALS['docs_see_entire_calendar']) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=".$viewtype."&func=view";
} else if ($_SESSION['userauthorized']) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=".$viewtype."&func=view&".$pcuStr;
} else {
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
    document.write('<frameset rows="*" cols="*" name="Main" frameborder="NO" border="0" framespacing="0" >');
    document.write(framesrc);
    document.write('</frameset>');
    document.close();

</script>
<!-- END (CHEMED) -->


<noframes><body bgcolor="#FFFFFF">
<?php echo xlt('Frame support required'); ?>
</body></noframes>

</HTML>
