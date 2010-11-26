<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
<SCRIPT LANGUAGE="JavaScript">

function validate(f) {
  if (f.form_date.value == "") {
    alert("<?php echo htmlspecialchars( xl('Please enter a date.'), ENT_QUOTES); ?>");
    f.form_date.focus();
    f.form_date.style.backgroundColor="red";
    return false;
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

$(document).ready(function(){
  $("#cancel").click(function() { parent.$.fn.fancybox.close(); });
});

</script>
</head>


<body class="body_top">
<?php

// Ensure user is authorized
if (!acl_check('patients', 'med')) {
  echo "<p>(" . htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES) . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}

if ($_POST['form_complete']) {
  // Save that form as a row in rule_patient_data table
  //  and then close the window/modul.

  // Collect and trim variables
  if (isset($_POST['form_entryID'])) $form_entryID = trim($_POST['form_entryID']);
  $form_date = trim($_POST['form_date']);
  $form_category = trim($_POST['form_category']);
  $form_item = trim($_POST['form_item']);
  $form_complete = trim($_POST['form_complete']);
  $form_result = trim($_POST['form_result']);

  if (!isset($form_entryID)) {
    // Insert new row of data into rule_patient_data table
    sqlInsert("INSERT INTO `rule_patient_data` (`date`, `pid`, `category`, `item`, `complete`, `result`) " .
      "VALUES (?,?,?,?,?,?)", array($form_date, $pid, $form_category, $form_item, $form_complete, $form_result) );
  }
  else { // $form_mode == "edit"
    // Modify selected row in rule_patient_data table
    sqlStatement("UPDATE `rule_patient_data` " .
      "SET `date`=?, `complete`=?, `result`=? " .
      "WHERE `id`=?", array($form_date,$form_complete,$form_result,$form_entryID) );
  }

  // Close this window and refresh the patient summary display.
  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  echo " window.close();\n";
  echo " if ( opener ) { opener.location.reload(); } else { parent.location.reload(); } \n";
  echo "</script>\n</body>\n</html>\n";
  exit();
}

// Display the form
// Collect and trim variables
$category = trim($_GET['category']);
$item = trim($_GET['item']);
if (isset($_GET['entryID'])) $entryID = trim($_GET['entryID']);

// Collect data if a specific entry is selected
if (isset($entryID)) {
  $selectedEntry = sqlQuery("SELECT `date`, `complete`, `result` " .
    "FROM `rule_patient_data` " .
    "WHERE `id`=?", array($entryID) );
  $form_date = $selectedEntry['date'];
  $form_complete = $selectedEntry['complete'];
  $form_result = $selectedEntry['result'];
}

?>
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
<td><span class="title"><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$category) .
" - " . generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$item); ?></span>&nbsp;&nbsp;&nbsp;</td>
<td><a href="javascript:submitme();" class="css_button"><span><?php echo htmlspecialchars( xl('Save'), ENT_NOQUOTES);?></span></a></td>
<td><a href="#" id="cancel" class="css_button large_button"><span class='css_button_span large_button_span'><?php echo htmlspecialchars( xl('Cancel'), ENT_NOQUOTES);?></span></a></td>
</tr>
</table>

