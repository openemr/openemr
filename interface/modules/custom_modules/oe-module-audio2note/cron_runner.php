<?php
error_log("AudioToNote cron_runner.php: Script execution started (simplified version).");

// For CLI scripts, define the site ID if not already set.
// This is crucial *before* globals.php is included.
if (empty($GLOBALS['site_id'])) {
    $GLOBALS['site_id'] = 'default';
    if (!defined('OPENEMR_SITE_ID')) {
        define('OPENEMR_SITE_ID', 'default');
    }
}

/**
 * Cron runner script for the OpenEMR Audio to Note Transcription Polling Service.
 * This script is called by OpenEMR's background task manager based on the entry
 * in the `background_services` table.
 */

// Ensure we have the OpenEMR environment.
if (file_exists(__DIR__ . '/../../../../interface/globals.php')) {
    require_once __DIR__ . '/../../../../interface/globals.php';
} elseif (file_exists(dirname(__FILE__, 4) . '/interface/globals.php')) {
    require_once dirname(__FILE__, 4) . '/interface/globals.php';
} else {
    error_log("CRITICAL ERROR in AudioToNote cron_runner.php: Could not find globals.php. Polling service cannot run.");
    exit(1);
}

// After globals.php, explicitly include forms.inc.php to ensure addForm() is available for cron context
if (isset($GLOBALS['fileroot']) && file_exists($GLOBALS['fileroot'] . '/library/forms.inc.php')) {
    require_once $GLOBALS['fileroot'] . '/library/forms.inc.php';
} else {
    error_log("CRITICAL ERROR in AudioToNote cron_runner.php: Could not find forms.inc.php to include explicitly.");
}

// Ensure _rest_config.php is loaded for RestConfig class
if (isset($GLOBALS['fileroot']) && file_exists($GLOBALS['fileroot'] . '/_rest_config.php')) {
    require_once $GLOBALS['fileroot'] . '/_rest_config.php';
} else {
    error_log("CRITICAL ERROR in AudioToNote cron_runner.php: Could not find _rest_config.php. Polling service may fail.");
}

// Include the TranscriptionPollingService class
require_once __DIR__ . '/src/Services/TranscriptionPollingService.php';

use OpenEMR\Modules\OpenemrAudio2Note\Services\TranscriptionPollingService;

/**
 * Global function called by the OpenEMR cron/background service manager.
 */
function runAudioToNotePolling()
{
    $systemLog = null;
    if (class_exists('OpenEMR\Common\Logging\SystemLogger')) {
        $systemLog = new \OpenEMR\Common\Logging\SystemLogger();
        $systemLog->info("runAudioToNotePolling: Cron function called.");
    } else {
        error_log("runAudioToNotePolling: Cron function called (SystemLogger class not found, using error_log).");
    }

    try {
        $pollingService = new TranscriptionPollingService();
        $pollingService->execute();
        if ($systemLog) {
            $systemLog->info("runAudioToNotePolling: Polling service execution completed.");
        } else {
            error_log("runAudioToNotePolling: Polling service execution completed (logged via error_log).");
        }
    } catch (\Throwable $e) {
        $errorMessage = "runAudioToNotePolling: Exception during polling service execution: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        if ($systemLog) {
            $systemLog->error($errorMessage);
        } else {
            error_log("ERROR in runAudioToNotePolling: " . $errorMessage);
        }
    }
}

// This part allows direct execution for testing, but OpenEMR's cron will call the function.

?>
