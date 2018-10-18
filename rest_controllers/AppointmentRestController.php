<?php
/**
 * AppointmentRestController
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

use OpenEMR\Services\AppointmentService;
use OpenEMR\RestControllers\RestControllerHelper;

class AppointmentRestController
{
    private $appointmentService;

    public function __construct()
    {
        $this->appointmentService = new AppointmentService();
    }

    public function getOne($eid)
    {
        $serviceResult = $this->appointmentService->getAppointment($eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient(null);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAllForPatient($pid)
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($pid, $data)
    {
        $serviceResult = $this->appointmentService->insert($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}