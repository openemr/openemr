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

$table_id = uniqid("table_addresses");

$contactService = new ContactService();
$addresses = $contactService->getContactsForPatient($pid) ?? [];
?>
<div id="<?php echo attr($table_id); ?>">
    <template class="template-text-addresses">
        <tr>
            <td class='text-addresses-full-address'></td>
        </tr>
    </template>
    <table class='table-text-addresses table table-sm'>
        <thead class ="thead-light">
        <tr>
            <th width="400"><?php echo xlt("Additional Addresses"); ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<script type="text/javascript">

    (function(window) {
        function setupFullAddressFromRecord(container, record) {
            let templateNode = container.querySelector(".template-text-addresses");
            let clonedNode = templateNode.content.cloneNode(true);

            let addressNode = clonedNode.querySelector('.text-addresses-full-address');

            var address = "";
            address = record.line1 + (isBlank(record.line1) ? "": ", ");
            address += record.line2 + ((isBlank(record.line2) || isBlank(address)) ? "": ", ");
            address += record.city + ((isBlank(record.city) || isBlank(address)) ? "": ", ");
            address += record.state + ((isBlank(record.state) && isBlank(address) && (!isBlank(record.full_zip))) ? "": " ");
            let full_zip = record.zip;

            address += full_zip + ((isBlank(full_zip) && isBlank(address) && isBlank(record.country)) ? "": ", ");
            address += record.country;
            address = address.replace(/(,\s*$)/g, "")

            addressNode.innerText = address;
            let tbody = container.querySelector('.table-text-addresses tbody');
            if (!tbody) {
                console.error("Failed to find DOM Node with tbody");
                return;
            }
            tbody.appendChild(clonedNode);
        }

        function isBlank(str) {
            return (!str || (str.trim().length === 0));
        }

        function init(containerId, addresses) {
            let container = window.document.querySelector('#' + containerId);
            if (!container) {
                console.error("Failed to find table DOM node with id " + containerId);
                return;
            }

            if (addresses && addresses.length && addresses.forEach) {
                addresses.forEach(function (record) {
                    setupFullAddressFromRecord(container, record);
                })
            }

        }
        window.document.addEventListener('DOMContentLoaded', function() {
            init(<?php echo js_escape($table_id); ?>, <?php echo json_encode($addresses); ?>);
        });
    })(window);
</script>
