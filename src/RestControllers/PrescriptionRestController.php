<?php

/**
 * PrescriptionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Ivan Googla <ivan.jo.dev@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2024 Ivan Googla
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\PrescriptionService;
use OpenEMR\RestControllers\RestControllerHelper;
use Psr\Http\Message\ResponseInterface;

class PrescriptionRestController
{
    private readonly PrescriptionService $prescriptionService;

    public function __construct()
    {
        $this->prescriptionService = new PrescriptionService();
    }

    /**
     * Process a HTTP POST request used to create a prescription record.
     *
     * @param array<string, mixed> $data array of prescription fields.
     * @param HttpRestRequest $request The HTTP request.
     * @return ResponseInterface 201/Created status code and the prescription identifier if successful.
     */
    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->prescriptionService->insert($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Deletes a prescription record.
     *
     * @param string|int $id The prescription id.
     * @return array<mixed> 200 status on success.
     */
    public function delete(string|int $id): array
    {
        $processingResult = $this->prescriptionService->delete($id);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Fetches a single prescription resource by id.
     * @param $uuid- The prescription uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->prescriptionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns prescription resources which match an optional search criteria.
     */
    public function getAll($search = [])
    {
        $processingResult = $this->prescriptionService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
