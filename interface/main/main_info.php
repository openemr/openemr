<?
include_once("../globals.php");
include_once("$srcdir/auth.inc");
?>




<HTML>
<HEAD>
<TITLE>
Main Screen
</TITLE>
</HEAD>

<frameset rows="*" cols="*" name="Main" frameborder="NO" border="0" framespacing="0">
<!--<frame src="onotes/office_comments.php" name="Comment" scrolling="auto" noresize frameborder="NO">-->
<?

if ($_SESSION['userauthorized']) {

?>

  <frame src="calendar/index.php?module=PostCalendar&viewtype=day&func=view&pc_username=<?=$_SESSION['authUser']?>" name="Calendar" scrolling="auto" frameborder="NO">

<?
}
else {

?>

  <frame src="calendar/index.php?module=PostCalendar&func=view" name="Calendar" scrolling="auto" frameborder="NO">

<?

}

?>


</frameset>


<noframes><body bgcolor="#FFFFFF">

</body></noframes>


</HTML>


