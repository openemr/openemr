<?php

namespace OpenEMR\RestControllers;

use OpenEMR\Services\TherapyGroupsService;

class TherapyGroupsRestController
{
    private TherapyGroupsService $therapyGroupService;

    public function __construct()
    {
        $this->therapyGroupService = new TherapyGroupsService();
    }

    public function post(array $data): array
    {
        $processingResult = $this->therapyGroupService->insert($data);

        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    public function getAll($search = []): array
    {
        $processingResult = $this->therapyGroupService->getAll($search);

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function addPatient(string $puuid, int $gpid, array $data): array
    {
        $processingResult = $this->therapyGroupService->addPatient($puuid, $gpid, $data);

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function removePatient(string $puuid, int $gpid): array
    {
        $processingResult = $this->therapyGroupService->removePatient($puuid, $gpid);

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function getAllForPatient(string $puuid): array
    {
        $processingResult = $this->therapyGroupService->getAllForPatient($puuid);

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
