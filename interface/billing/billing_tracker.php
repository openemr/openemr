<?php

/**
 * Interface that provides tracking information for a claim batch
 *
 * The back-end ajax that goes with this datatables implementation is
 * located in library/ajax/billing_tracker_ajax.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\{
    Acl\AclMain,
    Csrf\CsrfUtils,
    Twig\TwigContainer
};
use OpenEMR\Core\Header;

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'eob', '', 'write') && !AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    echo (
        new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render(
            'core/unauthorized.html.twig',
            ['pageTitle' => xl("Billing Manager")]
        );
    exit;
}

?>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs']); ?>
    <title><?php echo xlt("Claim File Tracker"); ?></title>
    <style>
        table.dataTable td.details-control:before {
            content: '\f152';
            font-family: 'Font Awesome\ 5 Free';
            cursor: pointer;
            font-size: 22px;
            color: #55a4be;
        }
        table.dataTable tr.shown td.details-control:before {
            content: '\f150';
            color: black;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            const serverUrl = "<?php echo $GLOBALS['webroot']; ?>/library/ajax/billing_tracker_ajax.php?csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
            const oTable = $('#billing-tracker-table').DataTable({
                "processing": true,
                // next 2 lines invoke server side processing
                "ajax": {
                    "type" : "GET",
                    "url" : serverUrl,
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                "columns": [
                    {
                        "class": 'details-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        "data": "status",
                        "render": function(data, type, row, meta) {
                            // Format the status with a nice looking badge
                            if (type === 'display') {
                                if (data == 'success') {
                                    data = '<span class="badge badge-success">' + jsText(data) + '</span>';
                                } else if (data == 'waiting') {
                                    data = '<span class="badge badge-info">' + jsText(data) + '</span>';
                                } else {
                                    data = '<span class="badge badge-warning">' + jsText(data) + '</span>';
                                }
                            }

                            return data;
                        }
                    },
                    { "data": "x12_partner_name" },
                    {
                        "data": "x12_filename",
                        "render": function(data, type, row, meta) {
                            // Build the URL so the user can download the claim batch file
                            if (type === 'display') {
                                const url = '<?php echo $GLOBALS['webroot']; ?>/interface/billing/get_claim_file.php?' +
                                    'key=' + encodeURIComponent(data) +
                                    '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?> +
                                    '&partner=' + encodeURIComponent(row.x12_partner_id);
                                data = '<a href="' + jsAttr(url) + '">' + jsText(data) + '</a>';
                            }

                            return data;
                        }
                    },
                    { "data": "created_at" },
                    { "data": "updated_at" },
                ],
                "order": [[4, 'desc']] // Order by 'Date Created' with newest first
            });

            oTable.on('preXhr.dt', function (e, settings, data) {
                console.log("before ajax call");
                top.restoreSession();
            });

            /* Formatting function for row details - modify as you need */
            function format (d) {
                // `d` is the original data object for the row
                // First output any messages from the SFTP
                let output = '';
                if (d.messages !== null) {
                    d.messages.forEach(message => {
                        output += '<div class="alert alert-info">' + jsText(message) + '</div>';
                    });
                }

                // Now output the claims in this batch
                output += '<table class="table" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
                output +=
                    '<thead>' +
                        '<tr>' +
                            '<th>' + jsText(<?php echo xlj('Patient ID'); ?>) + '</th>' +
                            '<th>' + jsText(<?php echo xlj('Encounter ID'); ?>) + '</th>' +
                            '<th>' + jsText(<?php echo xlj('Payor ID'); ?>) + '</th>' +
                        '</tr>' +
                    '</thead>';
                output += '<tbody>';
                d.claims.forEach(claim => {
                    output +=
                        '<tr>' +
                            '<td>' + jsText(claim.pid) + '</td>' +
                            '<td>' + jsText(claim.encounter) + '</td>' +
                            '<td>' + jsText(claim.payor_id) + '</td>' +
                        '</tr>';
                });
                output += '</tbody>';

                return output;
            }

            // Add event listener for opening and closing details
            $('#billing-tracker-table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).parents('tr');
                var row = oTable.row( tr );

                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
        });
    </script>
</head>
<body>
    <div id="container_div" class="mt-3">
         <div class="w-100 p-4">

             <table id="billing-tracker-table">
                 <thead>
                 <tr>
                     <th>&nbsp;</th>
                     <th><?php echo xlt('Status') ?></th>
                     <th><?php echo xlt('X-12 Partner') ?></th>
                     <th><?php echo xlt('File') ?></th>
                     <th><?php echo xlt('Date Created') ?></th>
                     <th><?php echo xlt('Date Updated') ?></th>
                 </tr>
                 </thead>
                 <tfoot>
                 <tr>
                     <th>&nbsp;</th>
                     <th><?php echo xlt('Status') ?></th>
                     <th><?php echo xlt('X-12 Partner') ?></th>
                     <th><?php echo xlt('File') ?></th>
                     <th><?php echo xlt('Date Created') ?></th>
                     <th><?php echo xlt('Date Updated') ?></th>
                 </tr>
                 </tfoot>
             </table>
        </div>
    </div>
</body>
</html>
