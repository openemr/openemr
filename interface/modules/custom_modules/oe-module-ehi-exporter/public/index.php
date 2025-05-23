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
$twig = $bootstrap->getTwig();
if (isset($_POST['submit'])) {
    try {
        if (!CsrfUtils::verifyCsrfToken($_POST['_token'] ?? '')) {
            throw new \InvalidArgumentException(xl("Invalid CSRF token"));
        }
        $memoryLimitUpdated = ini_set("memory_limit", "-1"); // set the memory limit to be unlimited so we can run the export.
        $pid = intval($_POST['pid'] ?? 0);
        $includeDocuments = intval($_POST['include_documents'] ?? 0) === 1;
        $fileSizeLimit = intval($_POST['file_size_limit'] ?? 500);
        if ($_POST['action'] == 'createExport') {
            if ($pid > 0) {
                $job = $exporter->createExportPatientJob($pid, $includeDocuments, $fileSizeLimit);
//                $result = $exporter->exportPatient($pid, $includeDocuments, $fileSizeLimit);
            } else {
                $job = $exporter->createExportPatientPopulationJob($includeDocuments, $fileSizeLimit);
//                $result = $exporter->exportAll($includeDocuments, $fileSizeLimit);
            }
            echo $twig->render(
                Bootstrap::MODULE_NAME . DIRECTORY_SEPARATOR . 'ehi-exporter-tasks.html.twig',
                [
                    'result' => $result
                    ,'job' => $job
                    , 'assetPath' => $bootstrap->getAssetPath()
                    ,'postUrl' => $GLOBALS['webroot'] . Bootstrap::MODULE_INSTALLATION_PATH . '/'
                                    . Bootstrap::MODULE_NAME . '/public/index.php'
                ]
            );
        // TODO: @adunsulag we really should move all of this into a controller to be cleaner, but we are time crunched here.
        } else if ($_POST['action'] == 'startExport') {
            try {
                $taskId = intval($_POST['taskId'] ?? 0);
                $task = $exporter->runExportTask($taskId);
                echo json_encode($task->getJSON());
            } catch (\Exception $exception) {
                $errorMessage = $exception->getMessage();
                $bootstrap->getLogger()->errorLogCaller($errorMessage, ['trace' => $exception->getTraceAsString()]);
                echo json_encode(['status' => 'failed', 'error_message' => $errorMessage, 'taskId' => $taskId]);
            }
            exit;
        } else if ($_POST['action'] == 'statusUpdate') {
            try {
                $taskId = intval($_POST['taskId'] ?? 0);
                $task = $exporter->getExportTaskForStatusUpdate($taskId);
                // will already have the encoded progress results in the task
                echo json_encode($task->getJSON());
            } catch (\Exception $exception) {
                $errorMessage = $exception->getMessage();
                $bootstrap->getLogger()->errorLogCaller($errorMessage, ['trace' => $exception->getTraceAsString()]);
                echo json_encode(['status' => 'failed', 'error_message' => $errorMessage, 'taskId' => $taskId]);
            }
            exit;
        }
    } catch (\Exception $exception) {
        $errorMessage = $exception->getMessage();
        $bootstrap->getLogger()->errorLogCaller($errorMessage, ['trace' => $exception->getTraceAsString()]);
    }
} else {
    $exportSizeSettings = $exporter->getExportSizeSettings($defaultZipSize);

    echo $twig->render(
        Bootstrap::MODULE_NAME . DIRECTORY_SEPARATOR . 'ehi-exporter.html.twig',
        [
            'result' => $result
            , 'exportSizeSettings' => $exportSizeSettings
            , 'memoryLimitUpdated' => $memoryLimitUpdated
            // TODO: @adunsulag add most recent exports here.
            , 'errorMessage' => $errorMessage
            , 'postAction' => $_SERVER['PHP_SELF']
            , 'site_addr_oath' => trim($GLOBALS['site_addr_oath'] ?? '')
            , 'assetPath' => $bootstrap->getAssetPath()
        ]
    );
}
exit;
