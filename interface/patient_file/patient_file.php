<?php
include_once("../globals.php");
include_once("$srcdir/pid.inc");
setpid($_GET["set_pid"]);
?>
<HTML>
<head>
<?php html_header_show();?>
<TITLE>
<?php echo $openemr_name ?>
</TITLE>
<script type="text/javascript" src="../../library/topdialog.js"></script>

<script language="JavaScript">
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

</HEAD>
<frameset rows="<?php echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="0" border="0" framespacing="0" onunload="imclosing()">

<?php if (isset($_GET["calenc"])) { ?>
  <frame src="navigation.php" name="Navigation" scrolling="NO" noresize frameborder="0">
  <frame src="encounter/encounter_title.php" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="encounter/patient_encounter.php?mode=new&calenc=<?php echo $_GET["calenc"];?>" name="Main" scrolling="AUTO" noresize frameborder="0">

<?php } elseif ($_GET['go'] == "encounter"){ ?>
  <frame src="navigation.php?pid=<?php echo $_GET['pid']?>&set_pid=<?php echo $_GET['pid']?>" name="Navigation" scrolling="NO" noresize frameborder="0">
  <frame src="encounter/encounter_title.php?pid=<?php echo $_GET['pid']?>&set_pid=<?php echo $_GET['pid']?>" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="encounter/patient_encounter.php?mode=new&pid=<?php echo $_GET['pid']?>&set_pid=<?php echo $_GET['pid']?>" name="Main" scrolling="AUTO" noresize frameborder="0">

<?php } elseif ($_GET['noteid']){ ?>
  <frame src="navigation.php" name="Navigation" scrolling="NO" noresize frameborder="0">
  <frame src="summary/summary_title.php" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="summary/pnotes_full.php?noteid=<?php echo $_GET['noteid'] ?>&active=1" name="Main" scrolling="AUTO" noresize frameborder="0">

<?php } else { ?>
  <frame src="navigation.php" name="Navigation" scrolling="NO" noresize frameborder="0">
  <frame src="summary/summary_title.php" name="Title" scrolling="no" noresize frameborder="0">
  <frame src="summary/patient_summary.php" name="Main" scrolling="AUTO" noresize frameborder="0">
<?php } ?>
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
