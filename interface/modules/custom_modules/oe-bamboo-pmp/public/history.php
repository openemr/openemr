<?php

/**
 * View history of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__DIR__, 4) . "/globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use Juggernaut\Module\Bamboo\Controllers\PatientDataRequestMinXmlBuilder;
use Juggernaut\Module\Bamboo\Controllers\GatewayRequests;

if (empty($_SESSION['pid'])) {
    die('No patient id set');
}

$xmlBuilderMin = new PatientDataRequestMinXmlBuilder();
$gatewayRequests = new GatewayRequests($xmlBuilderMin);
$gatewayRequests->url = "https://mutualauth.pmpgateway.net/v5_1/patient";

//fetching the report data
$report = $gatewayRequests->fetchReportData($gatewayRequests->url);

//return results
libxml_use_internal_errors(TRUE);
$simpleXml = simplexml_load_string($report);
$objXmlDocument = json_encode($simpleXml);
$arrOutput = json_decode($objXmlDocument, TRUE);

?>
<html>
<head>
    <title><?php echo xlt("Bamboo PMP"); ?></title>
    <?php Header::setupHeader('common'); ?>

</head>
<body>
<div id="container_div" class="container mt-3">
    <div class="row">
        <div class="col-md-6 mt-3">
            <div>
                <span style="font-size: xx-large; padding-right: 20px"><?php echo xlt("NarxScores") ?></span>
                <a href="../../../../patient_file/summary/demographics.php" onclick="top.restoreSession()"
                   title="<?php echo xla('Go Back') ?>">
                    <i id="advanced-tooltip" class="fa fa-undo fa-2x" aria-hidden="true"></i></a><?php xlt('Back') ?>
            </div>
            <?php
            if (!AclMain::aclCheckCore('patients', 'med', '', array('write','addonly'))):  ?>
                <div class="row">
                    <div class="col-sm-12">
                        what is this
                    </div>
                </div>
            <?php  endif; ?>
            <?php
            if (!$arrOutput) {
                echo "<p>" . xlt("Bamboo not authorized - return to dashboard") . "</p>";
                echo "</div></body>\n</html>\n";
                die;
            } elseif (isset($arrOutput['Error'])) {
                echo "<div class='alert alert-danger'>";
                echo "<p>" . xlt("Error fetching report - Missing data") . "</p>";
                echo "<p>" . xlt($arrOutput['Error']['Message']) . "</p>";
                echo "</div></body>\n</html>\n";
                die;
            }
            ?>
            <table class="table">
                <tr>
                    <th><?php echo xlt("Score Type") ?></th>
                    <th><?php echo xlt("Score") ?></th>
                </tr>
                <?php
                foreach ($arrOutput['Report']['NarxScores']['Score'] as $narxScore) {
                    echo "<tr>";
                    echo "<td>" . text($narxScore['ScoreType']) . "</td>";
                    echo "<td>" . text($narxScore['ScoreValue']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <h3><?php echo xlt("Report Exp") ?></h3>
            <p><?php echo $arrOutput['Report']['ReportExpiration'] ?></p>
            <p class="m-3"><?php echo $arrOutput['Report']['Message'] ?></p>
            <h3><?php echo xlt("Full Report") ?></h3>
            <p><button class="btn btn-primary" onclick="requestFullReport()"><?php echo xlt("Request Full Report") ?></button> </p>
            <h3><?php echo xlt("Disclaimer") ?></h3>
            <p><?php echo xlt($arrOutput['Disclaimer']) ?></p>
        </div>
    </div>

</div><!--end of container div -->
<script>
    //adding this as a warning message that is not visible to the user
    async function requestFullReport() {
        const url = "<?php echo $arrOutput['Report']['ReportRequestURLs']['ViewableReport']; ?>";

        fetch('fetchFullReport.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'url=' + url
        })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                window.open(data, '_blank');
            });
    }
</script>
</body>
</html>
