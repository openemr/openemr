<?php

/**
 * Handles the display of the address list datatype in LBF
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

$table_id = uniqid("table_text_addresses_");

$contactService = new ContactService();
$addresses = $contactService->getContactsForPatient($_SESSION['pid'] ?? null) ?? [];

$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");
?>

<div id="<?php echo attr($table_id); ?>" class="row mt-3">
    <div class="table_text_addresses col-12">
        <div class ="text_addresses_header label_custom pl-1" style="text-align: left !important; padding-top: 0.1rem; background-color: var(--gray300)">
            <?php echo xlt("Additional Addresses"); ?>
        </div>
        <div class="d-none no_addresses">
                <span class="text data pl-1"><?php echo xlt("NONE"); ?></span>
                <hr class="m-0 p-0" style="border-top-width: 2px; border-color: var(--gray300)" />
        </div>
    </div>

    <template class="template_text_addresses form-row">
        <div class="text_addresses_row form-group mb-0">
            <div class="text_addresses_subrow_1 form-row pl-1">
                <div class="col-2 text data">
                    <span class="text_addresses_use"></span>
                </div>
                <div class="col-6 text data">
                    <span class="text_addresses_full_address_1"></span>
                </div>
                <div class="col-4 text data">
                    <span class="text_addresses_period"></span>
                </div>
            </div>

            <div class="text_addresses_subrow_2 form-row pl-1">
                <div class="col-2 text data">
                </div>
                <div class="col-6 text data">
                    <span class="text_addresses_full_address_2"></span>
                </div>
                <div class="col-4 text data">
                    <span class="text_addresses_type"></span>
                </div>
            </div>
        </div>
        <hr class="m-0 p-0" style="border-top-width: 2px; border-color: var(--gray300)" />
    </template>
</div>

<script type="text/javascript">

    (function(window) {

        function init(containerId, addresses) {
            let container = window.document.querySelector('#' + containerId);
            if (!container) {
                console.error("Failed to find table DOM node with id " + containerId);
                return;
            }

            if (addresses && addresses.length && addresses.forEach) {
                addresses.forEach(function (record) {
                    createAddressRowFromRecord(container, record);
                })
            } else {
            showElement(document.querySelector(".no_addresses"));
            }
        }

        function createAddressRowFromRecord(container, record) {
            let fullAddress = fullAddressFromRecord(record, 2);
            let period = PeriodFromRecord(record);

            let templateNode = container.querySelector(".template_text_addresses");
            let clonedNode = templateNode.content.cloneNode(true);

            let fullAddressNode1 = clonedNode.querySelector('.text_addresses_full_address_1');
            let fullAddressNode2 = clonedNode.querySelector('.text_addresses_full_address_2');
            let useNode = clonedNode.querySelector('.text_addresses_use');
            let typeNode = clonedNode.querySelector('.text_addresses_type');
            let periodNode = clonedNode.querySelector('.text_addresses_period');

            fullAddressNode1.innerText = fullAddress[0];
            fullAddressNode2.innerText = fullAddress[1];
            useNode.innerText = getAddressUseTitle(record.use);
            typeNode.innerText = getAddressTypeTitle(record.type);
            periodNode.innerText = period;

            let addressTable = container.querySelector('.table_text_addresses');
            if (!addressTable) {
                console.error("Failed to find DOM Node with tbody");
                return;
            }
            addressTable.appendChild(clonedNode);
        }

        function fullAddressFromRecord(record, lines) {
            var fullAddress = [];
            var i = 0;

            fullAddress[0] = record.line1 + (isBlank(record.line1) ? "": ", ");
            fullAddress[0] += record.line2 + ((isBlank(record.line2) || isBlank(fullAddress[0])) ? "": ", ");

            if (lines > 1) {
                i = 1;
                fullAddress[0] = fullAddress[0].replace(/(,\s*$)/g, "");
                fullAddress[i] = "";
            }

            fullAddress[i] += record.city + ((isBlank(record.city) && isBlank(fullAddress[i])) ? "": ", ");
            fullAddress[i] += record.state + ((isBlank(record.state) && isBlank(fullAddress[i]) && (!isBlank(record.full_zip))) ? "": " ");
            let full_zip = record.zip;

            fullAddress[i] += full_zip + ((isBlank(full_zip) && isBlank(fullAddress[i]) && isBlank(record.country)) ? "": ", ");
            fullAddress[i] += record.country;
            fullAddress[i] = fullAddress[i].replace(/(,\s*$)/g, "");
            return fullAddress;
        }

        function getAddressUseTitle(addressUseOptionID) {
            addressUsesList = <?php echo json_encode($list_address_uses); ?>;
            return addressUsesList[addressUseOptionID];
        }

        function getAddressTypeTitle(addressTypeOptionID) {
            addressTypesList = <?php echo json_encode($list_address_types); ?>;
            return addressTypesList[addressTypeOptionID];
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

        function isBlank(str) {
            if (!str) { return true; }
            return str.trim().length === 0;
        }

        function showElement(element){
            element.classList.remove('d-none');
        }


        window.document.addEventListener('DOMContentLoaded', function() {
            init(<?php echo js_escape($table_id); ?>, <?php echo json_encode($addresses); ?>);
        });
    })(window);
</script>
