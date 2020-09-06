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

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientService;

class PatientRestController
{
    private $patientService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = array(
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
    );

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     * @param $data - array of patient fields.
     * @return a 201/Created status code and the patient identifier if successful.
     */
    public function post($data)
    {
        $processingResult = $this->patientService->insert($data);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing patient record.
     * @param $puuidString - The patient uuid identifier in string format.
     * @param $data - array of patient fields (full resource).
     * @return a 200/Ok status code and the patient resource.
     */
    public function put($puuidString, $data)
    {
        $processingResult = $this->patientService->update($puuidString, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Fetches a single patient resource by id.
     * @param $puuidString - The patient uuid identifier in string format.
     */
    public function getOne($puuidString)
    {
        $processingResult = $this->patientService->getOne($puuidString);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $validSearchFields = array_filter(
            $search,
            function ($key) {
                return in_array($key, self::SUPPORTED_SEARCH_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );

        $processingResult = $this->patientService->getAll($validSearchFields);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
