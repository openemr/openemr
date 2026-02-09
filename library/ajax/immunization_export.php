<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022-2023 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once(dirname(__FILE__, 3) . "/interface/globals.php");

use OpenEMR\Common\{
    Acl\AclMain,
    Csrf\CsrfUtils,
    Logging\SystemLogger,
};
use OpenEMR\Services\SpreadSheetService;
use OpenEMR\Common\Twig\TwigFactory;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo TwigFactory::createInstance()->render(
        'core/unauthorized.html.twig',
        ['pageTitle' => xl("Immunization Registry")]
    );
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$immunizations = json_decode((string) $_GET['data'], true);

try {
    $spreadsheet = new SpreadSheetService($immunizations, null, 'immunizations');
    if (!empty($spreadsheet->buildSpreadsheet())) {
        $spreadsheet->downloadSpreadsheet('Xls');
    }
} catch (\Throwable $e) {
    $logger = new SystemLogger();
    $logger->logError($e->getMessage());
}
