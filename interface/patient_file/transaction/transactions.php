<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_bottom">

<a href="add_transaction.php" onclick="top.restoreSession()">
<font class="title"><?php xl('Patient Transactions','e'); ?></font>
<font class='more'>&nbsp;(<?php xl('Add Transaction','e'); ?>)</font></a>

<a href="print_referral.php" target="_blank" onclick="top.restoreSession()">
<font class='more'>&nbsp;(<?php xl('Print Blank Referral Form','e'); ?>)</font></a>

<br>
<table>

<?php
if ($result = getTransByPid($pid)) {
  foreach ($result as $iter) {
    $transid = $iter['id'];
    $elink = "<a href='add_transaction.php?transid=$transid' " .
      "onclick='top.restoreSession()' title='" . xl('Click to edit') . "'>";
    $plink = "<a href='print_referral.php?transid=$transid' target='_blank' " .
      "onclick='top.restoreSession()' title='" . xl('Click to print') . "'>";
    if (getdate() == strtotime($iter['date'])) {
      $date_string = xl('Today') . ", " . date( "D F dS" ,strtotime($iter['date']));
    } else {
      $date_string = date( "D F dS" ,strtotime($iter['date']));
    }
    echo "<tr><td class='bold'>$elink" . $date_string . " (" . $iter['user'] . ")</a></td>";
    echo "<td class='text'>";
    if ($iter['title'] == 'Referral') {
      echo $plink . xl($iter['title']) . "</a>";
    } else {
      echo xl($iter['title']);
    }
    echo "</td>";
    echo "<td class='text'>" . stripslashes($iter['body']) . "</td></tr>\n";
  }
}
?>

</table>

</body>
</html>
