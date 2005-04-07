<!-- program was updated by Nikolai Vitsyn: 2004/02/13 -->

<?
include_once("../../globals.php");

?>

<HTML>
<HEAD>
<TITLE>
OpenEMR
</TITLE>
</HEAD>


<frameset rows="80%,20%" cols="*" frameborder="NO" border="0" framespacing="0">

 <frameset rows="*" cols="20%,20%,20%,20%" frameborder="NO" border="0" framespacing="0">

  <frame src="medical_problems.php?active=<?echo $active;?>" name="Medical Problems" scrolling="auto" noresize rameborder="NO">
  <frame src="medications.php?active=<?echo $active;?>" name="Medications" scrolling="auto" noresize frameborder="NO">
  <frame src="allergies.php?active=<?echo $active;?>" name="Allergies" scrolling="auto" noresize frameborder="NO">
  <frame src="surgeries.php?active=<?echo $active;?>" name="Surgery" scrolling="auto" noresize frameborder="NO">
  </frameset>
  
  <frameset rows="*" cols="*" frameborder="NO" border="0" framespacing="0">
 
  <frame src="immunizations.php?active=<?echo $active;?>" name="Immunizations" scrolling="auto" noresize frameborder="NO">
  
  </frameset>

  </frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML
