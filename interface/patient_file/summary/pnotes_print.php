<?php
 include_once("../../globals.php");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/options.inc.php");

 $prow = getPatientData($pid, "squad, title, fname, mname, lname");

 // Check authorization.
 $thisauth = acl_check('patients', 'notes');
 if (!$thisauth)
  die(xl('Not authorized'));
 if ($prow['squad'] && ! acl_check('squads', $prow['squad']))
  die(xl('Not authorized for this squad.'));

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
<?php html_header_show();?>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">

<p><?php echo "<b>" .
  generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $title) .
  "</b>" . xl('for','',' ',' ') . "<b>$ptname</b>"; ?></p>

<p><?php xl('Assigned To','e'); ?>: <?php echo $assigned_to; ?></p>

<p><?php xl('Active','e'); ?>: <?php echo $activity ? xl('Yes') : xl('No'); ?></p>

<p><?php echo nl2br($body); ?></p>

<script language='JavaScript'>
window.print();
</script>

</body>
</html>
