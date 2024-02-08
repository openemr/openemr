<?php

/**
 * weno_fragment.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('patients', 'med')) {
    exit;
}

$res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ? AND indication IS NOT NULL", array($pid));

function getProviderByWenoId($external_id): string
{
    $provider = sqlQuery("SELECT fname, mname, lname FROM users WHERE weno_prov_id = ? OR id = ?", array($external_id, $external_id));
    if ($provider) {
        return $provider['fname'] . " " . $provider['mname'] . " " . $provider['lname'];
    } else {
        return "Missing Weno User Id.";
    }
}

?>

<script src="../../modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>

<div class="row float-right mr-2 mb-2">
    <div class="mr-3 click" role="button" onclick="sync_weno()">
        <u><span><i id="sync-icon" class="fa-solid fa-rotate-right"></i></span><?php echo xlt("Synch"); ?></u>
    </div>
    <a href="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/templates/indexrx.php">
        <span><i class="fa-solid fa-pen"></i></span>
    </a>
</div>

<div id="sync-alert" class="d-none"></div>

<div class="table-responsive">
    <table class="table w-100">
        <thead class="thead-light border-bottom">
        <tr>
            <th><?php echo xlt("Drug Name"); ?></th>
            <th><?php echo xlt("Prescriber"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = sqlFetchArray($res)) { ?>
            <tr>
                <td><?php echo text($row["drug"]); ?></td>
                <td><?php echo text(getProviderByWenoId($row['external_id'])); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
