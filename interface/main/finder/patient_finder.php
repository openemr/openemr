<?
include_once("../../globals.php");
?>

<HTML>
<HEAD>
<TITLE>
Patient Finder
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="0" border="0" framespacing="0">
  <frame src="finder_navigation.php" name="Navigation" scrolling="no" noresize frameborder="0">
  <frame src="../main_title.php" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="patient_select.php?patient=<?echo $patient;?>&findBy=<?echo $findBy;?>" name="Comment" scrolling="auto" noresize frameborder="0">
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
