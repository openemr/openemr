<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Summary','e'); ?></title>
</head>

<?php if ($GLOBALS['athletic_team']) { ?>
<frameset cols="25%,50%,*">
 <frame src="stats.php" name="Stats" scrolling="auto">
 <frame src="pnotes.php" name="Notes" scrolling="auto">
 <frame src="fitness_status.php" name="Fitness" scrolling="auto">
</frameset>
<?php } else { ?>
<frameset cols="25%,*">
 <frame src="stats.php" name="Stats" scrolling="auto">
 <frame src="pnotes.php" name="Notes" scrolling="auto">
</frameset>
<?php } ?>

</html>
