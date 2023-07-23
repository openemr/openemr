<?php

/**
 * Edit Layout Properties.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Gacl\GaclApi;

$alertmsg = "";

// Check authorization.
$thisauth = AclMain::aclCheckCore('admin', 'super');
if (!$thisauth) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit Layout Properties")]);
    exit;
}

$layout_id = empty($_GET['layout_id']) ? '' : $_GET['layout_id'];
$group_id  = empty($_GET['group_id' ]) ? '' : $_GET['group_id' ];
?>
<html>
<head>
<title><?php echo xlt("Edit Layout Properties"); ?></title>
    <?php Header::setupHeader('opener'); ?>

<style>
td { font-size:10pt; }
</style>

<script>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// The name of the input element to receive a found code.
var current_sel_name = '';

// This invokes the "dynamic" find-code popup.
function sel_related(elem, codetype) {
 current_sel_name = elem ? elem.name : '';
 var url = '<?php echo $rootdir ?>/patient_file/encounter/find_code_dynamic.php';
 if (codetype) url += '?codetype=' + encodeURIComponent(codetype);
 dlgopen(url, '_blank', 800, 500);
}

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 // frc will be the input element containing the codes.
 var frc = f[current_sel_name];
 var s = frc.value;
 if (code) {
  if (s.length > 0) {
   s  += ';';
  }
  s  += codetype + ':' + code;
 } else {
  s  = '';
 }
 frc.value = s;
 return '';
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the active input element.
function del_related(s) {
  var f = document.forms[0];
  my_del_related(s, f[current_sel_name], false);
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
  var f = document.forms[0];
  if (current_sel_name) {
    return f[current_sel_name].value.split(';');
  }
  return new Array();
}

</script>

</head>

<body class="body_top">

<?php
if (!empty($_POST['form_submit']) && !$alertmsg) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($group_id) {
        $sets =
            "grp_subtitle = ?, "   .
            "grp_init_open = ?, "  .
            "grp_columns = ?";
        $sqlvars = array(
            $_POST['form_subtitle'],
            empty($_POST['form_init_open' ]) ? 0 : 1,
            intval($_POST['form_columns']),
        );
    } else {
        $sets =
            "grp_title = ?, "      .
            "grp_subtitle = ?, "   .
            "grp_mapping = ?, "    .
            "grp_seq = ?, "        .
            "grp_activity = ?, "   .
            "grp_repeats = ?, "    .
            "grp_columns = ?, "    .
            "grp_size = ?, "       .
            "grp_issue_type = ?, " .
            "grp_aco_spec = ?, "   .
            // "grp_save_close = ?, " .
            "grp_init_open = ?, "  .
            "grp_referrals = ?, "  .
            "grp_unchecked = ?, "  .
            "grp_services = ?, "   .
            "grp_products = ?, "   .
            "grp_diags = ?";
        $sqlvars = array(
            $_POST['form_title'],
            $_POST['form_subtitle'],
            $_POST['form_mapping'],
            intval($_POST['form_seq']),
            empty($_POST['form_activity']) ? 0 : 1,
            intval($_POST['form_repeats']),
            intval($_POST['form_columns']),
            intval($_POST['form_size']),
            $_POST['form_issue'],
            $_POST['form_aco'],
            // empty($_POST['form_save_close']) ? 0 : 1,
            empty($_POST['form_init_open' ]) ? 0 : 1,
            empty($_POST['form_referrals']) ? 0 : 1,
            empty($_POST['form_unchecked']) ? 0 : 1,
            empty($_POST['form_services']) ? '' : (empty($_POST['form_services_codes']) ? '*' : $_POST['form_services_codes']),
            empty($_POST['form_products']) ? '' : (empty($_POST['form_products_codes']) ? '*' : $_POST['form_products_codes']),
            empty($_POST['form_diags'   ]) ? '' : (empty($_POST['form_diags_codes'   ]) ? '*' : $_POST['form_diags_codes'   ]),
        );
    }

    if ($layout_id) {
        // They have edited an existing layout.
        $form_title = $_POST['form_title'] ?? '';
        if ($form_title == '' && !$group_id) {
            $alertmsg = xl('Title is required');
        } else {
            $sqlvars[] = $layout_id;
            $sqlvars[] = $group_id;
            sqlStatement(
                "UPDATE layout_group_properties SET $sets " .
                "WHERE grp_form_id = ? AND grp_group_id = ?",
                $sqlvars
            );
        }
    } elseif (!$group_id) {
        // They want to add a new layout. New groups not supported here.
        $form_form_id = $_POST['form_form_id'];
        $form_title = $_POST['form_title'];
        if ($form_form_id == '') {
            $alertmsg = xl('Layout ID is required');
        } elseif ($form_title == '') {
            $alertmsg = xl('Title is required');
        } elseif (preg_match('/(LBF|LBT|HIS)[0-9A-Za-z_]+/', $form_form_id)) {
            $tmp = sqlQuery(
                "SELECT grp_form_id FROM layout_group_properties WHERE " .
                "grp_form_id = ? AND grp_group_id = ''",
                array($form_form_id)
            );
            if (empty($row)) {
                $sqlvars[] = $form_form_id;
                sqlStatement(
                    "INSERT INTO layout_group_properties " .
                    "SET $sets, grp_form_id = ?, grp_group_id = ''",
                    $sqlvars
                );
                $layout_id = $form_form_id;
            } else {
                $alertmsg = xl('This layout ID already exists');
            }
        } else {
            $alertmsg = xl('Invalid layout ID');
        }
    }

    // Close this window and redisplay the layout editor.
    //
    echo "<script>\n";
    if ($alertmsg) {
        echo " alert(" . js_escape($alertmsg) . ");\n";
    }
    echo " if (opener.refreshme) opener.refreshme(" . js_escape($layout_id) . ");\n";
    echo " window.close();\n";
    echo "</script></body></html>\n";
    exit();
}

$row = array(
    'grp_form_id'    => '',
    'grp_title'      => '',
    'grp_subtitle'   => '',
    'grp_mapping'    => 'Clinical',
    'grp_seq'        => '0',
    'grp_activity'   => '1',
    'grp_repeats'    => '0',
    'grp_columns'    => '4',
    'grp_size'       => '9',
    'grp_issue_type' => '',
    'grp_aco_spec'   => '',
    // 'grp_save_close' => '0',
    'grp_init_open'  => '0',
    'grp_referrals'  => '0',
    'grp_unchecked'  => '0',
    'grp_services'   => '',
    'grp_products'   => '',
    'grp_diags'      => '',
    'grp_last_update' => '',
);

if ($layout_id) {
    $row = sqlQuery(
        "SELECT * FROM layout_group_properties WHERE " .
        "grp_form_id = ? AND grp_group_id = ?",
        array($layout_id, $group_id)
    );
    if (empty($row)) {
        die(xlt('This layout does not exist.'));
    }
}
?>

<form method='post' action='edit_layout_props.php?<?php echo "layout_id=" . attr_url($layout_id) . "&group_id=" . attr_url($group_id); ?>'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<center>

<table class='w-100 border-0'>
 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Layout ID'); ?>
  </td>
  <td>
<?php if (empty($layout_id)) { ?>
   <input type='text' class='form-control' size='31' maxlength='31' name='form_form_id' value='' /><br />
    <?php echo xlt('Visit form ID must start with LBF. Transaction form ID must start with LBT.') ?>
<?php } else { ?>
    <?php echo text($layout_id); ?>
<?php } ?>
  </td>
 </tr>

<?php if (empty($group_id) && !empty($row['grp_last_update'])) { ?>
 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Last Update'); ?>
  </td>
  <td>
    <?php echo text($row['grp_last_update']); ?>
  </td>
 </tr>
<?php } ?>

<?php if (empty($group_id)) { ?>
 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Title'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='40' name='form_title' value='<?php echo attr($row['grp_title']); ?>' />
  </td>
 </tr>
<?php } ?>

 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Subtitle'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='40' name='form_subtitle' value='<?php echo attr($row['grp_subtitle']); ?>' />
  </td>
 </tr>

<?php if (empty($group_id)) { ?>
<tr>
    <td></td>
    <td><?php echo xlt('For transactions, change category to Transactions'); ?></td>
</tr>
 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Category'); ?>
  </td>
  <td>

   <input type='text' class='form-control' size='40' name='form_mapping' value='<?php echo attr($row['grp_mapping']); ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Active{{Item}}'); ?>
  </td>
  <td>
   <input type='checkbox' name='form_activity' <?php echo ($row['grp_activity']) ? "checked" : ""; ?> />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Sequence'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='4' name='form_seq' value='<?php echo attr($row['grp_seq']); ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Repeats'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='4' name='form_repeats'
    value='<?php echo attr($row['grp_repeats']); ?>' />
  </td>
 </tr>

<?php } ?>

 <tr>
  <td valign='top' nowrap>
    <?php echo xlt('Layout Columns'); ?>
  </td>
  <td>
   <select name='form_columns' class='form-control'>
<?php
  echo "<option value='0'>" . xlt('Default') . "</option>\n";
for ($cols = 2; $cols <= 12; ++$cols) {
    echo "<option value='" . attr($cols) . "'";
    if ($cols == $row['grp_columns']) {
        echo " selected";
    }
    echo ">" . text($cols) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

<?php if (empty($group_id)) { ?>
 <tr>
  <td valign='top' nowrap>
    <?php echo xlt('Font Size'); ?>
  </td>
  <td>
   <select name='form_size' class='form-control'>
    <?php
    echo "<option value='0'>" . xlt('Default') . "</option>\n";
    for ($size = 5; $size <= 15; ++$size) {
        echo "<option value='" . attr($size) . "'";
        if ($size == $row['grp_size']) {
            echo " selected";
        }
        echo ">" . text($size) . "</option>\n";
    }
    ?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
    <?php echo xlt('Issue Type'); ?>
  </td>
  <td>
   <select name='form_issue' class='form-control'>
    <option value=''></option>
    <?php
    $itres = sqlStatement(
        "SELECT type, singular FROM issue_types " .
        "WHERE category = ? AND active = 1 ORDER BY singular",
        array($GLOBALS['ippf_specific'] ? 'ippf_specific' : 'default')
    );
    while ($itrow = sqlFetchArray($itres)) {
        echo "<option value='" . attr($itrow['type']) . "'";
        if ($itrow['type'] == $row['grp_issue_type']) {
            echo " selected";
        }
        echo ">" . xlt($itrow['singular']) . "</option>\n";
    }
    ?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
    <?php echo xlt('Access Control'); ?>
  </td>
  <td>
   <select name='form_aco' class='form-control'>
    <option value=''></option>
    <?php
    $gacl = new GaclApi();
    // collect and sort all aco objects
    $list_aco_objects = $gacl->get_objects(null, 0, 'ACO');
    ksort($list_aco_objects);
    foreach ($list_aco_objects as $seckey => $dummy) {
        if (empty($dummy)) {
            continue;
        }
        asort($list_aco_objects[$seckey]);
        $aco_section_data = $gacl->get_section_data($seckey, 'ACO');
        $aco_section_title = $aco_section_data[3];
        echo " <optgroup label='" . xla($aco_section_title) . "'>\n";
        foreach ($list_aco_objects[$seckey] as $acokey) {
            $aco_id = $gacl->get_object_id($seckey, $acokey, 'ACO');
            $aco_data = $gacl->get_object_data($aco_id, 'ACO');
            $aco_title = $aco_data[0][3];
            echo "  <option value='" . attr("$seckey|$acokey") . "'";
            if ("$seckey|$acokey" == $row['grp_aco_spec']) {
                echo " selected";
            }
            echo ">" . xlt($aco_title) . "</option>\n";
        }
        echo " </optgroup>\n";
    }
    ?>
   </select>
  </td>
 </tr>

    <?php /* ?>
 <tr>
  <td valign='top' width='1%' nowrap>
   <?php echo xlt('Enable Save and Close'); ?>
  </td>
  <td>
   <input type='checkbox' name='form_save_close' <?php echo ($row['grp_save_close']) ? "checked" : ""; ?> />
  </td>
 </tr>
<?php */ ?>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_services' <?php echo ($row['grp_services']) ? "checked" : ""; ?> />
    <?php echo xlt('Show Services Section'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='40' name='form_services_codes' onclick='sel_related(this, "MA")' value='<?php echo ($row['grp_services'] != '*') ? attr($row['grp_services']) : ""; ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_products' <?php echo ($row['grp_products']) ? "checked" : ""; ?> />
    <?php echo xlt('Show Products Section'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='40' name='form_products_codes' onclick='sel_related(this, "PROD")' value='<?php echo ($row['grp_products'] != '*') ? attr($row['grp_products']) : ""; ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_diags' <?php echo ($row['grp_diags']) ? "checked" : ""; ?> />
    <?php echo xlt('Show Diagnoses Section'); ?>
  </td>
  <td>
   <input type='text' class='form-control' size='40' name='form_diags_codes' onclick='sel_related(this, "ICD10")' value='<?php echo ($row['grp_diags'] != '*') ? attr($row['grp_diags']) : ""; ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_referrals' <?php echo ($row['grp_referrals']) ? "checked" : ""; ?> />
    <?php echo xlt('Show Referrals Section'); ?>
  </td>
  <td>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_init_open' <?php echo ($row['grp_init_open']) ? "checked" : ""; ?> />
    <?php echo xlt('Initially Open Sections'); ?>
  </td>
  <td>
   &nbsp;
  </td>
 </tr>

<?php } else { // else this is a group ?>
 <tr>
  <td valign='top' width='1%' nowrap>
    <?php echo xlt('Initially Open Group'); ?>
  </td>
  <td>
   <input type='checkbox' name='form_init_open' <?php echo ($row['grp_init_open']) ? "checked" : ""; ?> />
  </td>
 </tr>

<?php } ?>

 <tr>
  <td valign='top' width='1%' nowrap>
   <input type='checkbox' name='form_unchecked' <?php echo ($row['grp_unchecked']) ? "checked" : ""; ?> />
    <?php echo xlt('Show Unchecked Boxes'); ?>
  </td>
  <td>
   &nbsp;
  </td>
 </tr>

</table>

<input type='submit' class='btn btn-primary' name='form_submit' value='<?php echo xla('Submit'); ?>' />
<input type='button' class='btn btn-secondary' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</center>
</form>
<script>
<?php
if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
    echo " window.close();\n";
}
?>
</script>
</body>
</html>
