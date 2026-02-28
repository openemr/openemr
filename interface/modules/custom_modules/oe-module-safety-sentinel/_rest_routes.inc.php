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
use OpenEMR\Modules\SafetySentinel\RestControllers\ConversationRestController;
use OpenEMR\Modules\SafetySentinel\RestControllers\ScribeRestController;

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
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            return (new AuditLogRestController())->create($data);
        },

    "PUT /api/safety-sentinel/audit-log/:id/acknowledge" =>
        function (string $id, HttpRestRequest $request): array {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            return (new AuditLogRestController())->acknowledge((int)$id, $data);
        },

    "GET /api/safety-sentinel/health" =>
        function (HttpRestRequest $request): array {
            return (new AuditLogRestController())->health();
        },

    // ── Conversation history endpoints ──────────────────────────────────────
    // OpenEMR's route parser only pops ONE trailing :param when extracting the
    // resource name. Routes ending with two consecutive params (e.g. :puuid/:conv_id)
    // leave ":puuid" as the resource, producing an invalid scope string.
    // Fix: append a static "/messages" suffix so the resource is always "messages"
    // and the router sees only one dynamic segment at the path end.
    //
    // Scopes: conversations.r (list), messages.s (get), messages.c (save), messages.d (delete)
    "GET /api/safety-sentinel/conversations/:puuid" =>
        function (string $puuid, HttpRestRequest $request): array {
            $limit = (int)($request->getQueryParams()['limit'] ?? 10);
            return (new ConversationRestController())->listByPatient($puuid, $limit);
        },

    "GET /api/safety-sentinel/conversations/:puuid/messages" =>
        function (string $puuid, HttpRestRequest $request): array {
            $conv_id = $request->getQueryParams()['conv_id'] ?? '';
            return (new ConversationRestController())->getByConversation($puuid, $conv_id);
        },

    "POST /api/safety-sentinel/conversations/:puuid/messages" =>
        function (string $puuid, HttpRestRequest $request): array {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            $conv_id = $data['conv_id'] ?? '';
            return (new ConversationRestController())->save($puuid, $conv_id, $data);
        },

    "DELETE /api/safety-sentinel/conversations/:puuid/messages" =>
        function (string $puuid, HttpRestRequest $request): array {
            $conv_id = $request->getQueryParams()['conv_id'] ?? '';
            return (new ConversationRestController())->delete($puuid, $conv_id);
        },

    // ── Scribe encounter endpoints ───────────────────────────────────────────
    // Scopes: user/scribe-encounters.r  (list by patient)
    //         user/scribe-encounters.c  (create)
    //         user/scribe-encounters.u  (update)
    //         user/scribe-encounters.d  (delete draft)
    "GET /api/safety-sentinel/scribe-encounters/:puuid" =>
        function (string $puuid, HttpRestRequest $request): array {
            $limit  = (int)($request->getQueryParams()['limit'] ?? 10);
            $status = $request->getQueryParams()['status'] ?? '';
            return (new ScribeRestController())->listByPatient($puuid, $limit, $status);
        },

    "POST /api/safety-sentinel/scribe-encounters" =>
        function (HttpRestRequest $request): array {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            return (new ScribeRestController())->create($data);
        },

    "PUT /api/safety-sentinel/scribe-encounters/:id" =>
        function (string $id, HttpRestRequest $request): array {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            return (new ScribeRestController())->update((int)$id, $data);
        },

    "DELETE /api/safety-sentinel/scribe-encounters/:id" =>
        function (string $id, HttpRestRequest $request): array {
            return (new ScribeRestController())->delete((int)$id);
        },
];
