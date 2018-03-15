<?php
/**
 * Document Template Management Module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');

if (!acl_check('admin', 'super')) {
    die(htmlspecialchars(xl('Not authorized')));
}

$form_filename = strip_escape_custom($_REQUEST['form_filename']);

$templatedir = "$OE_SITE_DIR/documents/doctemplates";

// If downloading a file, do the download and nothing else.
// Thus the current browser page should remain displayed.
//
if (!empty($_POST['bn_download'])) {
    $templatepath = "$templatedir/$form_filename";
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
  // attachment, not inline
    header("Content-Disposition: attachment; filename=\"$form_filename\"");
  // Note we avoid providing a mime type that suggests opening the file.
    header("Content-Type: application/octet-stream");
    header("Content-Length: " . filesize($templatepath));
    ob_clean();
    flush();
    readfile($templatepath);
    exit;
}

if (!empty($_POST['bn_delete'])) {
    $templatepath = "$templatedir/$form_filename";
    if (is_file($templatepath)) {
        unlink($templatepath);
    }
}

if (!empty($_POST['bn_upload'])) {
  // Handle uploads.
    $tmp_name = $_FILES['form_file']['tmp_name'];
    if (is_uploaded_file($tmp_name) && $_FILES['form_file']['size']) {
        // Choose the destination path/filename.
        $form_dest_filename = $_POST['form_dest_filename'];
        if ($form_dest_filename == '') {
            $form_dest_filename = $_FILES['form_file']['name'];
        }

        $form_dest_filename = preg_replace("/[^a-zA-Z0-9_.]/", "_", basename($form_dest_filename));
        if ($form_dest_filename == '') {
            die(htmlspecialchars(xl('Cannot determine a destination filename')));
        }

        $templatepath = "$templatedir/$form_dest_filename";
        // If the site's template directory does not yet exist, create it.
        if (!is_dir($templatedir)) {
            mkdir($templatedir);
        }

        // If the target file already exists, delete it.
        if (is_file($templatepath)) {
            unlink($templatepath);
        }

        // Put the new file in its desired location.
        if (!move_uploaded_file($tmp_name, $templatepath)) {
            die(htmlspecialchars(xl('Unable to create') . " '$templatepath'"));
        }
    }
}

?>
<html>

<head>
<title><?php echo xlt('Document Template Management'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

</head>

<body class="body_top">
<form method='post' action='manage_document_templates.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>

<center>

<h2><?php echo xlt('Document Template Management'); ?></h2>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td align='center'><?php echo xlt('Upload a Template'); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' style='padding:10pt;' nowrap>
    <?php echo htmlspecialchars(xl('Source File')); ?>:
   <input type="hidden" name="MAX_FILE_SIZE" value="250000000" />
   <input type="file" name="form_file" size="40" />&nbsp;
    <?php echo htmlspecialchars(xl('Destination Filename')) ?>:
   <input type='text' name='form_dest_filename' size='30' />
   &nbsp;
   <input type='submit' name='bn_upload' value='<?php echo xlt('Upload') ?>' />
  </td>
 </tr>

</table>
</p>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td align='center'><?php echo xlt('Download or Delete a Template'); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' style='padding:10pt;' nowrap>
   <select name='form_filename'>
<?php
// Generate an <option> for each existing file.
if (file_exists($templatedir)) {
    $dh = opendir($templatedir);
} else {
    $dh = false;
}
if ($dh) {
    $templateslist = array();
    while (false !== ($sfname = readdir($dh))) {
        if (substr($sfname, 0, 1) == '.') {
            continue;
        }

        $templateslist[$sfname] = $sfname;
    }

    closedir($dh);
    ksort($templateslist);
    foreach ($templateslist as $sfname) {
        echo "    <option value='" . htmlspecialchars($sfname, ENT_QUOTES) . "'";
        echo ">" . htmlspecialchars($sfname) . "</option>\n";
    }
}
?>
   </select>
   &nbsp;
   <input type='submit' name='bn_download' value='<?php echo xlt('Download') ?>' />
   &nbsp;
   <input type='submit' name='bn_delete' value='<?php echo xlt('Delete') ?>' />
  </td>
 </tr>

</table>
</p>

</center>

</form>
</body>
</html>

