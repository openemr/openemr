<?php

/**
 * Helper for UB04 form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("ub04_codes.inc.php");

$lookup = isset($_GET["code_group"]) ? filter_input(INPUT_GET, 'code_group') : "";
$term = isset($_GET["term"]) ? filter_input(INPUT_GET, 'term') : '';
if ($lookup != "") {
    lookup_codes($lookup, $term);
    exit();
}

// Falling through for user dialog.
$users = sqlStatementNoLog("SELECT id,fname,lname,npi,taxonomy FROM users WHERE authorized=? AND active=?", [1,1]);
?>
    <html>
    <head>
        <script>
            function sendSelection(value)
            {
                let parentId = <?php echo js_escape($_GET['formid']); ?>;

                updateProvider(parentId, value);
                dialog.closeAjax();
            }
        </script>
    </head>
    <body>
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th><?php echo xlt('Provider')?></th>
                <th><?php echo xlt('User Id') ?></th>
                <th><?php echo xlt('NPI') ?></th>
                <th><?php echo xlt('Taxonomy') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            while ($row = sqlFetchArray($users)) {
                // Safely encode row JSON. HEX flags avoid accidental HTML sequences inside values.
                $data = json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                ?>
                <tr>
                    <td>
                        <button type="button"
                            class="btn btn-secondary btn-sm js-select-provider"
                            data-row="<?php echo attr($data); ?>">
                            <?php echo text($row['fname'] . ' ' . $row['lname']); ?>
                        </button>
                    </td>
                    <td><?php echo text($row['id']); ?></td>
                    <td><?php echo text($row['npi']); ?></td>
                    <td><?php echo text($row['taxonomy']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <script>
            // Delegated binding (no inline JS). Parses the safely transported JSON and forwards to sendSelection.
            document.addEventListener('click', function (ev) {
                var btn = ev.target.closest && ev.target.closest('.js-select-provider');
                if (!btn) return;
                try {
                    var raw = btn.getAttribute('data-row');
                    var row = JSON.parse(raw);
                    if (typeof sendSelection === 'function') {
                        sendSelection(row);
                    } else if (window.console && console.warn) {
                        console.warn('sendSelection() is not defined');
                    }
                } catch (e) {
                    if (window.console && console.error) console.error('Failed to parse provider row JSON', e);
                }
            });
        </script>
    </body>
    </html>
<?php
function lookup_codes($group, $term): void
{
    global $ub04_codes;
    $gotem = [];

    foreach ($ub04_codes as $v) {
        $match = stripos((string) $v['label'], (string) $term) !== false;
        if ($match && $v['code_group'] == $group) {
            $gotem[] = [
                'label' => $v['label'],
                'value' => $v['code']
            ];
        }
    }
    echo json_encode($gotem);
}
/**
 * Lookup lists
 * @param lookup group string $group
 * @param search string $term
 */
function get_codes_list($group, $term): void
{
    $term = "%" . $term . "%";
    $response = sqlStatement("SELECT CONCAT_WS(': ', isc.code, isc.primary_desc, isc.desc1) as label, isc.code as value, isc.code_group as cg FROM inst_support_codes as isc
HAVING label LIKE ? And cg = ? ORDER BY code ASC", [$term, $group ]);

    while ($row = sqlFetchArray($response)) {
        $resultpd[] = $row;
    }

    echo json_encode($resultpd);
}
