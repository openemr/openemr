<?php
/**
 * Upload and install a designated code set to the codes table.
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
 * @link    http://www.open-emr.org
 */

set_time_limit(0);

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once('../globals.php');
require_once($GLOBALS['srcdir'] . '/acl.inc');
require_once($GLOBALS['srcdir'] . '/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'] . '/formdata.inc.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

if (!acl_check('admin', 'super')) die(xlt('Not authorized'));

$form_replace = !empty($_POST['form_replace']);
$code_type = empty($_POST['form_code_type']) ? '' : $_POST['form_code_type'];
?>
<html>

<head>
<title><?php echo xlt('Install Code Set'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

</head>

<body class="body_top">

<?php
// Handle uploads.
if (!empty($_POST['bn_upload'])) {
  if (empty($code_types[$code_type])) die(xlt('Code type not yet defined') . ": '$code_type'");
  $code_type_id = $code_types[$code_type]['id'];
  $tmp_name = $_FILES['form_file']['tmp_name'];

  $inscount = 0;
  $repcount = 0;
  $seen_codes = array();

  if (is_uploaded_file($tmp_name) && $_FILES['form_file']['size']) {
    $zipin = new ZipArchive;
    $eres = null;
    if ($zipin->open($tmp_name) === true) {
      // Must be a zip archive.
      for ($i = 0; $i < $zipin->numFiles; ++$i) {
        $ename = $zipin->getNameIndex($i);
        // TBD: Expand the following test as other code types are supported.
        if ($code_type == 'RXCUI' && basename($ename) == 'RXNCONSO.RRF') {
          $eres = $zipin->getStream($ename);
          break;
        }
      }
    }
    else {
      $eres = fopen($tmp_name, 'r');
    }

    if (empty($eres)) die(xlt('Unable to locate the data in this file.'));

    if ($form_replace) {
      sqlStatement("DELETE FROM codes WHERE code_type = ?", array($code_type_id));
    }

    while (($line = fgets($eres)) !== false) {

      if ($code_type == 'RXCUI') {
        $a = explode('|', $line);
        if (count($a) < 18    ) continue;
        if ($a[17] != '4096'  ) continue;
        if ($a[11] != 'RXNORM') continue;
        $code = $a[0];
        if (isset($seen_codes[$code])) continue;
        $seen_codes[$code] = 1;
        ++$inscount;
        if (!$form_replace) {
          $tmp = sqlQuery("SELECT id FROM codes WHERE code_type = ? AND code = ? LIMIT 1",
            array($code_type_id, $code));
          if ($tmp['count']) {
            sqlStatement("UPDATE codes SET code_text = ? WHERE code_type = ? AND code = ?",
              array($a[14], $code_type_id, $code));
            ++$repcount;
            continue;
          }
        }
        sqlStatement("INSERT INTO codes SET code_type = ?, code = ?, code_text = ?, " .
          "fee = 0, units = 0",
          array($code_type_id, $code, $a[14]));
        ++$inscount;
      }

      // TBD: Clone/adapt the above for each new code type.

    }
    fclose($eres);
    $zipin->close();
  }

  echo "<p style='color:green'>" .
       xlt('LOAD SUCCESSFUL. Codes inserted') . ": $inscount, " .
       xlt('replaced') . ": $repcount" .
       "</p>\n";
}

?>
<form method='post' action='load_codes.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>

<center>

<p class='text'>
<table border='1' cellpadding='4'>
 <tr bgcolor='#dddddd' class='dehead'>
  <td align='center' colspan='2'>
   <?php echo xlt('Install Code Set'); ?>
  </td>
 </tr>
 <tr>
  <td class='detail' nowrap>
   <?php echo xlt('Code Type'); ?>
  </td>
  <td>
   <select name='form_code_type'>
<?php
foreach (array('RXCUI') as $codetype) {
  echo "    <option value='$codetype'>$codetype</option>\n";
}
?>
   </select>
  </td>
 </tr>
 <tr>
  <td class='detail' nowrap>
   <?php echo htmlspecialchars(xl('Source File')); ?>
   <input type="hidden" name="MAX_FILE_SIZE" value="350000000" />
  </td>
  <td class='detail' nowrap>
   <input type="file" name="form_file" size="40" />
  </td>
 </tr>
 <tr>
  <td class='detail' >
   <?php echo xlt('Replace entire code set'); ?>
  </td>
  <td class='detail' >
   <input type='checkbox' name='form_replace' value='1' checked />
  </td>
 </tr>
 <tr bgcolor='#dddddd'>
  <td align='center' class='detail' colspan='2'>
   <input type='submit' name='bn_upload' value='<?php echo xlt('Upload and Install') ?>' />
  </td>
 </tr>
</table>
</p>

<p class='bold'><?php echo xlt('Be patient, some files can take several minutes to process!'); ?></p>

</center>

<!-- No translation because this text is long and US-specific and quotes other English-only text. -->
<p class='text'>
<b>RXCUI codes</b> may be downloaded from
<a href='http://www.nlm.nih.gov/research/umls/rxnorm/docs/rxnormfiles.html' target='_blank'>
www.nlm.nih.gov/research/umls/rxnorm/docs/rxnormfiles.html</a>.
Get the "Current Prescribable Content Monthly Release" zip file, marked "no license required".
Then you can upload that file as-is here, or extract the file RXNCONSO.RRF from it and upload just
that (zipped or not). You may do the same with the weekly updates, but for those uncheck the
"<?php echo xlt('Replace entire code set'); ?>" checkbox above.
</p>

<!-- TBD: Another paragraph of instructions here for each code type. -->

</form>
</body>
</html>
