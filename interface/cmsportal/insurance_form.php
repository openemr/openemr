<?php
/**
 * Insurance form posting for the WordPress Patient Portal.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 */

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("portal.inc.php");

// Consider this a step towards converting the insurance form to layout-based.
// Faking it here makes things easier.
// Also note that some fields like SSN and most of the subscriber employer
// items have been omitted because they are not relevant for claims.
//
$insurance_layout = array(
  array('field_id'     => 'type',
        'title'        => 'Type',
        'uor'          => '2',
        'data_type'    => '1',
        'list_id'      => 'insurance_types',
        'edit_options' => '',
       ),
  array('field_id'     => 'date',
        'title'        => 'Effective Date',
        'uor'          => '2',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'provider',
        'title'        => 'Provider',
        'uor'          => '2',
        'data_type'    => '16',              // Insurance Providers
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'plan_name',
        'title'        => 'Plan Name',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'policy_number',
        'title'        => 'Policy Number',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'group_number',
        'title'        => 'Group Number',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_employer',
        'title'        => 'Group Name',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_lname',
        'title'        => 'Subscriber Last Name',
        'uor'          => '2',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_fname',
        'title'        => 'Subscriber First Name',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_mname',
        'title'        => 'Subscriber Middle Name',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_DOB',
        'title'        => 'Subscriber DOB',
        'uor'          => '2',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_sex',
        'title'        => 'Subscriber Sex',
        'uor'          => '2',
        'data_type'    => '1',               // List
        'list_id'      => 'sex',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_relationship',
        'title'        => 'Subscriber Relationship',
        'uor'          => '2',
        'data_type'    => '1',               // List
        'list_id'      => 'sub_relation',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_street',
        'title'        => 'Subscriber Street',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_city',
        'title'        => 'Subscriber City',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_state',
        'title'        => 'Subscriber State',
        'uor'          => '1',
        'data_type'    => '1',               // List
        'list_id'      => 'state',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_postal_code',
        'title'        => 'Subscriber Zip',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'subscriber_phone',
        'title'        => 'Subscriber Phone',
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
);

$postid = intval($_REQUEST['postid']);

if ($_POST['bn_save']) {
  $newdata = array();
  $ptid = intval($_POST['ptid']);
  foreach ($insurance_layout as $frow) {
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    // newInsuranceData() does not escape for mysql so we have to do it here.
    $newdata[$field_id] = add_escape_custom(get_layout_form_value($frow));
  }
  newInsuranceData(
    $ptid,
    $newdata['type'],
    $newdata['provider'],
    $newdata['policy_number'],
    $newdata['group_number'],
    $newdata['plan_name'],
    $newdata['subscriber_lname'],
    $newdata['subscriber_mname'],
    $newdata['subscriber_fname'],
    $newdata['subscriber_relationship'],
    '',                                    // subscriber_ss
    fixDate($newdata['subscriber_DOB']),
    $newdata['subscriber_street'],
    $newdata['subscriber_postal_code'],
    $newdata['subscriber_city'],
    $newdata['subscriber_state'],
    '',                                    // subscriber_country
    $newdata['subscriber_phone'],
    $newdata['subscriber_employer'],
    '',                                    // subscriber_employer_street
    '',                                    // subscriber_employer_city
    '',                                    // subscriber_employer_postal_code
    '',                                    // subscriber_employer_state
    '',                                    // subscriber_employer_country
    '',                                    // copay
    $newdata['subscriber_sex'],
    fixDate($newdata['date']),
    'TRUE',                                // accept_assignment
    ''                                     // policy_type
  );
  // Finally, delete the request from the portal.
  $result = cms_portal_call(array('action' => 'delpost', 'postid' => $postid));
  if ($result['errmsg']) {
    die(text($result['errmsg']));
  }
  echo "<html><body><script language='JavaScript'>\n";
  echo "if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();\n";
  echo "document.location.href = 'list_requests.php';\n";
  echo "</script></body></html>\n";
  exit();
}

// Get the portal request data.
if (!$postid) die(xlt('Request ID is missing!'));
$result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
if ($result['errmsg']) {
  die(text($result['errmsg']));
}

// Look up the patient in OpenEMR.
$ptid = lookup_openemr_patient($result['post']['user']);
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style>

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; background-color:#ddddff; }
td input  { background-color:transparent; }

</style>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function myRestoreSession() {
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

// This capitalizes the first letter of each word in the passed input
// element.  It also strips out extraneous spaces.
// Copied from demographics_full.php.
function capitalizeMe(elem) {
 var a = elem.value.split(' ');
 var s = '';
 for(var i = 0; i < a.length; ++i) {
  if (a[i].length > 0) {
   if (s.length > 0) s += ' ';
   s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
  }
 }
 elem.value = s;
}

function validate() {
 var f = document.forms[0];
 // TBD
 return true;
}

</script>
</head>

<body class="body_top">
<center>

<form method='post' action='insurance_form.php' onsubmit='return validate()'>

<input type='hidden' name='ptid'   value='<?php echo attr($ptid);   ?>' />
<input type='hidden' name='postid' value='<?php echo attr($postid); ?>' />

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <th align='left'><?php echo xlt('Field'        ); ?></th>
  <th align='left'><?php echo xlt('Current Value'); ?></th>
  <th align='left'><?php echo xlt('New Value'    ); ?></th>
 </tr>

<?php
$insrow = getInsuranceData($pid, $result['fields']['type']);

foreach ($insurance_layout as $lorow) {
  $data_type  = $lorow['data_type'];
  $field_id   = $lorow['field_id'];

  $list_id = $lorow['list_id'];
  $field_title = $lorow['title'];

  $currvalue  = '';
  if (isset($insrow[$field_id])) $currvalue = $insrow[$field_id];

  $newvalue = '';
  if (isset($result['fields'][$field_id])) $newvalue = trim($result['fields'][$field_id]);

  // Translate $newvalue for certain field types including lists.
  if ($newvalue !== '') {
    if ($list_id) {
      $tmp = sqlQuery("SELECT option_id FROM list_options WHERE " .
        "list_id = ? AND title = ? ORDER BY option_id LIMIT 1",
        array($list_id, $newvalue));      
      if (isset($tmp['option_id'])) $newvalue = $tmp['option_id'];
    }
    // Some data types like insurance provider are pretty hopeless, so let the display
    // logic generate a "Fix me" message and the user can translate it.
  }

  echo " <tr class='detail'>\n";
  echo "  <td class='bold'>" . text($field_title) . "</td>\n";
  echo "  <td>";
  echo generate_display_field($lorow, $currvalue);
  echo "</td>\n";
  echo "  <td>";
  generate_form_field($lorow, $newvalue);
  echo "</td>\n";
  echo " </tr>\n";
}
?>

</table>

<p>
<input type='submit' name='bn_save' value='<?php echo xla('Save and Delete Request'); ?>' />
&nbsp;
<input type='button' value='<?php echo xla('Back'); ?>' onclick="window.back()" />
<!-- Was: onclick="myRestoreSession();location='list_requests.php'" -->
</p>

</form>

<script language="JavaScript">

// Fix inconsistently formatted phone numbers from the database.
var f = document.forms[0];
if (f.form_phone) phonekeyup(f.form_phone, mypcc);

// This is a by-product of generate_form_field().
<?php echo $date_init; ?>

</script>

<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

</center>
</body>
</html>

