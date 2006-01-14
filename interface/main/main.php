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
<frameset rows="60%,35%" cols="*" name="Main">
<?
if(true /* $_SESSION['userauthorized'] */ ) {
?>
  <frame src="main_info.php" name="Comment" scrolling="auto">
  <frame src="authorizations/authorizations.php" name="Authorization" scrolling="auto">
<?
}
else {
?>
 <frame src="main_info.php" name="Comment" scrolling="auto">
 <frame src="calendar/find_patient.php?no_nav=1&mode=reset" name="fp" scrolling="auto">
<?
}
?>
</frameset>
<noframes><body bgcolor="#FFFFFF">

</body></noframes>
</HTML>
