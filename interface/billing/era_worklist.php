<?php

/**
 * ERA Worklist: what unattended posting did with the 835s received from X12 partners.
 *
 * Entry point only: the URL the Fees menu loads. The page itself is EraWorklistController
 * and templates/billing/era_worklist.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once('../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Billing\EraWorklist\WorklistRepository;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Interface\Billing\EraWorklistController;

$controller = new EraWorklistController(
    new WorklistRepository(),
    ServiceContainer::getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getClock(),
);

$controller->dispatchAction(CurrentRequest::get())->send();
