<?php
/**
 * new_search_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$fstart = isset($_REQUEST['fstart']) ? $_REQUEST['fstart'] + 0 : 0;

$searchcolor = empty($GLOBALS['layout_search_color']) ?
  '#ffff55' : $GLOBALS['layout_search_color'];
?>
<html>
<head>
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style>
form {
 padding: 0px;
 margin: 0px;
}
#searchCriteria {
 text-align: center;
 width: 100%;
 font-size: 0.8em;
 background-color: #ddddff;
 font-weight: bold;
 padding: 3px;
}
#searchResultsHeader {
 width: 100%;
 background-color: lightgrey;
}
#searchResultsHeader table {
 width: 96%;  /* not 100% because the 'searchResults' table has a scrollbar */
 border-collapse: collapse;
}
#searchResultsHeader th {
 font-size: 0.7em;
}
#searchResults {
 width: 100%;
 height: 80%;
 overflow: auto;
}

.srName  { width: 12%; }
.srPhone { width: 11%; }
.srSS    { width: 11%; }
.srDOB   { width:  8%; }
.srID    { width:  7%; }
.srMisc  { width: 10%; }

#searchResults table {
 width: 100%;
 border-collapse: collapse;
 background-color: white;
}
#searchResults tr {
 cursor: hand;
 cursor: pointer;
}
#searchResults td {
 font-size: 0.7em;
 border-bottom: 1px solid #eee;
}
.oneResult {
}
.topResult {
 background-color: <?php echo attr($searchcolor); ?>;
}
.billing {
 color: red;
 font-weight: bold;
}
.highlight {
 background-color: #336699;
 color: white;
}
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>

<script language="JavaScript">

// This is called when forward or backward paging is done.
//
function submitList(offset) {
 var f = document.forms[0];
 var i = parseInt(f.fstart.value) + offset;
 if (i < 0) i = 0;
 f.fstart.value = i;
 f.submit();
}

</script>

</head>
<body class="body_top">

<form method='post' action='new_search_popup.php' name='theform'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<input type='hidden' name='fstart'  value='<?php echo attr($fstart); ?>' />

<?php
$MAXSHOW = 100; // maximum number of results to display at once

// Construct query and save search parameters as form fields.
// An interesting requirement is to sort on the number of matching fields.

$message = "";
$numfields = 0;
$relevance = "0";
// array to hold the sql parameters for binding
//  Note in this special situation, there are two:
//   1. For the main sql statement - $sqlBindArray
//   2. For the _set_patient_inc_count function - $sqlBindArraySpecial
//      (this only holds $where and not $relevance binded values)
$sqlBindArray = array();
$sqlBindArraySpecial = array();
$where = "1 = 0";

foreach ($_REQUEST as $key => $value) {
    if (substr($key, 0, 3) != 'mf_') {
        continue; // "match field"
    }

    $fldname = substr($key, 3);
  // pubpid requires special treatment.  Match on that is fatal.
    if ($fldname == 'pubpid') {
        $relevance .= " + 1000 * ( ".add_escape_custom($fldname)." LIKE ? )";
        array_push($sqlBindArray, $value);
    } else {
        $relevance .= " + ( ".add_escape_custom($fldname)." LIKE ? )";
        array_push($sqlBindArray, $value);
    }

    $where .= " OR ".add_escape_custom($fldname)." LIKE ?";
    array_push($sqlBindArraySpecial, $value);
    echo "<input type='hidden' name='".attr($key)."' value='".attr($value)."' />\n";
    ++$numfields;
}

$sql = "SELECT *, ( $relevance ) AS relevance, " .
  "DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS " .
  "FROM patient_data WHERE $where " .
  "ORDER BY relevance DESC, lname, fname, mname " .
  "LIMIT ".escape_limit($fstart).", ".escape_limit($MAXSHOW)."";

$sqlBindArray = array_merge($sqlBindArray, $sqlBindArraySpecial);
$rez = sqlStatement($sql, $sqlBindArray);
$result = array();
while ($row = sqlFetchArray($rez)) {
    $result[] = $row;
}

_set_patient_inc_count($MAXSHOW, count($result), $where, $sqlBindArraySpecial);
?>

</form>

<table border='0' cellpadding='5' cellspacing='0' width='100%'>
 <tr>
  <td class='text'>
   &nbsp;
  </td>
  <td class='text' align='center'>
