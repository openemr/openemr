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

$contactService = new ContactService();
$addresses = $contactService->getContactsForPatient($pid);

$table_id = uniqid("table_form_addresses");
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
<div>
    <table class='form_addresses table table-sm' id="<?php echo attr($table_id); ?>">
        <thead class ="thead-light">
        <tr>
            <th colspan="2">
                <?php echo xlt("Additional Addresses"); ?>&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="fas fa-plus-square text-primary" onclick="addAddress(event);return false"></span>
            </th>
        </tr>
        </thead>
    </table>

</div>

<template class="template_add_address">
    <div class="display_addresses row">
        <div class="noPrint col-1">
            <span class="fas fa-fw fa-edit text-primary btn-edit-address"></span>
            <span class="fas fa-fw fa-trash-alt text-danger btn-delete-address"></span>
        </div>
        <div class="col-11 display_addresses_full_address"></div>
    </div>
    <div class='d-none form_addresses row ml-3 mr-3 mt-2 mb-2'>
        <input type="hidden" class="form_addresses_data_action" name="<?php echo attr($name_field_id); ?>[data_action][]" value="<?php echo xla("No Change"); ?>" />
        <input type="hidden" class="form_addresses_id" name="<?php echo attr($name_field_id); ?>[id][]" value="" />
        <input type="hidden" class="form_addresses_foreign_id" name="<?php echo attr($name_field_id); ?>[foreign_id][]" value="" />
        <div class="col-12">
            <!-- Header -->
            <div class="row">
                <div class="col-3">
                    <?php echo xlt("Address Use"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("Address Type"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("Start Date"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("End Date"); ?>
                </div>
            </div>

            <!-- Values -->
            <div class="row">
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
            <div class="row">
                <div class="col-3">
                    <?php echo xlt("Address"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("Address Line 2"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("City"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("County/District"); ?>
                </div>
            </div>

            <!-- VALUES -->
            <div class="row">
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
            <div class="row">
                <div class="col-3">
                    <?php echo xlt("State"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("Postal Code"); ?>
                </div>
                <div class="col-3">
                    <?php echo xlt("Country"); ?>
                </div>
            </div>

            <!-- Header -->
            <div class="row">
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

            <div class="row">
                <div class="col-12">
                    <input type='button' class="btn btn-primary btn-sm btn-close-address" value='<?php echo xla("Close"); ?>' tabindex='7'>
                </div>
            </div>

        </div>
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
            addresses.forEach(function(item) {
                createAddressFromJSON(tableId, item);
            });
        }

        //Populate Table Cell - Full Address
        var full_addresses = document.querySelectorAll(".display_addresses_full_address");

        for (i = 0; i < full_addresses.length; ++i) {
            form_addresses = full_addresses[i].closest('.display_addresses').nextElementSibling;
            full_addresses[i].innerText = fullAddress(form_addresses);
        }

        //Event Listener to Update Full Address on Data Entry
        document.getElementById(tableId).addEventListener('keyup', changeEdit);
        document.getElementById(tableId).addEventListener('mouseup', changeEdit);
        document.getElementById(tableId).addEventListener('touchend', changeEdit);
        // TODO: @adunsulag need to handle the onkeyup event.
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
        var element_full_address = row_display_addresses.querySelector(".display_addresses_full_address");
        element_full_address.innerText = fullAddress(row_form_addresses);
    }

    function editAddress(evt){
        evt.preventDefault();
        let element = evt.currentTarget;
        let toggleElement = element.closest('.display_addresses').nextElementSibling;
        toggleElement.classList.remove('d-none');
    }

    function closeAddressForm(evt){
        evt.preventDefault();
        let element = evt.currentTarget;
        var row_form_addresses = element.closest('.form_addresses');
        hideElement(row_form_addresses);
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

    function createAddressFromJSON(tableId, record) {
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

        row_address_clone.querySelector(".display_addresses_full_address").innerHTML  = "None";

        setupAddressButtonEventListeners(row_address_clone);

        let row = row_address_clone.querySelector(".form_addresses");
        document.getElementById(tableId).appendChild(row_address_clone);
        setupDatePickersForContainer(row);
        setupListAddButtons(row);
    }

    function addAddress(event){
        let target = event.currentTarget;
        let container = target.closest(".form_addresses")
        const row_address_template = document.querySelector(".template_add_address");
        var row_address_clone = row_address_template.content.cloneNode(true);
        row_address_clone.querySelector("input.form_addresses_data_action").value = ADDRESS_ACTION_VALUES.ADD;
        row_address_clone.querySelector(".display_addresses_full_address").innerHTML  = "None";

        // expand the element and put the cursor focus in the first element of the address
        let row = row_address_clone.querySelector('.form_addresses');
        row.classList.remove('d-none');
        setupAddressButtonEventListeners(row_address_clone);
        container.appendChild(row_address_clone);
        row.querySelector('input.form_addresses_line1').focus();
        setupDatePickersForContainer(row);
        setupListAddButtons(row);

    }

    function setupAddressButtonEventListeners(row) {
        row.querySelector(".btn-edit-address").addEventListener('click', editAddress);
        row.querySelector(".btn-delete-address").addEventListener('click', deleteAddress);
        row.querySelector(".btn-close-address").addEventListener('click', closeAddressForm);
    }

    function hideElement(element){
        element.classList.add('d-none');
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
    function fullAddress(row_form_addresses){
        var address = "";
        let line1 = getInputValue(row_form_addresses, "input.form_addresses_line1");
        let line2 = getInputValue(row_form_addresses, "input.form_addresses_line2");
        let city = getInputValue(row_form_addresses, "input.form_addresses_city");
        let full_zip = getInputValue(row_form_addresses, "input.form_addresses_postalcode");
        let state = getSelectValue(row_form_addresses, "select.form_addresses_state");
        let country = getSelectValue(row_form_addresses, "select.form_addresses_country");
        let district = getInputValue(row_form_addresses, "input.form_addresses_district");

        let addressUseValue = getSelectValue(row_form_addresses, "select.form_addresses_uses");
        let addressUse = getSelectDisplay(row_form_addresses, "select.form_addresses_uses");
        let typeValue = getSelectValue(row_form_addresses, "select.form_addresses_types");
        let type = getSelectDisplay(row_form_addresses, "select.form_addresses_types");

        let periodStart = getInputValue(row_form_addresses, "input.form_addresses_period_start");
        let periodEnd = getInputValue(row_form_addresses, "input.form_addresses_period_end");


        address = line1 + (isBlank(line1) ? "": ", ");
        address += line2 + (isBlank(line2) ? "": ", ");
        address += city + (isBlank(city) ? "": ", ");
        // address fields in USA require Co. for the county, not sure if our translations will even pick this up...
        address += isBlank(district) ? "" : (window.xl("Co.") + " " + district + ", ");
        address += state + ((isBlank(state) || isBlank(full_zip)) ? "": "  ");
        address += full_zip + ( (isBlank(country) || (isBlank(state) && isBlank(full_zip))) ? "": ", ");
        address += country;

        // now let's add our meta information
        address += " (";
        let meta = [];
        if (!isBlank(addressUseValue)) {
            if (!isBlank(typeValue)) {
                meta.push(addressUse + ",");
            } else {
                meta.push(addressUse);
            }
        }
        if (!isBlank(typeValue)) {
            meta.push(type);
        }
        if (!isBlank(periodStart) || !isBlank(periodEnd)) {
            if (!isBlank(periodStart) && isBlank(periodEnd)) {
                meta.push(periodStart + " - " + window.xl('Current'));
            } else if (isBlank(periodStart) && !isBlank(periodEnd)) {
                meta.push(window.xl('Expired')+ ":" + periodEnd);
            } else {
                meta.push(periodStart + " - " + periodEnd);
            }
        }
        address += meta.join(" ").trim() + ")";

        address = address.replace(/(,\s*$)/g, "");
        return (isBlank(address) ? "None": address);
    }

    function isBlank(str) {
        return (!str || (str.trim().length === 0));
    }

    function setupDatePickersForContainer(container) {
        let datepickers = container.querySelectorAll(".datepicker");

        // let's setup our date selectors now using our jquery plugin
        $(datepickers).datetimepicker(datePickerSettings);
    }

    function setupListAddButtons(container) {
        if (window.oeUI && window.oeUI.optionWidgets) {
            $(container).find(".addtolist").on("click", function (evt) {
                window.oeUI.optionWidgets.AddToList(this, evt);
            });
        }
    }

</script>