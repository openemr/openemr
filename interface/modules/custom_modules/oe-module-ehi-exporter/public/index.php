<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

require_once(__DIR__ . "/../../../../globals.php");

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$bootstrap = Bootstrap::instantiate($GLOBALS['kernel']->getEventDispatcher(), $GLOBALS['kernel']);
$exporter = $bootstrap->getExporter();

$result = null;
$includeDocuments = false;
$defaultZipSize = 500; // size in MB
$memoryLimitUpdated = false;
$errorMessage = "";
if (isset($_POST['submit'])) {
    try {
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
    ]
);
exit;
