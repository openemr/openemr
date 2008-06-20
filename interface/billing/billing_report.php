<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("../../custom/code_types.inc.php");

require_once("$srcdir/patient.inc");
require_once("$srcdir/billrep.inc");
require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");

$EXPORT_INC = "$webserver_root/custom/BillingExport.php";

$alertmsg = '';

if ($_POST['mode'] == 'export') {
  $sdate = $_POST['from_date'];
  $edate = $_POST['to_date'];

  $sql = "SELECT billing.*, concat(pd.fname, ' ', pd.lname) as name from billing "
  . "join patient_data as pd on pd.pid = billing.pid where billed = '1' and "
  . "(process_date > '" . mysql_real_escape_string($sdate)
  . "' or DATE_FORMAT( process_date, '%Y-%m-%d' ) = '" . mysql_real_escape_string($sdate) ."') "
  . "and (process_date < '" . mysql_real_escape_string($edate)
  . "'or DATE_FORMAT( process_date, '%Y-%m-%d' ) = '" . mysql_real_escape_string($edate) ."') "
  . "order by pid,encounter";
  $db = get_db();
  $results = $db->Execute($sql);
  $billings = array();
  if ($results->RecordCount() == 0) {
    echo xl("No Bills Found to Include in OFX Export<br>");
  }
  else {
    while(!$results->EOF) {
      $billings[] = $results->fields;
      $results->MoveNext();
    }
    $ofx = new OFX($billings);
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment; filename=openemr_ofx.ofx");
    header("Content-Type: text/xml");
    echo $ofx->get_OFX();
    exit;
  }
}

if ($_POST['mode'] == 'process') {
  if (exec("ps x | grep 'process_bills[.]php'")) {
    $alertmsg = xl('Request ignored - claims processing is already running!');
  }
  else {
    exec("cd $webserver_root/library/freeb;" .
      "php -q process_bills.php bill > process_bills.log 2>&1 &");
    $alertmsg = xl('Batch processing initiated; this may take a while.');
  }
}

//global variables:
if (!isset($_POST["mode"])) {
  if (!isset($_POST["from_date"])) { $from_date=date("Y-m-d"); } 
  else { $from_date = $_POST["from_date"]; }
  if (!isset($_POST["to_date"])) { $to_date = date("Y-m-d"); } 
  else { $to_date = $_POST["to_date"]; }
  if (!isset($_POST["code_type"])) { $code_type="all"; } 
  else { $code_type = $_POST["code_type"]; }
  if (!isset($_POST["unbilled"])) { $unbilled = "on"; } 
  else { $unbilled = $_POST["unbilled"]; }

  // if (!isset($_POST["authorized"])) {
  //  $my_authorized = "on";
  // } else {
  $my_authorized = $_POST["authorized"];
  // }
} else {
  $from_date = $_POST["from_date"];
  $to_date = $_POST["to_date"];
  $code_type = $_POST["code_type"];
  $unbilled = $_POST["unbilled"];
  $my_authorized = $_POST["authorized"];
}

$ofrom_date = $from_date;
$oto_date = $to_date;
$ocode_type = $code_type;
$ounbilled = $unbilled;
$oauthorized = $my_authorized;

?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
.subbtn { margin-top:3px; margin-bottom:3px; margin-left:2px; margin-right:2px }
</style>
<script>

function select_all() {
  for($i=0;$i < document.update_form.length;$i++) {
    $name = document.update_form[$i].name;
    if ($name.substring(0,7) == "claims[" && $name.substring($name.length -6) == "[bill]") {
      document.update_form[$i].checked = true;
    }
  }
  set_button_states();
}

function set_button_states() {
  var f = document.update_form;
  var count0 = 0; // selected and not billed or queued
  var count1 = 0; // selected and queued
  var count2 = 0; // selected and billed
  for($i = 0; $i < f.length; ++$i) {
    $name = f[$i].name;
    if ($name.substring(0, 7) == "claims[" && $name.substring($name.length -6) == "[bill]" && f[$i].checked == true) {
      if      (f[$i].value == '0') ++count0;
      else if (f[$i].value == '1' || f[$i].value == '5') ++count1;
      else ++count2;
    }
  }

  var can_generate = (count0 > 0 || count1 > 0 || count2 > 0);
  var can_mark     = (count1 > 0 || count0 > 0 || count2 > 0);
  var can_bill     = (count0 == 0 && count1 == 0 && count2 > 0);

<?php if (file_exists($EXPORT_INC)) { ?>
  f.bn_external.disabled        = !can_generate;
<?php } else { ?>
  f.bn_hcfa_print.disabled      = !can_generate;
  f.bn_hcfa.disabled            = !can_generate;
  f.bn_ub92_print.disabled      = !can_generate;
  f.bn_ub92.disabled            = !can_generate;
  f.bn_x12.disabled             = !can_generate;
  f.bn_electronic_file.disabled = !can_bill;
  f.bn_reopen.disabled          = !can_bill;
<?php } ?>
  f.bn_mark.disabled            = !can_mark;
}

