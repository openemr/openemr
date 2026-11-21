<?php

/**
 * Email Queue Report front controller.
 *
 * Displays email queue status and history with search and filtering.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/interface/globals.php';

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Controllers\Interface\Reports\EmailQueueReportController;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\Email\EmailQueueService;

$controller = new EmailQueueReportController(
    new EmailQueueService(),
    ServiceContainer::getTwig(),
    OEGlobalsBag::getInstance()->getWebRoot(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
