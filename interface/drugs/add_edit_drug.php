<?php

 // Copyright (C) 2006-2021 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

$alertmsg = '';
$drug_id = $_REQUEST['drug'];
$info_msg = "";
$tmpl_line_no = 0;

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Drug")]);
    exit;
}

// Write a line of data for one template to the form.
//
function writeTemplateLine($selector, $dosage, $period, $quantity, $refills, $prices, $taxrates, $pkgqty)
{
    global $tmpl_line_no;
    ++$tmpl_line_no;

    echo " <tr>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][selector]' value='" . attr($selector) . "' size='8' maxlength='100'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][dosage]' value='" . attr($dosage) . "' size='6' maxlength='10'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    generate_form_field(array(
    'data_type'   => 1,
    'field_id'    => 'tmpl[' . attr($tmpl_line_no) . '][period]',
    'list_id'     => 'drug_interval',
    'empty_title' => 'SKIP'
    ), $period);
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][quantity]' value='" . attr($quantity) . "' size='3' maxlength='7'>";
    echo "</td>\n";
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][refills]' value='" . attr($refills) . "' size='3' maxlength='5'>";
    echo "</td>\n";

    /******************************************************************
    echo "  <td class='tmplcell drugsonly'>";
    echo "<input type='text' class='form-control' name='form_tmpl[" . attr($tmpl_line_no) .
        "][pkgqty]' value='" . attr($pkgqty) . "' size='3' maxlength='5'>";
    echo "</td>\n";
    ******************************************************************/

    foreach ($prices as $pricelevel => $price) {
        echo "  <td class='tmplcell'>";
        echo "<input class='form-control' name='form_tmpl[" . attr($tmpl_line_no) . "][price][" . attr($pricelevel) . "]' value='" . attr($price) . "' size='6' maxlength='12'>";
        echo "</td>\n";
    }

    $pres = sqlStatement("SELECT option_id FROM list_options " .
    "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
    while ($prow = sqlFetchArray($pres)) {
        echo "  <td class='tmplcell'>";
        echo "<input type='checkbox' name='form_tmpl[" . attr($tmpl_line_no) . "][taxrate][" . attr($prow['option_id']) . "]' value='1'";
        if (strpos(":$taxrates", $prow['option_id']) !== false) {
            echo " checked";
        }

        echo " /></td>\n";
    }

    echo " </tr>\n";
}
?>
<html>
<head>
<title><?php echo $drug_id ? xlt("Edit") : xlt("Add New");
echo ' ' . xlt('Drug'); ?></title>

<?php Header::setupHeader(["opener"]); ?>

<style>

<?php if ($GLOBALS['sell_non_drug_products'] == 2) { // "Products but no prescription drugs and no templates" ?>
.drugsonly { display:none; }
<?php } else { ?>
.drugsonly { }
<?php } ?>

<?php if (empty($GLOBALS['ippf_specific'])) { ?>
.ippfonly { display:none; }
<?php } else { ?>
.ippfonly { }
<?php } ?>

</style>

<script>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
// The target element is set by the find-code popup
// (this allows use of this in multiple form elements on the same page)
function set_related_target(codetype, code, selector, codedesc, target_element, limit=0) {
    var f = document.forms[0];
    var s = f[target_element].value;
    if (code) {
        if (limit > 0) {
            s = codetype + ':' + code;
        }
        else {
            if (codetype != 'PROD') {
                // Return an error message if a service code is already selected.
                if (s.indexOf(codetype + ':') == 0 || s.indexOf(';' + codetype + ':') > 0) {
                    return <?php echo xlj('A code of this type is already selected. Erase the field first if you need to replace it.') ?>;
                }
            }
            if (s.length > 0) {
                s += ';';
            }
            s += codetype + ':' + code;
        }
    } else {
        s = '';
    }
    f[target_element].value = s;
    return '';
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
 return document.forms[0].form_related_code.value.split(';');
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
 my_del_related(s, document.forms[0].form_related_code, false);
}