// Process a click to go to an encounter.
function toencounter(pid, pname, enc, datestr) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.left_nav.setPatient(pname,pid,'');
 parent.left_nav.setEncounter(datestr, enc, othername);
 parent.left_nav.setRadio(othername, 'enc');
 parent.frames[othername].location.href =
  '../patient_file/encounter/encounter_top.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } else { ?>
 location.href = '../patient_file/encounter/patient_encounter.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } ?>
}

// Process a click to go to patient demographics.
// larry :: dbc insert
<?php if( $GLOBALS['dutchpc'] )
{
?>
function topatient(pid) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.frames[othername].location.href =
  '../patient_file/summary/demographics_full_dutch.php?pid=' + pid;
<?php } else { ?>
 location.href = '../patient_file/summary/demographics_full_dutch.php?pid=' + pid;
<?php } ?>
}

<?php } else
{ ?>

function topatient(pid) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.frames[othername].location.href =
  '../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } else { ?>
 location.href = '../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } ?>
}
<?php } ?>
// larry :: end of dbc change

</script>
</head>
<body class="body_top">

<p style='margin-top:5px;margin-bottom:5px;margin-left:5px'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<font class='title'><?php xl('Billing Report','e') ?></font>
<?php } else if ($userauthorized) { ?>
<a href="../main/main.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Billing Report','e') ?></font><font class=more> <?php echo $tback; ?></font></a>
<?php } else { ?>
<a href="../main/onotes/office_comments.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Billing Report','e') ?></font><font class=more><?php echo $tback; ?></font></a>
<?php } ?>

</p>

<form name='the_form' method='post' action='billing_report.php' onsubmit='return top.restoreSession()'>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>

<input type='hidden' name='mode' value='change'>

<table width='100%' border="1" cellspacing="0" cellpadding="0">
 <tr>
  <td nowrap>
   &nbsp;<span class='text'><?php xl('From: ','e') ?></span>
   <input type='text' size='10' name='from_date' id='from_date'
    value='<?php echo $from_date; ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd last date of this event' />
   <img src='../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_fromdate' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
   <script>
    Calendar.setup({inputField:"from_date", ifFormat:"%Y-%m-%d", button:"img_fromdate"});
   </script>
  </td>

  <td nowrap>
   &nbsp;<span class='text'><?php xl('To: ','e') ?></span>
   <input type='text' size='10' name='to_date' id='to_date'
    value='<?php echo $to_date; ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd last date of this event' />
   <img src='../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_todate' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date' />
   <script>
    Calendar.setup({inputField:"to_date", ifFormat:"%Y-%m-%d", button:"img_todate"});
   </script>
   <input type="hidden" name="code_type" value="%" />
  </td>

  <td nowrap>
   <input type='checkbox' name='unbilled' <?php if ($unbilled == "on") {echo "checked";}; ?> />
   <span class='text'><?php xl('Show Unbilled Only','e') ?></span>
  </td>

  <td nowrap>
   <input type='checkbox' name='authorized' <?php if ($my_authorized == "on") {echo "checked";}; ?> />
   <span class='text'><?php xl('Show Authorized Only','e') ?></span>
  </td>

  <td align='right' width='10%' nowrap>
   &nbsp;<span class='text'><a href="javascript:top.restoreSession();document.the_form.mode.value='change';document.the_form.submit()" class=link_submit><?php xl('[Change View]','e') ?></a>
   or
   <a href="javascript:top.restoreSession();document.the_form.mode.value='export';document.the_form.submit()" class='link_submit'><?php xl('[Export OFX]','e') ?></a></span>&nbsp;
  </td>

 </tr>

 <tr>

  <td nowrap>
   &nbsp;<a href="print_billing_report.php?<?php print "from_date=".urlencode($ofrom_date)."&to_date=".urlencode($oto_date)."&code_type=".urlencode($ocode_type)."&unbilled=".urlencode($ounbilled)."&authorized=".urlencode($oauthorized); ?>"
    class='link_submit' target='new' onclick='top.restoreSession()'><?php xl('[View Printable Report]','e') ?></a>
  </td>

  <td nowrap>
