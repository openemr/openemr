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
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\InsuranceCompanyService;

#[OA\Schema(
    schema: "api_insurance_company_request",
    description: "Schema for the insurance_company request",
    required: ["name"],
    properties: [
        new OA\Property(property: "name", description: "The name of insurance company.", type: "string"),
        new OA\Property(property: "attn", description: "The attn of insurance company.", type: "string"),
        new OA\Property(property: "cms_id", description: "The cms id of insurance company.", type: "string"),
        new OA\Property(property: "ins_type_code", description: "The insurance type code of insurance company. The insurance type code can be found by inspecting the route at (/api/insurance_type).", type: "string"),
        new OA\Property(property: "x12_receiver_id", description: "The x12 receiver id of insurance company.", type: "string"),
        new OA\Property(property: "x12_default_partner_id", description: "The x12 default partner id of insurance company.", type: "string"),
        new OA\Property(property: "alt_cms_id", description: "The alternate cms id of insurance company.", type: "string"),
        new OA\Property(property: "line1", description: "The line1 address of insurance company.", type: "string"),
        new OA\Property(property: "line2", description: "The line2 address of insurance company.", type: "string"),
        new OA\Property(property: "city", description: "The city of insurance company.", type: "string"),
        new OA\Property(property: "state", description: "The state of insurance company.", type: "string"),
        new OA\Property(property: "zip", description: "The zip of insurance company.", type: "string"),
        new OA\Property(property: "country", description: "The country of insurance company.", type: "string"),
    ],
    example: [
        "name" => "Cool Insurance Company",
        "attn" => null,
        "cms_id" => null,
        "ins_type_code" => "2",
        "x12_receiver_id" => null,
        "x12_default_partner_id" => null,
        "alt_cms_id" => "",
        "line1" => "123 Cool Lane",
        "line2" => "Suite 123",
        "city" => "Cooltown",
        "state" => "CA",
        "zip" => "12245",
        "country" => "USA",
    ]
)]
class InsuranceCompanyRestController
{
    private $insuranceCompanyService;
    private $addressService;

    public function __construct()
    {
        $this->insuranceCompanyService = new InsuranceCompanyService();
        $this->addressService = new AddressService();
    }

    #[OA\Get(
        path: "/api/insurance_company",
        description: "Retrieves all insurance companies",
        tags: ["standard"],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll()
    {
        $serviceResult = $this->insuranceCompanyService->getAll();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/insurance_company/{iid}",
        description: "Retrieves insurance company",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "iid",
                in: "path",
                description: "The id of the insurance company.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne($iid)
    {
        $serviceResult = $this->insuranceCompanyService->getOneById($iid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/insurance_type",
        description: "Retrieves all insurance types",
        tags: ["standard"],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getInsuranceTypes()
    {
        $serviceResult = $this->insuranceCompanyService->getInsuranceTypes();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Post(
        path: "/api/insurance_company",
        description: "Submits a new insurance company",
        tags: ["standard"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_insurance_company_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) {
            return $insuranceCompanyValidationHandlerResult;
        }

        $addressValidationResult = $this->addressService->validate(AddressData::fromArray($data));
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) {
            return $addressValidationHandlerResult;
        }

        $serviceResult = $this->insuranceCompanyService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, ['iid' => $serviceResult], 201);
    }

    #[OA\Put(
        path: "/api/insurance_company/{iid}",
        description: "Edit a insurance company",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "iid",
                in: "path",
                description: "The id for the insurance company.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_insurance_company_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($iid, $data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) {
            return $insuranceCompanyValidationHandlerResult;
        }

        $addressValidationResult = $this->addressService->validate(AddressData::fromArray($data));
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) {
            return $addressValidationHandlerResult;
        }

        $serviceResult = $this->insuranceCompanyService->update($data, $iid);
        return RestControllerHelper::responseHandler($serviceResult, ['iid' => $iid], 200);
    }
}
