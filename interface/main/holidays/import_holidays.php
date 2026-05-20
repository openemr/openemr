<?php

/**
 * Holiday import UI: upload a CSV and sync it onto the calendar.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    sharonco <sharonco@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

set_time_limit(0);

require_once('../../globals.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\HolidayCsvParser;
use OpenEMR\Services\HolidayService;

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for admin/super: Holidays management', xl('Holidays management'));
}

$holidayService = new HolidayService(new HolidayCsvParser());
$csvFileData = $holidayService->getCsvFileData();
$errorMessage = '';
$saved = false;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Download the stored CSV.
if (!empty($_GET['download_file']) && ($_GET['download_file'] == 1)) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

    $targetFile = $holidayService->getTargetFile();
    if (!file_exists($targetFile)) {
        echo xlt('file missing');
    } else {
        header('HTTP/1.1 200 OK');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=holiday.csv');
        readfile($targetFile);
        exit;
    }

    die();
}

// Upload, import, and sync in a single step.
if (!empty($_POST['bn_upload'])) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    $saved = $holidayService->uploadAndSync($_FILES);
    if ($saved) {
        $csvFileData = $holidayService->getCsvFileData();
    } else {
        $errorMessage = $holidayService->getLastError();
    }
}

// Re-import the already-stored CSV into calendar_external (advanced).
if (!empty($_POST['import_holidays'])) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    $saved = $holidayService->importHolidaysFromCsv();
    if (!$saved) {
        $errorMessage = $holidayService->getLastError();
    }
}

// Push calendar_external rows onto the live calendar (advanced).
if (!empty($_POST['sync'])) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    $saved = $holidayService->createHolidayEvents();
    if (!$saved && $errorMessage === '') {
        $errorMessage = xl('Operation Failed');
    }
}

?>

<html>
<head>
    <title><?php echo xlt('Holidays management'); ?></title>
    <?php Header::setupHeader(); ?>

</head>

<body class="body_top">
<?php
if ($saved) {
    echo "<p style='color:green'>" .
        xlt('Successfully Completed') .
        "</p>\n";
} elseif ($errorMessage !== '') {
    echo "<p style='color:red'>" .
        text($errorMessage) .
    "</p>\n";
}
?>
<div class="container-fluid">
<form method='post' action='import_holidays.php' enctype='multipart/form-data'
      onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
<div class="table-responsive">
        <table class='table table-bordered text' cellpadding='4'>
            <thead class='thead-light'>
                <tr>
                    <th align='center' colspan='2'>
                        <?php echo xlt('CSV'); ?>
                    </th>
                </tr>
            </thead>
            <tr>
                <td class='detail' nowrap>
                    <?php echo xlt('CSV File'); ?>
                    <input type="hidden" name="MAX_FILE_SIZE" value="350000000" />
                </td>
                <td class='detail' nowrap>
                    <input type="file" name="form_file" size="40" />
                </td>
            </tr>
            <tr>
                <td class='detail' nowrap>
                    <?php echo xlt('File on server (modification date)'); ?>
                </td>
                <td class='detail' nowrap>
                    <?php if (!empty($csvFileData)) { ?>
                        <a href="#" onclick='window.open("import_holidays.php?download_file=1&csrf_token_form=<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>")'><?php echo text($csvFileData['date']); ?></a>
                    <?php } else { ?>
                        <?php echo xlt('File not found'); ?>
                    <?php } ?>
                </td>
            </tr>

        <tr class='table-light'>
                <td align='center' class='detail' colspan='2'>
                    <input class='btn btn-primary' type='submit' name='bn_upload' value='<?php echo xla('Upload and apply to calendar'); ?>' />
                </td>
            </tr>
        </table>
    </div>
</form>
<div class="table-responsive">
        <table class='table table-bordered'>
            <thead class='thead-light'>
                <tr>
                    <th colspan='2'><?php echo xlt('Advanced'); ?></th>
                </tr>
            </thead>
            <tr>
                <td>
                    <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                        <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
                        <input type='submit' class='btn btn-secondary' name='import_holidays' value='<?php echo xla('Re-import from stored CSV'); ?>'><br />
                    </form>
                </td>
                <td>
                    <?php echo xlt('Re-read the previously uploaded CSV into the calendar_external staging table. Existing rows are replaced.'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <form method='post' action='import_holidays.php' onsubmit='return top.restoreSession()'>
                        <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
                        <input type='submit' class='btn btn-secondary' name='sync' value='<?php echo xla('Synchronize to calendar'); ?>' /><br />
                    </form>
                </td>
                <td>
                    <?php echo xlt('Re-publish the calendar_external rows onto the calendar. Existing holiday events are replaced.'); ?>
                </td>
            </tr>
        </table>
</div>
</div>
</body>
</html>