<?php
  print '&nbsp;';
  $acct_config = $GLOBALS['oer_config']['ws_accounting'];
  if($acct_config['enabled'] == true) {
    print '<span class=text><a href="javascript:void window.open(\'' . $acct_config['url_path'] . '\')">' . xl("[SQL-Ledger]") . '</a></span>';
    if (acl_check('acct', 'rep')) {
      print '<span class=text> &nbsp; <a href="javascript:void window.open(\'sl_receipts_report.php\')" onclick="top.restoreSession()">' . xl('[Reports]') . '</a></span>';
    }
    if (acl_check('acct', 'eob')) {
      print '<span class=text> &nbsp; <a href="javascript:void window.open(\'sl_eob_search.php\')" onclick="top.restoreSession()">' . xl('[EOBs]') . '</a></span>';
    }
  }
?>
  </td>

  <td colspan='2' nowrap>
   &nbsp;
<?php if (! file_exists($EXPORT_INC)) { ?>
   <a href="javascript:top.restoreSession();document.the_form.mode.value='process';document.the_form.submit()" class="link_submit"
    title="Process all queued bills to create electronic data (and print if requested)"><?php xl('[Start Batch Processing]','e') ?></a>
   &nbsp; <a href='../../library/freeb/process_bills.log' target='_blank' class='link_submit'
    title='See messages from the last batch processing run'><?php xl('[view log]','e') ?></a>
<?php } ?>
  </td>

  <td align='right' nowrap>
   <a href="javascript:select_all()" class="link_submit"><?php xl('[Select All]','e') ?></a>&nbsp;
  </td>

 </tr>
</table>

</form>

<form name='update_form' method='post' action='billing_process.php' onsubmit='return top.restoreSession()'>

<center>

<?php if (file_exists($EXPORT_INC)) { ?>
<input type="submit" class="subbtn" name="bn_external" value="Export Billing" title="<?php xl('Export to external billing system','e') ?>">
<input type="submit" class="subbtn" name="bn_mark" value="Mark as Cleared" title="<?php xl('Mark as billed but skip billing','e') ?>">
<?php } else { ?>
<input type="submit" class="subbtn" name="bn_hcfa_print" value="Queue HCFA &amp; Print" title="<?php xl('Queue for HCFA batch processing and printing','e') ?>">
<input type="submit" class="subbtn" name="bn_hcfa" value="Queue HCFA" title="<?php xl('Queue for HCFA batch processing','e')?>">
<input type="submit" class="subbtn" name="bn_ub92_print" value="Queue UB92 &amp; Print" title="<?php xl('Queue for UB-92 batch processing and printing','e')?>">
<input type="submit" class="subbtn" name="bn_ub92" value="Queue UB92" title="<?php xl('Queue for UB-92 batch processing','e')?>">
<input type="submit" class="subbtn" name="bn_x12" value="Process X12"
 title="<?php xl('Generate X12 claims and create batch','e')?>"
 onclick="alert('After saving your batch, click [view log] to check for errors.')">
<input type="submit" class="subbtn" name="bn_mark" value="Mark as Cleared" title="<?php xl('Post to accounting and mark as billed','e')?>">
<input type="submit" class="subbtn" name="bn_reopen" value="Re-Open" title="<?php xl('Mark as not billed','e')?>">
<input type="submit" class="subbtn" name="bn_electronic_file" value="Make Electronic Batch &amp; Clear" title="<?php xl('Download billing file, post to accounting and mark as billed','e')?>">
<?php } ?>

</center>

<input type='hidden' name='mode' value="bill" />
<input type='hidden' name='authorized' value="<?php echo $my_authorized; ?>" />
<input type='hidden' name='unbilled' value="<?php echo $unbilled; ?>" />
<input type='hidden' name='code_type' value="%" />
<input type='hidden' name='to_date' value="<?php echo $to_date; ?>" />
<input type='hidden' name='from_date' value="<?php echo $from_date; ?>" />