<br><br>
<form action='patient_data.php' name='patient_data' method='post'>
  <table border=0 cellpadding=1 cellspacing=1>
  <?php
    echo "<tr><td class='required'>";
    echo htmlspecialchars( xl('Date/Time'), ENT_NOQUOTES);
    echo ":</td><td class='text'>";
    echo "<input type='text' size='16' name='form_date' id='form_date' " .
      "value='" . htmlspecialchars( $form_date, ENT_QUOTES) . "' " .
      "onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' " .
      "title='" . htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES) . "' />";
    echo "<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      "id='img_date' border='0' alt='[?]' style='cursor:pointer'" .
      "title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />";
    echo "<script language='JavaScript'>Calendar.setup({inputField:'form_date', ifFormat:'%Y-%m-%d %H:%M:%S', button:'img_date', showsTime:'true'});</script>";
    echo "</td></tr>";

    echo "<tr><td class='required'>";
    echo htmlspecialchars( xl('Completed'), ENT_NOQUOTES);
    echo ":</td><td class='text'>";
    generate_form_field(array('data_type'=>1,'field_id'=>'complete','list_id'=>'yesno','empty_title'=>'SKIP'), ($form_complete) ? $form_complete : "YES");
    echo "</td></tr>";

    echo "<tr><td class='bold'>";
    echo htmlspecialchars( xl('Results/Details'), ENT_NOQUOTES);
    echo ":</td><td class='text'>";
    echo "<textarea name='form_result' cols='40' rows='3'>";
    echo htmlspecialchars( $form_result, ENT_NOQUOTES);
    echo "</textarea>";
    echo "</td></tr>";
  echo "</table>";
  echo "<input type='hidden' name='form_category' value='" .
    htmlspecialchars( $category, ENT_QUOTES)  . "' />";
  echo "<input type='hidden' name='form_item' value='" .
    htmlspecialchars( $item, ENT_QUOTES)  . "' />";
  if (isset($entryID)) {
    echo "<input type='hidden' name='form_entryID' value='" .
      htmlspecialchars( $entryID, ENT_QUOTES)  . "' />";
  }
?>
</form>
<?php

// Display the table of previous entries
// Collect previous data to show as table below the form
$res = sqlStatement("SELECT `id`, `date`, `complete`, `result` " .
  "FROM `rule_patient_data` " .
  "WHERE `category`=? AND `item`=? AND `pid`=? " .
  "ORDER BY `date` DESC", array($category,$item,$pid) );
?>
<br>
<hr />
<br>
<div>
<?php
if (sqlNumRows($res) >= 1) { //display table ?>
  <table class="showborder" cellspacing="0px" cellpadding="2px">
    <tr class='showborder_head'>
      <th>&nbsp;</th>
      <th><?php echo htmlspecialchars( xl('Date/Time'), ENT_NOQUOTES); ?></th>
      <th><?php echo htmlspecialchars( xl('Completed'), ENT_NOQUOTES); ?></th>
      <th><?php echo htmlspecialchars( xl('Results/Details'), ENT_NOQUOTES); ?></th>
    </tr>
    <?php
    while ($row = sqlFetchArray($res)) {
      if (isset($entryID) && ($entryID == $row['id'])) {
        echo "<tr class='text' style='background-color:LightGrey'>";
      }
      else {
        echo "<tr class='text'>";
      }
      if (isset($entryID) && ($entryID == $row['id'])) {
        // hide the edit button
        echo "<td>&nbsp;</td>";
      }
      else { // show the edit button
        echo "<td><a href='patient_data.php?category=" .
          htmlspecialchars( $category, ENT_QUOTES) . "&item=" .
          htmlspecialchars( $item, ENT_QUOTES) . "&entryID=" .
          htmlspecialchars( $row['id'], ENT_QUOTES) .
          "' onclick='top.restoreSession()' class='css_button_small'>" .
          "<span>" . htmlspecialchars( xl('Edit'), ENT_NOQUOTES) . "</span></a>" .
          "</td>";
      }
      echo "<td>" . htmlspecialchars( $row['date'], ENT_NOQUOTES) . "</td>";
      echo "<td align='center'>" . htmlspecialchars( $row['complete'], ENT_NOQUOTES) . "</td>";
      echo "<td>" . nl2br( htmlspecialchars( $row['result'], ENT_NOQUOTES) ) . "</td>";
      echo "</tr>";
    } ?>
  </table>
<?php } //display table if statement
else { //no entries
  echo "<p>" . htmlspecialchars( xl('No previous entries.'), ENT_NOQUOTES) . "</p>";
} ?>
</div>

</body>
</html>
