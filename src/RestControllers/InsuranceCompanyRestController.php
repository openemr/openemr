<?php

/**
 * InsuranceCompanyRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

#[OA\Schema(
    schema: 'api_insurance_company_request',
    description: 'Schema for the insurance_company request',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', description: 'The name of insurance company.', type: 'string'),
        new OA\Property(property: 'attn', description: 'The attn of insurance company.', type: 'string'),
        new OA\Property(property: 'cms_id', description: 'The cms id of insurance company.', type: 'string'),
        new OA\Property(property: 'ins_type_code', description: 'The insurance type code of insurance company. The insurance type code can be found by inspecting the route at (/api/insurance_type).', type: 'string'),
        new OA\Property(property: 'x12_receiver_id', description: 'The x12 receiver id of insurance company.', type: 'string'),
        new OA\Property(property: 'x12_default_partner_id', description: 'The x12 default partner id of insurance company.', type: 'string'),
        new OA\Property(property: 'alt_cms_id', description: 'The alternate cms id of insurance company.', type: 'string'),
        new OA\Property(property: 'line1', description: 'The line1 address of insurance company.', type: 'string'),
        new OA\Property(property: 'line2', description: 'The line2 address of insurance company.', type: 'string'),
        new OA\Property(property: 'city', description: 'The city of insurance company.', type: 'string'),
        new OA\Property(property: 'state', description: 'The state of insurance company.', type: 'string'),
        new OA\Property(property: 'zip', description: 'The zip of insurance company.', type: 'string'),
        new OA\Property(property: 'country', description: 'The country of insurance company.', type: 'string'),
    ],
    example: [
        'name' => 'Cool Insurance Company',
        'attn' => null,
        'cms_id' => null,
        'ins_type_code' => '2',
        'x12_receiver_id' => null,
        'x12_default_partner_id' => null,
        'alt_cms_id' => '',
        'line1' => '123 Cool Lane',
        'line2' => 'Suite 123',
        'city' => 'Cooltown',
        'state' => 'CA',
        'zip' => '12245',
        'country' => 'USA',
    ]
)]
class InsuranceCompanyRestController
{
    private readonly InsuranceCompanyService $insuranceCompanyService;
    private readonly AddressService $addressService;

    public function __construct()
    {
        $this->insuranceCompanyService = new InsuranceCompanyService();
        $this->addressService = new AddressService();
    }

    /**
     * Search parameters are read from the request query string.
     */
    #[OA\Get(
        path: '/api/insurance_company',
        description: 'Retrieves all insurance companies, optionally filtered by search parameters',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'query', description: 'The uuid of the insurance company.', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', in: 'query', description: 'Partial match on the insurance company name.', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'cms_id', in: 'query', description: 'Exact match on the cms id.', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'alt_cms_id', in: 'query', description: 'Exact match on the alternate cms id.', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'ins_type_code', in: 'query', description: 'The insurance type code.', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'inactive',
                in: 'query',
                description: 'Whether the insurance company is inactive (0 or 1).',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['0', '1'])
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        $processingResult = new ProcessingResult();
        try {
            $search = [];
            foreach ($request->getQueryParams() as $key => $value) {
                if (!is_string($value) && !is_array($value)) {
                    throw new SearchFieldException('search', 'unsupported search parameter');
                }
                $search[$key] = match ($key) {
                    'uuid' => new TokenSearchField('uuid', $value, true),
                    'name' => new StringSearchField('name', $value, SearchModifier::CONTAINS),
                    'cms_id', 'alt_cms_id' => new StringSearchField($key, $value, SearchModifier::EXACT),
                    'ins_type_code', 'inactive' => new TokenSearchField($key, $value),
                    default => throw new SearchFieldException('search', 'unsupported search parameter'),
                };
            }
            $processingResult = $this->insuranceCompanyService->search($search);
        } catch (SearchFieldException | \InvalidArgumentException) {
            // do not reflect raw parameter names or values back to the caller
            $processingResult->setValidationMessages(['search' => ['invalid or unsupported search parameter']]);
        }
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }

    #[OA\Get(
        path: '/api/insurance_company/{iid}',
        description: 'Retrieves insurance company',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'iid',
                in: 'path',
                description: 'The id of the insurance company.',
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
    public function getOne($iid)
    {
        $serviceResult = $this->insuranceCompanyService->getOneById($iid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: '/api/insurance_type',
        description: 'Retrieves all insurance types',
        tags: ['standard'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getInsuranceTypes()
    {
        $serviceResult = $this->insuranceCompanyService->getInsuranceTypes();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Post(
        path: '/api/insurance_company',
        description: 'Submits a new insurance company',
        tags: ['standard'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_insurance_company_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(HttpRestRequest $request, $data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        if (!$insuranceCompanyValidationResult->isValid()) {
            return RestControllerHelper::createProcessingResultResponse($request, $insuranceCompanyValidationResult, 400);
        }

        $addressValidationResult = $this->addressService->validate(AddressData::fromArray($data));
        if (!$addressValidationResult->isValid()) {
            // AddressService::validate() returns a Particle ValidationResult;
            // convert so the response helper receives a ProcessingResult
            $addressProcessingResult = new ProcessingResult();
            $addressProcessingResult->setValidationMessages($addressValidationResult->getMessages());
            return RestControllerHelper::createProcessingResultResponse($request, $addressProcessingResult, 400);
        }

        $serviceResult = $this->insuranceCompanyService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, ['iid' => $serviceResult], 201);
    }

    #[OA\Put(
        path: '/api/insurance_company/{iid}',
        description: 'Edit a insurance company',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'iid',
                in: 'path',
                description: 'The id for the insurance company.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_insurance_company_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function put(HttpRestRequest $request, $iid, $data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        if (!$insuranceCompanyValidationResult->isValid()) {
            return RestControllerHelper::createProcessingResultResponse($request, $insuranceCompanyValidationResult, 400);
        }

        $addressValidationResult = $this->addressService->validate(AddressData::fromArray($data));
        if (!$addressValidationResult->isValid()) {
            // AddressService::validate() returns a Particle ValidationResult;
            // convert so the response helper receives a ProcessingResult
            $addressProcessingResult = new ProcessingResult();
            $addressProcessingResult->setValidationMessages($addressValidationResult->getMessages());
            return RestControllerHelper::createProcessingResultResponse($request, $addressProcessingResult, 400);
        }

        $serviceResult = $this->insuranceCompanyService->update($data, $iid);
        return RestControllerHelper::responseHandler($serviceResult, ['iid' => $iid], 200);
    }
}