<?php
if ($my_authorized == "on" ) {
  $my_authorized = "1";
} else {
  $my_authorized = "%";
}
if ($unbilled == "on") {
  $unbilled = "0";
} else {
  $unbilled = "%";
}
?>

<input type='hidden' name='bill_list'
 value="<?php $list = getBillsListBetween($from_date,$to_date,$my_authorized,$unbilled,"%"); print $list; ?>"
 />

<!-- new form for uploading -->

<?php
if (!isset($_POST["mode"])) {
  if (!isset($_POST["from_date"])) {
    $from_date=date("Y-m-d");
  } else {
    $from_date = $_POST["from_date"];
  }
  if (!isset($_POST["to_date"])) {
    $to_date = date("Y-m-d");
  } else {
    $to_date = $_POST["to_date"];
  }
  if (!isset($_POST["code_type"])) {
    $code_type="all";
  } else {
    $code_type = $_POST["code_type"];
  }
  if (!isset($_POST["unbilled"])) {
    $unbilled = "on";
  } else {
    $unbilled = $_POST["unbilled"];
  }
  if (!isset($_POST["authorized"])) {
    $my_authorized = "on";
  } else {
    $my_authorized = $_POST["authorized"];
  }
} else {
  $from_date = $_POST["from_date"];
  $to_date = $_POST["to_date"];
  $code_type = $_POST["code_type"];
  $unbilled = $_POST["unbilled"];
  $my_authorized = $_POST["authorized"];
}

if ($my_authorized == "on" ) {
  $my_authorized = "1";
} else {
  $my_authorized = "%";
}

if ($unbilled == "on") {
  $unbilled = "0";
} else {
  $unbilled = "%";
}

if (isset($_POST["mode"]) && $_POST["mode"] == "bill") {
  billCodesList($list);
}
?>

<p>
<table border="0" cellspacing="0" cellpadding="0" width="100%">

