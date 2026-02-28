<?php

/**
 * Safety Sentinel Scribe Encounter REST Controller
 *
 * Thin controller — delegates all logic to ScribeEncounterService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\RestControllers;

use OpenEMR\Modules\SafetySentinel\Services\ScribeEncounterService;
use OpenEMR\RestControllers\RestControllerHelper;

class ScribeRestController
{
    private ScribeEncounterService $service;

    public function __construct()
    {
        $this->service = new ScribeEncounterService();
    }

    public function create(array $data): array
    {
        $result = $this->service->create($data);
        return RestControllerHelper::handleProcessingResult($result, 201);
    }

    public function listByPatient(string $puuid, int $limit = 10, string $status = ''): array
    {
        $result = $this->service->listByPatient($puuid, $limit, $status);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }

    public function update(int $id, array $data): array
    {
        $result = $this->service->update($id, $data);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }

    public function delete(int $id): array
    {
        $result = $this->service->delete($id);
        return RestControllerHelper::handleProcessingResult($result, 200);
    }
}
