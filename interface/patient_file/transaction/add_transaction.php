<?php
// add_transaction is a misnomer, as this script will now also edit
// existing transactions.

require_once("../../globals.php");
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");

$transid = empty($_REQUEST['transid']) ? 0 : $_REQUEST['transid'] + 0;
$mode    = empty($_POST['mode']) ? '' : $_POST['mode'];

$body_onload_code="";

if ($mode) {
  $sets =
    "title='"          . $_POST['title'] . "'" .
    ", user = '"       . $_SESSION['authUser'] . "'" .
    ", groupname = '"  . $_SESSION['authProvider'] . "'" .
    ", authorized = '" . $userauthorized . "'" .
    ", date = NOW()";

  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'REF' AND uor > 0 " .
    "ORDER BY group_name, seq");
  while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    $value = $_POST["form_$field_id"];
    if ($field_id == 'body' && $title != 'Referral') {
      $value = $_POST["body"];
    }
    $sets .= ", $field_id = '$value'";
  }

  if ($transid) {
    sqlStatement("UPDATE transactions SET $sets WHERE id = '$transid'");
  }
  else {
    $sets .= ", pid = '$pid'";
    $transid = sqlInsert("INSERT INTO transactions SET $sets");
  }

  if ($GLOBALS['concurrent_layout'])
    $body_onload_code = "javascript:location.href='transactions.php';";
  else
    $body_onload_code = "javascript:parent.Transactions.location.href='transactions.php';";
}

$trans_types = array(
  'Referral'          => xl('Referral'),
  'Patient Request'   => xl('Patient Request'),
  'Physician Request' => xl('Physician Request'),
  'Legal'             => xl('Legal'),
  'Billing'           => xl('Billing'),
);

$CPR = 4; // cells per row

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo " </table>\n";
    echo "</div>\n";
  }
}

// If we are editing a transaction, get its ID and data.
$trow = $transid ? getTransById($transid) : array();
?>
<html>
<head>
<?php html_header_show(); ?>

<style>

body, td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<script language="JavaScript">

var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

function titleChanged() {
 var sel = document.forms[0].title;
 var si = (sel.selectedIndex < 0) ? 0 : sel.selectedIndex;
 if (sel.options[si].value == 'Referral') {
  document.getElementById('otherdiv').style.display = 'none';
  document.getElementById('referdiv').style.display = 'block';
 } else {
  document.getElementById('referdiv').style.display = 'none';
  document.getElementById('otherdiv').style.display = 'block';
 }
 return true;
}

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

</script>

</head>
<body <?php echo $top_bg_line; ?> onload="<?php echo $body_onload_code; ?>"
 topmargin='4' rightmargin='4' leftmargin='4' bottommargin='4'
 marginwidth='4' marginheight='4'>

<form name='new_transaction' method='post'
 action='add_transaction.php?transid=<?php echo $transid ?>'>
<input type='hidden' name='mode' value='add'>

<span class='bold'><? xl('Transaction Type','e'); ?>:</span>&nbsp;
<select name='title' onchange='titleChanged()'>
<?php
foreach ($trans_types as $key => $value) {
  echo "    <option value='$key'";
  if ($key == $db_title) echo " selected";
  echo ">$value</option>\n";
}
?>
</select>

<p>
<div id='otherdiv' style='display:none'>
<span class='bold'><? xl('Details','e'); ?>:</span><br>
<textarea name='body' rows='6' cols='40' wrap='virtual'></textarea>
</div>
</p>

<p>
<div id='referdiv' style='display:none'>
<?php
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'REF' AND uor > 0 " .
  "ORDER BY group_name, seq");
$last_group = '';
$cell_count = 0;
$item_count = 0;
$display_style = 'block';

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];

  $currvalue  = '';
  if (isset($trow[$field_id])) $currvalue = $trow[$field_id];

  // Handle special-case default values.
  if (!$currvalue && !$transid) {
    if ($field_id == 'refer_date') {
      $currvalue = date('Y-m-d');
    }
    else if ($field_id == 'body') {
      $tmp = sqlQuery("SELECT reason FROM form_encounter WHERE " .
        "pid = '$pid' ORDER BY date DESC LIMIT 1");
      if (!empty($tmp)) $currvalue = $tmp['reason'];
    }
  }

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    $group_seq  = substr($this_group, 0, 1);
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
    echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
      "onclick='return divclick(this,\"div_$group_seq\");'";
    if ($display_style == 'block') echo " checked";
    echo " /><b>$group_name</b></span>\n";
    echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
    echo " <table border='0' cellpadding='0'>\n";
    $display_style = 'none';
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo " <tr>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td valign='top' colspan='$titlecols'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  echo "<b>";
  if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";
  echo "</b>";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td valign='top' colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_form_field($frow, $currvalue);
}

end_group();
?>
</div>
</p>

<p>
<a href="javascript:document.new_transaction.submit();" class='link_submit'>
[<?php xl('Save Transaction','e'); ?>]</a>
</p>

</form>

<script language="JavaScript">
<?php echo $date_init; ?>
titleChanged();
</script>

</body>
</html>
