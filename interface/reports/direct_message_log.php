<?php

/**
 * Report to view the Direct Message log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once '../globals.php';

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Interface\Reports\DirectMessageLogController;

$controller = new DirectMessageLogController(
    new DirectMessageLogRepository(),
    ServiceContainer::getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
