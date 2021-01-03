<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\FacilityProperties;

$data = new FacilityProperties();

if ($GLOBALS['weno_rx_enable']) {
    $data->ifcolumexist();
}

if ($_POST) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"])) {
        CsrfUtils::csrfNotVerified();
    }
    $data->facilityupdates = $_POST;
    $data->updateFacilityNumber();
}

$facilities = $data->getFacilities();

?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Weno Admin'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .hide {
            display: none;
        }
    </style>
</head>
<body class="body_top">
<div class="container"><br><br>
    <h1><?php print xlt("Facility ") ?>ID's</h1>

    <form name="wenofacilityinfo" method="post" action="facilities.php">
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
          print "<td><input type='hidden' name='location" . $i . "[]' value='" . $facility['id'] . "'></td>";
          print "<td>" . $facility["name"] . "</td><td>".$facility['street']
               . "</td><td>" . $facility['city'] . "</td><td><input type='text' id='weno_id' name='location" . $i
              . "[]' value='" . $facility['weno_id'] . "'></td>";
          print "</tr>";
          ++$i;
        }
        ?>
    </table>
        <input type="submit" value="update" id="save_weno_id" class="btn_primary">
    </form>

    <div style="padding-top: 20px">
        <h3><?php echo xlt('Import') . "/" . xlt('Update Pharmacies') ?></h3>
            <div id="importstatus" style="padding-top: 15px">
                <button class="btn btn-primary" id="connected" title="<?php echo xlt("Weno Connected Phamacies Only");?>">
                    <i id="loading" class="fa fa-sync fa-spin hide"></i><?php echo xlt(' Import') . "/" . xlt('Update')?></button>
            </div>
    </div>

</div>
<script src="weno.js"></script>
</body>
</html>

