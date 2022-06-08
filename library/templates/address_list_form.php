<?php

/**
 * Address List Form
 *
 * @package   OpenEMR
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
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
?>
<div>
    <table class='form_addresses table table-sm' id="<?php echo attr($table_id); ?>">
        <thead class ="thead-light">
        <tr>
            <th colspan="2">
                <?php echo xlt("Additional Addresses"); ?>&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="fas fa-plus-square" style="color:#007bff;" onclick="AddAddress(event);return false"></span>
            </th>
        </tr>
        </thead>
    </table>

</div>

<template class="template_add_address">
    <tr class='display_addresses'>
        <td class='noPrint col-1'>
            <span class="fas fa-fw fa-edit text-primary" onclick="EditAddress(this);return false"></span>
            <span class="fas fa-fw fa-trash-alt text-danger" onclick="DeleteAddress(this);return false"></span>
        </td>
        <td class='display_addresses_full_address'></td>

    </tr>

    <tr class='d-none form_addresses'>
        <input type="hidden" class="form_addresses_data_action" name="<?php echo attr($name_field_id); ?>[data_action][]" value="<?php echo xla("No Change"); ?>" />
        <input type="hidden" class="form_addresses_id" name="<?php echo attr($name_field_id); ?>[id][]" value="" />
        <input type="hidden" class="form_addresses_foreign_id" name="<?php echo attr($name_field_id); ?>[foreign_id][]" value="" />
        <td></td>
        <td colspan="1">
            <table class="table table-borderless"><tr>
                <tr>
                    <td width='200' colspan='3'><?php echo xlt("Address"); ?></td>
                    <td width='200' colspan='2'><?php echo xlt("Address Line 2"); ?></td>
                </tr>
                <tr>
                    <td colspan='3'><input type='text' class="form_addresses_line1" name="<?php echo attr($name_field_id); ?>[line_1][]" style='width:200px;' value='' tabindex='4'></td>
                    <td colspan='1'><input type='text' class="form_addresses_line2" name="<?php echo attr($name_field_id); ?>[line_2][]" style='width:200px;' value='' tabindex='4'></td>

                <tr height='10'><td></td></tr>
                <tr>
                    <td width='50' colspan='1'><?php echo xlt("City"); ?></td>
                    <td width='8' colspan='1'><?php echo xlt("State"); ?></td>
                    <td width='12' colspan='1'><?php echo xlt("Postal Code"); ?></td>
                    <td width='25' colspan='1'><?php echo xlt("Country"); ?></td>
                </tr>
                <tr>
                    <td colspan='1'><input type='text' class="form_addresses_city" name="<?php echo attr($name_field_id); ?>[city][]" style='width:150px;' value='' tabindex='4'></td>
                    <td colspan='1'>
                        <?php
                            echo generate_select_list(
                                $name_field_id . "[state][]",
                                'state',
                                '',
                                "State",
                                'Unassigned',
                                'addtolistclass_state' . $smallform . ' form_addresses_state',
                                '',
                                '',
                                ($disabled ? array('disabled' => 'disabled') : null),
                                false
                            );
                            ?>
                    </td>
                    <td colspan='1'><input type='text' class="form_addresses_postalcode" name="<?php echo attr($name_field_id); ?>[postalcode][]" style='width:55px;' value='' tabindex='4'></td>
                    <td colspan='1'>
<!--                        <input type='text' class="form_addresses_country" name="--><?php //echo attr($name_field_id); ?><!--[country][]" style='width:200px;' value='' tabindex='4'>-->
                        <?php
                        echo generate_select_list(
                            $name_field_id . "[country][]",
                            'country',
                            '',
                            "Country",
                            'Unassigned',
                            'addtolistclass_country' . $smallform . ' form_addresses_country',
                            '',
                            '',
                            ($disabled ? array('disabled' => 'disabled') : null),
                            false
                        );
                        ?>
                    </td>

                </tr>
                <tr class="mt-1">
                    <td>
                        <div>
                            <input type='button' class="btn btn-primary btn-sm" style="font-size: 0.9em;" value='<?php echo xla("Close"); ?>' onclick='CloseAddressForm(this);return false' tabindex='7'>
                        </div>
                    </td>
                </tr>
                <tr height='10'><td></td></tr>
            </table>
        </td>
    </tr>
</template>

<script type="text/javascript">

    let addressWidgets = [];
    let ADDRESS_ACTION_VALUES = {
        'DELETE': 'INACTIVATE'
        ,'ADD': 'ADD'
        ,'UPDATE': 'UPDATE'
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
                CreateAddressFromJSON(tableId, item);
            });
        }

        //Populate Table Cell - Full Address
        var full_addresses = document.querySelectorAll("td.display_addresses_full_address");

        for (i = 0; i < full_addresses.length; ++i) {
            form_addresses = full_addresses[i].closest('tr.display_addresses').nextElementSibling;
            full_addresses[i].innerText = FullAddress(form_addresses);
        }

        //Event Listener to Update Full Address on Data Entry
        document.getElementById(tableId).addEventListener('keyup', ChangeEdit);
        // TODO: @adunsulag need to handle the onkeyup event.
    }




    function ChangeEdit(){
        var row_form_addresses = event.target.closest('tr.form_addresses');
        var row_display_addresses = row_form_addresses.previousElementSibling;
        var element_full_address = row_display_addresses.querySelector("td.display_addresses_full_address");
        element_full_address.innerText = FullAddress(row_form_addresses);
    }

    function EditAddress(element){
        ShowElement(element.closest('tr.display_addresses').nextElementSibling);
    }

    function CloseAddressForm(element){
        var row_form_addresses = element.closest('tr.form_addresses');
        HideElement(row_form_addresses);
    }



    function DeleteAddress(element){
        var row_display_addresses = element.closest('tr.display_addresses');
        var row_form_addresses = row_display_addresses.nextElementSibling;
        let prompt = window.xl("ARE YOU REALLY REALLY SURE?");
        if (confirm(prompt)) {
            // seems odd to hide these when we can just remove them...
            HideElement(row_display_addresses);
            HideElement(row_form_addresses);
            // set the action to be delete so we can remove this index
            setInputValue(element, 'input.form_addresses_data_action', ADDRESS_ACTION_VALUES.DELETE)
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

    function CreateAddressFromJSON(tableId, record) {
        const row_address_template = document.querySelector(".template_add_address");
        var row_address_clone = row_address_template.content.cloneNode(true);

        setInputValue(row_address_clone, "input.form_addresses_id", record.id || "");
        setInputValue(row_address_clone, "input.form_addresses_line1", record.line1 || "");
        setInputValue(row_address_clone, "input.form_addresses_line2", record.line2 || "");
        setInputValue(row_address_clone, "input.form_addresses_city", record.city || "");
        setInputValue(row_address_clone, "input.form_addresses_postalcode", record.postalcode || "");
        setInputValue(row_address_clone, "input.form_addresses_data_action", ADDRESS_ACTION_VALUES.UPDATE);
        row_address_clone.querySelector("td.display_addresses_full_address").innerHTML  = "None";

        setInputValue(row_address_clone, "select.form_addresses_country", record.country || "");
        setInputValue(row_address_clone, "select.form_addresses_state", record.state || "");

        document.getElementById(tableId).appendChild(row_address_clone);
    }

    function AddAddress(event){
        let target = event.currentTarget;
        let container = target.closest(".form_addresses")
        const row_address_template = document.querySelector(".template_add_address");
        var row_address_clone = row_address_template.content.cloneNode(true);
        row_address_clone.querySelector("input.form_addresses_data_action").value = ADDRESS_ACTION_VALUES.ADD;
        row_address_clone.querySelector("td.display_addresses_full_address").innerHTML  = "None";

        // expand the element and put the cursor focus in the first element of the address
        let row = row_address_clone.querySelector('.form_addresses');
        row.classList.remove('d-none');
        container.appendChild(row_address_clone);
        row.querySelector('input.form_addresses_line1').focus();
    }

    function ShowElement(element){
        element.classList.remove('d-none');
    }

    function HideElement(element){
        element.classList.add('d-none');
    }

    function getSelectValue(root, selector) {
        let node = root.querySelector(selector);
        if (!node) {
            console.error("Failed to find DOM node with selector ", selector, ' in node ', root);
            return;
        }
        let options = node.selectedOptions;
        console.log("options found are ", options);
        if (options && options.length) {
            return options[0].value;
        }
        return "";
    }
    function FullAddress(row_form_addresses){
        var address = "";
        //var row_form_addresses = element.closest('tr.form_addresses');

        let line1 = getInputValue(row_form_addresses, "input.form_addresses_line1");
        let line2 = getInputValue(row_form_addresses, "input.form_addresses_line2");
        let city = getInputValue(row_form_addresses, "input.form_addresses_city");
        let full_zip = getInputValue(row_form_addresses, "input.form_addresses_postalcode");
        let state = getSelectValue(row_form_addresses, "select.form_addresses_state");
        let country = getSelectValue(row_form_addresses, "select.form_addresses_country");

        address = line1 + (isBlank(line1) ? "": ", ");
        address += line2 + (isBlank(line2) ? "": ", ");
        address += city + (isBlank(city) ? "": ", ");
        address += state + ((isBlank(state) || isBlank(full_zip)) ? "": "  ");
        address += full_zip + ( (isBlank(country) || (isBlank(state) && isBlank(full_zip))) ? "": ", ");
        address += country;
        address = address.replace(/(,\s*$)/g, "")
        console.log("Address is " , address);
        return (isBlank(address) ? "None": address);
    }

    function isBlank(str) {
        return (!str || (str.trim().length === 0));
    }



</script>