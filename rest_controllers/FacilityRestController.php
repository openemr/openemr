<?php
/**
 * FacilityRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->facilityService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array('fid' => $serviceResult), 201);
    }

    public function put($data)
    {
        $validationResult = $this->facilityService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->facilityService->update($data);
        return RestControllerHelper::responseHandler($serviceResult, array('fid' => $data['fid']), 200);
    }
}
