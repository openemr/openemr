<?php

/**
 * PrescriptionRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Ivan Googla <ivan.jo.dev@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2024 Ivan Googla
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PrescriptionService;
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
    #[OA\Post(
        path: '/api/prescription',
        description: 'Creates a new prescription',
        tags: ['standard'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'patient_id', description: 'Patient ID', type: 'integer'),
                    new OA\Property(property: 'drug', description: 'Drug name', type: 'string'),
                    new OA\Property(property: 'dosage', description: 'Dosage', type: 'string'),
                    new OA\Property(property: 'quantity', description: 'Quantity', type: 'string'),
                    new OA\Property(property: 'provider_id', description: 'Provider ID', type: 'integer'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: '201', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->prescriptionService->insert($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Soft-deletes a prescription record by setting active = 0.
     *
     * @param string $uuid The prescription uuid.
     * @param HttpRestRequest $request The HTTP request.
     * @return ResponseInterface 200 status on success, 400 if uuid is invalid.
     */
    #[OA\Delete(
        path: '/api/prescription/{uuid}',
        description: 'Soft-deletes a prescription (sets active = 0)',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the prescription.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function delete(string $uuid, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->prescriptionService->delete($uuid);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Fetches a single prescription resource by id.
     * @param $uuid- The prescription uuid identifier in string format.
     */
    #[OA\Get(
        path: '/api/prescription/{uuid}',
        description: 'Retrieves a prescription',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the prescription.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $uuid, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->prescriptionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) === 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns prescription resources which match an optional search criteria.
     */
    #[OA\Get(
        path: '/api/prescription',
        description: 'Retrieves a list of all prescriptions',
        tags: ['standard'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        $search = $request->getQueryParams();
        unset($search['_REWRITE_COMMAND']);
        $processingResult = $this->prescriptionService->getAll($search);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }
}
