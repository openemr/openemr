<?php

/**
 * PatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\SearchQueryConfig;
use Psr\Http\Message\ResponseInterface;

class PatientRestController
{
    private readonly PatientService $patientService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = [
        "fname",
        "lname",
        "ss",
        "street",
        "postal_code",
        "city",
        "state",
        "phone_home",
        "phone_biz",
        "phone_cell",
        'postal_contact',
        'sex',
        'country_code',
        "email",
        "DOB",
    ];

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     * @param $data - array of patient fields.
     * @return ResponseInterface 201/Created status code and the patient identifier if successful.
     */
    public function post($data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->patientService->insert($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing patient record.
     * @param $puuidString - The patient uuid identifier in string format.
     * @param $data - array of patient fields (full resource).
     * @return ResponseInterface 200/Ok status code and the patient resource.
     */
    public function put($puuidString, $data, HttpRestRequest $request)
    {
        $processingResult = $this->patientService->update($puuidString, $data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Fetches a single patient resource by id.
     * @param $puuidString - The patient uuid identifier in string format.
     */
    public function getOne($puuidString, HttpRestRequest $request)
    {
        $processingResult = $this->patientService->getOne($puuidString);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     * @param HttpRestRequest $request - The HTTP request object.
     * @param array $search - An array of search fields to filter the results.
     * @param SearchQueryConfig $config - The search query configuration object.
     */
    public function getAll(HttpRestRequest $request, array $search, SearchQueryConfig $config)
    {
        $validSearchFields = array_filter(
            $search,
            fn($key): bool => in_array($key, self::SUPPORTED_SEARCH_FIELDS),
            ARRAY_FILTER_USE_KEY
        );
        $processingResult = $this->patientService->getAll($validSearchFields, true, null, $config);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }
}
