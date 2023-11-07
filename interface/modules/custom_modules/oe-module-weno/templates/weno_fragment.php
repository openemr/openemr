<?php 
require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;

$twig = new TwigContainer(null, $GLOBALS['kernel']);

if(!AclMain::aclCheckCore('patients', 'med')){
    exit;
}
$sql = "SELECT * FROM prescriptions WHERE patient_id = ? " .
        "AND indication IS NOT NULL";
$res = sqlStatement($sql,array($pid));

function getProviderByWenoId($external_id){
    $provider = sqlQuery("SELECT fname, mname, lname FROM users WHERE weno_prov_id = ? ", array($external_id));
    if($provider){
            return $provider['fname'] . " " . $provider['mname'] . " " . $provider['lname'];
    } else{
            return "";
    }
}
?>

<script src="../../modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>

<div class="row float-right mr-2">
    <div class="mr-3 click" onclick="sync_weno()">
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
        while($row = sqlFetchArray($res)){ ?>
            <tr>
                <td><?php echo text($row["drug"]); ?></td>
                <td><?php echo text(getProviderByWenoId($row['external_id'])); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
