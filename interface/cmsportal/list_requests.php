<?php
/**
* Fetch and list pending requests from the WordPress portal.
*
* Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by the Free
* Software Foundation; either version 2 of the License, or (at your option) any
* later version.
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with
* this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("portal.inc.php");

/**
 * Get a list item title, translating if required.
 *
 * @param  string  $listid  List identifier.
 * @param  string  $value   List item identifier.
 * @return string  The item's title.
 */
function getListItem($listid, $value) {
  $lrow = sqlQuery("SELECT title FROM list_options " .
    "WHERE list_id = ? AND option_id = ?",
    array($listid, $value));
  $tmp = xl_list_label($lrow['title']);
  if (empty($tmp)) $tmp = "($report_status)";
  return $tmp;
}

/**
 * Adapt text to be suitable as the contents of a table cell.
 *
 * @param  string  $s  Input text.
 * @return string  Output text.
 */
function myCellText($s) {
  if ($s === '') return '&nbsp;';
  return text($s);
}

// Get patient name from OpenEMR, or empty if not there.
function patientNameFromLogin($login) {
  $ptname = '';
  if ($login) {
    $tmp = sqlQuery("SELECT fname, lname, mname, pid " .
      "FROM patient_data WHERE cmsportal_login = ? ORDER BY id LIMIT 1",
      array($login));
    if (!empty($tmp['pid'])) {
      $ptname = $tmp['lname'];
      if ($tmp['fname'] || $tmp['mname']) $ptname .= ',';
      if ($tmp['fname']) $ptname .= ' ' . $tmp['fname'];
      if ($tmp['mname']) $ptname .= ' ' . $tmp['mname'];
    }
  }
  return $ptname;
}

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xlt('Not authorized'));

$errmsg = '';

// If Delete clicked, delete selected posts/messages from the portal.
if (!empty($_POST['bn_delete'])) {
  if (is_array($_POST['form_req_cb'])) {
    foreach ($_POST['form_req_cb'] as $postid) {
      $result = cms_portal_call(array('action' => 'delpost', 'postid' => $postid));
      if ($result['errmsg']) {
        die(text($result['errmsg']));
      }
    }
  }
  if (is_array($_POST['form_msg_cb'])) {
    foreach ($_POST['form_msg_cb'] as $messageid) {
      $result = cms_portal_call(array('action' => 'delmessage', 'messageid' => $messageid));
      if ($result['errmsg']) {
        die(text($result['errmsg']));
      }
    }
  }
}
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Portal Requests'); ?></title>

