<?php

/**
 * TransactionRestController
 * This controller creates, updates, and retrieves transactions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jonathan Moore <Jdcmoore@aol.com>
 * @copyright Copyright (c) 2022 Jonathan Moore <Jdcmoore@aol.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientTransactionService;
use OpenEMR\Services\TransactionService;
use OpenEMR\Validators\ProcessingResult;

#[OA\Schema(
    schema: 'api_transaction_request',
    description: 'Schema for the transaction request',
    required: ['message', 'groupname', 'title'],
    properties: [
        new OA\Property(property: 'message', description: 'The message of the transaction.', type: 'string'),
        new OA\Property(property: 'type', description: 'The type of transaction. Use an option from resource=/api/transaction_type', type: 'string'),
        new OA\Property(property: 'groupname', description: "The group name (usually is 'Default').", type: 'string'),
        new OA\Property(property: 'referByNpi', description: 'NPI of the person creating the referral.', type: 'string'),
        new OA\Property(property: 'referToNpi', description: 'NPI of the person getting the referral.', type: 'string'),
        new OA\Property(property: 'referDiagnosis', description: 'The referral diagnosis.', type: 'string'),
        new OA\Property(property: 'riskLevel', description: 'The risk level. (Low, Medium, High)', type: 'string'),
        new OA\Property(property: 'includeVitals', description: 'Are vitals included (0,1)', type: 'string'),
        new OA\Property(property: 'referralDate', description: 'The date of the referral', type: 'string'),
        new OA\Property(property: 'authorization', description: 'The authorization for the referral', type: 'string'),
        new OA\Property(property: 'visits', description: 'The number of visits for the referral', type: 'string'),
        new OA\Property(property: 'validFrom', description: 'The date the referral is valid from', type: 'string'),
        new OA\Property(property: 'validThrough', description: 'The date the referral is valid through', type: 'string'),
    ],
    example: [
        'message' => 'Message',
        'type' => 'LBTref',
        'groupname' => 'Default',
        'referByNpi' => '9999999999',
        'referToNpi' => '9999999999',
        'referDiagnosis' => 'Diag 1',
        'riskLevel' => 'Low',
        'includeVitals' => '1',
        'referralDate' => '2022-01-01',
        'authorization' => 'Auth_123',
        'visits' => '1',
        'validFrom' => '2022-01-02',
        'validThrough' => '2022-01-03',
        'body' => 'Reason 1',
    ]
)]
class TransactionRestController
{
    /**
     * @var PatientTransactionService
     */
    private $patientTransactionService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = [
        'pid'
    ];

    public function __construct()
    {
        $this->patientTransactionService = new PatientTransactionService();
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     *
     * @param  $data - array of patient fields.
     * @return a 201/Created status code and the patient identifier if successful.
     */
    #[OA\Post(
        path: '/api/patient/{pid}/transaction',
        description: 'Submits a transaction',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_transaction_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function CreateTransaction($pid, $data)
    {
        $processingResult = new ProcessingResult();

        $serviceValidation = $this->patientTransactionService->validate($data);
        $controllerValidationResult = RestControllerHelper::validationHandler($serviceValidation);
        if (is_array($controllerValidationResult)) {
            $processingResult->setValidationMessages($controllerValidationResult);
        }


        $serviceResult = $this->patientTransactionService->insert($pid, $data);
        $processingResult->addData($serviceResult);

        return RestControllerHelper::handleProcessingResult($processingResult, 201, true);
    }

    #[OA\Put(
        path: '/api/transaction/{tid}',
        description: 'Updates a transaction',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'tid',
                in: 'path',
                description: 'The id for the transaction.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_transaction_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function UpdateTransaction($tid, $data)
    {
        $processingResult = new ProcessingResult();

        $data = $this->patientTransactionService->update($tid, $data);
        $processingResult->addData($data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, false);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     */
    #[OA\Get(
        path: '/api/patient/{pid}/transaction',
        description: 'Get Transactions for a patient',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient',
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
    public function GetPatientTransactions($pid)
    {
        $processingResult = $this->patientTransactionService->getAll($pid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
