<?php

/**
 * PractitionerRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\PractitionerService;
use OpenEMR\RestControllers\RestControllerHelper;

class PractitionerRestController
{
    private $practitionerService;

    /**
     * White list of practitioner search fields
     */
    private const WHITELISTED_FIELDS = [
        "title",
        "fname",
        "lname",
        "mname",
        "federaltaxid",
        "federaldrugid",
        "upin",
        "facility_id",
        "facility",
        "npi",
        "email",
        "specialty",
        "billname",
        "url",
        "assistant",
        "organization",
        "valedictory",
        "street",
        "streetb",
        "city",
        "state",
        "zip",
        "phone",
        "fax",
        "phonew1",
        "phonecell",
        "notes",
        "state_license_number",
        "username"
    ];

    public function __construct()
    {
        $this->practitionerService = new PractitionerService();
    }

    /**
     * Fetches a single practitioner resource by id.
     * @param $uuid- The practitioner uuid identifier in string format.
     * @param HttpRestRequest $request - The HTTP request object.
     */
    public function getOne($uuid, HttpRestRequest $request)
    {
        $processingResult = $this->practitionerService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns practitioner resources which match an optional search criteria.
     * @param HttpRestRequest $request - The HTTP request object.
     * @param array $search - An array of search fields to filter the results.
     */
    public function getAll(HttpRestRequest $request, $search = [])
    {
        $validSearchFields = $this->practitionerService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->getAll($validSearchFields);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }

    /**
     * Process a HTTP POST request used to create a practitioner record.
     * @param $data - array of practitioner fields.
     * @param HttpRestRequest $request - The HTTP request object.
     * @return a 201/Created status code and the practitioner identifier if successful.
     */
    public function post($data, HttpRestRequest $request)
    {
        $filteredData = $this->practitionerService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->insert($filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Processes a HTTP PATCH request used to update an existing practitioner record.
     * @param $uuid - The practitioner uuid identifier in string format.
     * @param $data - array of practitioner fields (full resource).
     * @param HttpRestRequest $request - The HTTP request object.
     * @return a 200/Ok status code and the practitioner resource.
     */
    public function patch($uuid, $data, HttpRestRequest $request)
    {
        $filteredData = $this->practitionerService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->update($uuid, $filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }
}
