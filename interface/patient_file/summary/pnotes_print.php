<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/options.inc.php");

 $prow = getPatientData($pid, "squad, title, fname, mname, lname");

 // Check authorization.
 $thisauth = acl_check('patients', 'notes');
 if (!$thisauth)
  die(htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES));
 if ($prow['squad'] && ! acl_check('squads', $prow['squad']))
  die(htmlspecialchars( xl('Not authorized for this squad.'), ENT_NOQUOTES));

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
  "</b>" . htmlspecialchars( xl('for','',' ',' '), ENT_NOQUOTES) .
  "<b>" . htmlspecialchars( $ptname, ENT_NOQUOTES) . "</b>"; ?></p>

<p><?php echo htmlspecialchars( xl('Assigned To'), ENT_NOQUOTES); ?>: <?php echo htmlspecialchars( $assigned_to, ENT_NOQUOTES); ?></p>

<p><?php echo htmlspecialchars( xl('Active'), ENT_NOQUOTES); ?>: <?php echo htmlspecialchars( ($activity ? xl('Yes') : xl('No')), ENT_NOQUOTES); ?></p>

<p><?php echo nl2br(htmlspecialchars( $body, ENT_NOQUOTES)); ?></p>

<script language='JavaScript'>
window.print();
</script>

</body>
</html>
