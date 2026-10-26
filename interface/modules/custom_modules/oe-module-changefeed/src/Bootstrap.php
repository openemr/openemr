<?php

/**
 * Change Feed module bootstrap - registers the REST route.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(
            RestApiCreateEvent::EVENT_HANDLE,
            $this->addRestRoutes(...)
        );
    }

    public function addRestRoutes(RestApiCreateEvent $event): void
    {
        $event->addToRouteMap(
            'GET /api/changefeed',
            fn(HttpRestRequest $request, OEGlobalsBag $globalsBag): array =>
                (new ChangeFeedRestController())->getChanges($request, $globalsBag)
        );
    }
}
