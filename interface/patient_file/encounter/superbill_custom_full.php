<?php
include_once("../../globals.php");
include_once("../../../custom/code_types.inc.php");
include_once("$srcdir/sql.inc");

// Translation for form fields.
function ffescape($field) {
  return mysql_real_escape_string($field);
}

// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = sprintf("%.2f", $amount);
    if ($amount != 0.00) return $amount;
  }
  return '';
}

$pagesize = 100;

$mode    = $_POST['mode'];
$code_id = 0;
$related_code = '';

if (isset($mode)) {
  $code_id    = $_POST['code_id'] + 0;
  $code       = $_POST['code'];
  $code_type  = $_POST['code_type'];
  $code_text  = $_POST['code_text'];
  $modifier   = $_POST['modifier'];
  // $units      = $_POST['units'];
  // $superbill  = $_POST['superbill'];
  $related_code = $_POST['related_code'];

  if ($mode == "delete") {
    sqlStatement("DELETE FROM codes WHERE id = '$code_id'");
    $code_id = 0;
  }
  else if ($mode == "add") {
    $sql =
      "code = '"         . ffescape($code)         . "', " .
      "code_type = '"    . ffescape($code_type)    . "', " .
      "code_text = '"    . ffescape($code_text)    . "', " .
      "modifier = '"     . ffescape($modifier)     . "', " .
      // "units = '"        . ffescape($units)        . "', " .
      // "superbill = '"    . ffescape($superbill)    . "', " .
      "related_code = '" . ffescape($related_code) . "'";
    if ($code_id) {
      sqlStatement("UPDATE codes SET $sql WHERE id = '$code_id'");
      sqlStatement("DELETE FROM prices WHERE pr_id = '$code_id' AND " .
        "pr_selector = ''");
    }
    else {
      $code_id = sqlInsert("INSERT INTO codes SET $sql");
    }
    foreach ($_POST['fee'] as $key => $value) {
      $value = $value + 0;
      if ($value) {
        sqlStatement("INSERT INTO prices ( " .
          "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
          "'$code_id', '', '$key', '$value' )");
      }
    }
    $code = $code_type = $code_text = $modifier = $superbill = "";
    $code_id = 0;
    $related_code = '';
  }
  else if ($mode == "edit") {
    $sql = "SELECT * FROM codes WHERE id = '$code_id'";
    $results = sqlQ($sql);
    while ($row = mysql_fetch_assoc($results)) {
      $code         = $row['code'];
      $code_text    = $row['code_text'];
      $code_type    = $row['code_type'];
      $modifier     = $row['modifier'];
      // $units        = $row['units'];
      // $superbill    = $row['superbill'];
      $related_code = $row['related_code'];
    }
  }
}

$related_desc = '';
if (!empty($related_code)) {
  $relrow = sqlQuery("SELECT code_text FROM codes WHERE code = '$related_code'");
  $related_desc = $related_code . ': ' . trim($relrow['code_text']);
}

$fstart = $_REQUEST['fstart'] + 0;
$filter = $_REQUEST['filter'] + 0;
$search = $_REQUEST['search'];

$where = "1 = 1";
if ($filter) {
  $where .= " AND code_type = '$filter'";
}
if (!empty($search)) {
  $where .= " AND code LIKE '" . ffescape($search) . "%'";
}

$crow = sqlQuery("SELECT count(*) AS count FROM codes WHERE $where");
$count = $crow['count'];
if ($fstart >= $count) $fstart -= $pagesize;
if ($fstart < 0) $fstart = 0;
$fend = $fstart + $pagesize;
if ($fend > $count) $fend = $count;
?>

<html>
<head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

// This is for callback by the find-code popup.
function set_related(code, codedesc) {
 var f = document.forms[0];
 if (code) {
  f.related_desc.value = code + ': ' + codedesc;
 } else {
  f.related_desc.value = '';
 }
 f.related_code.value = code;
}

// This invokes the find-code popup.
function sel_related() {
 var f = document.forms[0];
 var i = f.code_type.selectedIndex;
 var codetype = '';
 if (i >= 0) {
  var myid = f.code_type.options[i].value;
<?php
foreach ($code_types as $key => $value) {
  $codeid = $value['id'];
  $coderel = $value['rel'];
  if (!$coderel) continue;
  echo "  if (myid == $codeid) codetype = '$coderel';";
}
?>
 }
 if (!codetype) {
  alert('This code type has no related type defined.');
  return;
 }
 dlgopen('find_code_popup.php?codetype=' + codetype, '_blank', 500, 400);
}

