<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Twig\TwigContainer;
    use OpenEMR\Modules\ClaimRevConnector\ConnectivityInfo;

    $tab = "connectivity";

    //ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ClaimRev Connect - Account")]);
    exit;
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    <title><?php echo xlt("ClaimRev Connect - Account"); ?></title>
    <body>
        <div class="row">
            <div class="col">
                <?php require '../templates/navbar.php'; ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
            <?php $connectivityInfo = new ConnectivityInfo(); ?>
                <h3><?php echo xlt("Client Connection Information"); ?></h3>              
                <ul>
                
                    <li><?php echo xlt("Authority");?>: <?php echo text($connectivityInfo->client_authority); ?></li>
                    <li><?php echo xlt("Client ID");?>: <?php echo text($connectivityInfo->clientId); ?></li>
                    <li><?php echo xlt("Client Scope");?>: <?php echo text($connectivityInfo->client_scope); ?></li>
                    <li><?php echo xlt("API Server");?>: <?php echo text($connectivityInfo->api_server); ?></li>
                    <li><?php echo xlt("Default Account");?>: <?php echo text($connectivityInfo->defaultAccount); ?>  </li>
                    <li><?php echo xlt("Token");?>:  <?php echo text($connectivityInfo->hasToken); ?>  </li>
                </ul>
            </div>       
        </div>
        <div class="row">
            <div class="col">
                <a href="index.php"><?php echo xlt("Back to index"); ?></a>
            </div>
        </div>
    </body>
</html>

