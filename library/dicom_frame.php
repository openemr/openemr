<?php

/**
 * Dicom viewer wrapper script for documents
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author    Victor Kofia <https://kofiav.com> 'Viewer'
 * @author    Jerry Padgett <sjpadgett@gmail.com> 'Viewer wrapper'
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017-2018 Victor Kofia <https://kofiav.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Warning: This script wraps the Dicom viewer which is HTML5 compatible only and bootstrap styling
*  should not be used inside this script due to style conflicts with viewer, namely, hidden class.
*/

require_once('../interface/globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

if (!AclMain::aclCheckCore('patients', 'docs')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for patients/docs: Dicom Viewer", xl("Dicom Viewer"));
}

$web_path = $_REQUEST['web_path'] ?? null;
if ($web_path) {
    // CSRF only when the sensitive parameter is present. Bare navigation
    // (main-menu Dicom Viewer link) has no `web_path` and just renders the
    // viewer chrome — no token requirement in that case.
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    if (!is_string($web_path) || !str_starts_with($web_path, OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?')) {
        http_response_code(400);
        exit;
    }
    $patid = $_REQUEST['patient_id'] ?? null;
    $docid = $_REQUEST['document_id'] ?? $_REQUEST['doc_id'] ?? null;
    $d = new Document(attr($docid));
    $type = '.dcm';
    if ($d->get_mimetype() == 'application/dicom+zip') {
        $type = '.zip';
    }
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $csrf = CsrfUtils::collectCsrfToken(session: $session);
    $state_url = OEGlobalsBag::getInstance()->getWebRoot() . "/library/ajax/upload.php";
    $web_path = attr($web_path) . '&retrieve&patient_id=' . attr_url($patid) . '&document_id=' . attr_url($docid) . '&as_file=false&type=' . attr_url($type);
}
$twig = ServiceContainer::getTwig();
echo $twig->render("dicom/dicom-viewer.html.twig", [
    'assets_static_relative' => OEGlobalsBag::getInstance()->getKernel()->getAssetsRelative()
    ,'web_root' => OEGlobalsBag::getInstance()->getWebRoot()
    ,'web_path' => $web_path
    ,'state_url' => $state_url ?? null
    ,'docid' => $docid ?? null
]);
