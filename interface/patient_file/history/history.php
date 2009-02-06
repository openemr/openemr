<?php
 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("history.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<?php
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(History not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 $result = getHistoryData($pid);
 if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);	
 }
?>

<?php if ($thisauth == 'write' || $thisauth == 'addonly') { ?>
<a href="history_full.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>
 onclick="top.restoreSession()">
<span class="title"><?php xl('Patient History / Lifestyle','e'); ?></span>
<span class="more"><?php echo $tmore;?></span></a><br>
<?php } ?>

<!-- New stuff begins here. -->
<div id="HIS">
<table border='0' cellpadding='0' width='100%'>
<?php
display_layout_rows('HIS', $result);
?>
</table>
</div>

</body>
</html>
