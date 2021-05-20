<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\FacilityProperties;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('ACL Administration Not Authorized');
    exit;
}

$data = new FacilityProperties();

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
<script src="weno.js"></script>
</body>
</html>
