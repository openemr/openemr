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
 * @copyright Copyright (c) 2023 Omega Systems Group International. <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once $GLOBALS['srcdir'] . '/options.inc.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;

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

// should always be set, but just in case we will set it to 0 so we can grab it
$field_id_esc = $field_id_esc ?? '0';
$name_field_id = "form_" . $field_id_esc;
$smallform = $smallform ?? '';

$pharmacyService = new PharmacyService();
$prev_prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? [];
$prev_alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? [];
$prev_prim_pharmacy = json_encode($prev_prim_pharmacy);
$prev_alt_pharmacy = json_encode($prev_alt_pharmacy);

$sql = "SELECT list_id, option_id, title FROM list_options WHERE list_id = 'state'";
$res = sqlStatement($sql);

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

    <div class="d-flex">
        <div class="h4 text-primary">
            <?php echo xlt("Weno Pharmacy Selector"); ?>
        </div>
        <br />
    </div>
    <div class="small">
        <?php echo xlt("Fields marked with * are required"); ?>
    </div>
    <div class="row col-12">
        <div class="col pl-0">
            <input type="checkbox" name="weno_only" id="weno_only" onclick='onWenoChanged(this);'>
            <span><?php echo xlt("ON WENO ONLY"); ?></span>
        </div>
        <div class="col pl-0 mb-3">
            <select name="weno_coverage" class="form-control form-control-sm" id="weno_coverage" onchange="coverageChanged()">
                <option value=""><?php echo xlt("Select Coverage") . " *" ?></option>
                <option value="State"><?php echo xlt("State Wide Mail Order") ?></option>
                <option value="Local"><?php echo xlt("Local Retail") ?></option>
            </select>
        </div>
    </div>
    <div class="row col-12">
        <div class="col pl-0 mb-3">
            <span>
                <input class="" type="checkbox" name="24hr" id="24hr" onclick='fullDayChanged(this);'>
                <span for="24hr"><?php echo xlt("OPEN 24HRS"); ?></span>
            </span>
        </div>
        <div class="col pl-0">
            <input type="checkbox" name="weno_test_pharmacies" id="weno_test_pharmacies" onchange="testPharmaciesChanged(this);">
            <span><?php echo xlt("TEST PHARMACIES"); ?></span>
        </div>
    </div>
    <div class="row px-0 mx-0">
        <div class="mr-3 col px-0 mx-0">
            <select class="form-control form-control-sm" name="weno_state" id="weno_state" onchange="stateChanged()">
                <option value=""><?php echo xlt("State") . " *"; ?></option>
                <?php while ($row = sqlFetchArray($res)) { ?>
                    <option value="<?php echo attr($row['option_id']); ?>"><?php echo text($row['title']); ?></option>
                    <?php
                }
                ?>
            </select>
        </div>

        <div class="col px-0 mx-0">
            <select class="form-control" name="weno_city" id="weno_city" onchange="cityChanged()"><?php echo xlt("Enter City") . " *"; ?></select>
            <div class="warn"></div>
        </div>
        <span class="ml-1"><?php echo xlt("OR"); ?></span>
        <div class="mx-3">
            <input class="form-control form-control-sm" name="weno_zipcode" id="weno_zipcode" placeholder="Zipcode">
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm" onclick="search()"><?php echo xlt("Search"); ?></button>
        </div>
    </div>
    <div class="mt-2">
        <select class="form-control form-control-sm" name="form_weno_pharmacy" id="weno_pharmacy" onchange="pharmSelChanged()">
            <option value=""></option>
        </select>
    </div>
    <div class="mt-2 mb-1">
        <button type="button" class="btn btn-primary btn-sm mr-3" onclick="assignPrimaryPharmacy()"><?php echo xlt("Assign Primary Pharmacy"); ?></button>
        <button type="button" class="btn btn-primary btn-sm" onclick="assignAlternatePharmacy()"><?php echo xlt("Assign Alternate Pharmacy"); ?></button>
        <button type="button" class="btn btn-secondary btn-sm ml-3" onclick="resetForm()"><?php echo xlt("Reset"); ?></button>
    </div>

    <div class="small mb-1">
        <?php echo xlt("See below Weno Selected Pharmacies"); ?>
    </div>
    <div>
        <span class="font-weight-bold"><?php echo xlt("Weno Selected Primary Pharmacy: "); ?></span>
        <span id="weno_primary"></span>
    </div>
    <div>
        <span class="font-weight-bold"><?php echo xlt("Weno Selected Alternate Pharmacy: "); ?></span>
        <span id="weno_alt"></span>
    </div>
</template>

