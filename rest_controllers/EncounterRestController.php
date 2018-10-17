<?php
/**
 * EncounterRestController
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

use OpenEMR\Services\EncounterService;
use OpenEMR\RestControllers\RestControllerHelper;

class EncounterRestController
{
    private $encounterService;

    public function __construct()
    {
        $this->encounterService = new EncounterService();
    }

    public function getOne($pid, $eid)
    {
        $serviceResult = $this->encounterService->getEncounterForPatient($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll($pid)
    {
        $serviceResult = $this->encounterService->getEncountersForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getSoapNotes($pid, $eid)
    {
        $serviceResult = $this->encounterService->getSoapNotes($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getSoapNote($pid, $eid, $sid)
    {
        $serviceResult = $this->encounterService->getSoapNote($pid, $eid, $sid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function postSoapNote($pid, $eid, $data)
    {
        $serviceResult = $this->encounterService->insertSoapNote($pid, $eid, $data);
        return RestControllerHelper::responseHandler(
            $serviceResult,
            array(
                'sid' => $serviceResult[0],
                'fid' => $serviceResult[1]
            ),
            201);
    }

    public function putSoapNote($pid, $eid, $sid, $data)
    {
        $serviceResult = $this->encounterService->updateSoapNote($pid, $eid, $sid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array('sid' => $sid), 200);
    }
}