<?php if ($message) {
    echo "<font color='red'><b>".text($message)."</b></font>\n";
} ?>
  </td>
  <td class='text' align='right'>
<?php
// Show start and end row number, and number of rows, with paging links.
$count = $GLOBALS['PATIENT_INC_COUNT'];
$fend = $fstart + $MAXSHOW;
if ($fend > $count) {
    $fend = $count;
}
?>
<?php if ($fstart) { ?>
   <a href="javascript:submitList(-<?php echo attr($MAXSHOW); ?>)">
    &lt;&lt;
   </a>
   &nbsp;&nbsp;
<?php } ?>
    <?php echo ($fstart + 1) . text(" - $fend of $count") ?>
<?php if ($count > $fend) { ?>
   &nbsp;&nbsp;
   <a href="javascript:submitList(<?php echo attr($MAXSHOW); ?>)">
    &gt;&gt;
   </a>
<?php } ?>
  </td>
 </tr>
</table>

<div id="searchResultsHeader" class="head">
<table>
<tr>
<th class="srID"   ><?php echo xlt('Hits');?></th>
<th class="srName" ><?php echo xlt('Name');?></th>
<?php
// This gets address plus other fields that are mandatory, up to a limit of 5.
$extracols = array();
$tres = sqlStatement("SELECT field_id, title FROM layout_options " .
  "WHERE form_id = 'DEM' AND field_id != '' AND " .
  "( uor > 1 OR uor > 0 AND edit_options LIKE '%D%' ) AND " .
  "field_id NOT LIKE 'title' AND " .
  "field_id NOT LIKE '_name' " .
  "ORDER BY group_id, seq, title LIMIT 9");

while ($trow = sqlFetchArray($tres)) {
    $extracols[$trow['field_id']] = $trow['title'];
    echo "<th class='srMisc'>" . text(xl_layout_label($trow['title'])) . "</th>\n";
}
?>

</tr>
</table>
</div>

<div id="searchResults">

<table>
<tr>
<?php
$pubpid_matched = false;
if ($result) {
    foreach ($result as $iter) {
        $relevance = $iter['relevance'];
        if ($relevance > 999) {
            $relevance -= 999;
            $pubpid_matched = true;
        }

        echo "<tr id='" . attr($iter['pid']) . "' class='oneresult";
        // Highlight entries where all fields matched.
        echo $numfields <= $iter['relevance'] ? " topresult" : "";
        echo "'>";
        echo  "<td class='srID'>" . text($relevance) . "</td>\n";
        echo  "<td class='srName'>" . text($iter['lname'] . ", " . $iter['fname']) . "</td>\n";
        foreach ($extracols as $field_id => $title) {
            echo "<td class='srMisc'>" . text($iter[$field_id]) . "</td>\n";
        }
    }
}
?>
</table>
</div>  <!-- end searchResults DIV -->

<center>
<?php if ($pubpid_matched) { ?>
<input type='button' value='<?php echo xla('Cancel'); ?>'
 onclick='dlgclose();' />
<?php } else { ?>
<input type='button' value='<?php echo xla('Confirm Create New Patient'); ?>'
 onclick='dlgclose("srcConfirmSave", false);' />
<?php } ?>
</center>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(function() {
  $(".oneresult").mouseover(function() { $(this).addClass("highlight"); });
  $(".oneresult").mouseout(function() { $(this).removeClass("highlight"); });
  $(".oneresult").click(function() { SelectPatient(this); });
});

var SelectPatient = function (eObj) {
<?php
// The layout loads just the demographics frame here, which in turn
// will set the pid and load all the other frames.
  $newPage = "../patient_file/summary/demographics.php?set_pid=";
  $target = "document";

?>
  objID = eObj.id;
  var parts = objID.split("~");
  opener.<?php echo $target; ?>.location.href = '<?php echo $newPage; ?>' + parts[0];
  dlgclose();
  return true;
}

var f = opener.document.forms[0];
<?php if ($pubpid_matched) { ?>
alert(<?php echo xlj('A patient with this ID already exists.'); ?>);
<?php } else { ?>
opener.force_submit = true;
f.create.value = <?php echo xlj('Confirm Create New Patient'); ?>;
<?php } ?>

<?php if (!count($result)) { ?>
$("<td><?php echo xlt('No matches were found.'); ?></td>").appendTo("#searchResults tr");
<?php } ?>

</script>

</body>
</html>
