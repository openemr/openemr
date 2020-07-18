<?php
/**
 * CodesRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Alberto Moliner <amolicas79@gmail.com>
 * @copyright Copyright (c) 2020 Alberto Moliner <amolicas79@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\CodesService;

class CodesRestController
{
    private $codesService;

    public function __construct()
    {
        $this->codesService = new CodesService();
    }

    public function post($data)
    {
        $validationResult = $this->codesService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->codesService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array("cid" => $serviceResult), 201);
    }

    public function put($cid, $data)
    {
        $validationResult = $this->codesService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->codesService->update($cid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("cid" => $cid), 200);
    }

    public function getOne($cid)
    {
        $serviceResult = $this->codesService->getOne($cid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->codesService->getAll();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
