<?php

/**
 * MessageRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->messageService->update($pid, $mid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("mid" => $mid), 200);
    }

    public function post($pid, $data)
    {
        $validationResult = $this->messageService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->messageService->insert($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("mid" => $serviceResult), 201);
    }

    public function delete($pid, $mid)
    {
        $serviceResult = $this->messageService->delete($pid, $mid);
        return RestControllerHelper::responseHandler($serviceResult, true, 200);
    }
}
