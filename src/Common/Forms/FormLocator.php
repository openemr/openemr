<?php

/**
 * This class is used to locate the form files for the encounter forms.
 * @package openemr
 * @license   There are segments of code in this file that have been generated via Claude.ai and are licensed as Public Domain.  They have been marked with a header and footer.
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// AI GENERATED CODE: HEADER START
namespace OpenEMR\Common\Forms;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Events\Encounter\LoadEncounterFormFilterEvent;

class FormLocator
{
    private array $pathCache = [];
    private string $fileRoot;
    private SystemLogger $logger;

    // AI GENERATED CODE: HEADER END
    public function __construct(?SystemLogger $logger = null)
    {
        if (!$logger) {
            $logger = new SystemLogger();
        }
        $this->logger = $logger;
    // AI GENERATED CODE: HEADER START
        $this->fileRoot = $GLOBALS['fileroot'];
    }

    public function findFile(string $formDir, string $fileName, string $page): string
    {
        $cacheKey = $this->buildCacheKey($formDir, $fileName, $page);

        if (isset($this->pathCache[$cacheKey])) {
            return $this->pathCache[$cacheKey];
        }

        $path = $this->locateFile($formDir, $fileName, $page);
        $this->pathCache[$cacheKey] = $path;

        return $path;
    }

    private function buildCacheKey(string $formDir, string $fileName, string $page): string
    {
        return implode(':', [$formDir, $fileName, $page]);
    }

    private function locateFile(string $formDir, string $fileName, string $page): string
    {
        $isLBF = substr($formDir, 0, 3) === 'LBF';
        $basePath = $isLBF ? "/interface/forms/LBF/" : "/interface/forms/{$formDir}/";
        $initialPath = $this->fileRoot . $basePath;
        $initialFilename = $initialPath . $fileName;
        $event = new LoadEncounterFormFilterEvent($formDir, $initialPath, $fileName);
        $event->setIsLayoutBasedForm($isLBF);

        // AI GENERATED CODE: HEADER END
        $filteredEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, LoadEncounterFormFilterEvent::EVENT_NAME);

        $finalPath = $filteredEvent->getFormIncludePath();
        if ($finalPath != $initialFilename) {
            if (ModulesApplication::isSafeModuleFileForInclude($finalPath)) {
                return $finalPath;
            } else {
                $this->logger->errorLogCaller(
                    "Module attempted to load a file outside of its directory",
                    ['file' => $event->getFormIncludePath(), 'formdir' => $event->getFormName()]
                );
            }
        }
        if (!file_exists($finalPath)) {
            $this->logger->errorLogCaller("form is missing report.php file", ['file' => $finalPath, 'formdir' => $formDir]);
        }
        // AI GENERATED CODE: HEADER START

        return $finalPath;
    }
}
