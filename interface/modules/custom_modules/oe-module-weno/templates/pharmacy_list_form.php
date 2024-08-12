<?php

/**
 * Handles the creating and updating of the weno alternate and primary pharmacies.
 * Saving of these properties are handled by the scripts
 * that call the LBF form.  The current example can be found in
 * interface/patient_file/summary/demographics_save.php
 * and in interface/new/new_comprehensive_save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Omega Systems Group International. <info@omegasystemsgroup.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once $GLOBALS['srcdir'] . '/options.inc.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Pharmacy Selector")]);
    exit;
}

$widgetConstants = [
    'listWithAddButton' => 26
    , 'textDate' => 4
    , 'textbox' => 2
];

global $pid; // we need to grab our pid from our global settings.
$pid = ($frow['blank_form'] ?? null) ? 0 : $pid;

$logService = new WenoLogService();
$pharmacy_log = $logService->getLastPharmacyDownloadStatus('Success');

$activeStatus = sqlQuery("SELECT `active` FROM background_services WHERE `name` = 'WenoExchangePharmacies'");

// should always be set, but just in case we will set it to 0 so we can grab it
$field_id_esc = $field_id_esc ?? '0';
$name_field_id = "form_" . $field_id_esc;
$small_form = $small_form ?? '';

$pharmacyService = new PharmacyService();
$prev_prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? [];
$prev_alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? [];
$prev_prim_pharmacy = json_encode($prev_prim_pharmacy);
$prev_alt_pharmacy = json_encode($prev_alt_pharmacy);

$sql = "SELECT list_id, option_id, title FROM list_options WHERE list_id = 'state'";
$res = sqlStatement($sql);
$error = false;
?>

<style>
  .warn {
    color: red;
    font-size: 10px;
  }
</style>

<div id="weno_form"></div>

<template id="weno_template">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="text" name="primary_pharmacy" id="primary_pharmacy" hidden>
    <input type="text" name="alternate_pharmacy" id="alternate_pharmacy" hidden>
    <hr class="bg-light font-weight-bold text-dark my-0 my-1">
    <div class="d-flex">
        <span class="h4 text-primary">
            <?php echo xlt("Weno Pharmacy Selector"); ?>
        </span>
        <?php if (!empty($pharmacy_log['count'] ?? 0)) {
            $error = false; ?>
            <cite class="h6 text-success p-1">
                <?php
                echo xlt("Status") . ": " . (text($pharmacy_log['status']) ?? xlt("No Data")) . " " . xlt("Last Download") . ": " . (text($pharmacy_log['created_at']) ?? xlt("No Data"));
                ?>
            </cite>
        <?php } else {
            $error = true; ?>
            <cite class="h6 text-danger p-1 mt-1">
                <?php
                echo xlt("Currently No Pharmacies. Last Status") . ": " . (text($pharmacy_log['status']) ?? xlt("No Data")) . " " . xlt("Last Downloaded") . " " . (text($pharmacy_log['created_at']) ?? xlt("No Data"));
                ?>
            </cite>
        <?php } ?>
    </div>
    <?php if (!$error) { ?>
        <div class="row col-12 m-0 p-0 mb-1">
            <div class="col pl-0 form-inline">
                <label class="ml-1 form-check-inline">
                    <input type="checkbox" class="form-check-input" name="24hr" id="24hr" onclick="fullDayChanged(this);">
                    <?php echo xlt("Open 24 Hours"); ?>
                </label>
                <label class="ml-1 form-check-inline">
                    <input type="checkbox" class="form-check-input" name="weno_only" id="weno_only" onclick="onWenoChanged(this);">
                    <?php echo xlt("On Weno Only"); ?>
                </label>
                <label class="ml-1 form-check-inline">
                    <input type="checkbox" class="form-check-input" name="weno_test_pharmacies" id="weno_test_pharmacies" onchange="testPharmaciesChanged(this);">
                    <?php echo xlt("Test Pharmacies"); ?>
                </label>
                <i role="button" class="text-primary fa fa-search test-hide d-none" onclick="makeRequest()"></i>
            </div>
        </div>
        <div id="test-hide" class="test-hide">
            <cite class="small mb-1">
                <?php echo xlt("Search by Zipcode OR City and State."); ?>
            </cite>
            <div class="row px-0 mx-0">
                <select name="weno_coverage" class="form-control form-control-sm" id="weno_coverage" onchange="coverageChanged()">
                    <option value=""><?php echo xlt("Select Coverage") . " *" ?></option>
                    <option value="State"><?php echo xlt("State Wide Mail Order") ?></option>
                    <option value="Local" selected><?php echo xlt("Local Retail") ?></option>
                </select>
                <div>
                    <input type="text" size="16" class="form-control form-control-sm" name="weno_zipcode" id="weno_zipcode" placeholder="Zipcode" onchange="zipChanged()" value="">
                    <div class="warn"></div>
                </div>
                <span class="mx-1"><?php echo xlt("or"); ?></span>
                <select class="form-control form-control-sm" name="weno_state" id="weno_state" onchange="stateChanged()">
                    <option value=""><?php echo xlt("State"); ?></option>
                    <?php while ($row = sqlFetchArray($res)) { ?>
                        <option value="<?php echo attr($row['option_id']); ?>"><?php echo text($row['title']); ?></option>
                        <?php
                    } ?>
                </select>
                <select class="form-control" name="weno_city" id="weno_city" onchange="cityChanged()"><?php echo xlt("Enter City"); ?></select>
                <button type="button" class="btn btn-primary btn-sm mb-3" onclick="search()"><?php echo xlt("Search"); ?></button>
            </div>
        </div>
        <div>
        </div>
        <div class="show-hide">
            <select class="form-control form-control-sm bg-light text-dark" name="form_weno_pharmacy" id="weno_pharmacy" onchange="pharmSelChanged()">
                <option value=""></option>
            </select>
        </div>
        <div class="mt-2 mb-1">
            <button type="button" class="btn btn-primary btn-sm mr-3 show-hide" onclick="assignPrimaryPharmacy()"><?php echo xlt("Assign Primary Pharmacy"); ?></button>
            <button type="button" class="btn btn-primary btn-sm ml-3 show-hide" onclick="assignAlternatePharmacy()"><?php echo xlt("Assign Alternate Pharmacy"); ?></button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetForm()"><?php echo xlt("Reset"); ?></button>
        </div>
    <?php } ?>
    <div class="m-0 text-center">
        <cite><?php echo xlt("Current Weno Selected Pharmacies"); ?></cite>
    </div>
    <hr class="m-0 mb1 p-0 font-weight-bold bg-light text-dark" />
    <div>
        <span class="text-primary font-weight-bold mr-2"><?php echo xlt("Weno Selected Primary Pharmacy") . ':'; ?></span>
        <i id="weno_primary"></i>
    </div>
    <div class="mb-1">
        <span class="text-success font-weight-bold"><?php echo xlt("Weno Selected Alternate Pharmacy") . ':'; ?></span>
        <i id="weno_alt"></i>
        <hr class=" font-weight-bold bg-light text-dark" />
    </div>
</template>

<script>
    // csrf token
    let csrf = document.querySelector('input[name="csrf_token_form"]').value;

    var wenoState = null;
    var wenoCity = null;
    var wenoZipcode = null;
    var coverage = null;
    var fullDay = null;
    var wenoOnly = null;
    var testPharmacies = null;
    var wenoPrimPharm = null;
    var wenoValChanged = null;
    var wenoAltPharm = null;
    var requiredField = "City or Zipcode is required";

    window.onload = (event) => {
        //the template is hidden by default. use this to display the template
        var template = document.getElementById("weno_template");
        var weno_form = document.getElementById("weno_form");
        var clone = document.importNode(template.content, true);
        weno_form.appendChild(clone);

        let pid = <?php echo json_encode($pid); ?>;
        let prevPrimPharmacy = <?php echo js_escape($prev_prim_pharmacy); ?>;
        let prevAltPharmacy = <?php echo js_escape($prev_alt_pharmacy); ?>;

        if (pid > 0) {
            initPharmacyDisplay(prevPrimPharmacy, prevAltPharmacy);
        }
        //checking if weno_pharmacy div exists initialize select2
        let pharmacySelector = document.getElementById("weno_pharmacy");
        if (pharmacySelector !== null) {
            createWenoPharmacySelect2();
        }
        //checking if city div exists and initialize select2
        var citySelector = document.getElementById("weno_city");
        if (citySelector !== null) {
            createWenoCitySelect2();
        }
    };

    function initPharmacyDisplay(prevPrimPharmacy, prevAltPharmacy) {
        let jsPrim = JSON.parse(prevPrimPharmacy);
        let jsAlt = JSON.parse(prevAltPharmacy);

        if (jsPrim !== false && jsPrim !== null && jsPrim.business_name !== '') {
            $('#weno_primary').text(jsText((jsPrim.business_name) + ' - ' + (jsPrim.address_line_1)));
            $('#primary_pharmacy').val(jsAttr(jsPrim.primary_ncpdp));
        }
        if (jsAlt !== false && jsAlt !== null && jsAlt.business_name !== '') {
            $('#weno_alt').text(jsText((jsAlt.business_name) + ' - ' + (jsAlt.address_line_1)));
            $('#alternate_pharmacy').val(jsAttr(jsAlt.alternate_ncpdp));
        }
    }

    function pharmSelChanged() {
        const e = document.getElementById("weno_pharmacy");
        this.wenoValChanged = e ? e.options[e.selectedIndex].value : '';
        this.wenoPrimPharm = e ? e.options[e.selectedIndex].text : '';
    }

    function zipChanged() {
        var wenoZip = document.getElementById('weno_zipcode').value;
        this.wenoZipCode = wenoZip ? wenoZip : '';

        $('#weno_zipcode').removeClass("is-invalid");
        $('.warn').text('');
    }

    function stateChanged() {
        var wenoState = document.getElementById('weno_state').selectedOptions[0];
        this.wenoState = wenoState ? wenoState.value : '';
    }

    function cityChanged() {
        var wenoCity = document.getElementById('weno_city').selectedOptions[0];
        this.wenoCity = wenoCity ? wenoCity.value : '';
    }

    function onWenoChanged(cb) {
        this.wenoOnly = cb ? cb.checked : false;
    }

    function coverageChanged() {
        var coverage = document.getElementById('weno_coverage').selectedOptions[0];
        this.coverage = coverage ? coverage.value : '';
    }

    function fullDayChanged(cb) {
        this.fullDay = cb ? cb.checked : false;
    }

    function testPharmaciesChanged(cb) {
        let test = $('#weno_pharmacy');
        this.testPharmacies = cb ? cb.checked : false;
        $(".test-hide").toggleClass('d-none');
        if (cb.checked) {
            if (test.hasClass('select2-hidden-accessible')) {
                test.select2('destroy').off('select2:open');
            }
            makeRequest();
        }
    }

    function doAjax() {
        const state = document.getElementById('form_weno_state').selectedOptions[0].value;
        const coverage = document.getElementById('weno_coverage').selectedOptions[0].value;
        $.ajax({
            url: url,
            cache: false,
            type: "GET",
            data: {state: state, coverage: coverage},
            success: function (data) {
                var html = '';
                if (data.length > 0) {
                    $.each(JSON.parse(data), function (i, value) {
                        html += ('<option value="' + jsAttr(value.ncpdp) + '">' + jsText(value.name) + ' - ' + jsText(value.address) + '</option>');
                    });
                    $("#weno_pharmacy").html(html);
                }
            }
        });
    }

    function createWenoPharmacySelect2() {
        $('#weno_pharmacy').select2({
            width: '500px',
            ajax: {
                url: '<?php echo $GLOBALS['webroot']; ?>' + '/interface/modules/custom_modules/oe-module-weno/scripts/weno_pharmacy_search.php',
                dataType: 'json',
                data: function (params) {
                    return {
                        term: params.term,
                        searchFor: 'weno_pharmacy',
                        coverage: coverage,
                        weno_state: wenoState,
                        weno_city: wenoCity,
                        weno_only: wenoOnly,
                        full_day: fullDay,
                        csrf_token_form: csrf
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item, index) {
                            return {
                                text: item.name,
                                id: item.ncpdp,
                                value: item.ncpdp
                            }
                        })
                    };
                }
            },
            minimumInputLength: 3,
            cache: true,
            placeholder: 'Default Pharmacies',
            allowClear: true
        });
    }

    function createWenoCitySelect2() {
        $('#weno_city').select2({
            width: 'auto',
            allowClear: true,
            ajax: {
                url: '<?php echo $GLOBALS['webroot']; ?>' + '/interface/modules/custom_modules/oe-module-weno/scripts/weno_pharmacy_search.php',
                dataType: 'json',
                data: function (params) {
                    return {
                        term: params.term,
                        searchFor: 'weno_city',
                        csrf_token_form: csrf
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item, index) {
                            return {
                                text: item,
                                id: item,
                                value: item
                            }
                        })
                    };
                }
            },
            minimumInputLength: 3,
            cache: true,
            placeholder: 'Select a City'
        });
    }

    function search() {
        wenoZipcode = $('#weno_zipcode').val();
        wenoCity = $('#weno_city').val();
        wenoState = $('#weno_state').val();
        coverage = $('#weno_coverage').val();

        const isValidZipcode = wenoZipcode && coverage;
        const isValidCityAndState = wenoCity && wenoState && !wenoZipcode;

        if (isValidZipcode || isValidCityAndState) {
            $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode').removeClass("is-invalid");
            $('.warn').text('');

            if ($('#weno_pharmacy').hasClass('select2-hidden-accessible')) {
                $('#weno_pharmacy').select2('destroy').off('select2:open');
            }
            makeRequest();
        } else {
            $('#weno_city, #weno_state, #weno_coverage').removeClass("is-invalid");
            $('.warn').text('');

            if (!wenoCity && !wenoZipcode) {
                $('#weno_city').addClass("is-invalid");
                $('.warn').text(jsText(requiredField));
            }

            if (!wenoState && !wenoZipcode) {
                $('#weno_zipcode').addClass("is-invalid");
            }

            if (!coverage) {
                $('#weno_coverage').addClass("is-invalid");
            }
        }
    }

    function makeRequest() {
        // clear main search fields
        if (testPharmacies) {
            wenoState = '';
            wenoCity = '';
            wenoZipcode = '';
            coverage = '';
        }
        let data = {
            searchFor: 'weno_drop',
            weno_state: wenoState,
            weno_city: wenoCity,
            weno_zipcode: wenoZipcode,
            coverage: coverage,
            full_day: fullDay,
            test_pharmacy: testPharmacies,
            csrf_token_form: csrf
        };
        $.ajax({
            url: '<?php echo $GLOBALS['webroot']; ?>' + '/interface/modules/custom_modules/oe-module-weno/scripts/weno_pharmacy_search.php',
            type: "GET",
            data: data,
            success: function (data) {
                let html = '';
                data = JSON.parse(data);
                if (data === null || data.length === 0) { // Check for no data or empty array
                    html += '<option value="">' + jsText(xl("No Pharmacy Found")) + '</option>';
                    let msg = jsText(xl('No results found.'));
                    syncAlertMsg(msg, 2000, 'warning'); // Display warning message
                } else {
                    if (testPharmacies) {
                        html += '<option value="' + '">' + jsText(xl("Select a Test Pharmacy Here")) + '</option>';
                    } else {
                        html += '<option value="' + '">' + jsText(xl("Select a Pharmacy Here")) + '</option>';
                    }
                    $.each(data, function (i, value) {
                        html += '<option style="width: 100%" value="' + jsAttr(value.ncpdp) + '">' + jsText(value.name) + '</option>';
                    });
                    let msg = (testPharmacies ? (jsText(xl('Test')) + ' ') : '') + jsText(xl('Pharmacy search completed')) + ': ' + data.length + ' ' + jsText(xl('result(s) found.'));
                    syncAlertMsg(msg, 2000, 'warning', 'lg'); // Display success message
                }
                $("#weno_pharmacy").html(html); // Write HTML options to the select element
            },
            // Error handling
            error: function (error) {
                let msg = jsText(xl('Something went wrong. Try again!')) + ' ' + jsAttr(error);
                syncAlertMsg(msg, 5000, 'danger', 'lg'); // Display error message
            }
        });
    }

    function assignPrimaryPharmacy() {
        weno_pharm = $('#weno_pharmacy').val();
        weno_pharm_text = $('#weno_pharmacy').text();
        $('#weno_primary').text(jsText(wenoPrimPharm));
        $('#primary_pharmacy').val(weno_pharm);
    }

    function assignAlternatePharmacy() {
        weno_alt = $('#weno_pharmacy').val();
        weno_alt_text = $('#weno_pharmacy').text();
        $('#weno_alt').text(jsText(wenoPrimPharm));
        $('#alternate_pharmacy').val(weno_alt);
    }

    function resetForm() {
        const searchbox = document.getElementById("weno_state");
        searchbox.selectedIndex = 0;

        document.getElementById('weno_state').selectedOptions[0].value = '';
        document.getElementById('weno_zipcode').value = '';
        $('#weno_primary').text('');
        $('#weno_primary').val('');
        $('#primary_pharmacy').val();
        $('#weno_alt').text('');
        $('#alternate_pharmacy').val();

        resetSelect2();
    }

    function resetSelect2() {
        var select2field = document.getElementById('weno_pharmacy');
        var field = select2field.classList.contains('select2-hidden-accessible');

        if (field) {
            $('#weno_pharmacy').val('').trigger('change');
        } else {
            $('#weno_pharmacy').val('');
        }
    }

</script>
