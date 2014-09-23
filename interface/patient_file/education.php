<?php
/**
 * This is called as a pop-up to display patient education materials.
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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 */

$sanitize_all_escapes  = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");

$educationdir = "$OE_SITE_DIR/documents/education";

$codetype  = empty($_REQUEST['type'    ]) ? '' : $_REQUEST['type'    ];
$codevalue = empty($_REQUEST['code'    ]) ? '' : $_REQUEST['code'    ];
$language  = empty($_REQUEST['language']) ? '' : strtolower($_REQUEST['language']);
$source    = empty($_REQUEST['source'  ]) ? '' : $_REQUEST['source'  ];

$errmsg = '';

if ($_POST['bn_submit']) {
  if ($source == 'MLP') {
    // MedlinePlus Connect Web Application.  See:
    // http://www.nlm.nih.gov/medlineplus/connect/application.html
    $url = 'http://apps.nlm.nih.gov/medlineplus/services/mpconnect.cfm';
    // Set code type in URL.
    $url .= '?mainSearchCriteria.v.cs=';
    if ('ICD9'   == $codetype) $url .= '2.16.840.1.113883.6.103'; else
    if ('ICD10'  == $codetype) $url .= '2.16.840.1.113883.6.90' ; else
    if ('SNOMED' == $codetype) $url .= '2.16.840.1.113883.6.96' ; else
    if ('RXCUI'  == $codetype) $url .= '2.16.840.1.113883.6.88' ; else
    if ('NDC'    == $codetype) $url .= '2.16.840.1.113883.6.69' ; else
    if ('LOINC'  == $codetype) $url .= '2.16.840.1.113883.6.1'  ; else
    die(xlt('Code type not recognized') . ': ' . text($codetype));
    // Set code value in URL.
    $url .= '&mainSearchCriteria.v.c=' . urlencode($codevalue);
    // Set language in URL if relevant. MedlinePlus supports only English or Spanish.
    if ($language == 'es' || $language == 'spanish') {
      $url .= '&informationRecipient.languageCode.c=es';
    }
    // There are 2 different ways to get the data: have the server do it, or
    // have the browser do it.
    if (false) {
      $data = file_get_contents($url);
      echo $data;
    }
    else {
      echo "<html><body><script language='JavaScript'>\n";
      echo "document.location.href = '$url';\n";
      echo "</script></body></html>\n";
    }
    exit();
  }
  else {
    $lang = 'en';
    if ($language == 'es' || $language == 'spanish') $lang = 'es';
    $filename = strtolower("{$codetype}_{$codevalue}_{$lang}.pdf");
    $filepath = "$educationdir/$filename";
    if (is_file($filepath)) {
      header('Content-Description: File Transfer');
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      // attachment, not inline
      header("Content-Disposition: attachment; filename=\"$filename\"");
      header("Content-Type: application/pdf");
      header("Content-Length: " . filesize($filepath));
      ob_clean();
      flush();
      readfile($filepath);
      exit();
    }
    else {
      $errmsg = xl('There is no local content for this topic.');
    }
  }
}
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

</script>
</head>

<body class="body_top">
<center>

<h2>
<?php
echo xlt('Educational materials for');
echo ' ' . text($codetype) . ' ';
echo xlt('code');
echo ' "' . text($codevalue) . '"';
if ($language) {
  echo ' ' . xlt('with preferred language') . ' ' .
    text(getListItemTitle('language', $_REQUEST['language']));
}
?>
</h2>

<?php
  if ($errmsg) echo "<p style='color:red'>" . text($errmsg) . "</p>\n";
?>

<form method='post' action='education.php'>

<input type='hidden' name='type'     value='<?php echo attr($codetype ); ?>' />
<input type='hidden' name='code'     value='<?php echo attr($codevalue); ?>' />
<input type='hidden' name='language' value='<?php echo attr($language ); ?>' />

<p class='bold'>
 <?php echo xlt('Select source'); ?>:
 <select name='source'>
  <option value='MLP'  ><?php echo xlt('MedlinePlus Connect'); ?></option>
  <option value='Local'><?php echo xlt('Local Content'      ); ?></option>
 </select>
</p>

<p>
 <input type='submit' name='bn_submit' value='<?php echo xla('Submit'); ?>' />
 &nbsp;
 <input type='button' value='<?php echo xla('Cancel'); ?>' onclick="window.close()" />
</p>

</form>
</center>
</body>
</html>
