<?php
/**
 * Helper for UB04 form.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */
require_once("../globals.php");

$lookup = isset($_GET["code_group"]) ? filter_input(INPUT_GET, 'code_group') : "";
$term = isset($_GET["term"]) ? filter_input(INPUT_GET, 'term') : '';
if ($lookup != "") {
    get_codes_list($lookup, $term);
    exit();
}

// Falling through for user dialog.
$users = sqlStatementNoLog("SELECT id,fname,lname,npi FROM users WHERE " . "authorized=? AND active=?", array(1,1));
?>
<html>
<head>
<script>
function sendSelection(value)
{
    var parentId = <?php echo json_encode($_GET['formid']); ?>;
    //window.opener.updateValue(parentId, value);
    //window.close();
    updateProvider(parentId, value);
    eModal.close();
}
</script>
</head>
<body>
<table class="table table-striped">
    <thead>
        <tr>
            <th><?php echo xl('Provider')?></th>
                <th><?php echo xl('User Id') ?></th>
                <th><?php echo xl('NPI') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
while ($row = sqlFetchArray($users)) {
    $data = json_encode($row);
?>
<tr>
    <td><button onclick='sendSelection(<?php echo $data;?>)'><?php echo text($row['fname'] . ' ' . $row['lname'])?></button></td>
    <td><?php echo text($row['id']) ?></td>
    <td><?php echo text($row['npi']) ?></td>
 </tr>
<?php } ?>
</tbody>
</table>
</body>
</html>
<?php
/**
 * Lookup lists
* @param lookup group string $group
* @param search string $term
*/
function get_codes_list($group, $term)
{
    $term = "%" . $term . "%";
    $response = sqlStatement("SELECT CONCAT_WS(': ', isc.code, isc.primary_desc, isc.desc1) as label, isc.code as value, isc.code_group as cg FROM inst_support_codes as isc
HAVING label LIKE ? And cg = ? ORDER BY code ASC", array($term, $group ));

    while ($row = sqlFetchArray($response)) {
        $resultpd[] = $row;
    }

    echo json_encode($resultpd);
}
