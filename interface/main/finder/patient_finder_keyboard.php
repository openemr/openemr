<?php 
include_once("../../globals.php");

?>

<HTML>
<HEAD>
<TITLE>
<?php xl('Patient Finder','e'); ?>
</TITLE>
</HEAD>
<frameset rows="<?php echo "$GLOBALS[logoBarHeight],$GLOBALS[titleBarHeight]" ?>,50%,50%" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="../main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="../main_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="patient_select.php?patient=<?php echo $patient;?>" name="Comment" scrolling="auto" noresize frameborder="NO">
  <frame src="keyboard.php?patient=<?php echo $patient;?>" name="Authorization" scrolling="auto" frameborder="NO">	
  
</frameset>


<noframes><body bgcolor="#FFFFFF">

</body></noframes>



</HTML>
