<?php
/**
 * InsuranceRestController
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
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
        $serviceResult = $this->insuranceService->getOne($pid, $type);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function put($pid, $type, $data)
    {
        $data["type"] = $type;
        $data["pid"] = $pid;
        $validationResult = $this->insuranceService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->insuranceService->insert($pid, $type, $data);
        return RestControllerHelper::responseHandler($serviceResult, $type, 200); 
    }

    public function post($pid, $type, $data)
    {
        $data["type"] = $type;
        $data["pid"] = $pid;
        $validationResult = $this->insuranceService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->insuranceService->insert($pid, $type, $data);
        return RestControllerHelper::responseHandler($serviceResult, $type, 201); 
    }
}