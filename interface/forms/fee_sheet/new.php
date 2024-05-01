<?php

/*
 * Fee Sheet Program used to create charges, copays and add diagnosis codes to the encounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2005-2022 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/FeeSheetHtml.class.php");
require_once("codes.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

//acl check
if (!AclMain::aclCheckForm('fee_sheet')) { ?>
    <script>alert(<?php echo xlj("Not authorized"); ?>)</script>;
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
    if (!empty($code_types[$code_type]['diag'])) {
        echo "diags.push(" . js_escape($code_type . "|" . $code) . ");\n";
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

        $strike1 = $strike2 = "";
        if ($li['del']) {
            $strike1 = "<del>";
            $strike2 = "</del>";
        }

        echo " <tr>\n";

        echo "  <td class='billcell'>$strike1" . ($codetype == 'COPAY' ? xlt('COPAY') : text($codetype)) . $strike2;
        // if the line to ouput is copay, show the date here passed as $ndc_info,
        // since this variable is not applicable in the case of copay.
        if ($codetype == 'COPAY') {
            if (!empty($ndc_info)) {
                echo "(" . text($ndc_info) . ")";
            }
            $ndc_info = '';
        }

        if ($id) {
            echo "<input type='hidden' name='bill[" . attr($lino) . "][id]' value='" . attr($id) . "' />";
        }

        echo "<input type='hidden' name='bill[" . attr($lino) . "][code_type]' value='" . attr($codetype) . "' />";
        echo "<input type='hidden' name='bill[" . attr($lino) . "][code]' value='" . attr($code) . "' />";
        echo "<input type='hidden' name='bill[" . attr($lino) . "][billed]' value='" . attr($billed) . "' />";
        if (isset($li['hidden']['method'])) {
            echo "<input type='hidden' name='bill[" . attr($lino) . "][method]'   value='" . attr($li['hidden']['method'  ]) . "' />";
            echo "<input type='hidden' name='bill[" . attr($lino) . "][cyp]'      value='" . attr($li['hidden']['cyp'     ]) . "' />";
            echo "<input type='hidden' name='bill[" . attr($lino) . "][methtype]' value='" . attr($li['hidden']['methtype']) . "' />";
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
                    echo "  <td class='billcell text-center'>";
                    echo $fs->genPriceLevelSelect('', ' ', $li['hidden']['codes_id'], '', $pricelevel, true);
                    echo "</td>\n";
                }

                // Price display is conditional.
                if ($fs->pricesAuthorized()) {
                    echo "  <td class='billcell text-center'>" . text(oeFormatMoney($li['price'])) . "</td>\n";
                } else {
                    echo "  <td class='billcell' style='display:none'>&nbsp;</td>\n";
                }

                if ($codetype != 'COPAY') {
                    echo "  <td class='billcell text-center'>" . text($li['units']) . "</td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }

                echo "  <td class='billcell text-center' $justifystyle>$justify</td>\n";
            }

            // Show provider for this line, showing the actual default provider if none.
            echo "  <td class='billcell text-center' $liprovstyle>";
            echo $fs->genProviderSelect(
                '',
                '-- ' . xl("Default") . ' --',
                $li['provid'] ? $li['provid'] : $fs->provider_id,
                true
            );
            echo "</td>\n";

            if (($code_types[$codetype]['claim'] ?? null) && !($code_types[$codetype]['diag'] ?? null)) {
                echo "  <td class='billcell text-center' $usbillstyle>" .
                text($li['notecodes']) . "</td>\n";
            } else {
                echo "  <td class='billcell text-center' $usbillstyle></td>\n";
            }

            echo "  <td class='billcell text-center' $usbillstyle><input type='checkbox'" .
            ($li['auth'] ? " checked" : "") . " disabled /></td>\n";

            if (!empty($GLOBALS['gbl_auto_create_rx'])) {
                echo "  <td class='billcell text-center'>&nbsp;</td>\n";
            }

            echo "  <td class='billcell text-center'><input type='checkbox'" .
            " disabled /></td>\n";
        } else { // not billed
            if ($institutional) {
                if ($codetype != 'COPAY' && $codetype != 'ICD10') {
                    echo "  <td class='billcell'>" .
                        "<input type='text' class='revcode form-control form-control-sm' name='bill[" . attr($lino) . "][revenue_code]' " .
                        "title='" . xla("Revenue Code for this item. Type to search or double click for list") . "' " .
                        "value='" . attr($revenue_code) . "' size='4'></td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }
            }
            if (modifiers_are_used(true)) {
                if ($codetype != 'COPAY' && (!empty($code_types[$codetype]['mod']) || $modifier)) {
                    echo "  <td class='billcell'><input type='text' class='form-control form-control-sm' name='bill[" . attr($lino) . "][mod]' " .
                       "title='" . xla("Multiple modifiers can be separated by colons or spaces, maximum of 4 (M1:M2:M3:M4)") . "' " .
                       "value='" . attr($modifier) . "' size='" . attr($code_types[$codetype]['mod']) . "' onkeyup='policykeyup(this)' onblur='formatModifier(this)' /></td>\n";
                } else {
                    echo "  <td class='billcell'>&nbsp;</td>\n";
                }
            }

            if (fees_are_used()) {
                if ($codetype == 'COPAY' || !empty($code_types[$codetype]['fee']) || !empty($fee)) {
                    if ($price_levels_are_used) {
                        echo "  <td class='billcell text-center'>";
                        echo $fs->genPriceLevelSelect("bill[$lino][pricelevel]", ' ', $li['hidden']['codes_id'], '', $pricelevel);
                        echo "</td>\n";
                    }

                    // Price display is conditional.
                    if ($fs->pricesAuthorized()) {
                        echo "  <td class='billcell text-right'>" .
                            "<input type='text' class='form-control form-control-sm' name='bill[$lino][price]' " .
                            "value='" . attr($li['price']) . "' size='6' onchange='setSaveAndClose()'";
                        if (!AclMain::aclCheckCore('acct', 'disc')) {
                            echo " readonly";
                        }
                        echo "></td>\n";
                    } else {
                        echo "  <td class='billcell' style='display:none'>" .
                            "<input type='text' name='bill[$lino][price]' " .
                            "value='" . attr($li['price'] ? 'X' : '0') . "'></td>\n";
                    }

                    echo "  <td class='billcell text-center'>";
                    if ($codetype != 'COPAY') {
                        echo "<input type='text' class='form-control form-control-sm text-right' name='bill[" . attr($lino) . "][units]' " .
                        "value='" . attr($li['units']) . "' size='2'>";
                    } else {
                        echo "<input type='hidden' name='bill[" . attr($lino) . "][units]' value='" . attr($li['units']) . "' />";
                    }

                    echo "</td>\n";

                    if (!empty($code_types[$codetype]['just']) || !empty($li['justify'])) {
                        echo "  <td class='billcell' align='center'$justifystyle>";
                        echo "<select class='form-control form-control-sm' name='bill[" . attr($lino) . "][justify]' onchange='setJustify(this)'>";
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
            echo "  <td class='billcell text-center' $liprovstyle>";
            echo $fs->genProviderSelect("bill[$lino][provid]", '-- ' . xl("Default") . ' --', $li['provid']);
            echo "</td>\n";

            if (!empty($code_types[$codetype]['claim']) && empty($code_types[$codetype]['diag'])) {
                echo "  <td class='billcell text-center' $usbillstyle><input type='text' class='form-control form-control-sm' name='bill[" . attr($lino) . "][notecodes]' " .
                "value='" . text($li['notecodes']) . "' maxlength='10' size='8' /></td>\n";
            } else {
                echo "  <td class='billcell text-center' $usbillstyle></td>\n";
            }

            echo "  <td class='billcell text-center' $usbillstyle><input type='checkbox' name='bill[" . attr($lino) . "][auth]' " .
            "value='1'" . ($li['auth'] ? " checked" : "") . " /></td>\n";

            if (!empty($GLOBALS['gbl_auto_create_rx'])) {
                echo "  <td class='billcell text-center'>&nbsp;</td>\n";   // KHY: May need to confirm proper location of this cell
            }

            echo "  <td class='billcell text-center'><input type='checkbox' name='bill[" . attr($lino) . "][del]' " .
            "value='1'" . ($li['del'] ? " checked" : "") . " /></td>\n";
        }

        echo " </tr>\n";

        // If NDC info exists or may be required, add a line for it.
        if (isset($li['ndcnum'])) {
            echo " <tr>\n";
            echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
            echo "  <td class='billcell' colspan='6'>&nbsp;NDC:&nbsp;";
            echo "<input type='text' class='form-control form-control-sm' name='bill[" . attr($lino) . "][ndcnum]' value='" . attr($li['ndcnum']) . "' " .
            "size='11' />";
            echo " &nbsp;Qty:&nbsp;";
            echo "<input type='text' class='form-control form-control-sm text-left' name='bill[" . attr($lino) . "][ndcqty]' value='" . attr($li['ndcqty']) . "' " .
            "size='3' />";
            echo " ";
            echo "<select class='form-control form-control-sm' name='bill[" . attr($lino) . "][ndcuom]'>";
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
        } elseif (!empty($li['ndc_info'])) {
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

        $description = $li['code_text'];
        if ($selector !== $description) {
            $description .= ' / ' . $selector;
        }

        $strike1 = ($sale_id && $del) ? "<s>" : "";
        $strike2 = ($sale_id && $del) ? "</s>" : "";

        echo " <tr>\n";
        echo "  <td class='billcell'>{$strike1}" . xlt("Product") . "$strike2";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][sale_id]' value='" . attr($sale_id) . "' />";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][drug_id]' value='" . attr($drug_id) . "' />";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][selector]' value='" . attr($selector) . "' />";
        echo "<input type='hidden' name='prod[" . attr($lino) . "][billed]' value='" . attr($billed) . "' />";
        if (isset($li['hidden']['method'])) {
            echo "<input type='hidden' name='prod[" . attr($lino) . "][method]' value='"   . attr($li['hidden']['method'  ]) . "' />";
            echo "<input type='hidden' name='prod[" . attr($lino) . "][methtype]' value='" . attr($li['hidden']['methtype']) . "' />";
        }

        echo "</td>\n";

        echo "  <td class='billcell'>$strike1" . text($drug_id) . "$strike2</td>\n";

        echo "  <td class='billcell'>$strike1" . text($description) . "$strike2</td>\n";

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

                // Price display is conditional.
                if ($fs->pricesAuthorized()) {
                    echo "  <td class='billcell text-right'>" . text(oeFormatMoney($price)) . "</td>\n";
                } else {
                    echo "  <td class='billcell' style='display:none'>&nbsp;</td>\n";
                }

                echo "  <td class='billcell text-right'>" . text($units) . "</td>\n";
            }

            if (justifiers_are_used()) { // KHY Evaluate proper position/usage of if justifiers
                echo "  <td class='billcell text-center' $justifystyle>&nbsp;</td>\n"; // justify
            }

            // Show warehouse for this line.
            echo "  <td class='billcell text-center' $liprovstyle>";
            echo $fs->genWarehouseSelect('', ' ', $warehouse_id, true, $drug_id, $sale_id > 0);
            echo "</td>\n";
            //
            echo "  <td class='billcell text-center' $usbillstyle>&nbsp;</td>\n"; // note codes
            echo "  <td class='billcell text-center' $usbillstyle>&nbsp;</td>\n"; // auth
            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell text-center'><input type='checkbox'" . // rx
                " disabled /></td>\n";
            }

            echo "  <td class='billcell text-center'><input type='checkbox'" .   // del
            " disabled /></td>\n";
        } else { // not billed
            if (fees_are_used()) {
                if ($price_levels_are_used) {
                    echo "  <td class='billcell text-center'>";
                    echo $fs->genPriceLevelSelect("prod[$lino][pricelevel]", ' ', $drug_id, $selector, $pricelevel);
                    echo "</td>\n";
                }

                // Price display is conditional.
                if ($fs->pricesAuthorized()) {
                    echo "  <td class='billcell text-right'>" .
                    "<input type='text' class='form-control' name='prod[" . attr($lino) . "][price]' " .
                    "value='" . attr($price) . "' size='6' onchange='setSaveAndClose()'";
                    if (!AclMain::aclCheckCore('acct', 'disc')) {
                        echo " readonly";
                    }
                    echo " /></td>\n";
                } else {
                    echo "  <td class='billcell' style='display:none'>" .
                    "<input type='text' name='prod[" . attr($lino) . "][price]' " .
                    "value='" . ($price ? 'X' : '0') . "' /></td>\n";
                }

                echo "  <td class='billcell text-center'>";
                echo "<input type='text' class='form-control' name='prod[" . attr($lino) . "][units]' " .
                "value='" . attr($units) . "' size='2'>";
                echo "</td>\n";
            }

            if (justifiers_are_used()) {
                echo "  <td class='billcell'$justifystyle>&nbsp;</td>\n"; // justify
            }

            // Generate warehouse selector if there is a choice of warehouses.
            echo "  <td class='billcell text-center' $liprovstyle>";
            echo $fs->genWarehouseSelect("prod[$lino][warehouse]", ' ', $warehouse_id, false, $drug_id, $sale_id > 0);
            echo "</td>\n";
            //
            echo "  <td class='billcell text-center' $usbillstyle>&nbsp;</td>\n"; // note codes
            echo "  <td class='billcell text-center' $usbillstyle>&nbsp;</td>\n"; // auth
            if ($GLOBALS['gbl_auto_create_rx']) {
                echo "  <td class='billcell text-center'>" .
                "<input type='checkbox' name='prod[" . attr($lino) . "][rx]' value='1'" .
                ($rx ? " checked" : "") . " /></td>\n";
            }

            echo "  <td class='billcell text-center'><input type='checkbox' name='prod[" . attr($lino) . "][del]' " .
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

// this is for a save before we open justify dialog.
// otherwise current form state is over written in justify process.
if (!empty($_POST['running_as_ajax']) && !empty($_POST['dx_update'])) {
    $main_provid = (int) $_POST['ProviderID'];
    $main_supid  = (int) $_POST['SupervisorID'];
    $fs->save(
        $_POST['bill'],
        $_POST['prod'],
        $main_provid,
        $main_supid,
        $_POST['default_warehouse'] ?? null,
        $_POST['bn_save_close'] ?? null
    );

    unset($_POST['dx_update']);
    unset($_POST['bill']);
    unset($_POST['prod']);
}

// It's important to look for a checksum mismatch even if we're just refreshing
// the display, otherwise the error goes undetected on a refresh-then-save.
if (isset($_POST['form_checksum'])) {
    if ($_POST['form_checksum'] != $current_checksum) {
        $alertmsg = xl('Someone else has just changed this visit. Please cancel this page and try again.');
        $comment = "CHECKSUM ERROR, expecting '{$_POST['form_checksum']}'";
        EventAuditLogger::instance()->newEvent(
            "checksum",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            $comment,
            $pid,
            'open-emr',
            'fee sheet'
        );
    }
}

if (!$alertmsg && (!empty($_POST['bn_save']) || !empty($_POST['bn_save_close']))) {
    $alertmsg = $fs->checkInventory($_POST['prod']);
}

// If Save or Save-and-Close was clicked, save the new and modified billing
// lines; then if no error, redirect to $GLOBALS['form_exit_url'].
//
if (!$alertmsg && (!empty($_POST['bn_save']) || !empty($_POST['bn_save_close']) || !empty($_POST['bn_save_stay']))) {
    $main_provid = (int) ($_POST['ProviderID'] ?? 0);
    $main_supid  = 0 + (int)($_POST['SupervisorID'] ?? 0);

    $fs->save(
        $_POST['bill'],
        $_POST['prod'],
        $main_provid,
        $main_supid,
        ($_POST['default_warehouse'] ?? null),
        ($_POST['bn_save_close'] ?? null)
    );

    if (!empty($_POST['bn_save_stay'])) {
        $current_checksum = $fs->visitChecksum();
    }

    // Note: Taxes are computed at checkout time (in pos_checkout.php which
    // also posts to SL).  Currently taxes with insurance claims make no sense,
    // so for now we'll ignore tax computation in the insurance billing logic.

    if (!empty($_POST['running_as_ajax'])) {
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
            $tmp_form_id = $fs->doContraceptionForm(
                $_POST['ippfconmeth'] ?? '',
                $_POST['newmauser'] ?? '',
                $main_provid
            );
            if ($tmp_form_id) {
                // Contraceptive method does not match existing contraception data for this visit,
                // or there is no such data.  Open a new or existing Contraception Summary form.
                $tmpurl = "{$GLOBALS['rootdir']}/patient_file/encounter/view_form.php" .
                    "?formname=LBFcontra&id=" . ($tmp_form_id < 0 ? 0 : urlencode($tmp_form_id));
                if (!empty($_POST['bn_save_close']) && !empty($_POST['form_has_charges'])) {
                    $tmpurl .= "&from_save_and_checkout=1";
                }
                formJump($tmpurl);
                formFooter();
                exit;
            }
        }

        if ($rapid_data_entry || (!empty($_POST['bn_save_close']) && !empty($_POST['form_has_charges']))) {
            // In rapid data entry mode or if "Save and Checkout" was clicked,
            // we go directly to the Checkout page.
            formJump("{$GLOBALS['rootdir']}/patient_file/pos_checkout.php?framed=1" .
            "&ptid=" . urlencode($fs->pid) . "&enid=" . urlencode($fs->encounter) . "&rde=" . urlencode($rapid_data_entry));
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
// If there was a checkout this will undo it unless the global 'void_checkout_reopen' is turned off
// then it just reopens the fee sheet for editing
if (!$alertmsg && (!empty($_POST['bn_reopen']) || !empty($_POST['form_reopen']))) {
    if ($GLOBALS['void_checkout_reopen']) {
        BillingUtilities::doVoid(
            $fs->pid,
            $fs->encounter,
            true,
            'all',
            $_POST['form_reason'],
            $_POST['form_notes']
        );
    } else {
        BillingUtilities::reOpenEncounterForBilling(
            $fs->pid,
            $fs->encounter
        );
    }
    $current_checksum = $fs->visitChecksum();
    // Remove the line items so they are refreshed from the database on redisplay.
    unset($_POST['bill']);
    unset($_POST['prod']);
}

$billresult = BillingUtilities::getBillingByEncounter($fs->pid, $fs->encounter, "*");
?>
<html>
<head>
<?php Header::setupHeader(['common', 'knockout', 'jquery-ui', 'jquery-ui-base']);?>
<script>
var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;
var diags = new Array();

<?php
if ($billresult) {
    foreach ($billresult as $iter) {
        genDiagJS($iter["code_type"], trim($iter["code"]));
    }
}

if (!empty($_POST['bill'])) {
    foreach ($_POST['bill'] as $iter) {
        if (!empty($iter["del"])) {
            continue; // skip if Delete was checked
        }

        if (!empty($iter["id"])) {
            continue; // skip if it came from the database
        }

        genDiagJS($iter["code_type"], $iter["code"]);
    }
}

if (!empty($_POST['newcodes'])) {
    $arrcodes = explode('~', $_POST['newcodes']);
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }

        $arrcode = explode('|', $codestring);
        if (strpos($arrcode[1], ':') !== false) {
            $tmp = explode(':', $arrcode[1]);
            $code = $tmp[0] ?? '';
            $modifier = $tmp[1] ?? '';
            $modifier .= ($tmp[2] ?? '') ? ":" . $tmp[2] : '';
            $modifier .= ($tmp[3] ?? '') ? ":" . $tmp[3] : '';
            $modifier .= ($tmp[4] ?? '') ? ":" . $tmp[4] : '';
        } else {
            $code = $arrcode[1];
            $modifier = '';
        }
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
 let i = selobj ? selobj.selectedIndex : -1;
 if (i) {
  top.restoreSession();
  let f = document.forms[0];
  if (selobj) {
      f.newcodes.value = selobj.options[i].value;
  }
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

// Submit the form to complete a void operation.
function voidwrap(form_reason, form_notes) {
  top.restoreSession();
  var f = document.forms[0];
  f.form_reason.value = form_reason;
  f.form_notes.value  = form_notes;
  f.form_reopen.value = '1';
  f.submit();
}

function validate(f) {
 if (f.bn_reopen) {
  var reopening = f.bn_reopen.clicked;
  <?php if ($GLOBALS['void_checkout_reopen']) { ?>
  var voiding = reopening && f.bn_reopen.clicked == 2;
  <?php } else { ?>
  var voiding = false;
  <?php } ?>
  f.bn_reopen.clicked = false;
  if (reopening) {
   if (voiding) {
    if (!confirm(<?php echo xlj('Re-opening this visit will cause a void. Payment information will need to be re-entered. Do you want to proceed?'); ?>)) {
     return false;
    }
    // Collect void reason and notes.
    dlgopen('../../patient_file/void_dialog.php', '_blank', 500, 450);
    return false;
   } else {
    if (!confirm(<?php echo xlj('Do you want to re-open this visit for billing?'); ?>)) {
     return false;
    }
    var f = document.forms[0];
    f.form_reopen.value = '1';
    f.submit();
    return false;
   }
   top.restoreSession();
   return true;
  }
 }
 if (!f.ProviderID.value) {
  alert(<?php echo xlj("Please select a default provider."); ?>);
  return false;
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
   if (elem.value == 'X') return true; // X means a nonzero price is undisclosed.
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
  f.bn_save_close.value = <?php echo xlj('Save and Checkout'); ?>;
 }
 else {
  f.form_has_charges.value = '0';
  f.bn_save_close.value = <?php echo xlj('Save and Close'); ?>;
 }
 f.bn_save_close.innerHTML = f.bn_save_close.value; // Required for Bootstrap 4
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
 if (!confirm(<?php echo xlj('Do you really want to change Warehouse?'); ?>)) {
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
  var price = sel.options[sel.selectedIndex].id.substring(4);
  if (price != 'X') { // X means a nonzero price is undisclosed.
   price = parseFloat(price);
   if (isNaN(price)) price = 0;
  }
  f[prname].value = price;
 }
 else {
  alert(<?php echo xlj('Form element not found'); ?> + ': ' + prname);
 }
}

// Invoked when the default price level changes and sets all unbilled line items to that level.
function defaultPriceLevelChanged(sel) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var elem = f.elements[i];
  if (elem.name.indexOf('[pricelevel]') > 0) {
    elem.selectedIndex = sel.selectedIndex + 1;
    pricelevel_changed(elem);
  }
 }
}

function formatModifier(e) {
    let mods = e.value;
    mods = mods.substring(0, 11).trim();
    let modArray = mods.includes(':') ? mods.split(":") : mods.split(" ");
    let cntr = 0;
    modArray.forEach( function(m) {
        let l = m.length;
        if (l) {
            cntr++;
            if (l !== 2) {
               alert("Removing invalid modifier " + m);
               modArray.pop();
            }
        } else {
            modArray.pop();
        }
    });
    
    let modString = modArray.join(":");
    e.value = checkLastChar(modString);
}

function checkLastChar(s) {
    let last_char = s.slice(-1);
    if (last_char === ':') {
        s = s.substring(0, s.length - 1);
        return checkLastChar(s);
    } else {
        return s;
    }
}

</script>
<style>
    @media only screen and (max-width: 1024px) {
        div.category-display{
            width: 100% !important;
        }
        div.category-display > button {
        width: 75% !important;
        }
    }
</style>
<?php
$enrow = sqlQuery(
    "SELECT p.fname, p.mname, p.lname, fe.date FROM " .
    "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
    "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
    "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
    "fe.id = f.form_id LIMIT 1",
    array($pid, $encounter)
);
$name = $enrow['fname'] . ' ';
$name .= (!empty($enrow['mname'])) ? $enrow['mname'] . ' ' . $enrow['lname'] : $enrow['lname'];
$date = xl('for Encounter on') . ' ' . oeFormatShortDate(substr($enrow['date'], 0, 10));
$title = array(xl('Fee Sheet for'), text($name), text($date));
$heading =  join(" ", $title);
?>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl($heading),
    'include_patient_name' => false,// use only in appropriate pages
    'expandable' => true,
    'expandable_files' => array("fee_sheet_new_xpd"),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "fee_sheet_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>


<body>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
        <div class="row">
            <div class="col-12">
                <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
       </div>
        <div class="row">
            <div class="col-12">
                <form method="post" name="fee_sheet_form" id="fee_sheet_form" action="<?php echo $rootdir; ?>/forms/fee_sheet/new.php?<?php
                echo "rde=" . attr_url($rapid_data_entry) . "&addmore=" . attr_url($add_more_items); ?>"
                onsubmit="return validate(this)">
                    <input type='hidden' name='newcodes' value='' />
                    <?php
                    $isBilled = !$add_more_items && BillingUtilities::isEncounterBilled($fs->pid, $fs->encounter);
                    if ($isBilled) {
                        echo "<p class='text-success'>" .
                                xlt("This encounter has been billed. To make changes, re-open it or select Add More Items.") .
                             "</p>\n";
                    } else { // the encounter is not yet billed
                        ?>
                        <fieldset>
                        <legend><?php echo xlt('Set Price Level'); ?></legend>
                            <div class='form-group mx-5 text-center'>
                                <?php
                                // Allow the patient price level to be fixed here.
                                $plres = sqlStatement("SELECT option_id, title FROM list_options " .
                                "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
                                if (true) {
                                    $pricelevel = $fs->getPriceLevel();
                                    //echo "   <span class='billcell'><b>" . xlt('Default Price Level') . ":</b></span>\n";
                                    echo "   <select name='pricelevel' class='form-control' onchange='defaultPriceLevelChanged(this)' ";
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
                            </div>
                        </fieldset>

                    <fieldset>
                    <legend><?php echo xlt("Select Code")?></legend>
                    <div class='text-center'>
                        <table class="table" width="95%">
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
                                    // can cleave either one or two spaces from fs_category, fs_option to accomodate more than 9 custom categories
                                    $cleave_cat = is_numeric(substr($fs_category, 0, 2)) ? 2 : 1;
                                    $cleave_opt = is_numeric(substr($fs_option, 0, 2)) ? 2 : 1;
                                    echo ($i <= 1) ? " <tr>\n" : "";
                                    echo "  <td class='text-nowrap' width='50%'>\n";
                                    echo "   <select class='form-control' onchange='codeselect(this)'>\n";
                                    echo "    <option value=''> " . xlt(substr($fs_category, $cleave_cat)) . "</option>\n";
                                }
                                echo "    <option value='" . attr($fs_codes) . "'>" . xlt(substr($fs_option, $cleave_opt)) . "</option>\n";
                            }
                                endFSCategory();

                                // Create drop-lists based on categories defined within the codes.
                                $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                                "WHERE list_id = 'superbill' AND activity = 1 ORDER BY seq");
                            while ($prow = sqlFetchArray($pres)) {
                                global $code_types;
                                ++$i;
                                echo ($i <= 1) ? " <tr>\n" : "";
                                echo "  <td class='text-center text-nowrap' width='50%'>\n";
                                echo "   <select class='form-control' onchange='codeselect(this)'>\n";
                                echo "    <option value=''> " . text(xl_list_label($prow['title'])) . "\n";
                                $res = sqlStatement("SELECT code_type, code, code_text, modifier FROM codes " .
                                "WHERE superbill = ? AND active = 1 " .
                                "ORDER BY code_text", array($prow['option_id']));
                                while ($row = sqlFetchArray($res)) {
                                    $ctkey = $fs->alphaCodeType($row['code_type']);
                                    if ($code_types[$ctkey]['nofs']) {
                                        continue;
                                    }
                                    echo "    <option value='" . attr($ctkey) . "|" .
                                    attr($row['code']) . ':' . attr($row['modifier']) . "|'>" . text($row['code_text']) . "</option>\n";
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
                                echo "  <td class='text-center text-nowrap' width='50%'>\n";
                                echo "   <select name='Products' class='form-control' onchange='codeselect(this)'>\n";
                                echo "    <option value=''> " . xlt('Products') . "\n";
                                $tres = sqlStatement("SELECT dt.drug_id, dt.selector, d.name " .
                                "FROM drug_templates AS dt, drugs AS d WHERE " .
                                "d.drug_id = dt.drug_id AND d.active = 1 AND d.consumable = 0 " .
                                "ORDER BY d.name, dt.selector, dt.drug_id");
                                while ($trow = sqlFetchArray($tres)) {
                                    // Skip products that we don't have any of or that the user may not access.
                                    if (!isProductSelectable($trow['drug_id'])) {
                                        continue;
                                    }
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

                            $search_type = $GLOBALS['default_search_code_type'] ?? null;
                            if (!empty($_POST['search_type'])) {
                                $search_type = $_POST['search_type'];
                            }

                                $ndc_applies = true; // Assume all payers require NDC info.

                                echo $i ? "  <td></td>\n </tr>\n" : "";
                            ?>

                                </table>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend><?php echo xlt("Search for Additional Codes")?></legend>
                                <div class="text-center">
                                    <div class="form-group">
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

                                        <div class="btn-group" data-toggle="buttons">
                                            <?php
                                            foreach ($nofs_code_types as $key => $value) {
                                                echo"<label class='radio-inline btn btn-secondary'>";
                                                echo "   <input type='radio' name='search_type' value='" . attr($key) . "'";
                                                if ($key == $search_type) {
                                                    echo " checked";
                                                }
                                                echo " />&nbsp;" . xlt($value['label']) . "\n";
                                                echo " </label>";
                                            }
                                            ?>
                                        </div>
                                    </div>

                                <div class="mx-5 mb-3 text-center">
                                    <div class="input-group">
                                        <input type='text' class="form-control" name='search_term' value='' />
                                        <div class="input-group-append">
                                            <input type='submit' class='btn btn-primary' name='bn_search' value='<?php echo xla('Search');?>' onclick='return this.clicked = true;' />
                                        </div>
                                    </div>
                                </div>

                                <div class="mx-5 mb-3 text-center">
                                    <?php
                                    echo "<td colspan='" . attr($FEE_SHEET_COLUMNS) . "' class='text-center text-nowrap'>\n";

                                    // If Search was clicked, do it and write the list of results here.
                                    // There's no limit on the number of results!
                                    //
                                    $numrows = 0;
                                    if (!empty($_POST['bn_search']) && !empty($_POST['search_term'])) {
                                        $res = main_code_set_search($search_type, $_POST['search_term']);
                                        if (!empty($res)) {
                                            $numrows = sqlNumRows($res);
                                        }
                                    }
                                    if (empty($numrows)) {
                                        echo "   <select name='search_results' class='form-control text-danger' " .
                                        "onchange='codeselect(this)' disabled >\n";
                                    } else {
                                        echo "   <select name='search_results' style='background: lightyellow' " . "onchange='codeselect(this)' >\n";
                                    }

                                    echo "    <option value=''> " . xlt("Search Results") . " ($numrows " . xlt("items") . ")\n";

                                    if (!empty($numrows)) {
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
                                    ?>
                                </div>
                        </fieldset>

                    <?php } // end encounter not billed ?>
                    <fieldset>
                        <legend><?php echo xlt("Selected Fee Sheet Codes and Charges for Current Encounter")?></legend>
                        <div class='col-12'>
                            <table class="table" name='copay_review' id='copay_review'>
                                <tr>
                                    <?php
                                    if ($fs->ALLOW_COPAYS) { ?>
                                        <td class='col-md-6 float-right'>
                                            <button type="button" class="btn btn-primary btn-add" value='<?php echo xla('Add Copay'); ?>'
                                                onclick='copayselect()'>
                                                <?php echo xlt('Add Copay'); ?>
                                            </button>
                                        </td>
                                    <?php } ?>
                                </tr>
                            </table>
                        </div>
                        <div class='col-12 text-center table-responsive'>
                            <table name='selected_codes' id='selected_codes' class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th class='billcell'><?php echo xlt('Type');?></th>
                                        <th class='billcell'><?php echo xlt('Code');?></th>
                                        <th class='billcell'><?php echo xlt('Description');?></th>
                                        <?php if ($institutional) { ?>
                                            <th class='billcell'><?php echo xlt('Revenue');?></th>
                                        <?php } ?>
                                        <?php if (modifiers_are_used(true)) { ?>
                                            <th class='billcell'><?php echo xlt('Modifiers');?></th>
                                        <?php } ?>
                                        <?php if (fees_are_used()) { ?>
                                            <?php if ($price_levels_are_used) { ?>
                                                <th class='billcell'><?php echo xlt('Price Level');?>&nbsp;</th>
                                            <?php } ?>
                                            <!-- Price display is conditional. -->
                                            <?php if ($fs->pricesAuthorized()) { ?>
                                            <th class='billcell'><?php echo xlt('Price');?>&nbsp;</th>
                                            <?php } else { ?>
                                            <th class='billcell d-none'>&nbsp;</th>
                                            <?php } ?>
                                            <th class='billcell text-center'><?php echo xlt('Qty');?></th>
                                        <?php } ?>
                                        <?php if (justifiers_are_used()) { ?>
                                            <th class='billcell'<?php echo $justifystyle; ?>><?php echo xlt('Justify');?></th>
                                        <?php } ?>
                                        <th class='billcell' <?php echo $liprovstyle; ?>><?php echo xlt('Provider/Warehouse');?></th>
                                        <th class='billcell'<?php echo $usbillstyle; ?>><?php echo xlt('Note Codes');?></th>
                                        <th class='billcell'<?php echo $usbillstyle; ?>><?php echo xlt('Auth');?></th>
                                        <?php if (!empty($GLOBALS['gbl_auto_create_rx'])) { ?>
                                            <th class='billcell'><?php echo xlt('Rx'); ?></th>
                                        <?php } ?>
                                        <th class='billcell'><?php echo xlt('Delete');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $justinit = "var f = document.forms[0];\n";

                                    // Generate lines for items already in the billing table for this encounter,
                                    // and also set the rendering provider if we come across one.
                                    //
                                    // $bill_lino = 0;
                                if ($billresult) {
                                    foreach ($billresult as $iter) {
                                        if (empty($fs->ALLOW_COPAYS) && ($iter["code_type"] == 'COPAY')) {
                                            continue;
                                        }
                                        if ($iter["code_type"] == 'TAX') {
                                            continue;
                                        }
                                        // ++$bill_lino;
                                        $bill_lino = count($fs->serviceitems);
                                        $bline = $_POST['bill']["$bill_lino"] ?? null;
                                        $del = $bline['del'] ?? null; // preserve Delete if checked
                                        if ($institutional) {
                                            $revenue_code   = trim($iter["revenue_code"]);
                                        }
                                        $modifier   = trim($iter["modifier"] ?? '');
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
                                        if (!empty($bline['id']) && empty($iter["billed"])) {
                                            if ($institutional) {
                                                $revenue_code   = trim($bline['revenue_code']);
                                            }
                                            $modifier   = trim($bline['mod'] ?? '');
                                            $units = intval(trim($bline['units'] ?? ''));
                                            if (!$units) {
                                                $units = 1; // units may be negative.
                                            }
                                            // Price display is conditional.
                                            if ($fs->pricesAuthorized()) {
                                                $fee = formatMoneyNumber((trim($bline['price'] ?? 0)) * $units);
                                            }
                                            $authorized = $bline['auth'] ?? null;
                                            $ndc_info   = '';
                                            if (!empty($bline['ndcnum'])) {
                                                $ndc_info = 'N4' . trim($bline['ndcnum']) . '   ' . $bline['ndcuom'] .
                                                trim($bline['ndcqty']);
                                            }
                                            $justify    = $bline['justify'] ?? null;
                                            $notecodes  = trim($bline['notecodes'] ?? '');
                                            $provider_id = (int) ($bline['provid'] ?? null);
                                        }

                                        if ($iter['code_type'] == 'COPAY') { // moved copay display to below
                                            continue;
                                        }

                                        $fs->addServiceLineItem(array(
                                        'codetype'    => $iter['code_type'],
                                        'code'        => trim($iter['code']),
                                        'revenue_code'    => ($revenue_code ?? null),
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

                                if ($fs->ALLOW_COPAYS) {
                                    // Ajil added this 2012-04-28 and I don't know why. The condition above disables it. --Rod
                                    $resMoneyGot = sqlStatement(
                                        "SELECT pay_amount as PatientPay,session_id as id, date(post_time) as date " .
                                        "FROM ar_activity where deleted IS NULL AND pid = ? and encounter = ? and " .
                                        "payer_type = 0 and account_code = 'PCP'",
                                        array($fs->pid, $fs->encounter)
                                    ); //new fees screen copay gives account_code='PCP'
                                    while ($rowMoneyGot = sqlFetchArray($resMoneyGot)) {
                                        $PatientPay = $rowMoneyGot['PatientPay'] * -1;
                                        $id = $rowMoneyGot['id'];
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
                                }

                                    // Echo new billing items from this form here, but omit any line
                                    // whose Delete checkbox is checked.
                                    //
                                if (!empty($_POST['bill'])) {
                                    foreach ($_POST['bill'] as $key => $iter) {
                                        if (!empty($iter["id"])) {
                                            continue; // skip if it came from the database
                                        }
                                        if (!empty($iter["del"])) {
                                            continue; // skip if Delete was checked
                                        }
                                        $ndc_info = '';
                                        if (!empty($iter['ndcnum'])) {
                                            $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
                                            trim($iter['ndcqty']);
                                        }
                                        $units = intval(trim($iter['units']));
                                        if (!$units) {
                                            $units = 1; // units may be negative.
                                        }

                                        // Price display is conditional.
                                        if ($iter['price'] != 'X') {
                                            $fee = formatMoneyNumber((0 + trim($iter['price'] ?? null)) * $units);
                                        } else {
                                            $fee = $fs->getPrice($iter['pricelevel'], $iter['code_type'], $iter['code']);
                                            $fee = formatMoneyNumber((0 + $fee) * $units);
                                        }

                                        //the date is passed as $ndc_info, since this variable is not applicable in the case of copay.
                                        $ndc_info = '';
                                        if ($iter['code_type'] == 'COPAY') {
                                            $ndc_info = date("Y-m-d");
                                            if ($fee > 0) {
                                                $fee = 0 - $fee;
                                            }
                                        }
                                        $fs->addServiceLineItem(
                                            array(
                                                'codetype'    => $iter['code_type'],
                                                'code'        => trim($iter['code']),
                                                'revenue_code'    => $revenue_code ?? null,
                                                'modifier'    => trim($iter["mod"] ?? ''),
                                                'ndc_info'    => $ndc_info,
                                                'auth'        => $iter['auth'] ?? '',
                                                'del'         => $iter['del'] ?? null,
                                                'units'       => $units,
                                                'fee'         => $fee,
                                                'justify'     => $iter['justify'] ?? null,
                                                'provider_id' => $iter['provid'],
                                                'notecodes'   => $iter['notecodes'] ?? null,
                                                'pricelevel'  => $iter['pricelevel'] ?? null,
                                            )
                                        );
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
                                    $del = 0;
                                    $prod_lino = count($fs->productitems);
                                    $rx    = !empty($srow['prescription_id']);
                                    $sale_id = $srow['sale_id'];
                                    $drug_id = $srow['drug_id'];
                                    $selector = $srow['selector'];
                                    $pricelevel = $srow['pricelevel'];
                                    $units   = $srow['quantity'];
                                    $fee     = $srow['fee'];
                                    $billed  = $srow['billed'];
                                    $warehouse_id  = $srow['warehouse_id'];
                                    // Also preserve other items from the form, if present and unbilled.
                                    $convert_units = true;

                                    if (!empty($_POST['prod'])) {
                                        $pline = $_POST['prod']["$prod_lino"];
                                        $del   = $pline['del']; // preserve Delete if checked
                                        if ($pline['sale_id'] && !$srow['billed']) {
                                            $convert_units = false;
                                            $units = intval(trim($pline['units']));
                                            if (!$units) {
                                                $units = 1; // units may be negative.
                                            }
                                            // Price display is conditional.
                                            if ($fs->pricesAuthorized()) {
                                                $fee = formatMoneyNumber((0 + trim($pline['price'])) * $units);
                                            }
                                            $rx    = !empty($pline['rx']);
                                        }
                                    }

                                    $fs->addProductLineItem(
                                        array(
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
                                        ),
                                        $convert_units
                                    );
                                }

                                    // Echo new product items from this form here, but omit any line
                                    // whose Delete checkbox is checked.
                                    //
                                if (!empty($_POST['prod'])) {
                                    foreach ($_POST['prod'] as $key => $iter) {
                                        if ($iter["sale_id"]) {
                                            continue; // skip if it came from the database
                                        }
                                        if (!empty($iter["del"])) {
                                            continue; // skip if Delete was checked
                                        }
                                        $units = intval(trim($iter['units']));
                                        if (!$units) {
                                            $units = 1; // units may be negative.
                                        }

                                        // Price display is conditional.
                                        if ($iter['price'] != 'X') {
                                            $fee = formatMoneyNumber((0 + trim($iter['price'])) * $units);
                                        } else {
                                            $fee = $fs->getPrice($iter['pricelevel'], 'PROD', $iter['drug_id'], $iter['selector']);
                                            $fee = formatMoneyNumber((0 + $fee) * $units);
                                        }

                                        $rx    = !empty($iter['rx']); // preserve Rx if checked
                                        $warehouse_id = empty($iter['warehouse_id']) ? '' : $iter['warehouse_id'];
                                        $fs->addProductLineItem(
                                            array(
                                                'drug_id'      => $iter['drug_id'],
                                                'selector'     => $iter['selector'],
                                                'pricelevel'   => $iter['pricelevel'],
                                                'rx'           => $rx,
                                                'units'        => $units,
                                                'fee'          => $fee,
                                                'warehouse_id' => $warehouse_id,
                                            ),
                                            false
                                        );
                                    }
                                }

                                    // If new billing code(s) were <select>ed, add their line(s) here.
                                    //
                                if (!empty($_POST['newcodes']) && !$alertmsg) {
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
                                                $code = formatMoneyNumber($tmp['copay'] ?? 0);
                                                $fs->addServiceLineItem(array(
                                                'codetype'    => $newtype,
                                                'code'        => $code,
                                                'ndc_info'    => date('Y-m-d'),
                                                'auth'        => '1',
                                                'units'       => '1',
                                                'fee'         => formatMoneyNumber(0 - $code),
                                                ));
                                            } elseif ($newtype == 'PROD') {
                                                $result = sqlQuery("SELECT dt.quantity, d.route " .
                                                "FROM drug_templates AS dt, drugs AS d WHERE " .
                                                "dt.drug_id = ? AND dt.selector = ? AND " .
                                                "d.drug_id = dt.drug_id", array($newcode,$newsel));
                                                // $units = max(1, intval($result['quantity']));
                                                $units = 1; // user units, not inventory units
                                                // By default create a prescription if drug route is set.
                                                $rx = !empty($result['route']);
                                                $fs->addProductLineItem(
                                                    array(
                                                        'drug_id'      => $newcode,
                                                        'selector'     => $newsel,
                                                        'rx'           => $rx,
                                                        'units'        => $units,
                                                    ),
                                                    false
                                                );
                                            } else {
                                                if (strpos($newcode, ':') !== false) {
                                                    $tmp = explode(':', $arrcode[1]);
                                                    $code = $tmp[0] ?? '';
                                                    $modifier = $tmp[1] ?? '';
                                                    $modifier .= ($tmp[2] ?? '') ? ":" . $tmp[2] : '';
                                                    $modifier .= ($tmp[3] ?? '') ? ":" . $tmp[3] : '';
                                                    $modifier .= ($tmp[4] ?? '') ? ":" . $tmp[4] : '';
                                                } else {
                                                    $code = $newcode;
                                                    $modifier = '';
                                                }
                                                $ndc_info = '';
                                                // If HCPCS, find last NDC string used for this code.
                                                if ($newtype == 'HCPCS' && $ndc_applies) {
                                                    $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
                                                    "code_type = ? AND code = ? AND ndc_info LIKE 'N4%' " .
                                                    "ORDER BY date DESC LIMIT 1", array($newtype, $code));
                                                    if (!empty($tmp)) {
                                                        $ndc_info = $tmp['ndc_info'];
                                                    } else {
                                                        $tmp = sqlQuery("SELECT ndc_number FROM drugs WHERE " .
                                                            "related_code = ? AND active = 1", array($newtype . ":" . $code));
                                                        if (!empty($tmp)) {
                                                            $ndc_info = $tmp['ndc_number'];
                                                        }
                                                    }
                                                }
                                                $fs->addServiceLineItem(array(
                                                     'codetype' => $newtype,
                                                     'code' => $code,
                                                     'modifier' => trim($modifier),
                                                     'ndc_info' => $ndc_info,
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
                                </tbody>
                            </table>
                        </div>


                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt("Select Providers"); ?></legend>
                        <div class="row mx-5">
                            <div class="form-row col">
                                <label class="col-form-label col-2"><?php echo  xlt('Rendering'); ?></label>
                                <div class="col-10">
                                    <?php
                                    if (empty($GLOBALS['default_rendering_provider'])) {
                                        $default_rid = $fs->provider_id ? $fs->provider_id : 0;
                                        if (!$default_rid && $userauthorized) {
                                            $default_rid = $_SESSION['authUserID'];
                                        }
                                    } elseif ($GLOBALS['default_rendering_provider'] == '1') {
                                        $default_rid = $fs->provider_id;
                                    } else {
                                        $default_rid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : $fs->provider_id;
                                    }
                                    echo $fs->genProviderSelect(
                                        'ProviderID',
                                        '-- ' . xl("Please Select") . ' --',
                                        $default_rid,
                                        $isBilled,
                                        false,
                                        xl('This provider will be used as the default for services not specifying a provider.')
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="form-row col">
                                <?php
                                // Supervising Provider (skip for IPPF).
                                if (!$GLOBALS['ippf_specific']) { ?>
                                    <label class='col-form-label col-2'><?php echo xlt('Supervising'); ?></label>
                                    <div class="col-10">
                                    <?php
                                    $super_id = sqlQuery("Select supervisor_id From users Where id = ?", array($default_rid))['supervisor_id'];
                                    $select_id = !empty($fs->supervisor_id) ? $fs->supervisor_id : $super_id;
                                    echo $fs->genProviderSelect('SupervisorID', '-- ' . xl("N/A") . ' --', $select_id, $isBilled); ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </fieldset>

                    <?php
                    if ($fs->contraception_code && !$isBilled) {
                        // This will give the form save logic the associated contraceptive method.
                        echo "<input type='hidden' name='ippfconmeth' value='" . attr($fs->contraception_code) . "'>\n";
                        // If needed, this generates a dropdown to ask about prior contraception.
                        echo $fs->generateContraceptionSelector();
                    }
                    ?>

                    <div class="form-group">
                        <div class="col-sm-12 position-override">
                            <div class="btn-group" role="group">
                                <button type='button' class='btn btn-primary btn-calendar' onclick='newEvt()'>
                                    <?php echo xlt('New Appointment');?>
                                </button>
                                <?php if (!$isBilled) { // visit is not yet billed ?>
                                    <button type='submit' name='bn_refresh' class='btn btn-primary btn-refresh' value='<?php echo xla('Refresh');?>' onclick='return this.clicked = true;'>
                                        <?php echo xlt('Refresh');?>
                                    </button>
                                    <button type='submit' name='bn_save' class='btn btn-primary btn-save' value='<?php echo xla('Save');?>'
                                    <?php
                                    if ($rapid_data_entry) {
                                        echo " style='background-color: #cc0000'; color: var(--white)'";
                                    } ?>><?php echo xla('Save');?></button>
                                    <button type='submit' name='bn_save_stay' class='btn btn-primary btn-save' value='<?php echo xla('Save Current'); ?>'><?php echo xlt('Save Current'); ?></button>
                                    <?php if ($GLOBALS['ippf_specific'] && (AclMain::aclCheckForm('admin', 'super') || AclMain::aclCheckForm('acct', 'bill') || AclMain::aclCheckForm('acct', 'disc'))) { // start ippf-only stuff ?>
                                        <?php if ($fs->hasCharges) { // unbilled with charges ?>
                                                <button type='submit' name='bn_save_close' class='btn btn-primary btn-save' value='<?php echo xla('Save and Checkout'); ?>'><?php echo xlt('Save and Checkout'); ?></button>
                                        <?php } else { // unbilled with no charges ?>
                                                <button type='submit' name='bn_save_close' class='btn btn-primary btn-save'value='<?php echo xla('Save and Close'); ?>'><?php echo xlt('Save and Close'); ?></button>
                                        <?php } // end no charges ?>
                                    <?php } // end ippf-only ?>
                                <?php } else { // visit is billed ?>
                                    <?php if ($fs->hasCharges) { // billed with charges ?>
    <button type='button' class='btn btn-secondary btn-show'
     onclick="top.restoreSession();location='../../patient_file/pos_checkout.php?framed=1<?php echo "&ptid=" .
                                         attr_url($fs->pid) . "&enc=" . attr_url($fs->encounter); ?>'"
     value='<?php echo xla('Show Receipt'); ?>'>
                                            <?php echo xlt('Show Receipt'); ?>
    </button>
    <button type='submit' class='btn btn-secondary btn-undo' name='bn_reopen'
     onclick='return this.clicked = 2;' value='<?php echo xla('Void All Checkouts and Re-Open'); ?>'>
                                            <?php if ($GLOBALS['void_checkout_reopen']) {
                                                echo xlt('Void Checkout and Re-Open');
                                            } else {
                                                echo xla('Re-Open');
                                            }    ?>
    </button>
                                    <?php } else { ?>
    <button type='submit' class='btn btn-secondary btn-undo' name='bn_reopen'
     onclick='return this.clicked = true;' value='<?php echo xla('Re-Open Visit'); ?>'>
                                            <?php echo xlt('Re-Open Visit'); ?>
    </button>
                                    <?php } // end billed without charges ?>

                                    <button type='submit' class='btn btn-secondary btn-add' name='bn_addmore' onclick='return this.clicked = true;' value='<?php echo xla('Add More Items'); ?>'>
                                        <?php echo xlt('Add More Items'); ?>
                                    </button>
                                <?php } // end billed ?>
                                    <button type='button' class='btn btn-secondary btn-cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'">
                                    <?php echo xlt('Cancel');?></button>
                                    <input type='hidden' name='form_has_charges' value='<?php echo $fs->hasCharges ? 1 : 0; ?>' />
                                    <input type='hidden' name='form_checksum' value='<?php echo attr($current_checksum); ?>' />
                                    <input type='hidden' name='form_alertmsg' value='<?php echo attr($alertmsg); ?>' />
                                    <input type='hidden' name='form_reopen' value='' />
                                    <input type='hidden' name='form_reason' value='' />
                                    <input type='hidden' name='form_notes' value='' />
                            </div>
                        </div>
                    </div>
                </form>
                <br />
                <br />
            </div>
        </div>
    </div><!--End of div container -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script>
    $(function () {
        $('select').addClass("form-control");
    });
    </script>
    <script>
        setSaveAndClose();
        <?php
        echo $justinit;
        if ($alertmsg) {
            echo "alert(" . js_escape($alertmsg) . ");\n";
        }
        ?>
    </script>
<?php if (!empty($_POST['running_as_ajax'])) {
    exit();
} ?>
<?php require_once("review/initialize_review.php"); ?>
<?php require_once("code_choice/initialize_code_choice.php"); ?>
<?php if ($GLOBALS['ippf_specific']) {
    require_once("contraception_products/initialize_contraception_products.php");
} ?>
<script>
    var translated_price_header = <?php echo xlj("Price");?>;

    $("[name='search_term']").keydown(function (event) {
        if (event.keyCode == 13) {
            $("[name=bn_search]").trigger('click');
            return false;
        }
    });
    $("[name=search_term]").focus();
    <?php if (!empty($_POST['bn_search'])) { ?>
        document.querySelector("[name='search_term']") . scrollIntoView();
    <?php } ?>
</script>
</body>
</html>
