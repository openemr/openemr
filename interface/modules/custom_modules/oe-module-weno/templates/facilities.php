<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @author  Kofi Appiah <kkappiah@medsov.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

use OpenEMR\Modules\WenoModule\Services\FacilityProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno Admin")]);
    exit;
}

$data = new FacilityProperties();
$logService = new WenoLogService();

if ($_POST) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"])) {
        CsrfUtils::csrfNotVerified();
    }
    $data->facilityupdates = $_POST;
    $data->updateFacilityNumber();
}

$facilities = $data->getFacilities();
$pres_log = $logService->getLastPrescriptionLogStatus();
$pharm_log = $logService->getLastPharmacyDownloadStatus();


?>
<html>
<head>
    <title><?php echo xlt('Weno Admin'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .hide {
            display: none;
        }
    </style>

    <script>
        function activateManagement(){
            var pharm = document.getElementById('pharmacy');
            var exists = pharm.classList.contains('hide');
            if(exists){
                //exist so yes
                pharm.style.display= 'block';
            }

            var fac = document.getElementById('facility');
            fac.style.display='none';
        }
        function activateFacility(){
            var facility = document.getElementById('facility');
            facility.style.display= 'block';
            var management = document.getElementById('pharmacy');
            management.style.display='none';
        }
        
        function downloadPharmacies(){
            if (!window.confirm(xl("This download may take several minutes. Do you want to continue?"))) {
                return false;
            }
            $('#notch-pharm').removeClass("hide");
            $('#pharm-btn').attr("disabled", true);
            $.ajax({
                url: "<?php echo $GLOBALS['webroot']; ?>" + "/interface/modules/custom_modules/oe-module-weno/scripts/file_download.php",
                type: "GET",
                success: function (data) {
                    $('#notch-pharm').addClass("hide");
                    $('#pharm-btn').attr("disabled", false);
                    alert('Update Complete');
                },
                // Error handling
                error: function (error) {
                    $('#notch-pharm').addClass("hide");
                    $('#pharm-btn').attr("disabled", false);
                    console.log(`Error ${error}`);
                }
            });
        }

        function downloadPresLog(){
            $('#notch-presc').removeClass("hide");
            $('#presc-btn').attr("disabled", true);
            $.ajax({
                url: "<?php echo $GLOBALS['webroot']; ?>" + "/interface/modules/custom_modules/oe-module-weno/templates/synch.php",
                type: "GET",
                data: {key:'downloadLog'},
                success: function (data) {
                    $('#notch-presc').addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    alert('Update Complete');
                },
                // Error handling
                error: function (error) {
                    $('#notch-presc').addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    console.log(`Error ${error}`);
                }
            });
        }
    </script>
</head>
<body class="body_top">
<div class="container">
    <button class="btn btn-primary btn-small" id="fac-btn" onclick="activateFacility()"><?php echo xlt("Facility"); ?></button>
    <button class="btn btn-primary btn-small" id="mgt-btn" onclick="activateManagement()"><?php echo xlt("Management"); ?></button>
</div>
<div>
    <div class="container" id="facility"><br><br>
        <h1><?php print xlt("Facility ID's") ?></h1>

        <form name="wenofacilityinfo" method="post" action="facilities.php" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
        <table class="table">
            <thead>
                <th></th>
                <th><?php print xlt('Facility Name'); ?></th>
                <th><?php print xlt('Address'); ?></th>
                <th><?php print xlt('City'); ?></th>
                <th><?php print xlt('Weno ID'); ?></th>
            </thead>
            <?php
            $i = 0;
            foreach ($facilities as $facility) {
                print "<tr>";
                print "<td><input type='hidden' name='location" . $i . "[]' value='" . attr($facility['id']) . "'></td>";
                print "<td>" . text($facility["name"]) . "</td><td>" . text($facility['street'])
                    . "</td><td>" . text($facility['city']) . "</td><td><input type='text' id='weno_id' name='location" . $i
                    . "[]' value='" . text($facility['weno_id']) . "'></td>";
                print "</tr>";
                ++$i;
            }
            ?>
        </table>
            <input type="<?php echo xla('Submit'); ?>" value="update" id="save_weno_id" class="btn_primary">
        </form>
    </div>
    <div class="container hide" id="pharmacy">
        <h1><?php print xlt("Weno Management") ?></h1>
        <div>
            <?php echo xlt("Use this page to download Weno Pharmacy Directory and Weno Prescription Log"); ?>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col"><?php echo xlt("Description"); ?></th>
                <th scope="col"><?php echo xlt("Last Update"); ?></th>
                <th scope="col"><?php echo xlt("Status"); ?></th>
                <th scope="col"><?php echo xlt("Action"); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td><?php echo xlt("Weno Pharmacy Directory"); ?></td>
                    <td><?php echo text($pharm_log['created_at']); ?></td>
                    <td><?php echo xlt($pharm_log['status']); ?></td>
                    <td>
                        <button type="button" id="btn-pharm" onclick="downloadPharmacies();" class="btn btn-primary btn-sm">
                            <?php echo xlt("Download")?>
                            <span class="hide" id="notch-pharm">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </span>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td><?php echo xlt("Prescription log"); ?></td>
                    <td><?php echo text($pres_log['created_at']); ?></td>
                    <td><?php echo xlt($pres_log['status']); ?></td>
                    <td>
                        <button type="button" id="presc-btn" onclick="downloadPresLog();" class="btn btn-primary btn-sm">
                            <?php echo xlt("Download")?>
                            <span class="hide" id="notch-presc">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