// This invokes the find-code popup.
function sel_related(getter = '') {
 dlgopen('../patient_file/encounter/find_code_dynamic.php' + getter, '_blank', 900, 800);
}

// onclick handler for "allow inventory" checkbox.
function dispensable_changed() {
 var f = document.forms[0];
 var dis = !f.form_dispensable.checked;
 f.form_allow_multiple.disabled = dis;
 f.form_allow_combining.disabled = dis;
 return true;
}

function validate(f) {
 var saving = f.form_save.clicked ? true : false;
 f.form_save.clicked = false;
 if (saving) {
  if (f.form_name.value.search(/[^\s]/) < 0) {
   alert(<?php echo xlj('Product name is required'); ?>);
   return false;
  }
 }
 var deleting = f.form_delete.clicked ? true : false;
 f.form_delete.clicked = false;
 if (deleting) {
  if (!confirm(<?php echo xlj('This will permanently delete all lots of this product. Related reports will be incomplete or incorrect. Are you sure?'); ?>)) {
   return false;
  }
 }
 top.restoreSession();
 return true;
}

</script>

</head>

<body class="body_top">
<?php
// If we are saving, then save and close the window.
// First check for duplicates.
//
if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $drugName = trim($_POST['form_name']);
    if ($drugName === '') {
        $alertmsg = xl('Drug name is required');
    } else {
        $crow = sqlQuery(
            "SELECT COUNT(*) AS count FROM drugs WHERE " .
            "name = ? AND " .
            "form = ? AND " .
            "size = ? AND " .
            "unit = ? AND " .
            "route = ? AND " .
            "drug_id != ?",
            array(
                trim($_POST['form_name']),
                trim($_POST['form_form']),
                trim($_POST['form_size']),
                trim($_POST['form_unit']),
                trim($_POST['form_route']),
                $drug_id
            )
        );
        if ($crow['count']) {
            $alertmsg = xl('Cannot add this entry because it already exists!');
        }
    }
}

