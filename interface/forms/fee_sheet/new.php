<?php
/**
 * Fee Sheet Program used to create charges, copays and add diagnosis codes to the encounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/FeeSheetHtml.class.php");
require_once("codes.php");

//acl check
if (!acl_check_form('fee_sheet')) {
    ?>
    <script>alert('<?php echo xls("Not authorized"); ?>')</script>;
    <?php
    formJump();
}

// Some table cells will not be displayed unless insurance billing is used.
$usbillstyle = $GLOBALS['ippf_specific'] ? " style='display:none'" : "";
$justifystyle = justifiers_are_used() ? "" : " style='display:none'";

$liprovstyle = (isset($GLOBALS['support_fee_sheet_line_item_provider']) &&
  $GLOBALS['support_fee_sheet_line_item_provider'] != 1) ? " style='display:none'" : "";

// This flag comes from the LBFmsivd form and perhaps later others.
$rapid_data_entry = empty($_GET['rde']) ? 0 : 1;

// This comes from the Add More Items button, or is preserved from its previous value.
$add_more_items = (empty($_GET['addmore']) && empty($_POST['bn_addmore'])) ? 0 : 1;

$alertmsg = '';

// Determine if more than one price level is in use.
$tmp = sqlQuery("SELECT COUNT(*) AS count FROM list_options where list_id = 'pricelevel' AND activity = 1");
$price_levels_are_used = $tmp['count'] > 1;
// For revenue codes
$institutional = $GLOBALS['ub04_support'] == "1" ? true : false;
// Format a money amount with decimals but no other decoration.
// Second argument is used when extra precision is required.
function formatMoneyNumber($value, $extradecimals = 0)
{
    return sprintf('%01.' . ($GLOBALS['currency_decimals'] + $extradecimals) . 'f', $value);
}

// Helper function for creating drop-lists.
function endFSCategory()
{
    global $i, $last_category, $FEE_SHEET_COLUMNS;
    if (! $last_category) {
        return;
    }

    echo "   </select>\n";
    echo "  </td>\n";
    if ($i >= $FEE_SHEET_COLUMNS) {
        echo " </tr>\n";
        $i = 0;
    }
}

// Generate JavaScript to build the array of diagnoses.
function genDiagJS($code_type, $code)
{
    global $code_types;
    if ($code_types[$code_type]['diag']) {
        echo "diags.push('" . attr($code_type) . "|" . attr($code) . "');\n";
    }
}

// Write all service lines to the web form.
//
function echoServiceLines()
{
    global $code_types, $justinit, $usbillstyle, $liprovstyle, $justifystyle, $fs, $price_levels_are_used, $institutional;

    foreach ($fs->serviceitems as $lino => $li) {
        $id       = $li['hidden']['id'];
        $codetype = $li['hidden']['code_type'];
        $code     = $li['hidden']['code'];
        if ($institutional) {
            $revenue_code = $li['hidden']['revenue_code'];
        }
        $modifier = $li['hidden']['mod'];
        $billed   = $li['hidden']['billed'];
        $ndc_info = isset($li['ndc_info']) ? $li['ndc_info'] : '';
        $pricelevel = $li['pricelevel'];
        $justify  = $li['justify'];

        $strike1 = $li['del'] ? "<strike>" : "";
        $strike2 = $li['del'] ? "</strike>" : "";

        echo " <tr>\n";

        echo "  <td class='billcell'>$strike1" .
        ($codetype == 'COPAY' ? xl($codetype) : text($codetype)) . $strike2;
        // if the line to ouput is copay, show the date here passed as $ndc_info,
        // since this variable is not applicable in the case of copay.
        if ($codetype == 'COPAY') {
            echo "(" . text($ndc_info) . ")";
            $ndc_info = '';
        }

        if ($id) {
            echo "<input type='hidden' name='bill[" . attr($lino) . "][id]' value='" . attr($id) . "' />";
        }

        echo "<input type='hidden' name='bill[" . attr($lino) . "][code_type]' value='" . attr($codetype) . "' />";
        echo "<input type='hidden' name='bill[" . attr($lino) . "][code]' value='" . attr($code) . "' />";
        echo "<input type='hidden' name='bill[" . attr($lino)."][billed]' value='" . attr($billed)."' />";
        if (isset($li['hidden']['method'])) {
            echo "<input type='hidden' name='bill[$lino][method]'   value='" . attr($li['hidden']['method'  ]) . "' />";
            echo "<input type='hidden' name='bill[$lino][cyp]'      value='" . attr($li['hidden']['cyp'     ]) . "' />";
            echo "<input type='hidden' name='bill[$lino][methtype]' value='" . attr($li['hidden']['methtype']) . "' />";
        }

        echo "</td>\n";

        if ($codetype != 'COPAY') {
            echo "  <td class='billcell'>$strike1" . text($code) . "$strike2</td>\n";
        } else {
            echo "  <td class='billcell'>&nbsp;</td>\n";
        }

        echo "  <td class='billcell'>$strike1" . text($li['code_text']) . "$strike2</td>\n";

        if ($billed) {
            if ($institutional) {
                echo "  <td class='billcell'>$strike1" . text($revenue_code) . "$strike2" .
                "<input type='hidden' name='bill[" . attr($lino) . "][revenue_code]' value='" . attr($revenue_code) . "'></td>\n";
            }

            if (modifiers_are_used(true)) {
                echo "  <td class='billcell'>$strike1" . text($modifier) . "$strike2" .
                "<input type='hidden' name='bill[" . attr($lino) . "][mod]' value='" . attr($modifier) . "'></td>\n";
            }

            if (fees_are_used()) {
                if ($price_levels_are_used) {
                    // Show price level for this line.
                    echo "  <td class='billcell' align='center'>";
                    echo $fs->genPriceLevelSelect('', ' ', $li['hidden']['codes_id'], '', $pricelevel, true);
                    echo "</td>\n";
                }

                echo "  <td class='billcell' align='right'>" . text(oeFormatMoney($li['price'])) . "</td>\n";
                if ($codetype != 'COPAY') {
                    echo "  <td class='billcell' align='center'>" . text($li['units']) . "</td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }

                echo "  <td class='billcell' align='center'$justifystyle>$justify</td>\n";
            }

            // Show provider for this line.
            echo "  <td class='billcell' align='center' $liprovstyle>";
            echo $fs->genProviderSelect('', '-- ' .xl("Default"). ' --', $li['provid'], true);
            echo "</td>\n";

            if ($code_types[$codetype]['claim'] && !$code_types[$codetype]['diag']) {
                echo "  <td class='billcell' align='center'$usbillstyle>" .
                text($li['notecodes']) . "</td>\n";
            } else {
                echo "  <td class='billcell' align='center'$usbillstyle></td>\n";
            }

            echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox'" .
            ($li['auth'] ? " checked" : "") . " disabled /></td>\n";

            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell' align='center'>&nbsp;</td>\n";
            }

            echo "  <td class='billcell' align='center'><input type='checkbox'" .
            " disabled /></td>\n";
        } else { // not billed
            if ($institutional) {
                if ($codetype != 'COPAY' && $codetype != 'ICD10') {
                    echo "  <td class='billcell'><input type='text' class='revcode' name='bill[" . attr($lino) . "][revenue_code]' " .
                        "title='" . xla("Revenue Code for this item. Type for hints/search") . "' " .
                        "value='" . attr($revenue_code) . "' size='4'></td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }
            }
            if (modifiers_are_used(true)) {
                if ($codetype != 'COPAY' && ($code_types[$codetype]['mod'] || $modifier)) {
                    echo "  <td class='billcell'><input type='text' name='bill[" . attr($lino) . "][mod]' " .
                       "title='" . xla("Multiple modifiers can be separated by colons or spaces, maximum of 4 (M1:M2:M3:M4)") . "' " .
                       "value='" . attr($modifier) . "' size='" . attr($code_types[$codetype]['mod']) . "'></td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }
            }

            if (fees_are_used()) {
                if ($codetype == 'COPAY' || $code_types[$codetype]['fee'] || $fee != 0) {
                    if ($price_levels_are_used) {
                        echo "  <td class='billcell' align='center'>";
                        echo $fs->genPriceLevelSelect("bill[$lino][pricelevel]", ' ', $li['hidden']['codes_id'], '', $pricelevel);
                        echo "</td>\n";
                    }

                    echo "  <td class='billcell' align='right'>" .
                    "<input type='text' name='bill[$lino][price]' " .
                    "value='" . attr($li['price']) . "' size='6' onchange='setSaveAndClose()'";
                    if (acl_check('acct', 'disc')) {
                        echo " style='text-align:right'";
                    } else {
                        echo " style='text-align:right;background-color:transparent' readonly";
                    }

                    echo "></td>\n";

                    echo "  <td class='billcell' align='center'>";
                    if ($codetype != 'COPAY') {
                        echo "<input type='text' name='bill[" . attr($lino) . "][units]' " .
                        "value='" . attr($li['units']) . "' size='2' style='text-align:right'>";
                    } else {
                        echo "<input type='hidden' name='bill[" . attr($lino) . "][units]' value='" . attr($li['units']) . "'>";
                    }

                    echo "</td>\n";

                    if ($code_types[$codetype]['just'] || $li['justify']) {
                        echo "  <td class='billcell' align='center'$justifystyle>";
                        echo "<select name='bill[" . attr($lino) . "][justify]' onchange='setJustify(this)'>";
                        echo "<option value='" . attr($li['justify']) . "'>" . text($li['justify']) . "</option></select>";
                        echo "</td>\n";
                        $justinit .= "setJustify(f['bill[" . attr($lino) . "][justify]']);\n";
                    } else {
                        echo "  <td class='billcell'$justifystyle>&nbsp;</td>\n";
                    }
                } else {
                    if ($price_levels_are_used) {
                        echo "  <td class='billcell'>&nbsp;</td>\n";
                    }

                    echo "  <td class='billcell'>&nbsp;</td>\n";
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                    echo "  <td class='billcell'$justifystyle>&nbsp;</td>\n"; // justify
                }
            }

            // Provider drop-list for this line.
            echo "  <td class='billcell' align='center' $liprovstyle>";
            echo $fs->genProviderSelect("bill[$lino][provid]", '-- '.xl("Default").' --', $li['provid']);
            echo "</td>\n";

            if ($code_types[$codetype]['claim'] && !$code_types[$codetype]['diag']) {
                echo "  <td class='billcell' align='center'$usbillstyle><input type='text' name='bill[" . attr($lino) . "][notecodes]' " .
                "value='" . text($li['notecodes']) . "' maxlength='10' size='8' /></td>\n";
            } else {
                echo "  <td class='billcell' align='center'$usbillstyle></td>\n";
            }

            echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox' name='bill[".attr($lino) . "][auth]' " .
            "value='1'" . ($li['auth'] ? " checked" : "") . " /></td>\n";

            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell' align='center'>&nbsp;</td>\n";   // KHY: May need to confirm proper location of this cell
            }

            echo "  <td class='billcell' align='center'><input type='checkbox' name='bill[" . attr($lino) . "][del]' " .
            "value='1'" . ($li['del'] ? " checked" : "") . " /></td>\n";
        }

        echo " </tr>\n";

        // If NDC info exists or may be required, add a line for it.
        if (isset($li['ndcnum'])) {
            echo " <tr>\n";
            echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
            echo "  <td class='billcell' colspan='6'>&nbsp;NDC:&nbsp;";
            echo "<input type='text' name='bill[" . attr($lino) . "][ndcnum]' value='" . attr($li['ndcnum']) . "' " .
            "size='11' style='background-color:transparent'>";
            echo " &nbsp;Qty:&nbsp;";
            echo "<input type='text' name='bill[" . attr($lino) . "][ndcqty]' value='" . attr($li['ndcqty']) . "' " .
            "size='3' style='background-color:transparent;text-align:right'>";
            echo " ";
            echo "<select name='bill[" . attr($lino) . "][ndcuom]' style='background-color:transparent'>";
            foreach ($fs->ndc_uom_choices as $key => $value) {
                echo "<option value='" . attr($key) . "'";
                if ($key == $li['ndcuom']) {
                    echo " selected";
                }

                echo ">" . text($value) . "</option>";
            }

            echo "</select>";
            echo "</td>\n";
            echo " </tr>\n";
        } else if (!empty($li['ndc_info'])) {
            echo " <tr>\n";
            echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
            echo "  <td class='billcell' colspan='6'>&nbsp;" . xlt("NDC Data") . ": " . text($li['ndc_info']) . "</td>\n";
            echo " </tr>\n";
        }
    }
}

// Write all product lines to the web form.
//
function echoProductLines()
{
    global $code_types, $usbillstyle, $liprovstyle, $justifystyle, $fs, $price_levels_are_used;

    foreach ($fs->productitems as $lino => $li) {
        $drug_id      = $li['hidden']['drug_id'];
        $selector     = $li['hidden']['selector'];
        $sale_id      = $li['hidden']['sale_id'];
        $billed       = $li['hidden']['billed'];
        $fee          = $li['fee'];
        $price        = $li['price'];
        $pricelevel   = $li['pricelevel'];
        $units        = $li['units'];
        $del          = $li['del'];
        $warehouse_id = $li['warehouse'];
        $rx           = $li['rx'];

        $strike1 = ($sale_id && $del) ? "<strike>" : "";
        $strike2 = ($sale_id && $del) ? "</strike>" : "";

        echo " <tr>\n";
        echo "  <td class='billcell'>{$strike1}" . xlt("Product") . "$strike2";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][sale_id]' value='" . attr($sale_id) . "'>";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][drug_id]' value='" . attr($drug_id) . "'>";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][selector]' value='" . attr($selector) . "'>";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][billed]' value='" . attr($billed) . "'>";
        if (isset($li['hidden']['method'])) {
            echo "<input type='hidden' name='prod[$lino][method]' value='"   . attr($li['hidden']['method'  ]) . "' />";
            echo "<input type='hidden' name='prod[$lino][methtype]' value='" . attr($li['hidden']['methtype']) . "' />";
        }

        echo "</td>\n";

        echo "  <td class='billcell'>$strike1" . text($drug_id) . "$strike2</td>\n";

        echo "  <td class='billcell'>$strike1" . text($li['code_text']) . "$strike2</td>\n";

        if (modifiers_are_used(true)) {
            echo "  <td class='billcell'>&nbsp;</td>\n";
        }

        if ($billed) {
            if (fees_are_used()) {
                if ($price_levels_are_used) {
                    echo "  <td class='billcell' align='center'>";
                    echo $fs->genPriceLevelSelect('', ' ', $drug_id, $selector, $pricelevel, true);
                    echo "</td>\n";
                }

                echo "  <td class='billcell' align='right'>" . text(oeFormatMoney($price)) . "</td>\n";
                echo "  <td class='billcell' align='center'>" . text($units) . "</td>\n";
            }

            if (justifiers_are_used()) { // KHY Evaluate proper position/usage of if justifiers
                echo "  <td class='billcell' align='center'$justifystyle>&nbsp;</td>\n"; // justify
            }

            // Show warehouse for this line.
            echo "  <td class='billcell' align='center' $liprovstyle>";
            echo $fs->genWarehouseSelect('', ' ', $warehouse_id, true, $drug_id, $sale_id > 0);
            echo "</td>\n";
            //
            echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // note codes
            echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell' align='center'><input type='checkbox'" . // rx
                " disabled /></td>\n";
            }

            echo "  <td class='billcell' align='center'><input type='checkbox'" .   // del
            " disabled /></td>\n";
        } else { // not billed
            if (fees_are_used()) {
                if ($price_levels_are_used) {
                    echo "  <td class='billcell' align='center'>";
                    echo $fs->genPriceLevelSelect("prod[$lino][pricelevel]", ' ', $drug_id, $selector, $pricelevel);
                    echo "</td>\n";
                }

                echo "  <td class='billcell' align='right'>" .
                "<input type='text' name='prod[" . attr($lino) . "][price]' " .
                "value='" . attr($price) . "' size='6' onchange='setSaveAndClose()'";
                if (acl_check('acct', 'disc')) {
                    echo " style='text-align:right'";
                } else {
                    echo " style='text-align:right;background-color:transparent' readonly";
                }

                echo "></td>\n";
                echo "  <td class='billcell' align='center'>";
                echo "<input type='text' name='prod[" . attr($lino) . "][units]' " .
                "value='" . attr($units) . "' size='2' style='text-align:right'>";
                echo "</td>\n";
            }

            if (justifiers_are_used()) {
                echo "  <td class='billcell'$justifystyle>&nbsp;</td>\n"; // justify
            }

            // Generate warehouse selector if there is a choice of warehouses.
            echo "  <td class='billcell' align='center' $liprovstyle>";
            echo $fs->genWarehouseSelect("prod[$lino][warehouse]", ' ', $warehouse_id, false, $drug_id, $sale_id > 0);
            echo "</td>\n";
            //
            echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // note codes
            echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell' align='center'>" .
                "<input type='checkbox' name='prod[$lino][rx]' value='1'" .
                ($rx ? " checked" : "") . " /></td>\n";
            }

            echo "  <td class='billcell' align='center'><input type='checkbox' name='prod[" . attr($lino) . "][del]' " .
            "value='1'" . ($del ? " checked" : "") . " /></td>\n";
        }

        echo " </tr>\n";
    }
}

$fs = new FeeSheetHtml();

// $FEE_SHEET_COLUMNS should be defined in codes.php.
if (empty($FEE_SHEET_COLUMNS)) {
    $FEE_SHEET_COLUMNS = 2;
}

// Update price level in patient demographics if it's changed.
if (!empty($_POST['pricelevel'])) {
    $fs->updatePriceLevel($_POST['pricelevel']);
}

$current_checksum = $fs->visitChecksum();
// It's important to look for a checksum mismatch even if we're just refreshing
// the display, otherwise the error goes undetected on a refresh-then-save.
if (isset($_POST['form_checksum'])) {
    if ($_POST['form_checksum'] != $current_checksum) {
        $alertmsg = xl('Someone else has just changed this visit. Please cancel this page and try again.');
        $comment = "CHECKSUM ERROR, expecting '{$_POST['form_checksum']}'";
        newEvent("checksum", $_SESSION['authUser'], $_SESSION['authProvider'], 1, $comment, $pid);
    }
}

if (!$alertmsg && ($_POST['bn_save'] || $_POST['bn_save_close'])) {
    $alertmsg = $fs->checkInventory($_POST['prod']);
}

// If Save or Save-and-Close was clicked, save the new and modified billing
// lines; then if no error, redirect to $GLOBALS['form_exit_url'].
//
if (!$alertmsg && ($_POST['bn_save'] || $_POST['bn_save_close'] || $_POST['bn_save_stay'])) {
    $main_provid = 0 + $_POST['ProviderID'];
    $main_supid  = 0 + (int)$_POST['SupervisorID'];

    $fs->save(
        $_POST['bill'],
        $_POST['prod'],
        $main_provid,
        $main_supid,
        $_POST['default_warehouse'],
        $_POST['bn_save_close']
    );

    if ($_POST['bn_save_stay']) {
        $current_checksum = $fs->visitChecksum();
    }

  // Note: Taxes are computed at checkout time (in pos_checkout.php which
  // also posts to SL).  Currently taxes with insurance claims make no sense,
  // so for now we'll ignore tax computation in the insurance billing logic.

    if ($_POST['running_as_ajax']) {
        // In the case of running as an AJAX handler, we need to return this same
        // form with an updated checksum to properly support the invoking logic.
        // See review/js/fee_sheet_core.js for that logic.
        $current_checksum = $fs->visitChecksum(true);
        // Also remove form data for the newly entered lines so they are not
        // duplicated from the database.
        unset($_POST['bill']);
        unset($_POST['prod']);
    } elseif (!isset($_POST['bn_save_stay'])) { // not running as ajax
        // If appropriate, update the status of the related appointment to
        // "In exam room".
        updateAppointmentStatus($fs->pid, $fs->visit_date, '<');

        // More Family Planning stuff.
        if (isset($_POST['ippfconmeth'])) {
            $tmp_form_id = $fs->doContraceptionForm($_POST['ippfconmeth'], $_POST['newmauser'], $main_provid);
            if ($tmp_form_id) {
                // Contraceptive method does not match what is in an existing Contraception
                // form for this visit, or there is no such form.  Open the form.
                formJump("{$GLOBALS['rootdir']}/patient_file/encounter/view_form.php" .
                "?formname=LBFccicon&id=" . ($tmp_form_id < 0 ? 0 : $tmp_form_id));
                formFooter();
                exit;
            }
        }

        if ($rapid_data_entry || ($_POST['bn_save_close'] && $_POST['form_has_charges'])) {
            // In rapid data entry mode or if "Save and Checkout" was clicked,
            // we go directly to the Checkout page.
            formJump("{$GLOBALS['rootdir']}/patient_file/pos_checkout.php?framed=1" .
            "&ptid={$fs->pid}&enid={$fs->encounter}&rde=$rapid_data_entry");
        } else {
            // Otherwise return to the normal encounter summary frameset.
            //
            formHeader("Redirecting....");
            formJump();
        }

        formFooter();
        exit;
    } // end not running as ajax
} // end save or save-and-close

// Handle reopen request.  In that case no other changes will be saved.
// If there was a checkout this will undo it.
if (!$alertmsg && $_POST['bn_reopen']) {
    doVoid($fs->pid, $fs->encounter, true);
    $current_checksum = $fs->visitChecksum();
  // Remove the line items so they are refreshed from the database on redisplay.
    unset($_POST['bill']);
    unset($_POST['prod']);
}

$billresult = getBillingByEncounter($fs->pid, $fs->encounter, "*");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
.ui-autocomplete { max-height: 250px; max-width: 350px; overflow-y: auto; overflow-x: hidden; }
</style>
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-12-1/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css" />
<script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/knockout-3-4-0/dist/knockout.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script>
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
var diags = new Array();

<?php
if ($billresult) {
    foreach ($billresult as $iter) {
        genDiagJS($iter["code_type"], trim($iter["code"]));
    }
}

if ($_POST['bill']) {
    foreach ($_POST['bill'] as $iter) {
        if ($iter["del"]) {
            continue; // skip if Delete was checked
        }

        if ($iter["id"]) {
            continue; // skip if it came from the database
        }

        genDiagJS($iter["code_type"], $iter["code"]);
    }
}

if ($_POST['newcodes']) {
    $arrcodes = explode('~', $_POST['newcodes']);
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }

        $arrcode = explode('|', $codestring);
        list($code, $modifier) = explode(":", $arrcode[1]);
        genDiagJS($arrcode[0], $code);
    }
}
?>
function reinitForm(){
    var cache = {};
    $( ".revcode" ).autocomplete({
        minLength: 1,
        source: function( request, response ) {
            var term = request.term;
            request.code_group = "revenue_code";
            if ( term in cache ) {
              response( cache[ term ] );
              return;
            }
            $.getJSON( "<?php echo $GLOBALS['web_root'] ?>/interface/billing/ub04_helpers.php", request, function( data, status, xhr ) {
              cache[ term ] = data;
              response( data );
            })
        }
    }).dblclick(function(event) {
        $(this).autocomplete('search'," ");
       });
}

// This is invoked by <select onchange> for the various dropdowns,
// including search results.
function codeselect(selobj) {
 var i = selobj ? selobj.selectedIndex : -1;
 if (i) {
  top.restoreSession();
  var f = document.forms[0];
  if (selobj) f.newcodes.value = selobj.options[i].value;
  f.submit();
 }
}

function copayselect() {
 top.restoreSession();
 var f = document.forms[0];
 f.newcodes.value = 'COPAY||';
 f.submit();
}

<?php echo $fs->jsLineItemValidation(); ?>

function validate(f) {
 if (f.bn_reopen) {
  var reopening = f.bn_reopen.clicked;
  var voiding = reopening && f.bn_reopen.clicked == 2;
  f.bn_reopen.clicked = false;
  if (reopening) {
   if (voiding) {
    if (!confirm('<?php echo xls('Re-opening this visit will cause a void. Payment information will need to be re-entered. Do you want to proceed?'); ?>')) {
     return false;
    }
   }
   top.restoreSession();
   return true;
  }
 }
 var refreshing = false;
 if (f.bn_refresh) {
  refreshing = f.bn_refresh.clicked ? true : false;
  f.bn_refresh.clicked = false;
 }
 if (f.bn_addmore) {
  refreshing = refreshing || f.bn_addmore.clicked;
  f.bn_addmore.clicked = false;
 }
 var searching = false;
 if (f.bn_search) {
  searching = f.bn_search.clicked ? true : false;
  f.bn_search.clicked  = false;
 }
 if (!refreshing && !searching) {
  if (!jsLineItemValidation(f)) return false;
 }
 top.restoreSession();
 return true;
}

// When a justify selection is made, apply it to the current list for
// this procedure and then rebuild its selection list.
//
function setJustify(seljust) {
 var theopts = seljust.options;
 var jdisplay = theopts[0].text;
 // Compute revised justification string.  Note this does nothing if
 // the first entry is still selected, which is handy at startup.
 if (seljust.selectedIndex > 0) {
  var newdiag = seljust.value;
  if (newdiag.length == 0) {
   jdisplay = '';
  }
  else {
   if (jdisplay.length) jdisplay += ',';
   jdisplay += newdiag;
  }
 }
 // Rebuild selection list.
 var jhaystack = ',' + jdisplay + ',';
 var j = 0;
 theopts.length = 0;
 theopts[j++] = new Option(jdisplay,jdisplay,true,true);
 for (var i = 0; i < diags.length; ++i) {
  if (jhaystack.indexOf(',' + diags[i] + ',') < 0) {
   theopts[j++] = new Option(diags[i],diags[i],false,false);
  }
 }
 theopts[j++] = new Option('Clear','',false,false);
}

// Determine if there are any charges in this visit.
function hasCharges() {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var elem = f.elements[i];
  if (elem.name.indexOf('[price]') > 0) {
   var fee = Number(elem.value);
   if (!isNaN(fee) && fee != 0) return true;
  }
 }
 return false;
}

// Function to check if there are any charges in the form, and to enable
// or disable the Save and Close button accordingly.
//
function setSaveAndClose() {
 var f = document.forms[0];
 if (!f.bn_save_close) return;
 if (hasCharges()) {
  f.form_has_charges.value = '1';
  f.bn_save_close.value = '<?php echo xla('Save and Checkout'); ?>';
 }
 else {
  f.form_has_charges.value = '0';
  f.bn_save_close.value = '<?php echo xla('Save and Close'); ?>';
 }
}

// Open the add-event dialog.
function newEvt() {
 var f = document.forms[0];
 var url = '../../main/calendar/add_edit_event.php?patientid=<?php echo urlencode($fs->pid); ?>';
 if (f.ProviderID && f.ProviderID.value) {
  url += '&userid=' + parseInt(f.ProviderID.value);
 }
 dlgopen(url, '_blank', 600, 300);
 return false;
}

function warehouse_changed(sel) {
 if (!confirm('<?php echo xls('Do you really want to change Warehouse?'); ?>')) {
  // They clicked Cancel so reset selection to its default state.
  for (var i = 0; i < sel.options.length; ++i) {
   sel.options[i].selected = sel.options[i].defaultSelected;
  }
 }
}

// Invoked when a line item price level is changed.
function pricelevel_changed(sel) {
 var f = document.forms[0];
 var prname = sel.name.replace('pricelevel', 'price');
 if (f[prname]) {
  var price = parseFloat(sel.options[sel.selectedIndex].id.substring(4));
  if (isNaN(price)) price = 0;
  f[prname].value = price;
 }
 else {
  alert('<?php echo xls('Form element not found'); ?>: ' + prname);
 }
}

</script>

</head>

<body class="body_top">
<form method="post" action="<?php echo $rootdir; ?>/forms/fee_sheet/new.php?<?php
 echo "rde=" . urlencode($rapid_data_entry) . "&addmore=" . urlencode($add_more_items); ?>"
 onsubmit="return validate(this)">
<span class="title"><?php echo xlt('Fee Sheet'); ?></span><br>
<input type='hidden' name='newcodes' value=''>

<center>

<?php
$isBilled = !$add_more_items && isEncounterBilled($fs->pid, $fs->encounter);
if ($isBilled) {
    echo "<p><font color='green'>" .
    xlt("This encounter has been billed. To make changes, re-open it or select Add More Items.") .
    "</font></p>\n";
} else { // the encounter is not yet billed
?>

<table width='95%'>
<?php
$i = 0;
$last_category = '';

// Create drop-lists based on the fee_sheet_options table.
$res = sqlStatement("SELECT * FROM fee_sheet_options " .
  "ORDER BY fs_category, fs_option");
while ($row = sqlFetchArray($res)) {
    $fs_category = $row['fs_category'];
    $fs_option   = $row['fs_option'];
    $fs_codes    = $row['fs_codes'];
    if ($fs_category !== $last_category) {
        endFSCategory();
        $last_category = $fs_category;
        ++$i;
        echo ($i <= 1) ? " <tr>\n" : "";
        echo "  <td width='50%' align='center' nowrap>\n";
        echo "   <select style='width:96%' onchange='codeselect(this)'>\n";
        echo "    <option value=''> " . xlt(substr($fs_category, 1)) . "</option>\n";
    }

    echo "    <option value='" . attr($fs_codes) . "'>" . xlt(substr($fs_option, 1)) . "</option>\n";
}

endFSCategory();

// Create drop-lists based on categories defined within the codes.
$pres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'superbill' AND activity = 1 ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
    global $code_types;
    ++$i;
    echo ($i <= 1) ? " <tr>\n" : "";
    echo "  <td width='50%' align='center' nowrap>\n";
    echo "   <select style='width:96%' onchange='codeselect(this)'>\n";
    echo "    <option value=''> " . text(xl_list_label($prow['title'])) . "\n";
    $res = sqlStatement("SELECT code_type, code, code_text,modifier FROM codes " .
    "WHERE superbill = ? AND active = 1 " .
    "ORDER BY code_text", array($prow['option_id']));
    while ($row = sqlFetchArray($res)) {
        $ctkey = $fs->alphaCodeType($row['code_type']);
        if ($code_types[$ctkey]['nofs']) {
            continue;
        }

        echo "    <option value='" . attr($ctkey) . "|" .
        attr($row['code']) . ':'. attr($row['modifier']) . "|'>" . text($row['code_text']) . "</option>\n";
    }

    echo "   </select>\n";
    echo "  </td>\n";
    if ($i >= $FEE_SHEET_COLUMNS) {
        echo " </tr>\n";
        $i = 0;
    }
}

// Create one more drop-list, for Products.
if ($GLOBALS['sell_non_drug_products']) {
    ++$i;
    echo ($i <= 1) ? " <tr>\n" : "";
    echo "  <td width='50%' align='center' nowrap>\n";
    echo "   <select name='Products' style='width:96%' onchange='codeselect(this)'>\n";
    echo "    <option value=''> " . xlt('Products') . "\n";
    $tres = sqlStatement("SELECT dt.drug_id, dt.selector, d.name " .
    "FROM drug_templates AS dt, drugs AS d WHERE " .
    "d.drug_id = dt.drug_id AND d.active = 1 AND d.consumable = 0 " .
    "ORDER BY d.name, dt.selector, dt.drug_id");
    while ($trow = sqlFetchArray($tres)) {
        echo "    <option value='PROD|" . attr($trow['drug_id']) . '|' . attr($trow['selector']) . "'>";
        echo text($trow['name']);
        if ($trow['name'] !== $trow['selector']) {
            echo ' / ' . text($trow['selector']);
        }

        echo "</option>\n";
    }

    echo "   </select>\n";
    echo "  </td>\n";
    if ($i >= $FEE_SHEET_COLUMNS) {
        echo " </tr>\n";
        $i = 0;
    }
}

$search_type = $default_search_type;
if ($_POST['search_type']) {
    $search_type = $_POST['search_type'];
}

$ndc_applies = true; // Assume all payers require NDC info.

echo $i ? "  <td></td>\n </tr>\n" : "";
echo " <tr>\n";
echo "  <td colspan='" . attr($FEE_SHEET_COLUMNS) . "' align='center' nowrap>\n";

// If Search was clicked, do it and write the list of results here.
// There's no limit on the number of results!
//
$numrows = 0;
if ($_POST['bn_search'] && $_POST['search_term']) {
    $res = main_code_set_search($search_type, $_POST['search_term']);
    if (!empty($res)) {
        $numrows = sqlNumRows($res);
    }
}

echo "   <select name='Search Results' style='width:98%' " .
  "onchange='codeselect(this)'";
if (! $numrows) {
    echo ' disabled';
}

echo ">\n";
echo "    <option value=''> " . xlt("Search Results") . " ($numrows " . xlt("items") . ")\n";

if ($numrows) {
    while ($row = sqlFetchArray($res)) {
        $code = $row['code'];
        if ($row['modifier']) {
            $code .= ":" . $row['modifier'];
        }

        echo "    <option value='" . attr($search_type) . "|" . attr($code) . "|'>" . text($code) . " " .
        text($row['code_text']) . "</option>\n";
    }
}

echo "   </select>\n";
echo "  </td>\n";
echo " </tr>\n";
?>

</table>

<p style='margin-top:8px;margin-bottom:8px'>
<table>
 <tr>
<?php if ($fs->ALLOW_COPAYS) { ?>
  <td>
   <input type='button' value='<?php echo xla('Add Copay');?>'
    onclick="copayselect()" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
<?php } ?>
  <td>
    <?php echo xlt('Search'); ?>&nbsp;
  </td>
  <td class='billcell'>
<?php
  $nofs_code_types = array();
foreach ($code_types as $key => $value) {
    if (!empty($value['nofs'])) {
        continue;
    }

    $nofs_code_types[$key] = $value;
}

  $size_select = (count($nofs_code_types) < 5) ? count($nofs_code_types) : 5;
?>
<?php
foreach ($nofs_code_types as $key => $value) {
    echo "   <label><input type='radio' name='search_type' value='" . attr($key) . "'";
    if ($key == $search_type) {
        echo " checked";
    }

    echo " />" . xlt($value['label']) . "</label>&nbsp;\n";
}
?>
  </td>
  <td class='billcell'>
    <?php echo xlt('for'); ?>&nbsp;
  </td>
  <td>
   <input type='text' name='search_term' value=''> &nbsp;
  </td>
  <td>
   <input type='submit' name='bn_search' value='<?php echo xla('Search');?>'
    onclick='return this.clicked = true;'>
  </td>
 </tr>
</table>
</p>
<p style='margin-top:16px;margin-bottom:8px'>

<?php } // end encounter not billed ?>

<table cellspacing='5'>
    <?php if ($billresult) { ?>
 <tr>
  <td class='billcell'><b><?php echo xlt('Type');?></b></td>
  <td class='billcell'><b><?php echo xlt('Code');?></b></td>
  <td class='billcell'><b><?php echo xlt('Description');?></b></td>
<?php if ($institutional) { ?>
  <td class='billcell'><b><?php echo xlt('Revenue');?></b></td>
<?php } ?>
<?php if (modifiers_are_used(true)) { ?>
  <td class='billcell'><b><?php echo xlt('Modifiers');?></b></td>
<?php } ?>
<?php if (fees_are_used()) { ?>
<?php if ($price_levels_are_used) { ?>
  <td class='billcell' align='center'><b><?php echo xlt('Price Level');?></b>&nbsp;</td>
<?php } ?>
  <td class='billcell' align='right'><b><?php echo xlt('Price');?></b>&nbsp;</td>
  <td class='billcell' align='center'><b><?php echo xlt('Units');?></b></td>
<?php } ?>
<?php if (justifiers_are_used()) { ?>
  <td class='billcell' align='center'<?php echo $justifystyle; ?>><b><?php echo xlt('Justify');?></b></td>
<?php } ?>
  <td class='billcell' align='center' <?php echo $liprovstyle; ?>><b><?php echo xlt('Provider/Warehouse');?></b></td>
  <td class='billcell' align='center'<?php echo $usbillstyle; ?>><b><?php echo xlt('Note Codes');?></b></td>
  <td class='billcell' align='center'<?php echo $usbillstyle; ?>><b><?php echo xlt('Auth');?></b></td>
<?php if ($GLOBALS['gbl_auto_create_rx']) { ?>
  <td class='billcell' align='center'><b><?php echo xlt('Rx'); ?></b></td>
<?php } ?>
  <td class='billcell' align='center'><b><?php echo xlt('Delete');?></b></td>
 </tr>
    <?php } ?>
<?php
$justinit = "var f = document.forms[0];\n";

// Generate lines for items already in the billing table for this encounter,
// and also set the rendering provider if we come across one.
//
// $bill_lino = 0;
if ($billresult) {
    foreach ($billresult as $iter) {
        if (!$ALLOW_COPAYS && $iter["code_type"] == 'COPAY') {
            continue;
        }

        if ($iter["code_type"] == 'TAX') {
            continue;
        }

        // ++$bill_lino;
        $bill_lino = count($fs->serviceitems);
        $bline = $_POST['bill']["$bill_lino"];
        $del = $bline['del']; // preserve Delete if checked
        if ($institutional) {
            $revenue_code   = trim($iter["revenue_code"]);
        }
        $modifier   = trim($iter["modifier"]);
        $units      = $iter["units"];
        $fee        = $iter["fee"];
        $authorized = $iter["authorized"];
        $ndc_info   = $iter["ndc_info"];
        $justify    = trim($iter['justify']);
        $notecodes  = trim($iter['notecodes']);
        if ($justify) {
            $justify = substr(str_replace(':', ',', $justify), 0, strlen($justify) - 1);
        }

        $provider_id = $iter['provider_id'];

        // Also preserve other items from the form, if present.
        if ($bline['id'] && !$iter["billed"]) {
            if ($institutional) {
                //$revenue_code   = trim($bline['revenue_code']);
            }
            $modifier   = trim($bline['mod']);
            $units      = max(1, intval(trim($bline['units'])));
            $fee        = formatMoneyNumber((0 + trim($bline['price'])) * $units);
            $authorized = $bline['auth'];
            $ndc_info   = '';
            if ($bline['ndcnum']) {
                $ndc_info = 'N4' . trim($bline['ndcnum']) . '   ' . $bline['ndcuom'] .
                trim($bline['ndcqty']);
            }

            $justify    = $bline['justify'];
            $notecodes  = trim($bline['notecodes']);
            $provider_id = 0 + (int)$bline['provid'];
        }

        if ($iter['code_type'] == 'COPAY') { // moved copay display to below
            continue;
        }

        $fs->addServiceLineItem(array(
        'codetype'    => $iter['code_type'],
        'code'        => trim($iter['code']),
        'revenue_code'    => $revenue_code,
        'modifier'    => $modifier,
        'ndc_info'    => $ndc_info,
        'auth'        => $authorized,
        'del'         => $del,
        'units'       => $units,
        'pricelevel'  => $iter['pricelevel'],
        'fee'         => $fee,
        'id'          => $iter['id'],
        'billed'      => $iter['billed'],
        'code_text'   => trim($iter['code_text']),
        'justify'     => $justify,
        'provider_id' => $provider_id,
        'notecodes'   => $notecodes,
        ));
    }
}

$resMoneyGot = sqlStatement(
    "SELECT pay_amount as PatientPay,session_id as id,date(post_time) as date ".
    "FROM ar_activity where pid =? and encounter =? and payer_type=0 and account_code='PCP'",
    array($fs->pid, $fs->encounter)
); //new fees screen copay gives account_code='PCP'
while ($rowMoneyGot = sqlFetchArray($resMoneyGot)) {
    $PatientPay=$rowMoneyGot['PatientPay']*-1;
    $id=$rowMoneyGot['id'];
    $fs->addServiceLineItem(array(
    'codetype'    => 'COPAY',
    'code'        => '',
    'modifier'    => '',
    'ndc_info'    => $rowMoneyGot['date'],
    'auth'        => 1,
    'del'         => '',
    'units'       => '',
    'fee'         => $PatientPay,
    'id'          => $id,
    ));
}

// Echo new billing items from this form here, but omit any line
// whose Delete checkbox is checked.
//
if ($_POST['bill']) {
    foreach ($_POST['bill'] as $key => $iter) {
        if ($iter["id"]) {
            continue; // skip if it came from the database
        }

        if ($iter["del"]) {
            continue; // skip if Delete was checked
        }

        $ndc_info = '';
        if ($iter['ndcnum']) {
            $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
            trim($iter['ndcqty']);
        }

        $units = max(1, intval(trim($iter['units'])));
        $fee = formatMoneyNumber((0 + trim($iter['price'])) * $units);
        //the date is passed as $ndc_info, since this variable is not applicable in the case of copay.
        $ndc_info = '';
        if ($iter['code_type'] == 'COPAY') {
            $ndc_info = date("Y-m-d");
            if ($fee > 0) {
                $fee = 0 - $fee;
            }
        }

        $fs->addServiceLineItem(array(
        'codetype'    => $iter['code_type'],
        'code'        => trim($iter['code']),
        'revenue_code'        => trim($iter['revenue_code']),
        'modifier'    => trim($iter["mod"]),
        'ndc_info'    => $ndc_info,
        'auth'        => $iter['auth'],
        'del'         => $iter['del'],
        'units'       => $units,
        'fee'         => $fee,
        'justify'     => $iter['justify'],
        'provider_id' => $iter['provid'],
        'notecodes'   => $iter['notecodes'],
        'pricelevel'  => $iter['pricelevel'],
        ));
    }
}

// Generate lines for items already in the drug_sales table for this encounter.
//
$query = "SELECT ds.*, di.warehouse_id FROM drug_sales AS ds, drug_inventory AS di WHERE " .
  "ds.pid = ? AND ds.encounter = ?  AND di.inventory_id = ds.inventory_id " .
  "ORDER BY sale_id";
$sres = sqlStatement($query, array($fs->pid, $fs->encounter));
// $prod_lino = 0;
while ($srow = sqlFetchArray($sres)) {
  // ++$prod_lino;
    $prod_lino = count($fs->productitems);
    $pline = $_POST['prod']["$prod_lino"];
    $rx    = !empty($srow['prescription_id']);
    $del   = $pline['del']; // preserve Delete if checked
    $sale_id = $srow['sale_id'];
    $drug_id = $srow['drug_id'];
    $selector = $srow['selector'];
    $pricelevel = $srow['pricelevel'];
    $units   = $srow['quantity'];
    $fee     = $srow['fee'];
    $billed  = $srow['billed'];
    $warehouse_id  = $srow['warehouse_id'];
  // Also preserve other items from the form, if present and unbilled.
    if ($pline['sale_id'] && !$srow['billed']) {
        $units = max(1, intval(trim($pline['units'])));
        $fee   = formatMoneyNumber((0 + trim($pline['price'])) * $units);
        $rx    = !empty($pline['rx']);
    }

    $fs->addProductLineItem(array(
    'drug_id'      => $drug_id,
    'selector'     => $selector,
    'pricelevel'   => $pricelevel,
    'rx'           => $rx,
    'del'          => $del,
    'units'        => $units,
    'fee'          => $fee,
    'sale_id'      => $sale_id,
    'billed'       => $billed,
    'warehouse_id' => $warehouse_id,
    ));
}

// Echo new product items from this form here, but omit any line
// whose Delete checkbox is checked.
//
if ($_POST['prod']) {
    foreach ($_POST['prod'] as $key => $iter) {
        if ($iter["sale_id"]) {
            continue; // skip if it came from the database
        }

        if ($iter["del"]) {
            continue; // skip if Delete was checked
        }

        $units = max(1, intval(trim($iter['units'])));
        $fee   = formatMoneyNumber((0 + trim($iter['price'])) * $units);
        $rx    = !empty($iter['rx']); // preserve Rx if checked
        $warehouse_id = empty($iter['warehouse_id']) ? '' : $iter['warehouse_id'];
        $fs->addProductLineItem(array(
        'drug_id'      => $iter['drug_id'],
        'selector'     => $iter['selector'],
        'pricelevel'   => $iter['pricelevel'],
        'rx'           => $rx,
        'units'        => $units,
        'fee'          => $fee,
        'warehouse_id' => $warehouse_id,
        ));
    }
}

// If new billing code(s) were <select>ed, add their line(s) here.
//
if ($_POST['newcodes'] && !$alertmsg) {
    $arrcodes = explode('~', $_POST['newcodes']);

  // A first pass here checks for any sex restriction errors.
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }

        list($newtype, $newcode) = explode('|', $codestring);
        if ($newtype == 'MA') {
            list($code, $modifier) = explode(":", $newcode);
            $tmp = sqlQuery(
                "SELECT sex FROM codes WHERE code_type = ? AND code = ? LIMIT 1",
                array($code_types[$newtype]['id'], $code)
            );
            if ($tmp['sex'] == '1' && $fs->patient_male || $tmp['sex'] == '2' && !$fs->patient_male) {
                  $alertmsg = xl('Service is not compatible with the sex of this client.');
            }
        }
    }

    if (!$alertmsg) {
        foreach ($arrcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            $arrcode = explode('|', $codestring);
            $newtype = $arrcode[0];
            $newcode = $arrcode[1];
            $newsel  = $arrcode[2];
            if ($newtype == 'COPAY') {
                $tmp = sqlQuery("SELECT copay FROM insurance_data WHERE pid = ? " .
                "AND type = 'primary' ORDER BY date DESC LIMIT 1", array($fs->pid));
                $code = formatMoneyNumber(0 + $tmp['copay']);
                $fs->addServiceLineItem(array(
                  'codetype'    => $newtype,
                  'code'        => $code,
                  'ndc_info'    => date('Y-m-d'),
                  'auth'        => '1',
                  'units'       => '1',
                  'fee'         => formatMoneyNumber(0 - $code),
                ));
            } else if ($newtype == 'PROD') {
                $result = sqlQuery("SELECT dt.quantity, d.route " .
                "FROM drug_templates AS dt, drugs AS d WHERE " .
                "dt.drug_id = ? AND dt.selector = ? AND " .
                "d.drug_id = dt.drug_id", array($newcode,$newsel));
                $units = max(1, intval($result['quantity']));
                // By default create a prescription if drug route is set.
                $rx = !empty($result['route']);
                $fs->addProductLineItem(array(
                'drug_id'      => $newcode,
                'selector'     => $newsel,
                'rx'           => $rx,
                'units'        => $units,
                ));
            } else {
                list($code, $modifier) = explode(":", $newcode);
                $ndc_info = '';
                // If HCPCS, find last NDC string used for this code.
                if ($newtype == 'HCPCS' && $ndc_applies) {
                    $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
                    "code_type = ? AND code = ? AND ndc_info LIKE 'N4%' " .
                    "ORDER BY date DESC LIMIT 1", array($newtype,$code));
                    if (!empty($tmp)) {
                        $ndc_info = $tmp['ndc_info'];
                    }
                }

                $fs->addServiceLineItem(array(
                        'codetype'    => $newtype,
                        'code'        => $code,
                        'modifier'    => trim($modifier),
                        'ndc_info'    => $ndc_info,
                        ));
            }
        }
    }
}

// Write the form's line items.
echoServiceLines();
echoProductLines();
// Ensure DOM is updated.
echo "<script>reinitForm();</script>";
?>
</table>
</p>

<br />
&nbsp;

<?php
// Choose rendering and supervising providers.
echo "<span class='billcell'><b>\n";
echo xlt('Providers') . ": &nbsp;";

echo "&nbsp;&nbsp;" . xlt('Rendering') . "\n";
if ($GLOBALS['default_rendering_provider'] == '0') {
    $default_rid = '';
} elseif ($GLOBALS['default_rendering_provider'] == '1') {
    $default_rid = $fs->provider_id;
} else {
    $default_rid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : $fs->provider_id;
}
echo $fs->genProviderSelect('ProviderID', '-- ' . xl("Please Select") . ' --', $default_rid, $isBilled);

if (!$GLOBALS['ippf_specific']) {
    echo "&nbsp;&nbsp;" . xlt('Supervising') . "\n";
    echo $fs->genProviderSelect('SupervisorID', '-- '.xl("N/A").' --', $fs->supervisor_id, $isBilled);
}

echo "<input type='button' value='" . xla('New Appointment') . "' onclick='newEvt()' />\n";

echo "</b></span>\n";
?>

<p>
&nbsp;

<?php
if ($fs->contraception_code && !$isBilled) {
  // This will give the form save logic the associated contraceptive method.
    echo "<input type='hidden' name='ippfconmeth' value='" . attr($fs->contraception_code) . "'>\n";
  // If needed, this generates a dropdown to ask about prior contraception.
    echo $fs->generateContraceptionSelector();
}

// Allow the patient price level to be fixed here.
$plres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
if (true) {
    $pricelevel = $fs->getPriceLevel();
    echo "   <span class='billcell'><b>" . xlt('Default Price Level') . ":</b></span>\n";
    echo "   <select name='pricelevel'";
    if ($isBilled) {
        echo " disabled";
    }

    echo ">\n";
    while ($plrow = sqlFetchArray($plres)) {
        $key = $plrow['option_id'];
        $val = $plrow['title'];
        echo "    <option value='" . attr($key) . "'";
        if ($key == $pricelevel) {
            echo ' selected';
        }

        echo ">" . text(xl_list_label($val)) . "</option>\n";
    }

    echo "   </select>\n";
}
?>

&nbsp; &nbsp; &nbsp;

<?php if (!$isBilled) { // visit is not yet billed ?>
<input type='submit' name='bn_save' value='<?php echo xla('Save'); ?>' />
<input type='submit' name='bn_save_stay' value='<?php echo xla('Save Current'); ?>' />
<?php if ($rapid_data_entry) {
    echo " style='background-color:#cc0000';color:#ffffff'";
} ?>
<?php if ($GLOBALS['ippf_specific']) { // start ippf-only stuff ?>
<?php if ($fs->hasCharges) { // unbilled with charges ?>
<input type='submit' name='bn_save_close' value='<?php echo xla('Save and Checkout'); ?>' />
<?php } else { // unbilled with no charges ?>
<input type='submit' name='bn_save_close' value='<?php echo xla('Save and Close'); ?>' />
<?php } // end no charges ?>
&nbsp;
<?php } // end ippf-only ?>
<input type='submit' name='bn_refresh' onclick='return this.clicked = true;'
value='<?php echo xla('Refresh');?>' />
&nbsp;
<?php } else { // visit is billed ?>
<?php if ($fs->hasCharges) { // billed with charges ?>
<input type='button' value='<?php echo xla('Show Receipt'); ?>'
 onclick="top.restoreSession();location='../../patient_file/pos_checkout.php?framed=1<?php
    echo "&ptid=" . urlencode($fs->pid) . "&enc=" . urlencode($fs->encounter); ?>'" />
&nbsp;
<input type='submit' name='bn_reopen' onclick='return this.clicked = 2;'
 value='<?php echo xla('Void Checkout and Re-Open'); ?>' />
&nbsp;
<?php } else { ?>
<input type='submit' name='bn_reopen' onclick='return this.clicked = true;'
 value='<?php echo xla('Re-Open Visit'); ?>' />
&nbsp;
<?php } // end billed without charges ?>
<input type='submit' name='bn_addmore' onclick='return this.clicked = true;'
 value='<?php echo xla('Add More Items'); ?>' />
&nbsp;
<?php } // end billed ?>
<input type='hidden' name='form_has_charges' value='<?php echo $fs->hasCharges ? 1 : 0; ?>' />
<input type='hidden' name='form_checksum' value='<?php echo $current_checksum; ?>' />
<input type='hidden' name='form_alertmsg' value='<?php echo attr($alertmsg); ?>' />

<input type='button' value='<?php echo xla('Cancel');?>'
 onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />

</center>

</form>

<script type="text/javascript">
setSaveAndClose();

<?php
echo $justinit;
if ($alertmsg) {
    echo "alert('" . addslashes($alertmsg) . "');\n";
}
?>
</script>

</body>
</html>
<?php if (!empty($_POST['running_as_ajax'])) {
    exit();
} ?>
<?php require_once("review/initialize_review.php"); ?>
<?php require_once("code_choice/initialize_code_choice.php"); ?>
<?php if ($GLOBALS['ippf_specific']) {
    require_once("contraception_products/initialize_contraception_products.php");
} ?>
<script>
var translated_price_header="<?php echo xlt("Price");?>";

$( "[name='search_term']" ).keydown(function(event){
    if(event.keyCode==13){
        $("[name=bn_search]").trigger('click');
        return false;
     }
 });

$("[name=search_term]").focus();
</script>
