<?php

/**
 * Upload designated service codes as "services=" attributes for designated layouts.
 * This supports specifying related codes to determine the service codes to be used.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    die(xlt('Not authorized'));
}

$form_dryrun = !empty($_POST['form_dryrun']);

function applyCode($layoutid, $codetype, $code, $description)
{
    global $thecodes;
    if (!isset($thecodes[$layoutid])) {
        $thecodes[$layoutid] = array();
    }
    $thecodes[$layoutid]["$codetype:$code"] = $description;
}

?>
<html>

<head>
<title><?php echo xlt('Install Layout Service Codes'); ?></title>
<?php Header::setupHeader(); ?>

<style>
 .dehead {
   color: var(--black);
   font-family: sans-serif;
   font-size: 0.8125rem;
   font-weight: bold;
  }
 .detail {
   color: var(--black);
   font-family: sans-serif;
   font-size: 0.8125rem;
   font-weight:normal;
 }
</style>

</head>

<body class="body_top">

<?php
// Handle uploads.
if (!empty($_POST['bn_upload'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $thecodes = array();
    $tmp_name = $_FILES['form_file']['tmp_name'];

    if (is_uploaded_file($tmp_name) && $_FILES['form_file']['size']) {
        $fhcsv = fopen($tmp_name, 'r');
        if (empty($fhcsv)) {
            die(xlt('Cannot open') . text(" '$tmp_name'"));
        }

      // Columns are:
      // 0 - Layout ID, e.g. LBFVIA
      // 1 - Code type, e.g. IPPF2
      // 2 - Code
      //
        while (!feof($fhcsv)) {
            $codecount = 0;
            $acsv = fgetcsv($fhcsv, 1024);
            if (count($acsv) < 3) {
                continue;
            }
            $layoutid = trim($acsv[0]);
            $codetype = trim($acsv[1]);
            $code     = trim($acsv[2]);
            if (empty($layoutid) || empty($codetype) || empty($code)) {
                continue;
            }
          // If this is already a Fee Sheet code, add it.
            if (empty($code_types[$codetype]['nofs'])) {
                applyCode($layoutid, $codetype, $code, xl('Direct'));
                ++$codecount;
            }
          // Add all Fee Sheet codes that relate to this code.
            foreach ($code_types as $ct_key => $ct_arr) {
                if (!$ct_arr['active'] || $ct_arr['nofs']) {
                    continue;
                }
                $tmp = "$codetype:$code";
                $relres = sqlStatement(
                    "SELECT code, code_text FROM codes WHERE code_type = ? AND " .
                    "(related_code LIKE ? OR related_code LIKE ? OR related_code LIKE ? OR related_code LIKE ?) " .
                    "AND active = 1 ORDER BY code",
                    array($ct_arr['id'], $tmp, "$tmp;%", "%;$tmp", "%;$tmp;%")
                );
                while ($relrow = sqlFetchArray($relres)) {
                    applyCode($layoutid, $ct_key, $relrow['code'], $relrow['code_text']);
                    ++$codecount;
                }
            }
            if ($codecount == 0) {
                echo "<p style='color:red'>" . xlt('No matches for') . " '" . text($tmp) . "'.</p>\n";
            }
        } // end while
        fclose($eres);

      // Now zap the found service codes into the parameters for each layout.
        foreach ($thecodes as $layoutid => $arr) {
            $services = '';
            foreach ($arr as $key => $description) {
                if ($services) {
                    $services .= ';';
                }
                $services .= $key;
            }
            if (!$form_dryrun) {
                sqlStatement(
                    "UPDATE layout_group_properties SET grp_services = ? WHERE " .
                    "grp_form_id = ? AND grp_group_id = ''",
                    array($services, $layoutid)
                );
            }
        }
    } // end upload logic
}

?>
<form method='post' action='layout_service_codes.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p class='text'>
<table border='1' cellpadding='4'>
 <tr bgcolor='#dddddd' class='dehead'>
  <td align='center' colspan='2'>
    <?php echo xlt('Install Layout Service Codes'); ?>
  </td>
 </tr>
 <tr>
  <td class='detail' nowrap>
    <?php echo xlt('Source CSV File'); ?>
   <input type="hidden" name="MAX_FILE_SIZE" value="350000000" />
  </td>
  <td class='detail' nowrap>
   <input type="file" name="form_file" size="40" />
  </td>
 </tr>
 <tr>
  <td class='detail' nowrap>
    <?php echo xlt('Test only, skip updates'); ?>
  </td>
  <td class='detail' >
   <input type='checkbox' name='form_dryrun' value='1' checked />
  </td>
 </tr>
 <tr bgcolor='#dddddd'>
  <td align='center' class='detail' colspan='2'>
   <input type='submit' name='bn_upload' value='<?php echo xla('Upload and Apply') ?>' />
  </td>
 </tr>
</table>
</p>

<p>
<?php echo xlt('The input should be a CSV file with 3 columns: layout ID, code type and code.'); ?>
</p>

<p class='text'>
<table border='1' cellpadding='4'>
 <tr bgcolor='#dddddd' class='dehead'>
  <td><?php echo xlt('Category'); ?></td>
  <td><?php echo xlt('Layout'); ?></td>
  <td><?php echo xlt('Code'); ?></td>
  <td><?php echo xlt('Description'); ?></td>
 </tr>
<?php
$lastcat = '';
$lastlayout = '';

$res = sqlStatement("SELECT grp_form_id, grp_title, grp_mapping, grp_services FROM layout_group_properties " .
  "WHERE grp_group_id = '' AND grp_activity = 1 AND grp_services != '' ORDER BY grp_mapping, grp_title, grp_form_id");

while ($row = sqlFetchArray($res)) {
  // $jobj = json_decode($row['notes'], true);
    if ($row['grp_services'] == '*') {
        $row['grp_services'] = '';
    }
    $codes = explode(';', $row['grp_services']);
    foreach ($codes as $codestring) {
        echo " <tr>\n";

        echo "  <td class='detail'>";
        if ($row['grp_mapping'] != $lastcat) {
            $lastcat = $row['grp_mapping'];
            echo text($lastcat);
        }
        echo "&nbsp;</td>\n";

        echo "  <td class='detail'>";
        if ($row['grp_form_id'] != $lastlayout) {
            $lastlayout = $row['grp_form_id'];
            echo text($row['grp_title']);
        }
        echo "&nbsp;</td>\n";

        echo "  <td class='detail'>";
        echo text($codestring);
        echo "</td>\n";

        echo "  <td class='detail'>\n";
        list ($codetype, $code) = explode(':', $codestring);
        $crow = sqlQuery(
            "SELECT code_text FROM codes WHERE " .
            "code_type = ? AND code = ? AND active = 1 " .
            "ORDER BY id LIMIT 1",
            array($code_types[$codetype]['id'], $code)
        );
        echo text($crow['code_text']);
        echo "&nbsp;</td>\n";
        echo " </tr>\n";
    }
}
?>
</table>
</p>

</center>

</form>
</body>
</html>
