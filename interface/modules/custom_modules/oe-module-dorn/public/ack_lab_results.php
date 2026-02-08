<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    require_once __DIR__ . "/../../../../globals.php";

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Common\Twig\TwigContainer;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\Dorn\ConnectorApi;

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Procedure Provider")]);
    exit;
}

$resultsGuid = $_REQUEST['resultGuid'];
$rejectResults = $_REQUEST['rejectResults'];
if (empty($rejectResults)) {
    $rejectResults = false;
}

$rejectResults = $rejectResults == "true" ? true : false;
if ($resultsGuid) {
    ConnectorApi::sendAck($resultsGuid, $rejectResults, null);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <?php Header::setupHeader(['opener']);?>
        <title><?php echo xlt("Alert"); ?></title>
    </head>
    <body>
    <?php
    if ($rejectResults == true) {
        ?>
    <h3><?php echo xlt("Results Rejected"); ?></h3>
        <?php
    } else {
        ?>
        <h3><?php echo xlt("Results Accepted"); ?></h3>
        <?php
    }
    ?>
    </body>
</html>