<?php
if ($ret = getBillsBetween($from_date,$to_date,$my_authorized,$unbilled,"%")) {
  $loop = 0;
  $oldcode = "";
  $last_encounter_id = "";
  $lhtml = "";
  $rhtml = "";
  $lcount = 0;
  $rcount = 0;
  $bgcolor = "";
  $skipping = FALSE;

  foreach ($ret as $iter) {

    // We include encounters here that have never been billed.  However
    // if it had no selected billing items but does have non-selected
    // billing items, then it is not of interest.
    if (!$iter['id']) {
      $res = sqlQuery("SELECT count(*) AS count FROM billing WHERE " .
        "encounter = '" . $iter['enc_encounter'] . "' AND " .
        "pid='" . $iter['enc_pid'] . "' AND " .
        "activity = 1");
      if ($res['count'] > 0) continue;
    }

    $this_encounter_id = $iter['enc_pid'] . "-" . $iter['enc_encounter'];

    if ($last_encounter_id != $this_encounter_id) {
      if ($lhtml) {
        while ($rcount < $lcount) {
          $rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'>&nbsp;</td></tr>";
          ++$rcount;
        }
        echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
        echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n\n";
      }

      $lhtml = "";
      $rhtml = "";

      // If there are ANY unauthorized items in this encounter and this is
      // the normal case of viewing only authorized billing, then skip the
      // entire encounter.
      //
      $skipping = FALSE;
      if ($my_authorized == '1') {
        $res = sqlQuery("select count(*) as count from billing where " .
          "encounter = '" . $iter['enc_encounter'] . "' and " .
          "pid='" . $iter['enc_pid'] . "' and " .
          "activity = 1 and authorized = 0");
        if ($res['count'] > 0) {
          $skipping = TRUE;
          $last_encounter_id = $this_encounter_id;
          continue;
        }
      }

      $name = getPatientData($iter['enc_pid'], "fname, mname, lname");

      # Check if patient has primary insurance and a subscriber exists for it.
      # If not we will highlight their name in red.
      # TBD: more checking here.
      #
      $res = sqlQuery("select count(*) as count from insurance_data where " .
        "pid = " . $iter['enc_pid'] . " and " .
        "type='primary' and " .
        "subscriber_lname is not null and " .
        "subscriber_lname != '' limit 1");
      $namecolor = ($res['count'] > 0) ? "black" : "#ff7777";

      ++$encount;
      $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
      echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
      $lcount = 1;
      $rcount = 0;
      $oldcode = "";

      $ptname = $name['fname'] . " " . $name['lname'];
      $raw_encounter_date = date("Y-m-d", strtotime($iter['enc_date']));

      $lhtml .= "&nbsp;<span class=bold><font color='$namecolor'>$ptname" .
        "</font></span><span class=small>&nbsp;(" . $iter['enc_pid'] . "-" .
        $iter['enc_encounter'] . ")</span>";

      $lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" " .
        "href=\"javascript:window.toencounter(" . $iter['enc_pid'] .
        ",'" . addslashes($ptname) . "'," . $iter['enc_encounter'] .
        ",'$raw_encounter_date')\">[To&nbsp;Encounter]</a>";

      $lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" " .
        "href=\"javascript:window.topatient(" . $iter['enc_pid'] .
        ")\">[To&nbsp;Demographics]</a>";

      if ($iter['id']) {

        $lcount += 2;
        $lhtml .= "<br />\n";
        $lhtml .= "&nbsp;<span class=text>Bill: ";
        $lhtml .= "<select name='claims[" . $this_encounter_id . "][payer]' style='background-color:$bgcolor'>";

        $query = "SELECT id.provider AS id, id.type, id.date, " .
          "ic.x12_default_partner_id AS ic_x12id, ic.name AS provider " .
          "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
          "ic.id = id.provider AND " .
          "id.pid = '" . mysql_escape_string($iter['enc_pid']) . "' AND " .
          "id.date <= '$raw_encounter_date' " .
          "ORDER BY id.type ASC, id.date DESC";

        $result = sqlStatement($query);
        $count = 0;
        $default_x12_partner = $iter['ic_x12id'];
        $prevtype = '';

        while ($row = mysql_fetch_array($result)) {
          if (strcmp($row['type'], $prevtype) == 0) continue;
          $prevtype = $row['type'];
          if (strlen($row['provider']) > 0) {
            // This preserves any existing insurance company selection, which is
            // important when EOB posting has re-queued for secondary billing.
            $lhtml .= "<option value=\"" . $row['id'] . "\"";
            if (($count == 0 && !$iter['payer_id']) || $row['id'] == $iter['payer_id']) {
              $lhtml .= " selected";
              if (!is_numeric($default_x12_partner)) $default_x12_partner = $row['ic_x12id'];
            }
            $lhtml .= ">" . $row['type'] . ": " . $row['provider'] . "</option>";
          }
          $count++;
        }

        $lhtml .= "<option value='-1'>Unassigned</option>\n";
        $lhtml .= "</select>&nbsp;&nbsp;\n";
        $lhtml .= "<select name='claims[" . $this_encounter_id . "][partner]' style='background-color:$bgcolor'>";
        $x = new X12Partner();
        $partners = $x->_utility_array($x->x12_partner_factory());
        foreach ($partners as $xid => $xname) {
          $lhtml .= '<option label="' . $xname . '" value="' . $xid .'"';
          if ($xid == $default_x12_partner) {
            $lhtml .= "selected";
          }
          $lhtml .= '>' . $xname . '</option>';
        }
        $lhtml .= "</select>";
        $lhtml .= "<br>\n&nbsp;" . substr($iter['date'], 0, 16) . " " .
          xl("Encounter was coded");

        $query = "SELECT * FROM claims WHERE " .
          "patient_id = '" . $iter['enc_pid'] . "' AND " .
          "encounter_id = '" . $iter['enc_encounter'] . "' " .
          "ORDER BY version";
        $cres = sqlStatement($query);

        $lastcrow = false;

        while ($crow = sqlFetchArray($cres)) {
          $query = "SELECT id.type, ic.name " .
            "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
            "id.pid = '" . $iter['enc_pid'] . "' AND " .
            "id.provider = '" . $crow['payer_id'] . "' AND " .
            "id.date <= '$raw_encounter_date' AND " .
            "ic.id = id.provider " .
            "ORDER BY id.type ASC, id.date DESC";

          $irow= sqlQuery($query);

          if ($crow['bill_process']) {
            $lhtml .= "<br>\n&nbsp;" . substr($crow['bill_time'], 0, 16) . " " .
              xl("Queued for") . " {$irow['type']} {$crow['target']} " .
              xl("billing to ") . $irow['name'];
            ++$lcount;
          }
          else if ($crow['status'] > 1) {
            $lhtml .= "<br>\n&nbsp;" . substr($crow['bill_time'], 0, 16) . " " .
              xl("Marked as cleared");
            ++$lcount;
          }
          else {
            $lhtml .= "<br>\n&nbsp;" . substr($crow['bill_time'], 0, 16) . " " .
              xl("Re-opened");
            ++$lcount;
          }

          if ($crow['process_time']) {
            $lhtml .= "<br>\n&nbsp;" . substr($crow['process_time'], 0, 16) . " " .
              xl("Claim was generated to file ") .
              "<a href='get_claim_file.php?key=" . $crow['process_file'] .
              "' onclick='top.restoreSession()'>" .
              $crow['process_file'] . "</a>";
            ++$lcount;
          }

          $lastcrow = $crow;
        } // end while ($crow = sqlFetchArray($cres))

        if ($lastcrow && $lastcrow['status'] == 4) {
          $lhtml .= "<br>\n&nbsp;This claim has been closed.";
          ++$lcount;
        }

        if ($lastcrow && $lastcrow['status'] == 5) {
          $lhtml .= "<br>\n&nbsp;This claim has been canceled.";
          ++$lcount;
        }
      } // end if ($iter['id'])

    } // end if ($last_encounter_id != $this_encounter_id)

    if ($skipping) continue;

    ++$rcount;

    if ($rhtml) {
        $rhtml .= "<tr bgcolor='$bgcolor'>\n";
    }
    $rhtml .= "<td width='50'>";
    if ($iter['id'] && $oldcode != $iter['code_type']) {
        $rhtml .= "<span class=text>" . $iter['code_type'] . ": </span>";
    }

    $oldcode = $iter['code_type'];
    $rhtml .= "</td>\n";
    $justify = "";

    if ($iter['id'] && $code_types[$iter['code_type']]['just']) {
      $js = split(":",$iter['justify']);
      $counter = 0;
      foreach ($js as $j) {
        if(!empty($j)) {
          if ($counter == 0) {
            $justify .= " (<b>$j</b>)";
          }
          else {
            $justify .= " ($j)";
          }
          $counter++;
        }
      }
    }

    $rhtml .= "<td><span class=text>" . $iter{"code"}. "</span>" . '<span style="font-size:8pt;">' . $justify . "</span></td>\n";
    $rhtml .= '<td align="right"><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
    if ($iter['id'] && $iter['fee'] > 0) {
      $rhtml .= '$' . $iter['fee'];
    }
    $rhtml .= "</span></td>\n";
    $rhtml .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
    if ($iter['id']) $rhtml .= getProviderName($iter['provider_id']);
    $rhtml .= "</span></td>\n";
    $rhtml .= '<td width=100>&nbsp;&nbsp;&nbsp;<span style="font-size:8pt;">';
    if ($iter['id']) $rhtml .= date("Y-m-d",strtotime($iter{"date"}));
    $rhtml .= "</span></td>\n";
    if ($iter['id'] && $iter['authorized'] != 1) {
      $rhtml .= "<td><span class=alert>".xl("Note: This code was not entered by an authorized user. Only authorized codes may be uploaded to the Open Medical Billing Network for processing. If you wish to upload these codes, please select an authorized user here.")."</span></td>\n";
    }
    else {
      $rhtml .= "<td></td>\n";
    }
    if ($iter['id'] && $last_encounter_id != $this_encounter_id) {
      $tmpbpr = $iter['bill_process'];
      if ($tmpbpr == '0' && $iter['billed']) $tmpbpr = '2';
      $rhtml .= "<td><input type='checkbox' value='$tmpbpr' name='claims[" . $this_encounter_id . "][bill]' onclick='set_button_states()'>&nbsp;</td>\n";
    }
    else {
      $rhtml .= "<td></td>\n";
    }
    $rhtml .= "</tr>\n";
    $last_encounter_id = $this_encounter_id;

  } // end foreach

  if ($lhtml) {
    while ($rcount < $lcount) {
      $rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'>&nbsp;</td></tr>";
      ++$rcount;
    }
    echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
    echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
  }

}

?>

</table>
</form>

<script>
set_button_states();
<?php
if ($alertmsg) {
  echo "alert('$alertmsg');\n";
}
?>
</script>

</body>
</html>
