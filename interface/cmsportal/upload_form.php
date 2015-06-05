<?php
/**
 * Handles file uploads from the WordPress Patient Portal.
 *
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
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
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("portal.inc.php");

// This function builds an array of document categories recursively.
// Borrowed from interface/fax/fax_dispatch.php.
//
function getKittens($catid, $catstring, &$categories) {
  $cres = sqlStatement("SELECT id, name FROM categories " .
    "WHERE parent = ? ORDER BY name", array($catid));
  $childcount = 0;
  while ($crow = sqlFetchArray($cres)) {
    ++$childcount;
    getKittens($crow['id'], ($catstring ? "$catstring / " : "") .
      ($catid ? $crow['name'] : ''), $categories);
  }
  // If no kitties, then this is a leaf node and should be listed.
  if (!$childcount) $categories[$catid] = $catstring;
}

$postid    = empty($_REQUEST['postid'   ]) ? 0 : intval($_REQUEST['postid'   ]);
$messageid = empty($_REQUEST['messageid']) ? 0 : intval($_REQUEST['messageid']);

if ($_POST['bn_save']) {
  $ptid = intval($_POST['ptid']);
  echo "<html><body>\n";
  if (is_array($_POST['form_filename'])) {
    foreach ($_POST['form_filename'] as $uploadid => $filename) {
      $catid = $_POST['form_category'][$uploadid];
      if (!$catid) continue;
      echo xlt('Fetching following file from portal') . ": " . $filename . " ...<br />\n";
      flush();
      if ($messageid) {
        $result = cms_portal_call(array('action' => 'getmsgup', 'uploadid' => $uploadid));
      }
      else {
        $result = cms_portal_call(array('action' => 'getupload', 'uploadid' => $uploadid));
      }
      if ($result['errmsg']) die(text($result['errmsg']));
      $d = new Document();
      // With JSON-over-HTTP we would need to base64_decode the contents.
      $rc = $d->createDocument($ptid, $catid, $filename, $result['mimetype'],
        $result['contents']);
      if ($rc) die(text(xl('Error saving document') . ": $rc"));
    }
  }
  // Finally, delete the request or message from the portal.
  if ($messageid) {
    $result = cms_portal_call(array('action' => 'delmessage', 'messageid' => $messageid));
  }
  else {
    $result = cms_portal_call(array('action' => 'delpost', 'postid' => $postid));
  }
  if ($result['errmsg']) {
    die(text($result['errmsg']));
  }
  echo "<script language='JavaScript'>\n";
  echo "if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();\n";
  echo "document.location.href = 'list_requests.php';\n";
  echo "</script></body></html>\n";
  exit();
}

// Get the document categories list.
$categories = array();
getKittens(0, '', $categories);

// Get the portal request data.
if (!$postid && !$messageid) die(xlt('Request ID is missing!'));
if ($messageid) {
  $result = cms_portal_call(array('action' => 'getmessage', 'messageid' => $messageid));
}
else {
  $result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
}
if ($result['errmsg']) {
  die(text($result['errmsg']));
}

// Look up the patient in OpenEMR.
$userlogin = $messageid ? $result['message']['user'] : $result['post']['user'];
$ptid = lookup_openemr_patient($userlogin);
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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function myRestoreSession() {
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

</script>
</head>

<body class="body_top">
<center>

<form method='post' action='upload_form.php'>

<?php
if ($messageid) {
  echo "<p class='text'><b>" . xlt('Message Title') . ":</b> ";
  echo htmlspecialchars($result['message']['title']);
  echo "</p>\n";
  echo "<textarea style='width:90%;height:144pt;' readonly>";
  echo htmlspecialchars($result['message']['contents']);
  echo "</textarea>\n";
  echo "<p class='text'><i>";
  echo xlt('This message text is not saved automatically. Copy and save it as appropriate for the content.');
  echo "</i></p>\n";
}
?>

<input type='hidden' name='ptid'      value='<?php echo attr($ptid);      ?>' />
<input type='hidden' name='postid'    value='<?php echo attr($postid);    ?>' />
<input type='hidden' name='messageid' value='<?php echo attr($messageid); ?>' />

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <th align='left'><?php echo xlt('MIME Type'); ?></th>
  <th align='left'><?php echo xlt('Desired Filename'); ?></th>
  <th align='left'><?php echo xlt('Document Category or Discard'); ?></th>
 </tr>
<?php
if (is_array($result['uploads'])) {
  foreach ($result['uploads'] as $upload) {
    $id = intval($upload['id']);
    echo " <tr class='detail'>\n";
    // MIME type and view link
    echo "  <td><a href='upload_form_show.php?id=$id&messageid=$messageid'>" .
      text($upload['mimetype']) . "</a></td>\n";
    // Desired file name
    echo "  <td><input type='text' name='form_filename[$id]' value='" .
      attr($upload['filename']) . "' size='20' /></td>";
    // Desired document category with option to discard the file
    echo "  <td><select name='form_category[$id]'>\n";
    echo "<option value='0'>-- " . xlt('Discard') . " --</option>\n";
    $i = 0;
    foreach ($categories as $catkey => $catname) {
      echo "<option value='" . attr($catkey) . "'";
      if (++$i == 1) echo " selected";
      echo ">" . text($catname) . "</option>\n";
    }
    echo "</select></td>\n";
    //
    echo " </tr>\n";
  }
}
?>
</table>

<p>
<input type='submit' name='bn_save' value='<?php echo xla('Submit and Delete Request'); ?>' />
&nbsp;
<input type='button' value='<?php echo xla('Back'); ?>'
 onclick="myRestoreSession();location='list_requests.php'" />
</p>

</form>
</center>
</body>
</html>

