<?php

/**
 * Builds the review queue with the application's shared services.
 *
 * The composition root for ReviewQueueService: callers that cannot take the service through a
 * constructor (legacy portal scripts) get it here, and nothing else in the namespace reaches
 * for a global.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;

final class ReviewQueueFactory
{
    /**
     * @param iterable<ReviewHandlerInterface> $handlers
     */
    public static function create(iterable $handlers = []): ReviewQueueService
    {
        $group = OEGlobalsBag::getInstance()->getString('groupname');

        return new ReviewQueueService(
            new ReviewRequestRepository(new ReviewRequestMapper()),
            new EventAuditReviewTrail(EventAuditLogger::getInstance(), $group !== '' ? $group : 'none'),
            ServiceContainer::getClock(),
            ServiceContainer::getUuidFactory(),
            ServiceContainer::getCrypto(),
            $handlers,
        );
    }
}
