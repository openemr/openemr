<?php

/**
 * ListRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\ListService;
use OpenEMR\RestControllers\RestControllerHelper;

class ListRestController
{
    private $listService;

    public function __construct()
    {
        $this->listService = new ListService();
    }

    public function getAll($pid, $list_type)
    {
        $serviceResult = $this->listService->getAll($pid, $list_type);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getOne($pid, $list_type, $list_id)
    {
        $serviceResult = $this->listService->getOne($pid, $list_type, $list_id);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getOptions($list_name)
    {
        $serviceResult = $this->listService->getOptionsByListName($list_name);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($pid, $list_type, $data)
    {
        $data['type'] = $list_type;
        $data['pid'] = $pid;

        $validationResult = $this->listService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->listService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $serviceResult), 201);
    }

    public function put($pid, $list_id, $list_type, $data)
    {
        $data['type'] = $list_type;
        $data['pid'] = $pid;
        $data['id'] = $list_id;

        $validationResult = $this->listService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }


        $serviceResult = $this->listService->update($data);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $list_id), 200);
    }

    public function delete($pid, $list_id, $list_type)
    {
        $serviceResult = $this->listService->delete($pid, $list_id, $list_type);
        return RestControllerHelper::responseHandler($serviceResult, true, 200);
    }
}
