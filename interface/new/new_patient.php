<?php 
include_once("../globals.php");
$_SESSION["encounter"] = "";
?>
<HTML>
<HEAD>
<TITLE>
<?php echo $openemr_name; ?>
</TITLE>
</HEAD>
<frameset rows="<?php echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]"; ?>,*" cols="*" frameborder="0" border="0" framespacing="0">
  <frame src="new_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="new_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="new.php" name="Main" scrolling="auto" noresize frameborder="NO">

</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
