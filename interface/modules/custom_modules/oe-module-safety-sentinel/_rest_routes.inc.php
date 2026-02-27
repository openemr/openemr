<?php

/**
 * Safety Sentinel REST API Routes
 *
 * Registers audit log endpoints under /api/safety-sentinel/.
 * Loaded by openemr.bootstrap.php via the RestApiCreateEvent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Modules\SafetySentinel\RestControllers\AuditLogRestController;

return [
    // Literal route must come before the parameterized :puuid route to avoid conflict.
    "GET /api/safety-sentinel/audit-log/pending-review" =>
        function (HttpRestRequest $request): array {
            $limit = (int)($request->getQueryParams()['limit'] ?? 50);
            return (new AuditLogRestController())->getPending($limit);
        },

    "GET /api/safety-sentinel/audit-log/:puuid" =>
        function (string $puuid, HttpRestRequest $request): array {
            $limit = (int)($request->getQueryParams()['limit'] ?? 10);
            return (new AuditLogRestController())->getByPatient($puuid, $limit);
        },

    "POST /api/safety-sentinel/audit-log" =>
        function (HttpRestRequest $request): array {
            $data = $request->getRequestBodyJSON() ?? [];
            return (new AuditLogRestController())->create($data);
        },

    "PUT /api/safety-sentinel/audit-log/:id/acknowledge" =>
        function (string $id, HttpRestRequest $request): array {
            $data = $request->getRequestBodyJSON() ?? [];
            return (new AuditLogRestController())->acknowledge((int)$id, $data);
        },

    "GET /api/safety-sentinel/health" =>
        function (HttpRestRequest $request): array {
            return (new AuditLogRestController())->health();
        },
];
