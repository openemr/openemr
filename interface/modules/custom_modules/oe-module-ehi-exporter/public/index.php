<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

require_once(__DIR__ . "/../../../../globals.php");

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$bootstrap = Bootstrap::instantiate($GLOBALS['kernel']->getEventDispatcher(), $GLOBALS['kernel']);
$exporter = $bootstrap->getExporter();


if (!AclMain::aclCheckCore("admin", "super")) {
    $twig = $bootstrap->getTwig();
    echo $twig->render("error/400.html.twig", ['statusCode' => 401, 'errorMessage' => 'Access Denied']);
    exit;
}

$result = null;
$includeDocuments = false;
$defaultZipSize = 500; // size in MB
$memoryLimitUpdated = false;
$errorMessage = "";
if (isset($_POST['submit'])) {
    try {
        if (!CsrfUtils::verifyCsrfToken($_POST['_token'] ?? '')) {
            throw new \InvalidArgumentException(xl("Invalid CSRF token"));
        }
        $memoryLimitUpdated = ini_set("memory_limit", "-1"); // set the memory limit to be unlimited so we can run the export.
        $pid = intval($_POST['pid'] ?? 0);
        $includeDocuments = intval($_POST['include_documents'] ?? 0) === 1;
        $fileSizeLimit = intval($_POST['file_size_limit'] ?? 500);
        if ($pid > 0) {
            $result = $exporter->exportPatient($pid, $includeDocuments, $fileSizeLimit);
        } else {
            $result = $exporter->exportAll($includeDocuments, $fileSizeLimit);
        }
    } catch (\Exception $exception) {
        $errorMessage = $exception->getMessage();
        $bootstrap->getLogger()->errorLogCaller($errorMessage, ['trace' => $exception->getTraceAsString()]);
    }
}
$exportSizeSettings = $exporter->getExportSizeSettings($defaultZipSize);
$twig = $bootstrap->getTwig();
echo $twig->render(
    Bootstrap::MODULE_NAME . DIRECTORY_SEPARATOR . 'ehi-exporter.html.twig',
    [
        'result' => $result, 'exportSizeSettings' => $exportSizeSettings, 'memoryLimitUpdated' => $memoryLimitUpdated
        // TODO: @adunsulag add most recent exports here.
        ,'errorMessage' => $errorMessage
        ,'postAction' => htmlspecialchars($_SERVER['PHP_SELF'])
        ,'site_addr_oath' => trim($GLOBALS['site_addr_oath'] ?? '')
    ]
);
exit;
