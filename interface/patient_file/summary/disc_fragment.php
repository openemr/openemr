<?php

/**
 * disc_fragment.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

/**
 * Retrieve the recent 'N' disclosures.
 * @param $pid   -  patient id.
 * @param $limit -  certain limit up to which the disclosures are to be displyed.
 */
function getDisclosureByDate($pid, $limit)
{
    $discQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el" .
    " LEFT JOIN users u ON u.username = el.user " .
    " WHERE el.patient_id = ? AND el.event IN (SELECT option_id FROM list_options WHERE list_id = 'disclosure_type' AND activity = 1)" .
    " ORDER BY el.date DESC LIMIT 0, " . escape_limit($limit);
    $r1 = sqlStatement($discQry, array($pid));
    $result2 = array();
    for ($iter = 0; $frow = sqlFetchArray($r1); $iter++) {
        $result2[$iter] = $frow;
    }

    return $result2;
}
?>
<div id='pnotes' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br />
<table width='100%'>
<tr style='border-bottom:2px solid #000;' class='text'>
    <td valign='top' class='text'><b><?php  echo xlt('Type'); ?></b></td>
    <td valign='top' class='text'><b><?php  echo xlt('Provider'); ?></b></td>
    <td valign='top' class='text'><b><?php  echo xlt('Summary'); ?></b></td>
</tr>
<?php
//display all the disclosures for the day, as well as others from previous dates, up to a certain number, $N
$N = 3;
//$has_variable is set to 1 if there are disclosures recorded.
$has_disclosure = 0;
//retrieve all the disclosures.
$result = getDisclosureByDate($pid, $N);
if ($result != null) {
    $disclosure_count = 0;//number of disclosures so far displayed
    foreach ($result as $iter) {
        $has_disclosure = 1;
        $app_event = $iter["event"];
        $event = explode("-", $app_event);
        $description = $iter["description"];
        //listing the disclosures
        echo "<tr style='border-bottom:1px dashed' class='text'>";
            echo "<td valign='top' class='text'>";
        if ($event[1] == 'healthcareoperations') {
            echo "<b>";
            echo xlt('health care operations');
            echo "</b>";
        } else {
            echo "<b>" . text($event[1]) . "</b>";
        }

            echo "</td>";
            echo "<td>" . text($iter['user_fullname']) . "</td>";
            echo "<td  valign='top'class='text'>";
            echo text($iter["date"] . " (" . xl('Recipient') . ":" . $iter["recipient"] . ")");
                    echo " " . nl2br(text($description));
            echo "</td>";
        echo "</tr>";
    }
}
?>
</table>
<?php
if ($has_disclosure == 0) { //If there are no disclosures recorded
    ?>
    <span class='text'>
    <?php
    echo xlt("There are no disclosures recorded for this patient.");
    if (AclMain::aclCheckCore('patients', 'disclosure', '', array('write', 'addonly'))) {
        echo " ";
        echo xlt("To record disclosures, please click");
        echo " <a href='disclosure_full.php'>";
        echo xlt("here");
        echo "</a>.";
    }
    ?>
    </span>
    <?php
} else {
    ?>
    <br />
    <span class='text'> <?php
    echo xlt('Displaying the following number of most recent disclosures:');?><b><?php echo " " . text($N); ?></b><br />
    <a href='disclosure_full.php'><?php echo xlt('Click here to view them all.');?></a>
    </span><?php
} ?>
<br />
<br />
</div>
