<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<a href="add_transaction.php" onclick="top.restoreSession()">
<font class="title"><? xl('Patient Transactions','e'); ?></font>
<font class='more'>(Add Transaction)</font></a>

<br>
<table>

<?php
if ($result = getTransByPid($pid)) {
  foreach ($result as $iter) {
    $transid = $iter['id'];
    $link = "<a href='add_transaction.php?transid=$transid' onclick='top.restoreSession()'>";
    if (getdate() == strtotime($iter['date'])) {
      $date_string = "Today, " . date( "D F dS" ,strtotime($iter['date']));
    } else {
      $date_string = date( "D F dS" ,strtotime($iter['date']));
    }
    echo "<tr><td class='bold'>$link" . $date_string . " (" . $iter['user'] . ")</a></td>";
    echo "<td class='text'>$link" . $iter['title'] . "</a></td>";
    echo "<td class='text'>$link" . stripslashes($iter['body']) . "</a></td></tr>\n";
  }
}
?>

</table>

</body>
</html>
