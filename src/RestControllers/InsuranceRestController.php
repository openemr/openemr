<?php

/**
 * InsuranceRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\InsuranceService;
use OpenEMR\RestControllers\RestControllerHelper;

class InsuranceRestController
{
    private $insuranceService;

    public function __construct()
    {
        $this->insuranceService = new InsuranceService();
    }

    public function getAll($pid)
    {
        $serviceResult = $this->insuranceService->getAll($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getOne($pid, $type)
    {
        $serviceResult = $this->insuranceService->getOneByPid($pid, $type);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function put($pid, $type, $data)
    {
        $data["type"] = $type;
        $data["pid"] = $pid;
        $validationResult = $this->insuranceService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->insuranceService->insert($pid, $type, $data);
        return RestControllerHelper::responseHandler($serviceResult, $type, 200);
    }

    public function post($pid, $type, $data)
    {
        $data["type"] = $type;
        $data["pid"] = $pid;
        $validationResult = $this->insuranceService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->insuranceService->insert($pid, $type, $data);
        return RestControllerHelper::responseHandler($serviceResult, $type, 201);
    }
}
