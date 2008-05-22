<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<title><? xl('Patient Summary','e'); ?></title>
</head>

<frameset rows="50%,50%">

<?php if($GLOBALS['dutchpc']) { ?>
 <frame src="demographics_dutch.php" name="Demographics" scrolling="auto">
<?php } else { ?>
 <frame src="demographics.php" name="Demographics" scrolling="auto">
<?php } ?>

<?php if ($GLOBALS['athletic_team']) { ?>
 <frameset cols="25%,50%,*">
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
  <frame src="fitness_status.php" name="Fitness" scrolling="auto">
 </frameset>
<?php } else { ?>
 <frameset cols="20%,80%">
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
 </frameset>
<?php } ?>

</frameset>

</html>
