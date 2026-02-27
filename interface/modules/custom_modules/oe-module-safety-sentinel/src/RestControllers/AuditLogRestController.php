<?php

/**
 * Safety Sentinel Audit Log REST Controller
 *
 * Thin controller — delegates all logic to AuditLogService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\RestControllers;

use OpenEMR\Modules\SafetySentinel\Services\AuditLogService;
use OpenEMR\RestControllers\RestControllerHelper;

class AuditLogRestController
{
    private AuditLogService $service;

    public function __construct()
    {
        $this->service = new AuditLogService();
    }

    public function getByPatient(string $puuid, int $limit = 10): array
    {
        $result = $this->service->getByPatient($puuid, $limit);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }

    public function create(array $data): array
    {
        $result = $this->service->create($data);
        return RestControllerHelper::handleProcessingResult($result, 201);
    }

    public function acknowledge(int $id, array $data): array
    {
        $result = $this->service->acknowledge($id, $data);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }

    public function getPending(int $limit = 50): array
    {
        $result = $this->service->getPending($limit);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }

    public function health(): array
    {
        $result = $this->service->healthCheck();
        return RestControllerHelper::handleProcessingResult($result, 200);
    }
}