if ((!empty($_POST['form_save']) || !empty($_POST['form_delete'])) && !$alertmsg) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $new_drug = false;
    if ($drug_id) {
        if ($_POST['form_save']) { // updating an existing drug
            sqlStatement(
                "UPDATE drugs SET " .
                "name = ?, " .
                "ndc_number = ?, " .
                "drug_code = ?, " .
                "on_order = ?, " .
                "reorder_point = ?, " .
                "max_level = ?, " .
                "form = ?, " .
                "size = ?, " .
                "unit = ?, " .
                "route = ?, " .
                "cyp_factor = ?, " .
                "related_code = ?, " .
                "dispensable = ?, " .
                "allow_multiple = ?, " .
                "allow_combining = ?, " .
                "active = ?, " .
                "consumable = ? " .
                "WHERE drug_id = ?",
                array(
                    trim($_POST['form_name']),
                    trim($_POST['form_ndc_number']),
                    trim($_POST['form_drug_code']),
                    trim($_POST['form_on_order']),
                    trim($_POST['form_reorder_point']),
                    trim($_POST['form_max_level']),
                    trim($_POST['form_form']),
                    trim($_POST['form_size']),
                    trim($_POST['form_unit']),
                    trim($_POST['form_route']),
                    trim($_POST['form_cyp_factor']),
                    trim($_POST['form_related_code']),
                    (empty($_POST['form_dispensable'    ]) ? 0 : 1),
                    (empty($_POST['form_allow_multiple' ]) ? 0 : 1),
                    (empty($_POST['form_allow_combining']) ? 0 : 1),
                    (empty($_POST['form_active']) ? 0 : 1),
                    (empty($_POST['form_consumable'     ]) ? 0 : 1),
                    $drug_id
                )
            );
            sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
        } else { // deleting
            if (AclMain::aclCheckCore('admin', 'super')) {
                sqlStatement("DELETE FROM drug_inventory WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM drugs WHERE drug_id = ?", array($drug_id));
                sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
            }
        }
    } elseif ($_POST['form_save']) { // saving a new drug
        $new_drug = true;
        $drug_id = sqlInsert(
            "INSERT INTO drugs ( " .
            "name, ndc_number, drug_code, on_order, reorder_point, max_level, form, " .
            "size, unit, route, cyp_factor, related_code, " .
            "dispensable, allow_multiple, allow_combining, active, consumable " .
            ") VALUES ( " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?, " .
            "?)",
            array(
                trim($_POST['form_name']),
                trim($_POST['form_ndc_number']),
                trim($_POST['form_drug_code']),
                trim($_POST['form_on_order']),
                trim($_POST['form_reorder_point']),
                trim($_POST['form_max_level']),
                trim($_POST['form_form']),
                trim($_POST['form_size']),
                trim($_POST['form_unit']),
                trim($_POST['form_route']),
                trim($_POST['form_cyp_factor']),
                trim($_POST['form_related_code']),
                (empty($_POST['form_dispensable'    ]) ? 0 : 1),
                (empty($_POST['form_allow_multiple' ]) ? 0 : 1),
                (empty($_POST['form_allow_combining']) ? 0 : 1),
                (empty($_POST['form_active'         ]) ? 0 : 1),
                (empty($_POST['form_consumable'     ]) ? 0 : 1)
            )
        );
    }

    if ($_POST['form_save'] && $drug_id) {
        $tmpl = $_POST['form_tmpl'];
       // If using the simplified drug form, then force the one and only
       // selector name to be the same as the product name.
        if ($GLOBALS['sell_non_drug_products'] == 2) {
            $tmpl["1"]['selector'] = $_POST['form_name'];
        }

        sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
        for ($lino = 1; isset($tmpl["$lino"]['selector']); ++$lino) {
            $iter = $tmpl["$lino"];
            $selector = trim($iter['selector']);
            if ($selector) {
                $taxrates = "";
                if (!empty($iter['taxrate'])) {
                    foreach ($iter['taxrate'] as $key => $value) {
                        $taxrates .= "$key:";
                    }
                }

                sqlStatement(
                    "INSERT INTO drug_templates ( " .
                    "drug_id, selector, dosage, period, quantity, refills, taxrates, pkgqty " .
                    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )",
                    array(
                        $drug_id,
                        $selector,
                        trim($iter['dosage']),
                        trim($iter['period']),
                        trim($iter['quantity']),
                        trim($iter['refills']),
                        $taxrates,
                        // floatval(trim($iter['pkgqty']))
                        1.0
                    )
                );

                // Add prices for this drug ID and selector.
                foreach ($iter['price'] as $key => $value) {
                    if ($value) {
                         $value = $value + 0;
                         sqlStatement(
                             "INSERT INTO prices ( " .
                             "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
                             "?, ?, ?, ? )",
                             array($drug_id, $selector, $key, $value)
                         );
                    }
                } // end foreach price
            } // end if selector is present
        } // end for each selector
       // Save warehouse-specific mins and maxes for this drug.
        sqlStatement("DELETE FROM product_warehouse WHERE pw_drug_id = ?", array($drug_id));
        foreach ($_POST['form_wh_min'] as $whid => $whmin) {
            $whmin = 0 + $whmin;
            $whmax = 0 + $_POST['form_wh_max'][$whid];
            if ($whmin != 0 || $whmax != 0) {
                sqlStatement("INSERT INTO product_warehouse ( " .
                "pw_drug_id, pw_warehouse, pw_min_level, pw_max_level ) VALUES ( " .
                "?, ?, ?, ? )", array($drug_id, $whid, $whmin, $whmax));
            }
        }
    } // end if saving a drug

  // Close this window and redisplay the updated list of drugs.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert('" . addslashes($info_msg) . "');\n";
    }

    echo " if (opener.refreshme) opener.refreshme();\n";
    if ($new_drug) {
        echo " window.location.href='add_edit_lot.php?drug=" . attr_url($drug_id) . "&lot=0'\n";
    } else {
        echo " window.close();\n";
    }

    echo "</script></body></html>\n";
    exit();
}

