<?
include_once("../../globals.php");

?>

<HTML>
<HEAD>
<TITLE>
Patient Finder
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[logoBarHeight],$GLOBALS[titleBarHeight]" ?>,50%,50%" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="../main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="../main_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="patient_select.php?patient=<?echo $patient;?>" name="Comment" scrolling="auto" noresize frameborder="NO">
  <frame src="keyboard.php?patient=<?echo $patient;?>" name="Authorization" scrolling="auto" frameborder="NO">	
  
</frameset>


<noframes><body bgcolor="#FFFFFF">

</body></noframes>



</HTML>
