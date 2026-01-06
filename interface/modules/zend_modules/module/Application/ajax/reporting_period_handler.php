<?php

/**
 * Standalone AJAX handler for reporting period updates
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Bootstrap OpenEMR
require_once("../../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Cqm\QrdaControllers\QrdaReportController;

header('Content-Type: application/json');

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$action = $_POST['ajax_mode'] ?? '';

try {
    $response = match ($action) {
        'update_reporting_period' => handleUpdateReportingPeriod(),
        'get_measures_for_period' => handleGetMeasuresForPeriod(),
        default => ['success' => false, 'message' => 'Unknown action: ' . $action],
    };
} catch (Exception $e) {
    error_log("Reporting period AJAX error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error occurred'];
}

echo js_escape($response);
exit;

/**
 * Handle updating the reporting period
 */
function handleUpdateReportingPeriod()
{
    $period = $_POST['period'] ?? '';

    if (empty($period)) {
        return ['success' => false, 'message' => 'Period is required'];
    }

    // Validate the period exists in list_options
    $sql = "SELECT COUNT(*) as count FROM list_options
            WHERE list_id = 'ecqm_reporting_period'
                AND option_id = ?
                AND activity = 1";

    $result = sqlQuery($sql, [$period]);

    if ($result['count'] > 0) {
        // Update session
        $_SESSION['selected_ecqm_period'] = $period;

        // Update global
        $GLOBALS['cqm_performance_period'] = $period;

        // Optionally update database global setting
        try {
            $globalExists = sqlQuery("SELECT COUNT(*) as count FROM globals WHERE gl_name = 'cqm_performance_period'");

            if ($globalExists['count'] > 0) {
                sqlStatement(
                    "UPDATE globals SET gl_value = ? WHERE gl_name = 'cqm_performance_period'",
                    [$period]
                );
            } else {
                sqlStatement(
                    "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('cqm_performance_period', 0, ?)",
                    [$period]
                );
            }
        } catch (Exception $e) {
            // Log error but don't fail the request
            error_log("Could not update global setting: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => xl('Reporting period updated successfully'),
            'period' => $period
        ];
    } else {
        return [
            'success' => false,
            'message' => xl('Invalid reporting period selected')
        ];
    }
}

/**
 * Handle getting measures for a period
 */
function handleGetMeasuresForPeriod()
{
    $period = $_POST['period'] ?? '';

    if (empty($period)) {
        return ['success' => false, 'message' => 'Period is required'];
    }

    // Temporarily update the global to get measures for the selected period
    $originalPeriod = $GLOBALS['cqm_performance_period'] ?? null;
    $GLOBALS['cqm_performance_period'] = $period;

    $measures = [];

    try {
        // QrdaReportController
        if (count($measures ?: []) === 0 && class_exists(\OpenEMR\Cqm\QrdaControllers\QrdaReportController::class)) {
            try {
                $reportController = new QrdaReportController();
                $controllerMeasures = $reportController->reportMeasures ?? [];

                foreach ($controllerMeasures as $measure) {
                    $measures[] = [
                        'measure_id' => $measure['measure_id'] ?? '',
                        'title' => $measure['title'] ?? 'Unknown Measure',
                        'description' => $measure['description'] ?? ''
                    ];
                }
            } catch (Exception $e) {
                error_log("Could not load measures from QrdaReportController: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'measures' => $measures,
            'period' => $period,
            'count' => count($measures)
        ];
    } catch (Exception $e) {
        // Restore original period on error
        if ($originalPeriod !== null) {
            $GLOBALS['cqm_performance_period'] = $originalPeriod;
        }

        return [
            'success' => false,
            'message' => 'Error loading measures: ' . $e->getMessage()
        ];
    }
}
