<?php

/**
 * Email Queue Detail front controller.
 *
 * Returns email details by ID for the report modal.
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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Interface\Reports\EmailQueueDetailController;
use OpenEMR\Reports\Email\EmailQueueService;

$controller = new EmailQueueDetailController(
    new EmailQueueService(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getLogger(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
