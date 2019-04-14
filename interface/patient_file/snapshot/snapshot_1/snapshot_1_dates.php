<?php
/**
 * Get snapshot dates of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once("../../../globals.php");
if (!verifyCsrfToken($_GET["csrf_token_form"])) {
        csrfNotVerified();
}
if (isset($_GET['pid'])) {
    $get_pid = $_GET['pid'];
    $get_snapshot = $_GET['snapshot'];
    if (strpos($get_snapshot, "~") > 0) {
        $array_snapshots = explode('~', $get_snapshot);
        $array_show_div = $array_snapshots; // to be used in jQuery to show relevant divs
        $snapshot_elem = count($array_snapshots);
        $array_query = trim(str_repeat("? ", $snapshot_elem));// trims last blank space
        $array_query = str_replace(" ", ",", $array_query);// to get the correct number of question marks for the array
        array_push($array_snapshots, $get_pid, 1); // to get he correct number and values for the parametets in the array
    } else {
        $array_snapshots = array();
        array_push($array_snapshots, $get_snapshot, $get_pid, 1);
        $array_query = "?";
    }
} else {
    $alert_msg = xl("As there is no pid, therefore cannot retrieve date values for the patient. Will close");
    echo "<script>alert('$alert_msg')</script>";
    exit();
}
    
    $sql = "SELECT DISTINCT
                pbd.date
            FROM
                psychosocial_behavior_data AS pbd
            INNER JOIN psychosocial_behavior_codes AS pbc
            ON
                pbc.loinc_que_code = pbd.loinc_que_code
            WHERE
                SUBSTRING(pbc.form_link, 1, 5) IN($array_query) AND
				pbd.pid = ? AND pbd.active = ?
            ORDER BY
                pbd.date
            DESC";
    
    $dates = sqlStatement($sql, $array_snapshots);
if (sqlNumRows($dates)) {?>
     
     <select name="date-snapshot" id ="date-snapshot" class="form-control">
            <?php
            while ($row = sqlFetchArray($dates)) {
                echo "<option value='" . attr($row['date']) . "'";
                echo ">" . attr($row['date']) . "</option>";
            }
        ?>
     </select>
     
        <?php
} else {
    $alert_msg = xlt('There are no previous snapshots. Please click the Add button to create the initial snapshot. After adding the initial snapshot click refresh button twice') . "." ;
                
     echo "<script language='JavaScript'>\n";
                
     echo "alert('$alert_msg');";
     echo "$('#edit_button').addClass('hide_div');";
     echo "</script></body></html>\n";
     exit();
} ?>
    <input type='hidden' name='snapshot_1_type' id='snapshot_1_type' value = '<?php echo attr($get_snapshot)?>'>