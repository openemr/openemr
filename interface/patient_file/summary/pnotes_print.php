<?php
 include_once("../../globals.php");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 // Check authorization.
 $thisauth = acl_check('patients', 'notes');
 if (!$thisauth)
  die("Not authorized.");
 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die("Not authorized for this squad.");

$noteid = $_REQUEST['noteid'];

$title       = '';
$assigned_to = '';
$body        = '';
$activity    = 0;
if ($noteid) {
  $prow = getPnoteById($noteid, 'title,assigned_to,activity,body');
  $title = $prow['title'];
  $assigned_to = $prow['assigned_to'];
  $activity = $prow['activity'];
  $body = $prow['body'];
}
?>
<html>
<head>
<link rel='stylesheet' href="<?echo $css_header;?>" type="text/css">
</head>

<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<p><b><?php echo $title; ?></b></p>

<p>Assigned To: <?php echo $assigned_to; ?></p>

<p>Active: <?php echo $activity ? 'Yes' : 'No'; ?></p>

<p><?php echo $body; ?></p>

<script language='JavaScript'>
window.print();
</script>

</body>
</html>
