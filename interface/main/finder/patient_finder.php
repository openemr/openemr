<?php
include_once("../../globals.php");
?>

<HTML>
<head>
<?php html_header_show();?>
<TITLE>
<?php xl('Patient Finder', 'e'); ?>
</TITLE>

<script language='JavaScript'>
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

</HEAD>
<frameset rows="<?php echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="0" border="0" framespacing="0">
  <frame src="finder_navigation.php?patient=<?php echo $patient;?>&findBy=<?php echo $findBy;?>" name="Navigation" scrolling="no" noresize frameborder="0">
  <frame src="../main_title.php" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="patient_select.php?patient=<?php echo $patient;?>&findBy=<?php echo $findBy;?>" name="Comment" scrolling="auto" noresize frameborder="0">
</frameset>

<noframes><body bgcolor="#FFFFFF">
Frame support required
</body></noframes>

</HTML>