function submitAdd() {
 var f = document.forms[0];
 if (!f.code.value) {
  alert('No code was specified!');
  return;
 }
 f.mode.value = 'add';
 f.code_id.value = '';
 f.submit();
}

function submitUpdate() {
 var f = document.forms[0];
 if (! parseInt(f.code_id.value)) {
  alert('Cannot update because you are not editing an existing entry!');
  return;
 }
 if (!f.code.value) {
  alert('No code was specified!');
  return;
 }
 f.mode.value = 'add';
 f.submit();
}

function submitList(offset) {
 var f = document.forms[0];
 var i = parseInt(f.fstart.value) + offset;
 if (i < 0) i = 0;
 f.fstart.value = i;
 f.submit();
}

function submitEdit(id) {
 var f = document.forms[0];
 f.mode.value = 'edit';
 f.code_id.value = id;
 f.submit();
}

function submitDelete(id) {
 var f = document.forms[0];
 f.mode.value = 'delete';
 f.code_id.value = id;
 f.submit();
}

</script>

</head>
<body <?php echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<?php if ($GLOBALS['concurrent_layout']) {
// <a href="superbill_codes.php">
// <span class=title>??php xl('Superbill Codes','e'); ??</span>
// <font class=more>??php echo $tback;??</font></a>
} else { ?>
<a href='patient_encounter.php?codefrom=superbill' target='Main'>
<span class='title'><?php xl('Superbill Codes','e'); ?></span>
<font class='more'><?php echo $tback;?></font></a>
<?php } ?>

<form method='post' action='superbill_custom_full.php' name='theform'>

<input type='hidden' name='mode' value=''>

<br>

<center>
<table border='0' cellpadding='0' cellspacing='0'>

 <tr>
  <td colspan="3"> <?php xl('Not all fields are required for all codes or code types.','e'); ?><br><br></td>
 </tr>

 <tr>
  <td><?php xl('Type','e'); ?>:</td>
  <td width="5"></td>
  <td>
   <select name="code_type">
<?php foreach ($code_types as $key => $value) { ?>
    <option value="<?php  echo $value['id'] ?>"<?php if ($GLOBALS['code_type'] == $value['id']) echo " selected" ?>><?php echo $key ?></option>
<?php } ?>
   </select>
   &nbsp;&nbsp;
   <?php xl('Code','e'); ?>:
   <input type='text' size='6' name='code' value='<?php echo $code ?>'>
   &nbsp;&nbsp;<?php xl('Modifier','e'); ?>:
   <input type='text' size='3' name='modifier' value='<?php echo $modifier ?>'>
  </td>
 </tr>

 <tr>
  <td><?php xl('Description','e'); ?>:</td>
  <td></td>
  <td>
   <input type='text' size='50' name="code_text" value='<?php echo $code_text ?>'>
  </td>
 </tr>

 <tr>
  <td><?php xl('Relate To','e'); ?>:</td>
  <td></td>
  <td>
   <input type='text' size='50' name='related_desc'
    value='<?php echo $related_desc ?>' onclick="sel_related()"
    title='<?php xl('Click to select related code','e'); ?>' readonly />
   <input type='hidden' name='related_code' value='<?php echo $related_code ?>' />
  </td>
 </tr>

 <tr>
  <td><?php xl('Fees','e'); ?>:</td>
  <td></td>
  <td>