if ($drug_id) {
    $row = sqlQuery("SELECT * FROM drugs WHERE drug_id = ?", array($drug_id));
    $tres = sqlStatement("SELECT * FROM drug_templates WHERE " .
    "drug_id = ? ORDER BY selector", array($drug_id));
} else {
    $row = array(
    'name' => '',
    'active' => '1',
    'dispensable' => '1',
    'allow_multiple' => '1',
    'allow_combining' => '',
    'consumable' => '0',
    'ndc_number' => '',
    'on_order' => '0',
    'reorder_point' => '0',
    'max_level' => '0',
    'form' => '',
    'size' => '',
    'unit' => '',
    'route' => '',
    'cyp_factor' => '',
    'related_code' => '',
    );
}
$title = $drug_id ? xl("Update Drug") : xl("Add Drug");
?>
<h3 class="ml-1"><?php echo text($title);?></h3>
<form method='post' name='theform' action='add_edit_drug.php?drug=<?php echo attr_url($drug_id); ?>'
 onsubmit='return validate(this);'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <div class="form-group">
        <label><?php echo xlt('Name'); ?>:</label>
        <input class="form-control" size="40" name="form_name" maxlength="80" value='<?php echo attr($row['name']) ?>' />
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('Attributes'); ?>:</label>
        <input type='checkbox' name='form_active' value='1'<?php
        if ($row['active']) {
            echo ' checked';
        } ?> />
        <?php echo xlt('Active{{Drug}}'); ?>
        <input type='checkbox' name='form_consumable' value='1'<?php
        if ($row['consumable']) {
            echo ' checked';
        } ?> />
        <?php echo xlt('Consumable'); ?>
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('Allow'); ?>:</label>
        <input type='checkbox' name='form_dispensable' value='1' onclick='dispensable_changed();'<?php
        if ($row['dispensable']) {
            echo ' checked';
        } ?> />
        <?php echo xlt('Inventory'); ?>
        <input type='checkbox' name='form_allow_multiple' value='1'<?php
        if ($row['allow_multiple']) {
            echo ' checked';
        } ?> />
        <?php echo xlt('Multiple Lots'); ?>
        <input type='checkbox' name='form_allow_combining' value='1'<?php
        if ($row['allow_combining']) {
            echo ' checked';
        } ?> />
        <?php echo xlt('Combining Lots'); ?>
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('NDC Number'); ?>:</label>
        <input class="form-control w-100" size="40" name="form_ndc_number" maxlength="20" value='<?php echo attr($row['ndc_number']) ?>' onkeyup='maskkeyup(this,"<?php echo attr(addslashes($GLOBALS['gbl_mask_product_id'])); ?>")' onblur='maskblur(this,"<?php echo attr(addslashes($GLOBALS['gbl_mask_product_id'])); ?>")' />
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('RXCUI Code'); ?>:</label>
        <input class="form-control w-100" type="text" size="50" name="form_drug_code" value='<?php echo attr($row['drug_code']) ?>'
             onclick='sel_related("?codetype=RXCUI&limit=1&target_element=form_drug_code")' title='<?php echo xla('Click to select RXCUI code'); ?>' data-toggle="tooltip" data-placement="top" readonly />
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('On Order'); ?>:</label>
        <input class="form-control" size="5" name="form_on_order" maxlength="7" value='<?php echo attr($row['on_order']) ?>' />
    </div>

    <div class="form-group mt-3">
        <label><?php echo xlt('Limits'); ?>:</label>
        <table class="table table-borderless pl-5">
            <tr>
                <td class="align-top ">
                    <?php echo !empty($GLOBALS['gbl_min_max_months']) ? xlt('Months') : xlt('Units'); ?>
                </td>
                <td class="align-top"><?php echo xlt('Global'); ?></td>
