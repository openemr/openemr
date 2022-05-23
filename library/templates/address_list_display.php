<?php

/**
 *
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
            <th width="400">Previous Addresses</th>
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
            let plus_four = record.plus_four;
            let full_zip = record.zip + ((isBlank(record.zip) || isBlank(plus_four)) ? "": "-" + plus_four);

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
            init("<?php echo $table_id; ?>", <?php echo json_encode($addresses); ?>);
        });
    })(window);
</script>
