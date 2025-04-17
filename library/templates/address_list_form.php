<?php

/**
 * Handles the editing, updating, creating, and deleting of the address list datatype in LBF.  Reuses several LBF components
 * inside of it.  Also functions as a repeater for address items.  Saving of these properties are handled by the scripts
 * that call the LBF form.  The current example can be found in interface/patient_file/summary/demographics_save.php
 * and in interface/new/new_comprehensive_save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;

global $pid; // we need to grab our pid from our global settings.
$pid = ($frow['blank_form'] ?? null) ? 0 : $pid;

$contactService = new ContactService();
$addresses = $contactService->getContactsForPatient($pid);

$table_id = uniqid("table_edit_addresses_");
// should always be set, but just in case we will set it to 0 so we can grab it
$field_id_esc = $field_id_esc ?? '0';
$addresses = $addresses ?? [];

$name_field_id = "form_" . $field_id_esc;
$smallform = $smallform ?? '';
$widgetConstants = [
        'listWithAddButton' => 26
        ,'textDate' => 4
        ,'textbox' => 2
];

// TODO: @adunsulag could we actually design out an actual layout and then just generate/display it in here?  Seems like that would provide the most extensible option?
// TODO: @adunsulag the repeating nature of this as a layout display would be problematic... we'd need some kind of repeater widget, would be a fun project.
?>

<style>
    div.table_edit_addresses div.label_custom, div.form_addresses div.label_custom {
        text-align: left !important;
    }
</style>

<div id="<?php echo attr($table_id); ?>" class="row mt-3">
    <div class ="table_edit_addresses col-12">
        <div class="display_addresses_header pl-1" style="display: flex; line-height: 1.5; padding-top: 0.1rem; background-color: var(--gray300)">
            <div class="label_custom mb-0"><?php echo xlt("Additional Addresses"); ?></div>
            <div class="fas fa-plus-square text-primary pl-3 pb-1"  style="display: inline-block; line-height: 1.5;"onclick="addAddress(event);return false"></div>
        </div>
        <div class="d-none no_addresses">
            <span class="label_custom pl-1" style="line-height: 2.0;"><?php echo xlt("NONE"); ?></span>
            <hr class="m-0 p-0" style="border-top-width: 2px; border-color: var(--gray300)" />
        </div>
    </div>
</div>

<template class="template_add_address">
    <div class="addresses_group col-12 pl-0">
        <div class="display_addresses form-row no-gutters justify-content-between pl-1">
            <div class="display_addresses_use_column px-1" style="flex: 0 0 7em;">
                <i class="fas fa-solid fa-caret-right fa-lg text-primary btn-edit-address" style="width:10px; line-height: 1.2;"></i>
                <span class="display_addresses_use label_custom px-0" style="vertical-align: 0.1rem;"></span>
            </div>

            <div class="col-6 px-0">
                <span class="display_addresses_full_address label_custom" style="vertical-align: -0.1rem;"></span>
            </div>

            <div class="col-3 display_addresses_period_column px-0">
                <span class="display_addresses_period label_custom" style="vertical-align: -0.1rem;"></span>
            </div>

            <div class="fas fa-fw fa-trash-alt text-danger btn-delete-address text-center" role="button" style="flex: 0 0 2em; line-height: 1.5;">
            </div>
        </div>

        <div class='d-none form_addresses form-row mx-3 my-2'>
            <input type="hidden" class="form_addresses_data_action" name="<?php echo attr($name_field_id); ?>[data_action][]" value="<?php echo xla("No Change"); ?>" />
            <input type="hidden" class="form_addresses_id" name="<?php echo attr($name_field_id); ?>[id][]" value="" />
            <input type="hidden" class="form_addresses_foreign_id" name="<?php echo attr($name_field_id); ?>[foreign_id][]" value="" />
            <div class="col-12">

                <!-- Header -->
                <div class="form-row">
                    <div class="col-3 label_custom">
                        <?php echo xlt("Address Use"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("Address Type"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("Start Date"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("End Date"); ?>
                    </div>
                </div>

                <!-- Values -->
                <div class="form-row">
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['listWithAddButton']
                            ,'field_id' => $field_id_esc . "[use][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_uses'
                            ,'list_id' => 'address-uses'
                            ,'empty_name' => 'Unassigned'
                            ,'edit_options' => $edit_options ?? null
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['listWithAddButton']
                            ,'field_id' => $field_id_esc . "[type][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_types'
                            ,'list_id' => 'address-types'
                            ,'empty_name' => 'Unassigned'
                            ,'edit_options' => $edit_options ?? null
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textDate']
                            ,'field_id' => $field_id_esc . "[period_start][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_period_start'
                        ], date("Y-m-d"));
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textDate']
                            ,'field_id' => $field_id_esc . "[period_end][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_period_end'
                        ], '');
                        ?>
                    </div>
                </div>

                <!-- Header -->
                <div class="form-row">
                    <div class="col-3 label_custom">
                        <?php echo xlt("Address"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("Address Line 2"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("City"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("County/District"); ?>
                    </div>
                </div>

                <!-- VALUES -->
                <div class="form-row">
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textbox']
                            ,'field_id' => $field_id_esc . "[line_1][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_line1'
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textbox']
                            ,'field_id' => $field_id_esc . "[line_2][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_line2'
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textbox']
                            ,'field_id' => $field_id_esc . "[city][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_city'
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textbox']
                            ,'field_id' => $field_id_esc . "[district][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_district'
                        ], '');
                        ?>
                    </div>
                </div>

                <!-- Header -->
                <div class="form-row">
                    <div class="col-3 label_custom">
                        <?php echo xlt("State"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("Postal Code"); ?>
                    </div>
                    <div class="col-3 label_custom">
                        <?php echo xlt("Country"); ?>
                    </div>
                </div>

                <!-- Header -->
                <div class="form-row">
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['listWithAddButton']
                            ,'field_id' => $field_id_esc . "[state][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_state'
                            ,'list_id' => 'state'
                            ,'empty_name' => 'Unassigned'
                            ,'edit_options' => $edit_options ?? null
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['textbox']
                            ,'field_id' => $field_id_esc . "[postalcode][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_postalcode'
                        ], '');
                        ?>
                    </div>
                    <div class="col-3">
                        <?php
                        generate_form_field([
                            'data_type' => $widgetConstants['listWithAddButton']
                            ,'field_id' => $field_id_esc . "[country][]"
                            ,'smallform' => ($smallform ?? '') . ' form_addresses_country'
                            ,'list_id' => 'country'
                            ,'empty_name' => 'Unassigned'
                            ,'edit_options' => $edit_options ?? null
                        ], '');
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <hr class="m-0 p-0" style="border-top-width: 2px; border-color: var(--gray300)" />

    </div>
</template>

<script type="text/javascript">

    let addressWidgets = [];
    let ADDRESS_ACTION_VALUES = {
        'DELETE': 'INACTIVATE'
        ,'ADD': 'ADD'
        ,'UPDATE': 'UPDATE'
    };
    let datePickerSettings = {
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'; ?>
    };

    // IFF to trap anything in our variables here
    (function() {

        // make sure we don't leak anything
        let addresses = <?php echo json_encode($addresses); ?>;
        let tableId = <?php echo js_escape($table_id); ?>;
        init(tableId, addresses);
    })();

    function init(tableId, addresses){
        //IE Compatibility
        // TODO: @adunsulag, not sure how much this matters as IE compatability is so very small of a userbase...
        if (!Element.prototype.matches) {
            Element.prototype.matches =
                Element.prototype.msMatchesSelector ||
                Element.prototype.webkitMatchesSelector;
        }

        if (!Element.prototype.closest) {
            Element.prototype.closest = function(s) {
                var el = this;

                do {
                    if (Element.prototype.matches.call(el, s)) return el;
                    el = el.parentElement || el.parentNode;
                } while (el !== null && el.nodeType === 1);
                return null;
            };
        }

        // go through and populate our addresses from our JSON array
        if (addresses && addresses.length && addresses.forEach) {
            addresses.forEach(function(record) {
                createEditAddressRowFromRecord(tableId, record);
            });
        } else {
            showElement(document.querySelector(".no_addresses"));
        }

        //Event Listener to Update Full Address on Data Entry
        document.getElementById(tableId).addEventListener('keyup', changeEdit);
        document.getElementById(tableId).addEventListener('mouseup', changeEdit);
        document.getElementById(tableId).addEventListener('touchend', changeEdit);
        // TODO: @adunsulag need to handle the onkeyup event.
    }

    function createEditAddressRowFromRecord(tableId, record) {
        const row_address_template = document.querySelector(".template_add_address");
        var row_address_clone = row_address_template.content.cloneNode(true);

        setInputValue(row_address_clone, "input.form_addresses_id", record.id || "");
        setInputValue(row_address_clone, "input.form_addresses_line1", record.line1 || "");
        setInputValue(row_address_clone, "input.form_addresses_line2", record.line2 || "");
        setInputValue(row_address_clone, "input.form_addresses_city", record.city || "");
        setInputValue(row_address_clone, "input.form_addresses_postalcode", record.postalcode || "");
        setInputValue(row_address_clone, "input.form_addresses_data_action", ADDRESS_ACTION_VALUES.UPDATE);

        setInputValue(row_address_clone, "input.form_addresses_district", record.district || "");
        setInputValue(row_address_clone, "select.form_addresses_country", record.country || "");
        setInputValue(row_address_clone, "select.form_addresses_state", record.state || "");
        setInputValue(row_address_clone, "select.form_addresses_uses", record.use || "");
        setInputValue(row_address_clone, "select.form_addresses_types", record.type || "");
        setInputValue(row_address_clone, "input.form_addresses_period_start", record.period_start || "");
        setInputValue(row_address_clone, "input.form_addresses_period_end", record.period_end || "");

        let useDisplay = getSelectDisplay(row_address_clone, "select.form_addresses_uses");
        let fullAddress = fullAddressFromRecord(record, 1);
        let period = PeriodFromRecord(record);

        row_address_clone.querySelector(".display_addresses_full_address").innerHTML  = fullAddress[0];
        row_address_clone.querySelector(".display_addresses_use").innerHTML  = useDisplay;
        row_address_clone.querySelector(".display_addresses_period").innerHTML  = period;

        let form_row = row_address_clone.querySelector(".form_addresses");
        let display_row = row_address_clone.querySelector(".display_addresses");

        document.getElementById(tableId).querySelector(".table_edit_addresses").appendChild(row_address_clone);

        setupAddressesRowButtonEventListeners(display_row);
        setupContainerDatePickers(form_row);
        setupContainerListAddButtons(form_row);
    }

    function addAddress(event){
        if (!document.querySelector(".no_addresses").classList.contains('d-none')) {
            hideElement(document.querySelector(".no_addresses"));
        }

        let target = event.currentTarget;
        let container = target.closest(".table_edit_addresses")

        const row_address_template = document.querySelector(".template_add_address");
        var row_address_clone = row_address_template.content.cloneNode(true);
        let row_form_addresses = row_address_clone.querySelector(".form_addresses");
        let row_display_addresses = row_address_clone.querySelector(".display_addresses");
        container.appendChild(row_address_clone);

        row_form_addresses.querySelector("input.form_addresses_data_action").value = ADDRESS_ACTION_VALUES.ADD;
        row_display_addresses.querySelector(".display_addresses_full_address").innerHTML  = "";
        row_display_addresses.querySelector(".btn-edit-address").className = "fas fa-solid fa-caret-down fa-lg text-primary btn-edit-address";

        setupAddressesRowButtonEventListeners(row_display_addresses);
        setupContainerDatePickers(row_form_addresses);
        setupContainerListAddButtons(row_form_addresses)

        // expand the element and put the cursor focus in the first element of the address
        showElement(row_form_addresses);
        row_form_addresses.querySelector('input.form_addresses_line1').focus();

    }

    function changeEdit(){
        // note we intentially keep this to be event.target as we have the event at the overarching container level
        // and need to work on the input level.
        var row_form_addresses = event.target.closest('.form_addresses');
        if (!row_form_addresses) {
            console.error("Failed to find element with class .form_addresses");
            return;
        }
        var row_display_addresses = row_form_addresses.previousElementSibling;
        if (!row_display_addresses) {
            console.error("Failed to find previousElementSibling for .form_addresses");
            return;
        }

        let record = createRecordFromInput(row_form_addresses);
        let useDisplay = getSelectDisplay(row_form_addresses, "select.form_addresses_uses");
        let typeDisplay = getSelectDisplay(row_form_addresses, "select.form_addresses_types");
        let fullAddress = fullAddressFromRecord(record, 1);
        let period = PeriodFromRecord(record);

        row_display_addresses.querySelector(".display_addresses_full_address").innerHTML  = fullAddress[0];
        row_display_addresses.querySelector(".display_addresses_use").innerHTML  = useDisplay;
        row_display_addresses.querySelector(".display_addresses_period").innerHTML  = period;

    }

    function editAddress(evt){
        evt.preventDefault();
        let element = evt.currentTarget;
        if (element.className == "fas fa-solid fa-caret-right fa-lg text-primary btn-edit-address") {
            let toggleElement = element.closest('.addresses_group').querySelector('.form_addresses');
            toggleElement.classList.remove('d-none');
            element.className = "fas fa-solid fa-caret-down fa-lg text-primary btn-edit-address";
        } else {
            let toggleElement = element.closest('.addresses_group').querySelector('.form_addresses');
            toggleElement.classList.add('d-none');
            element.className = "fas fa-solid fa-caret-right fa-lg text-primary btn-edit-address";
        }
    }

    function deleteAddress(evt){
        evt.preventDefault();
        var row_display_addresses = evt.currentTarget.closest('.display_addresses');
        if (!row_display_addresses) {
            console.error("Failed to find element with class .display_addresses");
            return;
        }
        var row_form_addresses = row_display_addresses.nextElementSibling;

        //var row_display_addresses = element.closest('.display_addresses');
        //var row_form_addresses = row_display_addresses.nextElementSibling;
        let prompt = window.xl("Are you sure you wish to delete this address?");
        if (confirm(prompt)) {
            // seems odd to hide these when we can just remove them...
            hideElement(row_display_addresses);
            hideElement(row_form_addresses);
            // set the action to be delete so we can remove this index
            setInputValue(row_form_addresses, 'input.form_addresses_data_action', ADDRESS_ACTION_VALUES.DELETE)
        }

    }

    function setupAddressesRowButtonEventListeners(row) {
        row.querySelector(".btn-edit-address").addEventListener('click', editAddress);
        row.querySelector(".btn-delete-address").addEventListener('click', deleteAddress);
    }

    function setupContainerDatePickers(container) {
        let datepickers = container.querySelectorAll(".datepicker");

        // let's setup our date selectors now using our jquery plugin
        $(datepickers).datetimepicker(datePickerSettings);
    }

    function setupContainerListAddButtons(container) {
        if (window.oeUI && window.oeUI.optionWidgets) {
            $(container).find(".addtolist").on("click", function (evt) {
                window.oeUI.optionWidgets.AddToList(this, evt);
            });
        }
    }

    function createRecordFromInput(row_form_addresses) {
        var record = [];

        record.line1 = getInputValue(row_form_addresses, "input.form_addresses_line1");
        record.line2 = getInputValue(row_form_addresses, "input.form_addresses_line2");
        record.city = getInputValue(row_form_addresses, "input.form_addresses_city");
        record.full_zip = getInputValue(row_form_addresses, "input.form_addresses_postalcode");
        record.zip = getInputValue(row_form_addresses, "input.form_addresses_postalcode");
        record.state = getSelectValue(row_form_addresses, "select.form_addresses_state");
        record.country = getSelectValue(row_form_addresses, "select.form_addresses_country");
        record.district = getInputValue(row_form_addresses, "input.form_addresses_district");

        record.use = getSelectValue(row_form_addresses, "select.form_addresses_uses");
        record.useDisplay = getSelectDisplay(row_form_addresses, "select.form_addresses_uses");

        record.type = getSelectValue(row_form_addresses, "select.form_addresses_types");
        record.typeDisplay = getSelectDisplay(row_form_addresses, "select.form_addresses_types");

        record.period_start = getInputValue(row_form_addresses, "input.form_addresses_period_start");
        record.period_end = getInputValue(row_form_addresses, "input.form_addresses_period_end");

        return record;
    }

    function fullAddressFromRecord(record, lines) {
        var fullAddress = [];
        var i = 0;

        fullAddress[0] = record.line1 + (isBlank(record.line1) ? "": ", ");
        fullAddress[0] += record.line2 + ((isBlank(record.line2) || isBlank(fullAddress[0])) ? "": ", ");

        fullAddress[1] = "";
        if (lines > 1) {
            let i = 1;
            fullAddress[0] = fullAddress[0].replace(/(,\s*$)/g, "");
        }

        fullAddress[i] += record.city + ((isBlank(record.city) && isBlank(fullAddress[i])) ? "": ", ");
        fullAddress[i] += record.state + ((isBlank(record.state) && isBlank(fullAddress[i]) && (!isBlank(record.full_zip))) ? "": " ");
        let full_zip = record.zip;

        fullAddress[i] += full_zip + ((isBlank(full_zip) && isBlank(fullAddress[i]) && isBlank(record.country)) ? "": ", ");
        fullAddress[i] += record.country;
        fullAddress[i] = fullAddress[i].replace(/(,\s*$)/g, "");

        return ((isBlank(fullAddress[0]) && isBlank(fullAddress[1])) ? "None": fullAddress);
    }

    function PeriodFromRecord(record) {
        let period = [];
        let periodStart = record.period_start;
        let periodEnd = record.period_end;
        if (!isBlank(periodStart) || !isBlank(periodEnd)) {
            if (!isBlank(periodStart) && isBlank(periodEnd)) {
                period.push(periodStart + " to " + window.xl('Current'));
            } else if (isBlank(periodStart) && !isBlank(periodEnd)) {
                period.push(window.xl('Expired')+ ":" + periodEnd);
            } else {
                period.push(periodStart + " to " + periodEnd);
            }
        }
        return period;
    }

    function setInputValue(root, selector, value) {
        let node = root.querySelector(selector);
        if (!node) {
            console.error("Failed to find DOM node with selector ", selector, ' in node ', root);
            return;
        }
        node.value = value;
    }

    function getInputValue(root, selector) {
        let node = root.querySelector(selector);
        if (!node) {
            console.error("Failed to find DOM node with selector ", selector, ' in node ', root);
            return;
        }
        return node.value;
    }

    function getSelectDisplay(root, selector) {
        let node = root.querySelector(selector);
        if (!node) {
            console.error("Failed to find DOM node with selector ", selector, ' in node ', root);
            return;
        }
        let options = node.selectedOptions;
        if (options && options.length) {
            return options[0].textContent;
        }
        return "";
    }

    function getSelectValue(root, selector) {
        let node = root.querySelector(selector);
        if (!node) {
            console.error("Failed to find DOM node with selector ", selector, ' in node ', root);
            return;
        }
        let options = node.selectedOptions;
        if (options && options.length) {
            return options[0].value;
        }
        return "";
    }

    function hideElement(element){
        element.classList.add('d-none');
    }

    function showElement(element){
        element.classList.remove('d-none');
    }

    function isBlank(str) {
        if (!str) { return true; }
        return str.trim().length === 0;
    }

</script>
