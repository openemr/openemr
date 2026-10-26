<?php

/**
 * REST controller for GET /api/changefeed.
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
use OpenEMR\RestControllers\Config\RestConfig;

class ChangeFeedRestController
{
    public function __construct(
        private readonly ChangeFeedService $service = new ChangeFeedService()
    ) {
    }

    /**
     * Serve the changes after ?_since=<cursor> (default 0), at most ?_limit=<n>
     * (default 100, max 1000). The returned array is JSON-encoded by the REST
     * view subscriber.
     *
     * @return array{
     *     since: int,
     *     cursor: int,
     *     watermark: int,
     *     count: int,
     *     changes: list<array{resourceType: string, id: string, operation: string, cursor: int, changedAt: string}>
     * }
     */
    public function getChanges(HttpRestRequest $request, OEGlobalsBag $globalsBag): array
    {
        // The feed exposes patient and encounter changes; gate it on the same
        // ACL as viewing patient demographics.
        RestConfig::request_authorization_check($request, 'patients', 'demo');

        $since = max(0, (int) $request->getQueryParam('_since'));

        $limitParam = $request->getQueryParam('_limit');
        $limit = is_numeric($limitParam) ? (int) $limitParam : null;

        return $this->service->getChanges($since, $limit);
    }
}
