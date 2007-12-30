<?php
require_once("../globals.php");
?>
<HTML>
<HEAD>
<TITLE>
Main Screen
</TITLE>
</HEAD>

<frameset rows="*" cols="*" name="Main" frameborder="NO" border="0" framespacing="0">
<!--<frame src="onotes/office_comments.php" name="Comment" scrolling="auto" noresize frameborder="NO">-->

<?php if ($_SESSION['userauthorized'] && $GLOBALS['docs_see_entire_calendar']) { ?>
  <frame src="calendar/index.php?module=PostCalendar&viewtype=day&func=view"
<?php } else if ($_SESSION['userauthorized']) { ?>
  <frame src="calendar/index.php?module=PostCalendar&viewtype=day&func=view&pc_username=<?=$_SESSION['authUser']?>"
<?php } else { ?>
  <frame src="calendar/index.php?module=PostCalendar&func=view"
<?php } ?>
   name="Calendar" scrolling="auto" frameborder="NO">

</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
