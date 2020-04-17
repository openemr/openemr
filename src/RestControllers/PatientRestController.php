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
        "email",
        "DOB",
    );

    function __construct($pid)
    {
        $this->patientService = new PatientService();
        $this->patientService->setPid($pid);
    }

    function post($data)
    {
        $serviceResult = $this->patientService->insert($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($serviceResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $serviceResult), 201);
    }

    function put($pid, $data)
    {
        $serviceResult = $this->patientService->update($pid, $data);
        $validationHandlerResult = RestControllerHelper::validationHandler($serviceResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $pid), 200);
    }

    /**
     * Fetches a single patient resource by id.
     */
    function getOne()
    {
        $serviceResult = $this->patientService->getOne();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     */
    function getAll($search = array())
    {
        $validSearchFields = array_filter(
            $search, function ($key) {
                return in_array($key, self::SUPPORTED_SEARCH_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );

        $serviceResult = $this->patientService->getAll($validSearchFields);

        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
