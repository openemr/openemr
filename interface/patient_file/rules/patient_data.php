<?php

/**
 * patient_data.php
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html>
<head>

    <?php Header::setupHeader(['datetime-picker', 'opener', 'common']); ?>

<script>

function validate(f) {
  var bValid = true;
  if (f.form_date.value == "") {
    alert(<?php echo xlj('Please enter a date.'); ?>);
    f.form_date.focus();
    f.form_date.style.backgroundColor="red";
    return false;
  } else {
    var form_date = f.form_date.value.split( " " );
    var date_split = form_date[0].split( "-" );
    var time_split = form_date[1].split( ":" );
    var d = new Date( date_split[0], date_split[1]-1, date_split[2], time_split[0], time_split[1], time_split[2] );
    var now = new Date();
    if ( d > now &&
        f.form_complete.value == "YES" ) {
        alert(<?php echo xlj('You cannot enter a future date with a completed value of YES.'); ?>);
        f.form_date.focus();
        f.form_date.style.backgroundColor="red";
        return false;
    }
  }
  return true;
}

function submitme() {
 var f = document.forms['patient_data'];
 if (validate(f)) {
  top.restoreSession();
  f.submit();
 }
}

$(function () {
  $("#cancel").click(function() {
      dlgclose();
  });

  $('.datetimepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = true; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });

});

</script>
</head>


<body class="body_top">
<?php

// Ensure user is authorized
if (!AclMain::aclCheckCore('patients', 'med')) {
    echo "<p>(" . xlt('Not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

if ($_POST['form_complete']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // Save that form as a row in rule_patient_data table
    //  and then close the window/modul.

    // Collect and trim variables
    if (isset($_POST['form_entryID'])) {
        $form_entryID = trim($_POST['form_entryID']);
    }

    $form_date = trim($_POST['form_date']);
    $form_category = trim($_POST['form_category']);
    $form_item = trim($_POST['form_item']);
    $form_complete = trim($_POST['form_complete']);
    $form_result = trim($_POST['form_result']);

    if (!isset($form_entryID)) {
        // Insert new row of data into rule_patient_data table
        sqlStatement("INSERT INTO `rule_patient_data` (`date`, `pid`, `category`, `item`, `complete`, `result`) " .
        "VALUES (?,?,?,?,?,?)", array($form_date, $pid, $form_category, $form_item, $form_complete, $form_result));
    } else { // $form_mode == "edit"
        // Modify selected row in rule_patient_data table
        sqlStatement("UPDATE `rule_patient_data` " .
        "SET `date`=?, `complete`=?, `result`=? " .
        "WHERE `id`=?", array($form_date,$form_complete,$form_result,$form_entryID));
    }

    // Close this window and refresh the patient summary display.
    echo "<html>\n<body>\n<script>\n";
    echo " dlgclose();\n";
    echo " top.restoreSession();\n";
    // refreshed by dialog callback- if issue with refresh try to do elsewhere as here is an IE11 issue.
    echo "</script>\n</body>\n</html>\n";
    exit();
}

// Display the form
// Collect and trim variables
$category = trim($_GET['category']);
$item = trim($_GET['item']);
if (isset($_GET['entryID'])) {
    $entryID = trim($_GET['entryID']);
}

// Collect data if a specific entry is selected
if (isset($entryID)) {
    $selectedEntry = sqlQuery("SELECT `date`, `complete`, `result` " .
    "FROM `rule_patient_data` " .
    "WHERE `id`=?", array($entryID));
    $form_date = $selectedEntry['date'];
    $form_complete = $selectedEntry['complete'];
    $form_result = $selectedEntry['result'];
}

?>
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
<td><span class="title"><?php echo generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category) .
" - " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $item); ?></span>&nbsp;&nbsp;&nbsp;</td>
<td><a href="javascript:submitme();" class="btn btn-primary"><?php echo xlt('Save'); ?></a></td>
<td><a href="#" id="cancel" class="btn btn-secondary"><?php echo xlt('Cancel'); ?></a></td>
</tr>
</table>

<br />
<form action='patient_data.php' name='patient_data' method='post' onsubmit='return top.restoreSession()'>
  <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

  <table border=0 cellpadding=1 cellspacing=1>
    <?php
    echo "<tr><td class='required'>";
    echo xlt('Date/Time');
    echo ":</td><td class='text'>";
    echo "<input type='text' size='16' class='datetimepicker' name='form_date' id='form_date' " .
      "value='" . attr($form_date) . "' " .
      "title='" . xla('yyyy-mm-dd hh:mm:ss') . "' />";
    echo "</td></tr>";

    echo "<tr><td class='required'>";
    echo xlt('Completed');
    echo ":</td><td class='text'>";
    generate_form_field(array('data_type' => 1,'field_id' => 'complete','list_id' => 'yesno','empty_title' => 'SKIP'), ($form_complete) ? $form_complete : "YES");
    echo "</td></tr>";

    echo "<tr><td class='bold'>";
    echo xlt('Results/Details');
    echo ":</td><td class='text'>";
    echo "<textarea name='form_result' cols='40' rows='3'>";
    echo attr($form_result);
    echo "</textarea>";
    echo "</td></tr>";
    echo "</table>";
    echo "<input type='hidden' name='form_category' value='" .
    attr($category)  . "' />";
    echo "<input type='hidden' name='form_item' value='" .
    attr($item)  . "' />";
    if (isset($entryID)) {
        echo "<input type='hidden' name='form_entryID' value='" .
        attr($entryID)  . "' />";
    }
    ?>
</form>
<?php

// Display the table of previous entries
// Collect previous data to show as table below the form
$res = sqlStatement("SELECT `id`, `date`, `complete`, `result` " .
  "FROM `rule_patient_data` " .
  "WHERE `category`=? AND `item`=? AND `pid`=? " .
  "ORDER BY `date` DESC", array($category,$item,$pid));
?>
<hr />
<br />
<div>
<?php
if (sqlNumRows($res) >= 1) { //display table ?>
  <table class="showborder" cellspacing="0px" cellpadding="2px">
    <tr class='showborder_head'>
      <th>&nbsp;</th>
      <th><?php echo xlt('Date/Time'); ?></th>
      <th><?php echo xlt('Completed'); ?></th>
      <th><?php echo xlt('Results/Details'); ?></th>
    </tr>
    <?php
    while ($row = sqlFetchArray($res)) {
        if (isset($entryID) && ($entryID == $row['id'])) {
            echo "<tr class='text' style='background-color:LightGrey'>";
        } else {
            echo "<tr class='text'>";
        }

        if (isset($entryID) && ($entryID == $row['id'])) {
            // hide the edit button
            echo "<td>&nbsp;</td>";
        } else { // show the edit button
            echo "<td><a href='patient_data.php?category=" .
            attr_url($category) . "&item=" .
            attr_url($item) . "&entryID=" .
            attr_url($row['id']) .
            "' onclick='top.restoreSession()' class='btn btn-primary btn-sm'>" .
            "<span>" . xlt('Edit') . "</span></a>" .
            "</td>";
        }

        echo "<td>" . text($row['date']) . "</td>";
        echo "<td align='center'>" . text($row['complete']) . "</td>";
        echo "<td>" . nl2br(text($row['result'])) . "</td>";
        echo "</tr>";
    } ?>
  </table>
<?php } else { //no entries
    echo "<p>" . xlt('No previous entries.') . "</p>";
} ?>
</div>
</body>
</html>
