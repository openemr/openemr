<?php

/**
 * body_composition new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$scale_file_name = '/tmp/tanita_scale.txt';
$scale_file_age = -1;
$row = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname)
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        return "NULL";
    }

    return "$tmp";
}

function rbinput($name, $value, $desc, $colname)
{
    global $row;
    $ret  = "<input type='radio' name='" . attr($name) . "' value='" . attr($value) . "'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }
    $ret .= " />" . text($desc);
    return $ret;
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

 // If updating an existing form...
 //
    if ($formid) {
        $query = "UPDATE form_body_composition SET 
        body_type = ?, height = ?, weight = ?, bmi = ?, bmr = ?, impedance = ?, 
        fat_pct = ?, fat_mass = ?, ffm = ?, tbw = ?, other = ? WHERE id = ?";

        sqlStatement($query, array(rbvalue('form_body_type'),  trim($_POST['form_height']), trim($_POST['form_weight']), trim($_POST['form_bmi']),
         trim($_POST['form_bmr']), trim($_POST['form_impedance']), trim($_POST['form_fat_pct']), trim($_POST['form_fat_mass']),  trim($_POST['form_ffm']),
         trim($_POST['form_tbw']), trim($_POST['form_other']), $formid ));
    } else { // If adding a new form...
        $query = 'INSERT INTO form_body_composition (
         body_type, height, weight, bmi, bmr, impedance, fat_pct, fat_mass, ffm, tbw, other
         ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $newid = sqlInsert($query, array(rbvalue('form_body_type'), trim($_POST['form_height']), trim($_POST['form_weight']), trim($_POST['form_bmi']),
         trim($_POST['form_bmr']), trim($_POST['form_impedance']), trim($_POST['form_fat_pct']), trim($_POST['form_fat_mass']),
         trim($_POST['form_ffm']), trim($_POST['form_tbw']), trim($_POST['form_other'])));

        addForm($encounter, "Body Composition", $newid, "body_composition", $pid, $userauthorized);
    }

    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

if ($formid) {
    $row = sqlQuery("SELECT * FROM form_body_composition WHERE " .
    "id = ? AND activity = '1'", array($formid));
} else {
 // Get the most recent scale reading.
    $items = explode(',', trim(file_get_contents($scale_file_name)));
    if ($items && count($items) > 11) {
        $scale_file_age = round((time() - filemtime($scale_file_name)) / 60);
        $row['body_type'] = $items[0] ? 'Athletic' : 'Standard';
        $row['height']    = $items[2];
        $row['weight']    = $items[3];
        $row['bmi']       = $items[10];
        $row['bmr']       = $items[11];
        $row['impedance'] = $items[4];
        $row['fat_pct']   = $items[5];
        $row['fat_mass']  = $items[6];
        $row['ffm']       = $items[7];
        $row['tbw']       = $items[8];
    }
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body <?php echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/body_composition/new.php?id=<?php echo attr_url($formid) ?>"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table border='0' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='3' align='center'><b>Body Composition</b></td>
 </tr>

 <tr>
  <td width='5%' nowrap>Body Type</td>
  <td colspan='2' nowrap>
    <?php echo rbinput('form_body_type', 'Standard', 'Standard', 'body_type') ?>&nbsp;
    <?php echo rbinput('form_body_type', 'Athletic', 'Athletic', 'body_type') ?>&nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Height in inches</td>
  <td nowrap>
   <input type='text' name='form_height' size='6'
    value='<?php echo attr($row['height']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Weight in pounds</td>
  <td nowrap>
   <input type='text' name='form_weight' size='6'
    value='<?php echo attr($row['weight']) ?>' /> &nbsp;
  </td>
  <td align='center' nowrap>
<?php
if ($scale_file_age >= 0) {
    echo "<font color='blue'>This reading was taken " . text($scale_file_age) . " minutes ago.</font>\n";
} else {
    echo "&nbsp;\n";
}
?>
  </td>
 </tr>

 <tr>
  <td nowrap>BMI</td>
  <td nowrap>
   <input type='text' name='form_bmi' size='6'
    value='<?php echo attr($row['bmi']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>BMR in kj</td>
  <td nowrap>
   <input type='text' name='form_bmr' size='6'
    value='<?php echo attr($row['bmr']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Impedance in ohms</td>
  <td nowrap>
   <input type='text' name='form_impedance' size='6'
    value='<?php echo attr($row['impedance']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat %</td>
  <td nowrap>
   <input type='text' name='form_fat_pct' size='6'
    value='<?php echo attr($row['fat_pct']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat Mass in pounds</td>
  <td nowrap>
   <input type='text' name='form_fat_mass' size='6'
    value='<?php echo attr($row['fat_mass']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>FFM in pounds</td>
  <td nowrap>
   <input type='text' name='form_ffm' size='6'
    value='<?php echo attr($row['ffm']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>TBW in pounds</td>
  <td nowrap>
   <input type='text' name='form_tbw' size='6'
    value='<?php echo attr($row['tbw']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Notes</td>
  <td colspan='2' nowrap>
   <textarea name='form_other' rows='8' style='width:100%'><?php echo text($row['other']) ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
</body>
</html>
