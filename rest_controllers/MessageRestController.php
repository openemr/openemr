<?php
/**
 * MessageRestController
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

use OpenEMR\Services\MessageService;
use OpenEMR\RestControllers\RestControllerHelper;

class MessageRestController
{
    private $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
    }

    public function put($pid, $mid, $data)
    {
        $validationResult = $this->messageService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->messageService->update($pid, $mid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("id" => $mid), 200); 
    }

    public function post($pid, $data)
    {
        $validationResult = $this->messageService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) { return $validationHandlerResult; }

        $serviceResult = $this->messageService->insert($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("id" => $serviceResult), 201); 
    }

    public function delete($pid, $mid)
    {
        $serviceResult = $this->messageService->delete($pid, $mid);
        return RestControllerHelper::responseHandler($serviceResult, true, 200);
    }
}