<?php
                    // One column header per warehouse title.
                    $pwarr = array();
                    $pwres = sqlStatement(
                        "SELECT lo.option_id, lo.title, " .
                        "pw.pw_min_level, pw.pw_max_level " .
                        "FROM list_options AS lo " .
                        "LEFT JOIN product_warehouse AS pw ON " .
                        "pw.pw_drug_id = ? AND " .
                        "pw.pw_warehouse = lo.option_id WHERE " .
                        "lo.list_id = 'warehouse' AND lo.activity = 1 ORDER BY lo.seq, lo.title",
                        array($drug_id)
                    );
                    while ($pwrow = sqlFetchArray($pwres)) {
                        $pwarr[] = $pwrow;
                        echo "     <td class='align-top'>" . text($pwrow['title']) . "</td>\n";
                    }
                    ?>
            </tr>
            <tr>
                <td class="align-top"><?php echo xlt('Min'); ?>&nbsp;</td>
                <td class="align-top">
                    <input class="form-control" size='5' name='form_reorder_point' maxlength='7' value='<?php echo attr($row['reorder_point']) ?>' title='<?php echo xla('Reorder point, 0 if not applicable'); ?>' data-toggle="tooltip" data-placement="top" />
                </td>
                <?php
                foreach ($pwarr as $pwrow) {
                    echo "     <td class='align-top'>";
                    echo "<input class='form-control' name='form_wh_min[" .
                    attr($pwrow['option_id']) .
                    "]' value='" . attr(0 + $pwrow['pw_min_level']) . "' size='5' " .
                    "title='" . xla('Warehouse minimum, 0 if not applicable') . "' data-toggle='tooltip' data-placement='top' />";
                    echo "&nbsp;&nbsp;</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td class="align-top"><?php echo xlt('Max'); ?>&nbsp;</td>
                <td>
                    <input class='form-control' size='5' name='form_max_level' maxlength='7' value='<?php echo attr($row['max_level']) ?>' title='<?php echo xla('Maximum reasonable inventory, 0 if not applicable'); ?>' data-toggle="tooltip" data-placement="top" />
                </td>
                <?php
                foreach ($pwarr as $pwrow) {
                    echo "     <td class='align-top'>";
                    echo "<input class='form-control' name='form_wh_max[" .
                    attr($pwrow['option_id']) .
                    "]' value='" . attr(0 + $pwrow['pw_max_level']) . "' size='5' " .
                    "title='" . xla('Warehouse maximum, 0 if not applicable') . "' data-toggle='tooltip' data-placement='top' />";
                    echo "</td>\n";
                }
                ?>
            </tr>
        </table>
    </div>

    <div class="form-group mt-3 drugsonly">
        <label><?php echo xlt('Form'); ?>:</label>
        <?php
            generate_form_field(array('data_type' => 1,'field_id' => 'form','list_id' => 'drug_form','empty_title' => 'SKIP'), $row['form']);
        ?>
    </div>

    <div class="form-group mt-3 drugsonly">
        <label><?php echo xlt('Size'); ?>:</label>
        <input class="form-control" size="5" name="form_size" maxlength="7" value='<?php echo attr($row['size']) ?>' />
    </div>

    <div class="form-group mt-3 drugsonly" title='<?php echo xlt('Measurement Units'); ?>'>
        <label><?php echo xlt('Units'); ?>:</label>
        <?php
            generate_form_field(array('data_type' => 1,'field_id' => 'unit','list_id' => 'drug_units','empty_title' => 'SKIP'), $row['unit']);
        ?>
    </div>

    <div class="form-group mt-3 drugsonly">
        <label><?php echo xlt('Route'); ?>:</label>
        <?php
            generate_form_field(array('data_type' => 1,'field_id' => 'route','list_id' => 'drug_route','empty_title' => 'SKIP'), $row['route']);
        ?>
    </div>

    <div class="form-group mt-3 ippfonly" style='display:none'> <!-- Removed per CV 2017-03-29 -->
        <label><?php echo xlt('CYP Factor'); ?>:</label>
        <input class="form-control" size="10" name="form_cyp_factor" maxlength="20" value='<?php echo attr($row['cyp_factor']) ?>' />
    </div>

    <div class="form-group mt-3 drugsonly">
        <label><?php echo xlt('Relate To'); ?>:</label>
        <input class="form-control w-100" type="text" size="50" name="form_related_code" value='<?php echo attr($row['related_code']) ?>'
             onclick='sel_related("?target_element=form_related_code")' title='<?php echo xla('Click to select related code'); ?>' data-toggle="tooltip" data-placement="top" readonly />
    </div>

    <div class="form-group mt-3">
        <label>
            <?php echo $GLOBALS['sell_non_drug_products'] == 2 ? xlt('Fees') : xlt('Templates'); ?>:
        </label>
        <table class='table table-borderless'>
            <thead>
                <tr>
                    <th class='drugsonly'><?php echo xlt('Name'); ?></th>
                    <th class='drugsonly'><?php echo xlt('Schedule'); ?></th>
                    <th class='drugsonly'><?php echo xlt('Interval'); ?></th>
                    <th class='drugsonly'><?php echo xlt('Basic Units'); ?></th>
                    <th class='drugsonly'><?php echo xlt('Refills'); ?></th>
                    <?php
                    // Show a heading for each price level.  Also create an array of prices
                    // for new template lines.
                    $emptyPrices = array();
                    $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq");
                    while ($prow = sqlFetchArray($pres)) {
                        $emptyPrices[$prow['option_id']] = '';
                        echo "     <th>" .
                        generate_display_field(array('data_type' => '1','list_id' => 'pricelevel'), $prow['option_id']) .
                        "</th>\n";
                    }

                    // Show a heading for each tax rate.
                    $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                        "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
                    while ($prow = sqlFetchArray($pres)) {
                        echo "     <th>" .
                            generate_display_field(array('data_type' => '1','list_id' => 'taxrate'), $prow['option_id']) .
                            "</th>\n";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 1 : 3;
            if ($tres) {
                while ($trow = sqlFetchArray($tres)) {
                    $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 0 : 1;
                    $selector = $trow['selector'];
                // Get array of prices.
                    $prices = array();
                    $pres = sqlStatement(
                        "SELECT lo.option_id, p.pr_price " .
                        "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                        "p.pr_id = ? AND p.pr_selector = ? AND " .
                        "p.pr_level = lo.option_id " .
                        "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq",
                        array($drug_id, $selector)
                    );
                    while ($prow = sqlFetchArray($pres)) {
                        $prices[$prow['option_id']] = $prow['pr_price'];
                    }

                    writeTemplateLine(
                        $selector,
                        $trow['dosage'],
                        $trow['period'],
                        $trow['quantity'],
                        $trow['refills'],
                        $prices,
                        $trow['taxrates'],
                        $trow['pkgqty']
                    );
                }
            }

            for ($i = 0; $i < $blank_lines; ++$i) {
                $selector = $GLOBALS['sell_non_drug_products'] == 2 ? $row['name'] : '';
                writeTemplateLine($selector, '', '', '', '', $emptyPrices, '', '1');
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="btn-group">
        <button type='submit' class="btn btn-primary btn-save" name='form_save'
         value='<?php echo  $drug_id ? xla('Update') : xla('Add') ; ?>'
         onclick='return this.clicked = true;'
         ><?php echo $drug_id ? xlt('Update') : xlt('Add') ; ?></button>
        <?php if (AclMain::aclCheckCore('admin', 'super') && $drug_id) { ?>
        <button class="btn btn-danger" type='submit' name='form_delete'
         onclick='return this.clicked = true;' value='<?php echo xla('Delete'); ?>'
         ><?php echo xlt('Delete'); ?></button>
        <?php } ?>
        <button type='button' class="btn btn-secondary btn-cancel" onclick='window.close()'><?php echo xlt('Cancel'); ?></button>
    </div>
</form>

<script>

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

dispensable_changed();

<?php
if ($alertmsg) {
    echo "alert('" . addslashes($alertmsg) . "');\n";
}
?>

</script>

</body>
</html>
