<?
include_once("../globals.php");

$_SESSION["encounter"] = "";

if ($userauthorized) {
?>

<HTML>
<HEAD>
<TITLE>
OpenEMR
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="usergroup_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="usergroup_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="usergroup_admin.php" name="Main" scrolling="auto" noresize frameborder="NO">
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>

<?
} else {
?>

<html>
<body>
<script language="Javascript">

window.location="<?echo "$rootdir/main/main_screen.php";?>";

</script>

</body>
</html>

<?
}
?>
