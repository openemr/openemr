<?php

/**
 * FacilityRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\FacilityService;
use OpenEMR\RestControllers\RestControllerHelper;

class FacilityRestController
{
    private $facilityService;

    /**
     * White list of facility search fields
     */
    private const WHITELISTED_FIELDS = array(
        "name",
        "phone",
        "fax",
        "street",
        "city",
        "state",
        "postal_code",
        "country_code",
        "federal_ein",
        "website",
        "email",
        "domain_identifier",
        "facility_npi",
        "facility_taxonomy",
        "facility_code",
        "billing_location",
        "accepts_assignment",
        "oid",
        "service_location"
    );

    public function __construct()
    {
        $this->facilityService = new FacilityService();
    }

    /**
     * Fetches a single facility resource by id.
     * @param $uuid - The facility uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->facilityService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns facility resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $validSearchFields = $this->facilityService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->getAll($validSearchFields);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    /**
     * Process a HTTP POST request used to create a facility record.
     * @param $data - array of facility fields.
     * @return a 201/Created status code and the facility identifier if successful.
     */
    public function post($data)
    {
        $filteredData = $this->facilityService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->insert($filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing facility record.
     * @param $puuidString - The facility uuid identifier in string format.
     * @param $data - array of facility fields (full resource).
     * @return a 200/Ok status code and the facility resource.
     */
    public function patch($uuid, $data)
    {
        $filteredData = $this->facilityService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->update($uuid, $filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
