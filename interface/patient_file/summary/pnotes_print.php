<?php
 include_once("../../globals.php");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 $prow = getPatientData($pid, "squad, title, fname, mname, lname");

 // Check authorization.
 $thisauth = acl_check('patients', 'notes');
 if (!$thisauth)
  die("Not authorized.");
 if ($prow['squad'] && ! acl_check('squads', $prow['squad']))
  die("Not authorized for this squad.");

$noteid = $_REQUEST['noteid'];

$ptname = $prow['title'] . ' ' . $prow['fname'] . ' ' . $prow['mname'] .
  ' ' . $prow['lname'];

$title       = '';
$assigned_to = '';
$body        = '';
$activity    = 0;
if ($noteid) {
  $nrow = getPnoteById($noteid, 'title,assigned_to,activity,body');
  $title = $nrow['title'];
  $assigned_to = $nrow['assigned_to'];
  $activity = $nrow['activity'];
  $body = $nrow['body'];
}
?>
<html>
<head>
<? html_header_show();?>
<link rel='stylesheet' href="<?echo $css_header;?>" type="text/css">
</head>

<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<p><?php echo "<b>$title</b> for <b>$ptname</b>"; ?></p>

<p>Assigned To: <?php echo $assigned_to; ?></p>

<p>Active: <?php echo $activity ? 'Yes' : 'No'; ?></p>

<p><?php echo $body; ?></p>

<script language='JavaScript'>
window.print();
</script>

</body>
</html>
