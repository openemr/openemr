<?php

/**
 * InsuranceRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class InsuranceRestController
{
    private $insuranceService;

    public function __construct()
    {
        $this->insuranceService = new InsuranceService();
    }

    public function getAll($searchParams)
    {
        if (isset($searchParams['uuid'])) {
            $searchParams['uuid'] = new TokenSearchField('uuid', $searchParams['uuid'], true);
        }
        if (isset($searchParams['puuid'])) {
            $searchParams['puuid'] = new TokenSearchField('puuid', $searchParams['puuid'], true);
        }
        $serviceResult = $this->insuranceService->search($searchParams);
        return RestControllerHelper::handleProcessingResult($serviceResult, null, 200);
    }
    public function getOne($insuranceUuid, $puuid)
    {
        $searchParams = [];
        // we do this again cause we have to handle the 404 result here.
        $searchParams['uuid'] = new TokenSearchField('uuid', $insuranceUuid, true);
        $searchParams['puuid'] = new TokenSearchField('puuid', $puuid, true);
        $processingResult = $this->insuranceService->search($searchParams);
        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, false);
    }
    public function put($puuid, $insuranceUuid, $data)
    {
        $data['uuid'] = $insuranceUuid;
        $data['type'] = $data['type'] ?? 'primary';

        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $data['pid'] = $pid;

        $updatedResults = $this->insuranceService->update($data);
        return RestControllerHelper::handleProcessingResult($updatedResults, 200, false);
    }

    public function post($puuid, $data)
    {
        $data['type'] = $data['type'] ?? 'primary';

        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $data['pid'] = $pid;
        $insertedResult = $this->insuranceService->insert($data);
        if (!$insertedResult->isValid()) {
            return RestControllerHelper::handleProcessingResult($insertedResult, 200, false);
        } else if (empty($insertedResult->hasData())) {
            $insertedResult = new ProcessingResult();
            $insertedResult->addInternalError('Insurance Policy record not found after insert');
            return RestControllerHelper::handleProcessingResult($insertedResult, 200);
        }
        $insertedUuid = $insertedResult->getData()[0]['uuid'];

        $processingResult = $this->insuranceService->getOne($insertedUuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function operationSwapInsurance(string $puuid, string $type, string $insuranceUuid)
    {
        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $processingResult = $this->insuranceService->swapInsurance($pid, $type, $insuranceUuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
