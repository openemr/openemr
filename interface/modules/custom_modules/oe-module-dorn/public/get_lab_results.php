<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2024-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    require_once __DIR__ . "/../../../../globals.php";

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Common\Twig\TwigContainer;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\Dorn\ReceiveHl7Results;

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

if ($resultsGuid) {
    $hl7Results = new ReceiveHl7Results();
    $response = $hl7Results->receiveResults($resultsGuid, $rejectResults);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <?php Header::setupHeader(['opener']); ?>
        <title><?php echo xlt("Alert"); ?></title>
    </head>
    <body>
    <?php
    if (isset($response) && is_array($response)) {
        if (count($response) > 1) {
            echo '<div class="alert alert-info" role="alert">';
            echo xlt('Results are split.');
            echo '</div>';
        }

        foreach ($response as $resultModel) {
            echo '<div class="alert ' . ($resultModel->isSuccess ? 'alert-success' : 'alert-danger') . '" role="alert">';
            echo '<strong>' . ($resultModel->isSuccess ? xlt('Success') : xlt('Error')) . ':</strong> ' . text("$resultModel->message");
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning" role="alert">';
        echo xlt('No results to display.');
        echo '</div>';
    }
    ?>
    </body>
</html>
