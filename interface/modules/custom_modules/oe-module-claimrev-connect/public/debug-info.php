<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AccessDeniedHelper;
    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\ClaimRevConnector\ConnectivityInfo;

    $tab = "connectivity";

    //ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - Connectivity", xl("ClaimRev Connect - Connectivity"));
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Account"); ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <?php $connectivityInfo = new ConnectivityInfo(); ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo xlt("Client Connection Information"); ?></h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><?php echo xlt("Authority");?>: <?php echo text($connectivityInfo->client_authority); ?></li>
                        <li><?php echo xlt("Client ID");?>: <?php echo text($connectivityInfo->clientId); ?></li>
                        <li><?php echo xlt("Client Scope");?>: <?php echo text($connectivityInfo->client_scope); ?></li>
                        <li><?php echo xlt("API Server");?>: <?php echo text($connectivityInfo->api_server); ?></li>
                        <li><?php echo xlt("Default Account");?>: <?php echo text($connectivityInfo->defaultAccount); ?></li>
                        <li><?php echo xlt("Token");?>: <?php echo $connectivityInfo->hasToken ? xlt("Yes") : xlt("No"); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </body>
</html>