<?php
$pres = sqlStatement("SELECT lo.option_id, lo.title, p.pr_price " .
  "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
  "p.pr_id = '$code_id' AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
  "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
for ($i = 0; $prow = sqlFetchArray($pres); ++$i) {
  if ($i) echo "&nbsp;&nbsp;";
  echo $prow['title'] . " ";
  echo "<input type='text' size='6' name='fee[" . $prow['option_id'] . "]' " .
    "value='" . $prow['pr_price'] . "' >\n";
}
?>
  </td>
 </tr>

 <tr>
  <td colspan="3" align="center">
   <input type="hidden" name="code_id" value="<?php echo $code_id ?>"><br>
   <a href='javascript:submitUpdate();' class='link'>[<? xl('Update','e'); ?>]</a>
   &nbsp;&nbsp;
   <a href='javascript:submitAdd();' class='link'>[<? xl('Add as New','e'); ?>]</a>
  </td>
 </tr>

</table>

<table border='0' cellpadding='5' cellspacing='0' width='96%'>
 <tr>

  <td class='text'>
   <select name='filter' onchange='submitList(0)'>
    <option value='0'>All</option>
<?php
foreach ($code_types as $key => $value) {
  echo "<option value='" . $value['id'] . "'";
  if ($value['id'] == $filter) echo " selected";
  echo ">$key</option>\n";
}
?>
   </select>
   &nbsp;&nbsp;&nbsp;&nbsp;

   <input type="text" name="search" size="5" value="<?php echo $search ?>">&nbsp;
   <input type="submit" name="go" value="Search">
   <input type='hidden' name='fstart' value='<?php echo $fstart ?>'>
  </td>

  <td class='text' align='right'>
<?php if ($fstart) { ?>
   <a href="javascript:submitList(-<?php echo $pagesize ?>)">
    &lt;&lt;
   </a>
   &nbsp;&nbsp;
<?php } ?>
   <?php echo ($fstart + 1) . " - $fend of $count" ?>
   &nbsp;&nbsp;
   <a href="javascript:submitList(<?php echo $pagesize ?>)">
    &gt;&gt;
   </a>
  </td>

 </tr>
</table>

</form>

<table border='0' cellpadding='5' cellspacing='0' width='96%'>
 <tr>
  <td><span class='bold'><?php xl('Code','e'); ?></span></td>
  <td><span class='bold'><?php xl('Mod','e'); ?></span></td>
  <td><span class='bold'><?php xl('Type','e'); ?></span></td>
  <td><span class='bold'><?php xl('Description','e'); ?></span></td>
  <!--
  <td><span class='bold'><?php // xl('Modifier','e'); ?></span></td>
  <td><span class='bold'><?php // xl('Units','e'); ?></span></td>
  <td><span class='bold'><?php // xl('Fee','e'); ?></span></td>
  -->
<?php
$pres = sqlStatement("SELECT title FROM list_options " .
  "WHERE list_id = 'pricelevel' ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
  echo "  <td class='bold' align='right' nowrap>" . $prow['title'] . "</td>\n";
}
?>
  <td></td>
  <td></td>
 </tr>
<?php

$res = sqlStatement("SELECT * FROM codes WHERE $where " .
  "ORDER BY code_type, code, code_text LIMIT $fstart, " . ($fend - $fstart));

for ($i = 0; $row = sqlFetchArray($res); $i++) $all[$i] = $row;

if (!empty($all)) {
  $count = 0;
  foreach($all as $iter) {
    $count++;

    $has_fees = false;
    foreach ($code_types as $key => $value) {
      if ($value['id'] == $iter['code_type']) {
        $has_fees = $value['fee'];
        break;
      }
    }

    echo " <tr>\n";
    echo "  <td class='text'>" . $iter["code"] . "</td>\n";
    echo "  <td class='text'" . $iter["modifier"] . "</td>\n";
    echo "  <td class='text'>$key</td>\n";
    echo "  <td class='text'>" . $iter['code_text'] . "</td>\n";

    // echo "<td>";
    // if ($has_fees) {
    //   echo "<span class='text'>" . $iter['modifier'] . "</span>";
    // }
    // echo "</td>";
    // echo "<td>";
    // if ($has_fees) {
    //   echo "<span class='text'>" . $iter['units'] . "</span>";
    // }
    // echo "</td>";
    // echo "<td>";
    // if ($has_fees) {
    //   echo "<span class='text'>$" . sprintf("%01.2f", $iter['fee']) . "</span>";
    // }
    // echo "</td>";

    $pres = sqlStatement("SELECT p.pr_price " .
      "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
      "p.pr_id = '" . $iter['id'] . "' AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
      "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
    while ($prow = sqlFetchArray($pres)) {
      echo "<td class='text' align='right'>" . bucks($prow['pr_price']) . "</td>\n";
    }

    echo "  <td align='right'><a class='link' href='javascript:submitDelete(" . $iter['id'] . ")'>[Del]</a></td>\n";
    echo "  <td align='right'><a class='link' href='javascript:submitEdit("   . $iter['id'] . ")'>[Edit]</a></td>\n";
    echo " </tr>\n";

  }
}

?>

</table>

</center>

</body>
</html>
