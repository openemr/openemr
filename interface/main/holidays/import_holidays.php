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

declare(strict_types=1);

set_time_limit(0);

require_once('../../globals.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\HolidayService;
use OpenEMR\Services\InvalidHolidayCsvException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for admin/super: Holidays management', xl('Holidays management'));
}

$request = Request::createFromGlobals();
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$service = HolidayService::createForLegacyContext();

// CSV download branch.
if ($request->query->getInt('download_file') === 1) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    $target = $service->getTargetFile();
    if (!is_file($target)) {
        (new Response(xlt('file missing'), Response::HTTP_NOT_FOUND))->send();
        return;
    }
    $response = new BinaryFileResponse($target);
    $response->headers->set('Content-Type', 'text/csv');
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'holiday.csv');
    $response->send();
    return;
}

$status = null;
$errorMessage = '';

try {
    if ($request->request->get('bn_upload') !== null) {
        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
        $upload = $request->files->get('form_file');
        if (!$upload instanceof UploadedFile) {
            throw new InvalidHolidayCsvException(xl('No file uploaded'));
        }
        $service->uploadAndSync($upload);
        $status = 'success';
    } elseif ($request->request->get('import_holidays') !== null) {
        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
        $service->importHolidaysFromCsv();
        $status = 'success';
    } elseif ($request->request->get('sync') !== null) {
        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
        $service->publishHolidayEvents();
        $status = 'success';
    }
} catch (InvalidHolidayCsvException | RuntimeException $e) {
    $errorMessage = $e->getMessage();
}

$twig = (new TwigContainer())->getTwig();
echo $twig->render('holidays/import.html.twig', [
    'status' => $status,
    'errorMessage' => $errorMessage,
    'storedCsvModifiedAt' => $service->getStoredCsvModifiedAt(),
    'csrfToken' => CsrfUtils::collectCsrfToken(session: $session),
]);
