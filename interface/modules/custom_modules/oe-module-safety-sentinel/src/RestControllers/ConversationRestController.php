<?php

/**
 * Safety Sentinel Conversation REST Controller
 *
 * Thin controller — delegates all logic to ConversationService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\RestControllers;

use OpenEMR\Modules\SafetySentinel\Services\ConversationService;
use OpenEMR\RestControllers\RestControllerHelper;

class ConversationRestController
{
    private ConversationService $service;

    public function __construct()
    {
        $this->service = new ConversationService();
    }

    public function listByPatient(string $puuid, int $limit = 10): array
    {
        $result = $this->service->listByPatient($puuid, $limit);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }

    public function getByConversation(string $puuid, string $conv_id, int $limit = 500): array
    {
        $result = $this->service->getByConversation($conv_id, $puuid, $limit);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }

    public function save(string $puuid, string $conv_id, array $data): array
    {
        // conv_id may come from the route (empty string when routed via /messages suffix)
        // or from the request body when the static /messages suffix is used.
        $resolvedConvId = !empty($conv_id) ? $conv_id : ($data['conv_id'] ?? '');
        $messages = $data['messages'] ?? [];
        $result = $this->service->save($resolvedConvId, $puuid, $messages);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }

    public function delete(string $puuid, string $conv_id): array
    {
        $result = $this->service->delete($conv_id, $puuid);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }
}
