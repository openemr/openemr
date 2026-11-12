<?php

/**
 * CoreEventSubscribers registers OpenEMR's always-on event subscribers on the kernel dispatcher.
 *
 * These are core behaviour, not optional modules, so they register directly rather than going through the modules
 * system. interface/globals.php calls register() once per request, after OPENEMR_GLOBALS_LOADED is defined.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

use OpenEMR\FHIR\SMART\Subscriber\PortalSmartLaunchSubscriber;
use OpenEMR\Services\CodeTypes\Subscriber\CodeTypeEventsSubscriber;
use OpenEMR\Services\FHIR\Subscriber\CalculatedObservationEventsSubscriber;
use OpenEMR\Services\FHIR\Subscriber\UuidMappingEventsSubscriber;
use OpenEMR\Services\PatientFlowBoard\Subscriber\PatientFlowBoardEventsSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CoreEventSubscribers
{
    public function register(EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->addSubscriber(new UuidMappingEventsSubscriber());
        $dispatcher->addSubscriber(new CalculatedObservationEventsSubscriber());
        $dispatcher->addSubscriber(new CodeTypeEventsSubscriber());
        $dispatcher->addSubscriber(new PatientFlowBoardEventsSubscriber());
        $dispatcher->addSubscriber(new PortalSmartLaunchSubscriber());
    }
}
