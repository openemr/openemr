<?php

/**
 * interface/eRx.php Redirect to NewCrop pages.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 ZMG LLC <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/globals.php');
require_once($GLOBALS['fileroot'] . '/interface/eRxGlobals.php');
require_once($GLOBALS['fileroot'] . '/interface/eRxStore.php');
require_once($GLOBALS['fileroot'] . '/interface/eRxXMLBuilder.php');
require_once($GLOBALS['fileroot'] . '/interface/eRxPage.php');

set_time_limit(0);

function array_key_exists_default($key, $search, $default = null)
{
    if (array_key_exists($key, $search)) {
        $value = $search[$key];
    } else {
        $value = $default;
    }

    return $value;
}

$GLOBALS_REF = $GLOBALS;
$eRxPage = new eRxPage(
    new eRxXMLBuilder(
        new eRxGlobals($GLOBALS_REF),
        new eRxStore()
    )
);

$eRxPage->setAuthUserId(array_key_exists_default('authUserID', $_SESSION))
    ->setDestination(array_key_exists_default('page', $_REQUEST))
    ->setPatientId(array_key_exists_default('pid', $GLOBALS))
    ->setPrescriptionIds(array_key_exists_default('id', $_REQUEST))
    ->setPrescriptionCount(60);

?>
<html>
    <head>
        <title><?php echo xlt('New Crop'); ?></title>
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
    $messages = $eRxPage->buildXML();

    if (count($messages['demographics']) > 0) {
        ?>
        <strong><?php echo xlt('Warning'); ?>:</strong>
        <p><?php echo xlt('The following fields have to be filled to send a request.'); ?></p>
        <ul>
            <?php foreach ($messages['demographics'] as $message) {
                echo '<li>' . text($message) . '</li>';
            } ?>
        <ul>
        <p><?php echo xlt('You will be automatically redirected to Demographics. You may make the necessary corrections and navigate to NewCrop again.'); ?></p>
        <?php

        ob_end_flush();

        ?>
        <script>
            window.setTimeout(function() {
                window.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics_full.php";
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
        <strong><?php echo xlt('NewCrop call failed'); ?></strong>
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
            <?php require($GLOBALS['srcdir'] . '/restoreSession.php'); ?>
        </script>
        <form name="info" method="post" action="<?php echo $GLOBALS['erx_newcrop_path']; ?>" onsubmit="return top.restoreSession()">
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
