<?php

/**
 * interface/eRx.php Redirect to Ensora eRx pages.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2011 ZMG LLC <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Rx\Ensora\ErxLaunchBlock;
use OpenEMR\Rx\Ensora\ErxLaunchBlockedException;
use OpenEMR\Rx\Ensora\ErxPage;
use OpenEMR\Rx\Ensora\ErxXmlBuilder;

require_once(__DIR__ . '/globals.php');
require_once(OEGlobalsBag::getInstance()->getProjectDir() . '/interface/eRxGlobals.php');
require_once(OEGlobalsBag::getInstance()->getProjectDir() . '/interface/eRxStore.php');

if (!AclMain::aclCheckCore('patients', 'rx')) {
    AccessDeniedHelper::deny('eRx.php: patients/rx not granted');
}

// The page carries the eRx launch request, credentials included; keep browsers from storing it.
header('Cache-Control: no-store');

set_time_limit(0);

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$GLOBALS_REF = $GLOBALS;
$eRxPage = new ErxPage(
    new ErxXmlBuilder(
        new eRxGlobals($GLOBALS_REF),
        new eRxStore(),
        ServiceContainer::getGuzzle(),
        OEGlobalsBag::getInstance()->getBoolean('http_verify_ssl', true),
    ),
    ServiceContainer::getLogger(),
);

$authUserId = $session->get('authUserID');
if (is_string($authUserId) && ctype_digit($authUserId)) {
    $authUserId = (int) $authUserId;
}
if (!is_int($authUserId)) {
    AccessDeniedHelper::deny('eRx.php: no authenticated user');
}

$request = CurrentRequest::get();
$eRxPage->setAuthUserId($authUserId)
    ->setDestination($request->query->getString('page'))
    ->setPatientId(OEGlobalsBag::getInstance()->getInt('pid'))
    ->setPrescriptionIds($request->query->getString('id'))
    ->setPrescriptionCount(60);

?>
<html>
    <head>
        <title><?php echo xlt('Ensora eRx'); ?></title>
    </head>
    <body>
<?php

$missingExtensions = $eRxPage->checkForMissingExtensions();

if (count($missingExtensions) > 0) {
    ?>
        <strong><?php echo xlt('Error'); ?>:</strong>
        <p><?php echo xlt('Please contact your systems administrator, the following component(s) are required but are missing.'); ?></p>
        <ul>
            <?php foreach ($missingExtensions as $missingExtension) {
                echo '<li>' . text($missingExtension) . '</li>';
            } ?>
        <ul>
    <?php
} else {
    $blocked = null;
    $messages = ['demographics' => [], 'empty' => [], 'warning' => []];
    try {
        $messages = $eRxPage->buildXML();
    } catch (ErxLaunchBlockedException $e) {
        $blocked = $e->reason;
    }

    if ($blocked instanceof ErxLaunchBlock) {
        echo match ($blocked) {
            ErxLaunchBlock::UnauthorizedRole => xlt('Unauthorized access to ePrescription'),
            ErxLaunchBlock::MissingTaxId => xlt('Please select a Primary Business Entity facility with \'Tax ID\' as your facility Tax ID. If you are an individual practitioner, use your tax id. This is used for identifying you in the NewCrop system.'),
        };
    } elseif (count($messages['demographics']) > 0) {
        ?>
        <strong><?php echo xlt('Warning'); ?>:</strong>
        <p><?php echo xlt('The following fields have to be filled to send a request.'); ?></p>
        <ul>
            <?php foreach ($messages['demographics'] as $message) {
                echo '<li>' . text($message) . '</li>';
            } ?>
        <ul>
        <p><?php echo xlt('You will be automatically redirected to Demographics. You may make the necessary corrections and navigate to Ensora again.'); ?></p>
        <?php

        ob_end_flush();

        ?>
        <script>
            window.setTimeout(function() {
                window.location = "<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/patient_file/summary/demographics_full.php";
            }, <?php echo (count($messages) * 2000) + 3000; ?>);
        </script>
        <?php
    } elseif (count($messages['empty']) > 0) {
        ?>
        <p><?php echo xlt('The following fields have to be filled to send a request.'); ?></p>
        <ul>
            <?php foreach ($messages['empty'] as $message) {
                echo '<li>' . text($message) . '</li>';
            } ?>
        <ul>
        <?php
    } else {
        if (count($messages['warning']) > 0) {
            ?>
        <strong><?php echo xlt('Warning'); ?></strong>
        <p><?php echo xlt('The following fields are empty.'); ?></p>
        <ul>
            <?php foreach ($messages['warning'] as $message) {
                echo '<li>' . text($message) . '</li>';
            } ?>
        <ul>
        <p><strong><?php echo xlt('This will not prevent you from going to the e-Prescriptions site.'); ?></strong></p>
            <?php

            ob_end_flush();
            $delay = (count($messages) * 2000) + 3000;
        } else {
            $delay = 1;
        }

        $xml = $eRxPage->getXML();

        $errors = $eRxPage->checkError($xml);

        if (count($errors) > 0) {
            ?>
        <strong><?php echo xlt('Ensora call failed'); ?></strong>
        <ul>
            <?php foreach ($errors as $message) {
                echo '<li>' . text($message) . '</li>';
            } ?>
        <ul>
            <?php
        } else {
            $eRxPage->updatePatientData();

            ?>
        <script>
            <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/restoreSession.php'); ?>
        </script>
        <form name="info" method="post" action="<?php echo OEGlobalsBag::getInstance()->getString('erx_newcrop_path'); ?>" onsubmit="return top.restoreSession()">
            <input type="submit" style="display:none">
            <input type="hidden" id="RxInput" name="RxInput" value="<?php echo attr($xml); ?>">
        </form>
        <script>
            window.setTimeout(function() {
                document.forms[0].submit();
            }, <?php echo $delay; ?>);
        </script>
            <?php
        }
    }
}

?>
    </body>
</html>
