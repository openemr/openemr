<?php
/**
 * PatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->patientService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $serviceResult), 201);
    }

    public function put($pid, $data)
    {
        $validationResult = $this->patientService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

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
