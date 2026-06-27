<?php

/**
 * This module is used to find and add insurance companies.
 * It is opened as a popup window.  The opener may have a
 * JavaScript function named set_insurance(id, name), in which
 * case selecting or adding an insurance company will cause the
 * function to be called passing the ID and name of that company.
 *
 * When used for searching, this module will in turn open another
 * popup window ins_list.php, which lists the matched results and
 * permits selection of one of them via the same set_insurance()
 * function.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\{AddressService, InsuranceCompanyService, PhoneNumberService};

// Putting a message here will cause a popup window to display it.
$info_msg = "";

// Grab insurance type codes from service
$insuranceCompany = new InsuranceCompanyService();
$phoneNumber = new PhoneNumberService();
$ins_type_code_array = $insuranceCompany->getInsuranceTypes();
$session = SessionWrapperFactory::getInstance()->getActiveSession();
?>
<html>
<head>
    <title><?php echo xlt('Insurance Company Search/Add'); ?></title>

    <?php Header::setupHeader(['opener', 'topdialog']); ?>

    <style>
        td {
            font-size: 0.8125rem;
        }

        #form_entry {
            display: block;
        }

        #form_list {
            display: none;
        }

    </style>

    <script>

        <?php require(OEGlobalsBag::getInstance()->getSrcDir() . "/restoreSession.php"); ?>

        // This is invoked when our Search button is clicked.
        function dosearch() {

            $("#form_entry").hide();
            var f = document.forms[0];
            const params = new URLSearchParams({
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>,
                form_addr1: f.form_addr1.value,
                form_addr2: f.form_addr2.value,
                form_attn: f.form_attn.value,
                form_city: f.form_city.value,
                form_cms_id: f.form_cms_id.value,
                form_name: f.form_name.value,
                form_phone: f.form_phone.value,
                form_state: f.form_state.value,
                form_zip: f.form_zip.value
            });
            var search_list = 'ins_list.php?' + params;

            top.restoreSession();
            $("#form_list").load(search_list).show();

            return false;
        }

        // The ins_list.php window calls this to set the selected insurance.
        function set_insurance(ins_id, ins_name) {
            window.top.restoreSession(); // make sure to restore the session before we do anything else
            if (!window.opener) {
                return; // nothing to do here as somehow we got here without the opener
            }
            let postMessage = {
                action: 'insurance-search-set-insurance'
                , insuranceId: ins_id
                , insuranceName: ins_name
            };
            // fire off a message so we can decouple things so we don't have to have a specific function
            // name in the global scope of the opener
            opener.postMessage(postMessage, window.location.origin);
            if (opener.closed) {
                alert('The target form was closed; I cannot apply your selection.');
            } else if (opener.set_insurance) {
                opener.set_insurance(ins_id, ins_name);
                dlgclose();
            } else {
                // if we don't have a set_insurance function then we will just close the window as the opener is
                // using post message to receive events.
                dlgclose();
            }
        }

        // This is set to true on a mousedown of the Save button.  The
        // reason is so we can distinguish between clicking on the Save
        // button vs. hitting the Enter key, as we prefer the "default"
        // action to be search and not save.
        var save_clicked = false;
        let update_clicked = false;

        // Onsubmit handler.
        function validate(f) {
            // If save was not clicked then default to searching.
            if (!(save_clicked || update_clicked)) return dosearch();
            save_clicked = false;

            let msg = '';
            if (update_clicked && !f.form_id.value.length) msg += 'Id is missing for Update \n';
            if (!f.form_name.value.length) msg += 'Company name is missing. ';
            if (!f.form_addr1.value.length) msg += 'Address is missing. ';
            if (!f.form_city.value.length) msg += 'City is missing. ';
            if (!f.form_state.value.length) msg += 'State is missing. ';
            if (!f.form_zip.value.length) msg += 'Zip is missing.';
            update_clicked = false;

            if (msg) {
                alert(msg);
                return false;
            }

            top.restoreSession();
            return true;
        }

        function clearForm() {
            let f = document.forms[0];
            f.form_id.value = '';
            f.form_name.value = '';
            f.form_attn.value = '';
            f.form_addr1.value = '';
            f.form_addr2.value = '';
            f.form_city.value = '';
            f.form_state.value = '';
            f.form_country.value = '';
            f.form_zip.value = '';
            f.form_phone.value = '';
            f.form_cms_id.value = '';
            f.form_ins_type_code.value = '';
            f.form_partner.value = '';
            f.form_cqm_sop.value = '';
        }

    </script>

</head>

<body class="body_top">
    <?php
    $post = filter_input_array(INPUT_POST, [
        'csrf_token_form' => FILTER_UNSAFE_RAW,
        'form_save' => FILTER_UNSAFE_RAW,
        'form_update' => FILTER_UNSAFE_RAW,
        'form_id' => FILTER_UNSAFE_RAW,
        'form_name' => FILTER_UNSAFE_RAW,
        'form_attn' => FILTER_UNSAFE_RAW,
        'form_addr1' => FILTER_UNSAFE_RAW,
        'form_addr2' => FILTER_UNSAFE_RAW,
        'form_city' => FILTER_UNSAFE_RAW,
        'form_state' => FILTER_UNSAFE_RAW,
        'form_country' => FILTER_UNSAFE_RAW,
        'form_zip' => FILTER_UNSAFE_RAW,
        'form_phone' => FILTER_UNSAFE_RAW,
        'form_cms_id' => FILTER_UNSAFE_RAW,
        'form_ins_type_code' => FILTER_UNSAFE_RAW,
        'form_partner' => FILTER_UNSAFE_RAW,
        'form_cqm_sop' => FILTER_UNSAFE_RAW,
    ]);

    // If we are saving, then save and close the window.
    //
    if (
        ($post['form_save'] ?? '')
        || ($post['form_update'] ?? '')
    ) {
        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

        // The insert-vs-update decision and the form-field mapping live in the
        // service (saveFromForm/buildSaveDataFromForm) so they can be unit and
        // integration tested. The view only owns the request boundary (the CSRF
        // check above) and the response (the script below).
        // saveFromForm() throws RuntimeException on failure; let it propagate.
        $saved = $insuranceCompany->saveFromForm($post);

        $ins_id = (string) $saved['id'];
        $ins_name = $saved['name'];

        // Close this window and tell our opener to select the new company.
        //
        echo "<script>\n";
        if ($info_msg) {
            echo " alert(" . js_escape($info_msg) . ");\n";
        }

        // call the set_insurance method in our header
        echo " set_insurance(" . js_escape($ins_id) . "," . js_escape($ins_name) . ");\n";
        echo "</script></body></html>\n";
        exit();
    } else {
        // Guard against missing or non-numeric ?ins=. The downstream services
        // type-hint int and would throw a TypeError on an empty string (#12307).
        // Default $ins_co to [] (rather than null) so the form-rendering code
        // below can read $ins_co['name'] ?? '' etc. without a PHP 8 warning
        // about offset-access-on-null.
        $ins_id = filter_input(INPUT_GET, 'ins', FILTER_VALIDATE_INT) ?: null;
        // (array) so that getOneById's untyped return (could be false on
        // miss) collapses to [] for safe offset access below.
        $ins_co = (array) ($ins_id ? (new InsuranceCompanyService())->getOneById($ins_id) : []);
        $ins_co_address = $ins_id ? (new AddressService())->getOneByForeignId($ins_id) : null;
        $ins_co_phone = $ins_id ? (new PhoneNumberService())->getOneByForeignId($ins_id) : null;
    }

    // Query x12_partners.
    $xres = sqlStatement(
        "SELECT id, name FROM x12_partners ORDER BY name"
    );
    ?>
    <div id="form_entry">

        <form method='post' name='theform' action='ins_search.php' onsubmit='return validate(this)'>
            <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
            <center>

                <p>
                <table class="w-100 border-0">
                    <tr>
                        <td class="fw-bold" width='1%' nowrap><?php echo xlt('Id'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_id' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Id of insurance company'); ?>'
                                readonly='readonly' value='<?php echo attr($ins_co['id'] ?? ''); ?>' />
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold" width='1%' nowrap><?php echo xlt('Name'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_name' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Name of insurance company'); ?>'
                                value='<?php echo attr($ins_co['name'] ?? ''); ?>' />
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Attention'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_attn' maxlength='35' class='form-control form-control-sm' title='<?php echo xla('Contact name'); ?>'
                                value='<?php echo attr($ins_co['attn'] ?? ''); ?>' />
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Address1'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_addr1' maxlength='35' class='form-control form-control-sm' title='First address line'
                                value='<?php echo attr($ins_co_address?->line1 ?? ''); ?>' />
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Address2'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_addr2' maxlength='35' class='form-control form-control-sm' title='Second address line, if any'
                                value='<?php echo attr($ins_co_address?->line2 ?? ''); ?>' />
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('City/State'); ?>:</td>
                        <td class="row gx-2">
                            <div class="col">
                                <input type='text' size='20' name='form_city' maxlength='25' class='form-control form-control-sm' title='City name'
                                    value='<?php echo attr($ins_co_address?->city ?? ''); ?>' />
                            </div>
                            <div class="col">
                                <input type='text' size='3' name='form_state' maxlength='35' class='form-control form-control-sm' title='State or locality'
                                    value='<?php echo attr($ins_co_address?->state ?? ''); ?>' />
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Zip/Country:'); ?></td>
                        <td class="row gx-2">
                            <div class="col">
                                <input type='text' size='20' name='form_zip' maxlength='10' class='form-control form-control-sm' title='Postal code'
                                    value='<?php echo attr(($ins_co_address?->zip ?? '') . ($ins_co_address?->plusFour ?? '')); ?>' />
                            </div>
                            <div class="col">
                                <input type='text' size='20' class="form-control form-control-sm" name='form_country' maxlength='35' title='Country name'
                                    value='<?php echo attr($ins_co_address?->country ?? 'USA'); ?>' />
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Phone'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_phone' maxlength='20' class='form-control form-control-sm' title='Telephone number'
                                value='<?php echo attr((
                                    ($ins_co_phone['area_code'] ?? '') .
                                    ($ins_co_phone['prefix'] ?? '') .
                                    ($ins_co_phone['number'] ?? '')
                                )); ?>'
                            />
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Payer ID'); ?>:</td>
                        <td>
                            <input type='text' size='20' name='form_cms_id' maxlength='15' class='form-control form-control-sm' title='Identifier assigned by CMS'
                                value='<?php echo attr($ins_co['cms_id'] ?? ''); ?>' />
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('Payer Type'); ?>:</td>
                        <td>
                            <select name='form_ins_type_code' class="form-control form-control-sm">
                                <?php
                                for ($i = 1; $i < count($ins_type_code_array); ++$i) {
                                    echo "   <option value='" . attr($i) . "'";
                                    // Null-safe comparison to avoid PHPStan warning when $ins_co is null
                                    if ($i == ($ins_co['ins_type_code'] ?? null)) {
                                        echo " selected";
                                    }
                                    echo ">" . text($ins_type_code_array[$i]) . "\n";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('X12 Partner'); ?>:</td>
                        <td>
                            <select name='form_partner' title='Default X12 Partner' class="form-control form-control-sm">
                                <option value=""><?php echo '-- ' . xlt('None{{Partner}}') . ' --'; ?></option>
                                <?php
                                while ($xrow = sqlFetchArray($xres)) {
                                    echo "   <option value='" . attr($xrow['id']) . "'";
                                    if ($xrow['id'] == ($ins_co['x12_default_partner_id'] ?? null)) {
                                        echo " selected";
                                    }
                                    echo ">" . text($xrow['name']) . "</option>\n";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold" nowrap><?php echo xlt('CQM Source of Payment'); ?>:</td>
                        <td>
                            <select name='form_cqm_sop' title='CQM Source of Payment' class="form-control form-control-sm">
                                <option value=""><?php echo '-- ' . xlt('None{{CQM SOP}}') . ' --'; ?></option>
                                <?php
                                $cqm_sop_array = $insuranceCompany->getInsuranceCqmSop();
                                foreach ($cqm_sop_array as $key => $value) {
                                    echo "   <option value='" . attr($key) . "'";
                                    if (($ins_co['cqm_sop'] ?? '') === $key) {
                                        echo " selected";
                                    }
                                    echo ">" . text($value) . "</option>\n";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                </table>

                <input type='button' value='<?php echo xla('Search'); ?>' class='btn btn-primary' onclick='dosearch()' />
                <input type='submit' value='<?php echo xla('Save as New'); ?>' class='btn btn-primary' name='form_save' onmousedown='save_clicked=true' />
                <input type='submit' value='<?php echo xla('Update'); ?>' class='btn btn-primary' name='form_update' onmousedown='update_clicked=true' />
                <input type='button' value='<?php echo xla('Clear'); ?>' class='btn btn-primary' onclick='clearForm()' />
                <input type='button' value='<?php echo xla('Cancel'); ?>' class='btn btn-primary' onclick='window.close();' />

            </center>
        </form>
    </div>

    <div id="form_list">
    </div>

</body>
</html>
