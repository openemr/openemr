<?php
/**
 * FacilityRestController
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

use OpenEMR\Services\FacilityService;
use OpenEMR\RestControllers\RestControllerHelper;

class FacilityRestController
{
    private $facilityService;

    public function __construct()
    {
        $this->facilityService = new FacilityService();
    }

    public function getOne($id)
    {
        $serviceResult = $this->facilityService->getById($id);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->facilityService->getAll();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($data)
    {
        $validationResult = $this->facilityService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->facilityService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $serviceResult), 201);
    }

    public function put($data)
    {
        $validationResult = $this->facilityService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->facilityService->update($data);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $data['fid']), 200);
    }
}