<style>

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function myRestoreSession() {
 // This works whether we are a popup or in the OpenEMR frameset.
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

function openRequest(postid, type) {
 myRestoreSession();
 // To open results in a new window. The options parameter serves to defeat
 // Firefox's "open windows in a new tab".
 // window.open('single_order_results.php?orderid=' + orderid, '_blank',
 //  'toolbar=0,location=0,menubar=0,scrollbars=yes');
 //
 // To open results in the same frame:
 if (type.indexOf('Demographics') == 0) {
  document.location.href = 'patient_select.php?postid=' + postid;
 } else
 if (type.indexOf('Insurance') == 0) {
  document.location.href = 'insurance_form.php?postid=' + postid;
 } else
 if (type.indexOf('History') == 0) {
  document.location.href = 'history_form.php?postid=' + postid;
 } else
 if (type.indexOf('Issue') == 0) {
  document.location.href = 'issue_form.php?postid=' + postid;
 } else
 if (type.indexOf('LBF') == 0) {
  document.location.href = 'lbf_form.php?postid=' + postid;
 } else
 if (type.indexOf('Upload') == 0) {
  document.location.href = 'upload_form.php?postid=' + postid;
 } else

 // TBD: more types to be handled

 {
  alert('<?php echo xla('Request type not implemented') ?>: ' + type);
 }
 //
 // To open results in the "other" frame:
 // var w = window;
 // var othername = (w.name == 'RTop') ? 'RBot' : 'RTop';
 // w.parent.left_nav.forceDual();
 // w.parent.left_nav.setRadio(othername, 'ore');
 // w.parent.left_nav.loadFrame('ore1', othername, 'orders/single_order_results.php?orderid=' + orderid);
}

function openMessage(messageid) {
 myRestoreSession();
 document.location.href = 'upload_form.php?messageid=' + messageid;
}

</script>

</head>

<body class="body_top">
<form method='post' action='list_requests.php' onsubmit='return myRestoreSession()'>

<?php
$form_from_date = empty($_POST['form_from_date']) ? '' : trim($_POST['form_from_date']);
$form_to_date = empty($_POST['form_to_date']) ? '' : trim($_POST['form_to_date']);
// if (empty($form_to_date)) $form_to_date = $form_from_date;

$form_patient = !empty($_POST['form_patient']);

// Post a form to the WP portal that asks for the request list and get the response.
// Write a row for each request that is reported.

$result = cms_portal_call(array(
  'action'    => 'list',
  'date_from' => $form_from_date,
  'date_to'   => $form_to_date,
));

if ($result['errmsg']) {
  echo "<font color='red'>" . text($result['errmsg']) . "</font><br />\n";
}
?>
<center>

<table width='100%'>
 <tr>
  <td class='text' align='center'>
   <?php echo xlt('From'); ?>:
   <input type='text' size='8' name='form_from_date' id='form_from_date'
    value='<?php echo attr($form_from_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />
   &nbsp;
   <?php echo xlt('To'); ?>:
   <input type='text' size='8' name='form_to_date' id='form_to_date'
    value='<?php echo attr($form_to_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />
   &nbsp;
   <input type='submit' name='form_refresh' value=<?php echo xla('Submit'); ?>>
  </td>
 </tr>
</table>

<table width='100%' cellpadding='1' cellspacing='2'>

 <tr class='head'>
  <th colspan='2'><?php echo xlt('Patient'); ?></td>
  <th colspan='3'><?php echo xlt('CMS Portal Request'); ?></td>
 </tr>

 <tr class='head'>
  <th><?php echo xlt('Portal ID'   ); ?></td>
  <th><?php echo xlt('Name in EMR' ); ?></td>
  <th><?php echo xlt('Date/Time'   ); ?></td>
  <th><?php echo xlt('Request Type'); ?></td>
  <th><?php echo xlt('Delete'      ); ?></td>
 </tr>

<?php
// Generate a table row for each pending portal request or message.
// This logic merges requests with messages by date.
$v1 = each($result['list']);
$v2 = each($result['messages']);
while ($v1 || $v2) {
  echo " <tr class='detail' bgcolor='#ddddff'>\n";
  if (!$v2 || $v1 && $v1[1]['datetime'] < $v2[1]['datetime']) {
    $postid = $v1[1]['postid'];
    $ptname = patientNameFromLogin($v1[1]['user']);
    echo "  <td>" . text($v1[1]['user']) . "</td>\n";
    echo "  <td>" . text($ptname       ) . "</td>\n";
    echo "  <td style='cursor:pointer;color:blue;'";
    echo " onclick=\"openRequest(" .
         "'" . addslashes($postid)      . "'," .
         "'" . addslashes($v1[1]['type']) . "'"  .
         ")\">" . text($v1[1]['datetime']) . "</td>\n";
    echo "  <td>" . text($v1[1]['type'    ]) . "</td>\n";
    echo "  <td align='center'><input type='checkbox' name='form_req_cb[" .
         attr($postid) . "]' value='" . attr($postid) . "' /></td>\n";
    $v1 = each($result['list']);
  }
  else {
    $messageid = $v2[1]['messageid'];
    $ptname = patientNameFromLogin($v2[1]['user']);
    echo "  <td>" . text($v2[1]['user']) . "</td>\n";
    echo "  <td>" . text($ptname       ) . "</td>\n";
    echo "  <td style='cursor:pointer;color:blue;'";
    echo " onclick=\"openMessage(" .
         "'" . addslashes($messageid)      . "'" .
         ")\">" . text($v2[1]['datetime']) . "</td>\n";
    echo "  <td>" . text($v2[1]['user'] == $v2[1]['fromuser'] ?
         xl('Message from patient') : xl('Message to patient')) . "</td>\n";
    echo "  <td align='center'><input type='checkbox' name='form_msg_cb[" .
         attr($messageid) . "]' value='" . attr($messageid) . "' /></td>\n";
    $v2 = each($result['messages']);
  }
  echo " </tr>\n";
}
?>

</table>

<p>
<input type='button' value='<?php echo xla('Close Window'); ?>' onclick="window.close();" />
&nbsp;
<input type='submit' name='bn_delete' value='<?php echo xla('Delete Selected Requests'); ?>' />
</p>

</center>

<script language='JavaScript'>

// Initialize calendar widgets for "from" and "to" dates.
Calendar.setup({inputField:'form_from_date', ifFormat:'%Y-%m-%d',
 button:'img_from_date'});
Calendar.setup({inputField:'form_to_date', ifFormat:'%Y-%m-%d',
 button:'img_to_date'});

</script>

</form>
</body>
</html>
