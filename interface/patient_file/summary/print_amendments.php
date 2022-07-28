<?php

/**
 * Print Amendments
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Hema Bandaru <hemab@drcloudemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

//ensure user has proper access
if (!AclMain::aclCheckCore('patients', 'amendment')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Amendment Print")]);
    exit;
}

$amendments = $_REQUEST["ids"];
$amendments = rtrim($amendments, ",");
$amendmentsList = explode(",", $amendments);

$patientDetails = getPatientData($pid, "fname,lname");
$patientName = $patientDetails['lname'] . ", " . $patientDetails['fname'];

function printAmendment($amendmentID, $lastAmendment)
{
    $query = "SELECT lo.title AS 'amendmentFrom', lo1.title AS 'amendmentStatus',a.* FROM amendments a
		LEFT JOIN list_options lo ON a.amendment_by = lo.option_id AND lo.list_id = 'amendment_from' AND lo.activity = 1
		LEFT JOIN list_options lo1 ON a.amendment_status = lo1.option_id AND lo1.list_id = 'amendment_status' AND lo1.activity = 1
		WHERE a.amendment_id = ?";
    $resultSet = sqlQuery($query, array($amendmentID));
    echo "<table>";
    echo "<tr class=text>";
    echo "<td class=bold>" . xlt("Requested Date") . ":"  . "</td>";
    echo "<td>" . text(oeFormatShortDate($resultSet['amendment_date'])) . "</td>";
    echo "</tr>";

    echo "<tr class=text>";
    echo "<td class=bold>" . xlt("Requested By") . ":"  . "</td>";
    echo "<td>" . generate_display_field(array('data_type' => '1','list_id' => 'amendment_from'), $resultSet['amendment_by']) . "</td>";
    echo "</tr>";

    echo "<tr class=text>";
    echo "<td class=bold>" . xlt("Request Status") . ":"  . "</td>";
    echo "<td>" . generate_display_field(array('data_type' => '1','list_id' => 'amendment_status'), $resultSet['amendment_status']) . "</td>";
    echo "</tr>";

    echo "<tr class=text>";
    echo "<td class=bold>" . xlt("Request Description") . ":"  . "</td>";
    echo "<td>" . text($resultSet['amendment_desc']) . "</td>";
    echo "</tr>";

    echo "</table>";

    echo "<hr>";
    echo "<span class='bold'>" . xlt("History") . "</span><br />";
    $pageBreak = ( $lastAmendment ) ? "" : "page-break-after:always";
    echo "<table border='1' cellspacing=0 cellpadding=3 style='width:75%;margin-top:10px;margin-bottom:20px;" . $pageBreak . "'>";
    echo "<tr class='text bold'>";
    echo "<th align=left style='width:10%'>" . xlt("Date") . "</th>";
    echo "<th align=left style='width:20%'>" . xlt("By") . "</th>";
    echo "<th align=left >" . xlt("Comments") . "</th>";
    echo "</tr>";

    $query = "SELECT u.fname,u.lname,ah.* FROM amendments_history ah INNER JOIN users u ON ah.created_by = u.id WHERE ah.amendment_id = ?";
    $resultSet = sqlStatement($query, array($amendmentID));
    while ($row = sqlFetchArray($resultSet)) {
        echo "<tr class=text>";
        $created_date = date('Y-m-d', strtotime($row['created_time']));
        echo "<td>" . text(oeFormatShortDate($created_date)) . "</td>";
        echo "<td>" . text($row['lname']) . ", " . text($row['fname']) . "</td>";
        echo "<td>" . text($row['amendment_note']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body class="body_top">
    <span class='title'><?php echo xlt("Amendments for") . " " . text($patientName); ?></span>
    <p></p>

    <?php
    for ($i = 0; $i < count($amendmentsList); $i++) {
        $lastAmendment = ( $i == count($amendmentsList) - 1 ) ? true : false;
        printAmendment($amendmentsList[$i], $lastAmendment);
    }
    ?>

<script>
    opener.top.printLogPrint(window);
</script>

</body>

</html>