<script type="text/javascript">
    //crsf
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
    var requirdField = "Field is required";

    window.onload = (event) => {
        //the template is hidden by default. use this to display the template
        var template = document.getElementById("weno_template");
        var weno_form = document.getElementById("weno_form");
        var clone = document.importNode(template.content, true);
        weno_form.appendChild(clone);

        let pid = <?php echo json_encode($pid); ?>;
        let prevPrimPharmacy = <?php echo json_encode($prev_prim_pharmacy); ?>;
        let prevAltPharmacy = <?php echo json_encode($prev_alt_pharmacy); ?>;

        if (pid > 0) {
            init(prevPrimPharmacy, prevAltPharmacy);
        }

        //checking if weno_pharmacy div exists initialize select2
        var pharmacySelector = document.getElementById("weno_pharmacy");
        if (pharmacySelector !== null) {
            createWenoPharmacySelect2();
        }

        //checking if city div div exists and initialize select2
        var citySelector = document.getElementById("weno_city");
        if (citySelector !== null) {
            createWenoCitySelect2();
        }

    };

    function init(prevPrimPharmacy, prevAltPharmacy) {
        let jsPrim = JSON.parse(prevPrimPharmacy);
        let jsAlt = JSON.parse(prevAltPharmacy);
        if (jsPrim != false) {
            var html = ('<option value="' + jsAttr(jsPrim.primary_ncpdp) + '">' +
                jsText(jsPrim.business_name) + ' - ' +
                jsText(jsPrim.address_line_1) + '</option>');
            $("#weno_pharmacy").html(html);

            //setting the form value for Weno Primary Pharmacy and Disply Text respectivley
            $('#weno_primary').text(" " + jsPrim.business_name);
            $('#primary_pharmacy').val(jsPrim.primary_ncpdp);
        }

        if (jsAlt != false) {
            $('#weno_alt').text(" " + jsAlt.business_name);
            $('#alternate_pharmacy').val(jsAlt.alternate_ncpdp);
        }
    }

    function pharmSelChanged() {
        var e = document.getElementById("weno_pharmacy");
        this.wenoValChanged = e.options[e.selectedIndex].value;
        this.wenoPrimPharm = e.options[e.selectedIndex].text;
    }

    function stateChanged() {
        var wenoState = document.getElementById('weno_state').selectedOptions[0].value;
        this.wenoState = wenoState;
    }

    function cityChanged() {
        var wenoCity = document.getElementById('weno_city').selectedOptions[0].value;
        this.wenoCity = wenoCity;
    }

    function onWenoChanged(cb) {
        this.wenoOnly = cb.checked;
    }

    function coverageChanged() {
        var coverage = document.getElementById('weno_coverage').selectedOptions[0].value;
        this.coverage = coverage;
    }

    function fullDayChanged(cb) {
        this.fullDay = cb.checked;
    }

    function testPharmaciesChanged(cb) {
        this.testPharmacies = cb.checked;
    }

    function doAjax() {
        var state = document.getElementById('form_weno_state').selectedOptions[0].value;
        var coverage = document.getElementById('weno_coverage').selectedOptions[0].value;

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
            placeholder: 'Enter desired Pharmacy',
            allowClear: true,
        });
    }

    function createWenoCitySelect2() {
        $('#weno_city').select2({
            width: '98%',
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
            placeholder: 'Enter City *'
        });
    }

    function search() {
        this.wenoZipcode = $('#weno_zipcode').val();
        if (( wenoCity > '' || this.wenoZipcode > '') && wenoState > '' && coverage > '') {
            $('#weno_city').removeClass("is-invalid");
            $('.warn').text('');
            $('#weno_state').removeClass("is-invalid");
            $('#weno_coverage').removeClass("is-invalid");
            var select2field = document.getElementById('weno_pharmacy');
            var field = select2field.classList.contains('select2-hidden-accessible');
            //check if select2 is active and remove it before using the normal dropdown
            if (field) {
                $('#weno_pharmacy').select2('destroy').off('select2:open');
            }
            makeRequest();
        } else {
            // reset errors
            $('#weno_city').removeClass("is-invalid");
            $('.warn').text('');
            $('#weno_state').removeClass("is-invalid");
            $('#weno_coverage').removeClass("is-invalid");
            if (wenoCity <= '' && this.wenoZipcode <= '') {
                $('#weno_city').addClass("is-invalid");
                $('.warn').text(requirdField);
            }
            if (this.wenoState <= '') {
                $('#weno_state').addClass("is-invalid");
            }
            if (coverage <= '') {
                $('#weno_coverage').addClass("is-invalid");
            }
        }
    }

    function makeRequest() {
        let data = {
            searchFor: 'weno_drop',
            weno_state: wenoState,
            weno_city: wenoCity,
            coverage: coverage,
            weno_zipcode: wenoZipcode,
            full_day: fullDay,
            test_pharmacy: testPharmacies,
            csrf_token_form: csrf
        };
        $.ajax({
            url: '<?php echo $GLOBALS['webroot']; ?>' + '/interface/modules/custom_modules/oe-module-weno/scripts/weno_pharmacy_search.php',
            type: "GET",
            data: data,
            success: function (data) {
                var html = '';
                data = JSON.parse(data);
                if (data == null) {
                    html += ('<option value="' + '">' + jsText(xl("No Data Found")) + '</option>');
                } else {
                    html += ('<option value="' + '">' + jsText(xl("Select from the dropdown")) + '</option>');
                    $.each(data, function (i, value) {
                        html += ('<option style="width: 100%" value="' + jsAttr(value.ncpdp) + '">' + jsText(value.name) + '</option>');
                    });
                }

                $("#weno_pharmacy").html(html);
            },
            // Error handling
            error: function (error) {
            }
        });

    }

    function assignPrimaryPharmacy() {
        weno_pharm = $('#weno_pharmacy').val();
        weno_pharm_text = $('#weno_pharmacy').text();
        $('#weno_primary').text(" " + wenoPrimPharm);
        $('#primary_pharmacy').val(weno_pharm);
    }

    function assignAlternatePharmacy() {
        weno_alt = $('#weno_pharmacy').val();
        weno_alt_text = $('#weno_pharmacy').text();
        $('#weno_alt').text(" " + wenoPrimPharm);
        $('#alternate_pharmacy').val(weno_alt);
    }

    function resetForm() {
        var searchbox = document.getElementById("weno_state");
        searchbox.selectedIndex = 0;

        $('#weno_alt').text('');
        $('#weno_primary').text('');
        $('#weno_primary').val('');
        $('#primary_pharmacy').val();
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
