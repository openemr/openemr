<?php

/**
 * This class is used to render the report for the encounter forms. It takes into account any module
 * forms and will render the report for the form.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

use OpenEMR\Common\Logging\SystemLogger;

class FormReportRenderer
{
    private SystemLogger $logger;
    private FormLocator $locator;
    public function __construct(?FormLocator $locator = null, ?SystemLogger $logger = null)
    {
        $this->locator = $locator ?? new FormLocator();
        $this->logger = $logger ?? new SystemLogger();
    }

    public function renderReport(string $formDir, string $page, $attendant_id, $encounter, $columns, $formId, $noWrap = true)
    {
        $isLBF = str_starts_with($formDir, 'LBF');
        $formLocator = new FormLocator();
        $formPath = $formLocator->findFile($formDir, 'report.php', $page);
        include_once $formPath;
        if ($isLBF) {
            lbf_report($attendant_id, $encounter, $columns, $formId, $formDir, $noWrap);
        } else {
            if (function_exists($formDir . "_report")) {
                call_user_func($formDir . "_report", $attendant_id, $encounter, $columns, $formId);
            } else {
                $this->logger->errorLogCaller("form is missing report function", ['formdir' => $formDir, 'formId' => $formId]);
            }
        }
    }
}
