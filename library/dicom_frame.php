<?php

/**
 * Dicom viewer wrapper script for documents
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author    Victor Kofia <https://kofiav.com> 'Viewer'
 * @author    Jerry Padgett <sjpadgett@gmail.com> 'Viewer wrapper'
 * @copyright Copyright (c) 2017-2018 Victor Kofia <https://kofiav.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Warning: This script wraps the Dicom viewer which is HTML5 compatible only and bootstrap styling
*  should not be used inside this script due to style conflicts with viewer, namely, hidden class.
*/

require_once('../interface/globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'docs')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Dicom Viewer")]);
    exit;
}

$web_path = $_REQUEST['web_path'] ?? null;
if ($web_path) {
    $patid = $_REQUEST['patient_id'] ?? null;
    $docid = isset($_REQUEST['document_id']) ? $_REQUEST['document_id'] : ($_REQUEST['doc_id'] ?? null);
    $d = new Document(attr($docid));
    $type = '.dcm';
    if ($d->get_mimetype() == 'application/dicom+zip') {
        $type = '.zip';
    }
    $csrf = attr(CsrfUtils::collectCsrfToken());
    $state_url = $GLOBALS['web_root'] . "/library/ajax/upload.php";
    $web_path = attr($web_path) . '&retrieve&patient_id=' . attr_url($patid) . '&document_id=' . attr_url($docid) . '&as_file=false&type=' . attr_url($type);
}
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
echo $twig->render("dicom/dicom-viewer.html.twig", [
    'assets_static_relative' => $GLOBALS['assets_static_relative']
    ,'web_path' => $web_path
    ,'state_url' => $state_url
    ,'docid' => $docid
]);
