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

//require_once("../../../../globals.php");
require_once $GLOBALS['srcdir'] . '/options.inc.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

if (!AclMain::aclCheckCore('patients', 'rx')) {
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
$prev_prim_pharmacy = js_escape($prev_prim_pharmacy);
$prev_alt_pharmacy = js_escape($prev_alt_pharmacy);

$sql = "SELECT list_id, option_id, title FROM list_options WHERE list_id = 'state'";
$res = sqlStatement($sql);
$error = false;

$pharmacyService = new PharmacyService();
$defaultFilters = $pharmacyService->getWenoLastSearch($pid) ?? array();

?>

<style>
  .warn {
    color: red;
    font-size: 12px;
  }

  .select2-container--default .select2-selection--single .select2-selection__clear {
    cursor: pointer;
    float: right;
    font-weight: bold;
    margin-left: 10px;
  }

  /* Ensure Select2 elements match the styling */
  .select2-container--default .select2-selection--single {
    height: calc(2.25rem + 2px);
    background-color: var(--light);
    color: var(--dark);
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: .25rem;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--dark);
    line-height: 1.5;
    padding-left: 4px;
    padding-right: 6px;
  }

</style>

<div id="weno_form"></div>

<template id="weno_template">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="text" name="primary_pharmacy" id="primary_pharmacy" hidden>
    <input type="text" name="alternate_pharmacy" id="alternate_pharmacy" hidden>
    <hr class="bg-light font-weight-bold text-dark my-0 my-1">
    <div class="d-flex">
        <div class="h4 text-primary">
            <?php echo xlt("Weno Pharmacy"); ?>
        </div>
        <?php if (!empty($pharmacy_log['count'] ?? 0)) {
            $error = false; ?>
            <cite class="text-primary p-1">
                <?php
                echo xlt("Status") . ": " . (text($pharmacy_log['status']) ?? xlt("No Data")) . " " . (text($pharmacy_log['created_at']) ?? xlt("No Data"));
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
            <div>
                <label class="bg-light text-success mb-1">
                    <?php echo xlt("Optional Filters:"); ?>
                </label>
            </div>
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
            <cite class="small mb-1 text-success">
                <?php echo xlt("Additionally Filter by Zipcode(takes precedence), State OR City and State with Local or Statewide Coverage."); ?>
            </cite>
            <div class="row px-0 mx-0">
                <select name="weno_coverage" class="form-control form-control" id="weno_coverage" onchange="coverageChanged()">
                    <option value="Local" selected><?php echo xlt("Local Retail") ?></option>
                    <option value="State"><?php echo xlt("State Wide Mail Order") ?></option>
                    <option value=""><?php echo xlt("Select Coverage") . " *" ?></option>
                </select>
                <div>
                    <input type="text" size="16" class="form-control" name="weno_zipcode" id="weno_zipcode" placeholder="Zipcode" onchange="zipChanged()" value="">
                    <div class="warn"></div>
                </div>
                <select class="form-control form-control" name="weno_state" id="weno_state" onchange="stateChanged()">
                    <option value=""><?php echo xlt("State"); ?></option>
                    <?php while ($row = sqlFetchArray($res)) { ?>
                        <option value="<?php echo attr($row['option_id']); ?>"><?php echo text($row['title']); ?></option>
                        <?php
                    } ?>
                </select>
                <span id="weno_city_select"><select class="form-control bg-light text-dark" name="weno_city" id="weno_city" onchange="cityChanged()"><?php echo xlt("Enter City"); ?></select></span>
            </div>
        </div>
        <div>
        </div>
        <cite class="mb-1 text-success text-center">
            <?php echo xlt("Search Result Actions."); ?>
        </cite>
        <span class="ml-1 my-2" role="group">
            <button id="list-search-button" type="button" class="btn btn-success btn-sm my-2" onclick="search()"><?php echo xlt("List Search"); ?></button>
            <button id="name-search-button" type="button" class="btn btn-success btn-sm" onclick="searchOn()"><?php echo xlt("Name Search"); ?></button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()"><?php echo xlt("Clear"); ?></button>
            <span class="h5 alert-danger mt-3" id="searchResults"></span>
        </span>
        <div id="select-div" class="form-group mt-2">
            <select class="form-control form-control bg-light text-dark mr-1 mb-1" name="form_weno_pharmacy" id="weno_pharmacy" onchange="pharmSelChanged()">
                <option value=""></option>
            </select>
        </div>
        <hr class="m-0 mt-2 mb-1 p-0 font-weight-bold bg-light text-dark" />
        <div class="mt-2 mb-1">
            <button type="button" class="btn btn-primary btn-sm mr-1 show-hide" onclick="assignPrimaryPharmacy()"><?php echo xlt("Assign Primary Pharmacy"); ?></button>
            <button type="button" class="btn btn-primary btn-sm show-hide" onclick="assignAlternatePharmacy()"><?php echo xlt("Assign Alternate Pharmacy"); ?></button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetForm()"><?php echo xlt("Reset"); ?></button>
        </div>
    <?php } ?>
    <hr class="m-0 mb-1 p-0 font-weight-bold bg-light text-dark" />
    <div class="m-0 mt-1 text-center">
        <cite class="small text-primary bg-light"><?php echo xlt("Assigned Pharmacies"); ?></cite>
    </div>
    <div>
        <span class="text-primary font-weight-bold mr-2"><?php echo xlt("Assigned Primary") . ':'; ?></span>
        <i id="weno_primary"></i>
    </div>
    <div class="mb-1">
        <span class="text-primary font-weight-bold"><?php echo xlt("Assigned Alternate") . ':'; ?></span>
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

    window.onload = (event) => {
        // Initialize the form with the last search values
        const defaultFilters = <?php echo js_escape($defaultFilters); ?>;

        // Display the hidden template
        const template = document.getElementById("weno_template");
        const wenoForm = document.getElementById("weno_form");
        const clone = document.importNode(template.content, true);
        wenoForm.appendChild(clone);

        const pid = <?php echo js_escape($pid); ?>;
        const prevPrimPharmacy = <?php echo js_escape($prev_prim_pharmacy); ?>;
        const prevAltPharmacy = <?php echo js_escape($prev_alt_pharmacy); ?>;

        if (pid > 0) {
            initPharmacyDisplay(prevPrimPharmacy, prevAltPharmacy);
        }

        // Initialize Select2 for pharmacy if the element exists
        const pharmacySelector = document.getElementById("weno_pharmacy");
        if (pharmacySelector !== null) {
            createWenoPharmacySelect2();
        }

        // Initialize Select2 for city if the element exists
        const citySelector = document.getElementById("weno_city");
        if (citySelector !== null) {
            createWenoCitySelect2();
        }

        // Initialize the default filters
        document.getElementById("weno_coverage").value = defaultFilters.weno_coverage || 'Local';
        document.getElementById("weno_zipcode").value = defaultFilters.weno_zipcode || '';
        document.getElementById("weno_state").value = defaultFilters.weno_state || '';

        const defaultCity = defaultFilters.weno_city;
        if (defaultCity) {
            const cityOption = new Option(defaultCity, defaultCity, true, true);
            $('#weno_city').append(cityOption).trigger('change');
        }

        const allDay = document.getElementById('24hr');
        if (allDay) {
            allDay.checked = defaultFilters.all_day === "on";
            fullDay = document.getElementById('24hr').checked;
        }

        const wenoOnlyEle = document.getElementById('weno_only');
        if (wenoOnlyEle) {
            wenoOnlyEle.checked = defaultFilters.weno_only === "on";
            wenoOnly = document.getElementById('weno_only').checked;
        }

        const defaultCoverage = defaultFilters.weno_coverage;
        if (defaultCoverage == 'State') {
            document.getElementById('weno_zipcode').style.display = 'none';
            document.getElementById('weno_city_select').style.display = 'none';
        }

        let triggerSearch = defaultFilters.weno_zipcode || defaultFilters.weno_state || defaultFilters.weno_city;
        if (triggerSearch) {
            // Initialize the search results
            $('#list-search-button').trigger('click');
            // Trigger the search after 1 second delay
            setTimeout(() => {
                $('#name-search-button').trigger('click');
            }, 2000);
        }
    };

    function initPharmacyDisplay(prevPrimPharmacy, prevAltPharmacy) {
        let jsPrim = JSON.parse(prevPrimPharmacy);
        let jsAlt = JSON.parse(prevAltPharmacy);

        if (jsPrim !== false && jsPrim !== null && jsPrim.business_name !== '') {
            $('#weno_primary').text(jsText((jsPrim.business_name) + ' - ' + (jsPrim.address_line_1) + ' ' + (jsPrim.city) + ', ' + (jsPrim.state)));
            $('#primary_pharmacy').val(jsAttr(jsPrim.primary_ncpdp));
        }
        if (jsAlt !== false && jsAlt !== null && jsAlt.business_name !== '') {
            $('#weno_alt').text(jsText((jsAlt.business_name) + ' - ' + (jsAlt.address_line_1) + ' ' + (jsAlt.city) + ', ' + (jsAlt.state)));
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
        $("#searchResults").text('');
    }

    function stateChanged() {
        var wenoState = document.getElementById('weno_state').selectedOptions[0];
        this.wenoState = wenoState ? wenoState.value : '';
        $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode').removeClass("is-invalid");
        $('.warn').text('');
        $("#searchResults").text('');
    }

    function cityChanged() {
        var wenoCity = document.getElementById('weno_city').selectedOptions[0];
        this.wenoCity = wenoCity ? wenoCity.value : '';
        $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode').removeClass("is-invalid");
        $('.warn').text('');
        $("#searchResults").text('');
    }

    function onWenoChanged(cb) {
        this.wenoOnly = cb ? cb.checked : false;
        $("#searchResults").text('');
    }

    function coverageChanged() {
        $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode').removeClass("is-invalid");
        $('.warn').text('');
        $("#searchResults").text('');
        const coverageElement = document.getElementById('weno_coverage');
        const coverage = coverageElement.selectedOptions[0] ? coverageElement.selectedOptions[0].value : '';
        const zipcodeElement = document.getElementById('weno_zipcode');
        const cityElement = document.getElementById('weno_city_select');

        if (coverage === 'State') {
            zipcodeElement.style.display = 'none';
            cityElement.style.display = 'none';
        } else {
            zipcodeElement.disabled = false;
            zipcodeElement.style.display = 'block';
            cityElement.style.display = 'block';
        }
    }

    function fullDayChanged(cb) {
        this.fullDay = cb ? cb.checked : false;
        $("#searchResults").text('');
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
            placeholder: 'Click here for Selected Filters Pharmacy Search by Name.',
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

    function destroyWenoCitySelect2() {
        if ($('#weno_city').data('select2')) {
            $('#weno_city').select2('destroy');
        }
    }

    function searchOn() {
        let pharmacySelector = document.getElementById("weno_pharmacy");
        if ($('#weno_pharmacy').hasClass('select2-hidden-accessible')) {
            $('#weno_pharmacy').select2('destroy').off('select2:open');
        }
        if (pharmacySelector !== null) {
            createWenoPharmacySelect2();
        }
    }

    function search() {
        document.getElementById("select-div").style.visibility = 'hidden';
        wenoZipcode = $('#weno_zipcode').val();
        wenoCity = $('#weno_city').val();
        wenoState = $('#weno_state').val();
        coverage = $('#weno_coverage').val();
        $("#searchResults").text('');

        if (coverage && (wenoState || wenoZipcode)) {
            $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode').removeClass("is-invalid");
            $('.warn').text('');

            if ($('#weno_pharmacy').hasClass('select2-hidden-accessible')) {
                $('#weno_pharmacy').select2('destroy').off('select2:open');
            }
            makeRequest();
        } else {
            $('#weno_city, #weno_state, #weno_coverage').removeClass("is-invalid");
            $('.warn').text('');
            if (!coverage) {
                $('#weno_coverage').addClass("is-invalid");
                $('.warn').text(jsText('Coverage is required'));
            } else if (!wenoState && coverage == 'Local') {
                $('#weno_state').addClass("is-invalid");
                $('.warn').text(jsText('State or Zipcode is required'));
            } else if (!wenoState && coverage == 'State') {
                $('#weno_state').addClass("is-invalid");
                $('.warn').text(jsText('State is required'));
            } else if (!coverage && !wenoZipcode) {
                $('#weno_zipcode').addClass("is-invalid");
                $('.warn').text(jsText('Zipcode is required'));
            }
        }
    }

    function makeRequest() {
        testPharmacies = document.getElementById('weno_test_pharmacies').checked;
        // clear main search fields
        if (testPharmacies) {
            wenoState = '';
            wenoCity = '';
            wenoZipcode = '';
            coverage = '';
        }
        wenoOnly = document.getElementById('weno_only').checked;
        fullDay = document.getElementById('24hr').checked;
        let data = {
            searchFor: 'weno_drop',
            weno_state: wenoState,
            weno_city: wenoCity,
            weno_zipcode: wenoZipcode,
            coverage: coverage,
            full_day: fullDay,
            weno_only: wenoOnly,
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
                    $("#searchResults").text(msg);
                } else {
                    if (testPharmacies) {
                        html += '<option value="' + '">' + jsText(xl("Select a Test Pharmacy Here")) + '</option>';
                    } else {
                        html += '<option value="' + '">' + jsText(xl("Select a Pharmacy Here")) + '</option>';
                    }
                    $.each(data, function (i, value) {
                        html += '<option style="width: 100%" value="' + jsAttr(value.ncpdp) + '">' + jsText(value.name) + '</option>';
                    });
                    let msg = jsAttr(data.length) + ' ' + jsText(xl('result(s) found.'));
                    $("#searchResults").text(msg);
                }
                $("#weno_pharmacy").html(html); // Write HTML options to the select elementresult(s) found
                document.getElementById("select-div").style.visibility = 'visible';
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
        $("#searchResults").text('');

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

    function clearFilters() {
        // Clear warning messages and search results
        $('.warn').text('');
        $("#searchResults").text('');

        // Clear individual filter fields
        document.getElementById('weno_state').selectedIndex = 0;
        document.getElementById('weno_zipcode').value = '';
        $('#weno_city').val(null).trigger('change');
        $('#weno_state').val(null).trigger('change');
        $('#weno_coverage').val('Local').trigger('change');

        // Reset global variables (assuming these are defined elsewhere)
        wenoState = null;
        wenoCity = null;
        wenoZipcode = null;
        coverage = null;
        fullDay = null;
        wenoOnly = null;
        testPharmacies = null;

        // Remove invalid class from filter elements
        const multi = $('#weno_city, #weno_state, #weno_coverage, #weno_zipcode');
        multi.removeClass("is-invalid");
    }

</script>
