<?php
/**
 * PatientRestController
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

use OpenEMR\Services\PatientService;
use OpenEMR\RestControllers\RestControllerHelper;

class PatientRestController
{
    private $patientService;

    public function __construct($pid)
    {
        $this->patientService = new PatientService();
        $this->patientService->setPid($pid);
    }

    public function post($data)
    {
        $validationResult = $this->patientService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->patientService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $serviceResult), 201);
    }

    public function put($pid, $data)
    {
        $validationResult = $this->patientService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->patientService->update($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $pid), 200);
    }

    public function getOne()
    {
        $serviceResult = $this->patientService->getOne();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll($search)
    {
        $serviceResult = $this->patientService->getAll(array(
            'fname' => $search['fname'],
            'lname' => $search['lname'],
            'dob' => $search['dob']
        ));

        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}