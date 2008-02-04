<?php
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
var framesrc = '';
<?php if ($_SESSION['userauthorized'] && $GLOBALS['docs_see_entire_calendar']) { ?>
framesrc +='  <frame src="calendar/index.php?module=PostCalendar&viewtype=day&func=view';
<?php } else if ($_SESSION['userauthorized']) { ?>
framesrc +='  <frame src="calendar/index.php?module=PostCalendar&viewtype=day&func=view&pc_username=<?=$_SESSION['authUser']?>';
<?php } else { ?>
framesrc +='  <frame src="calendar/index.php?module=PostCalendar&func=view';
<?php } ?>
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

</body></noframes>

</HTML>
