<?php

/**
 * AppointmentRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\PatientService;
use OpenEMR\Validators\ProcessingResult;

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

    public function getOneForPatient($auuid, $patientUuid)
    {
        $serviceResult = $this->appointmentService->search(['puuid' => $patientUuid, 'pc_uuid' => $auuid]);
        $data = ProcessingResult::extractDataArray($serviceResult);
        return RestControllerHelper::responseHandler($data[0] ?? [], null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient(null);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAllForPatientByUuid($puuid)
    {
        $patientService = new PatientService();
        $result = ProcessingResult::extractDataArray($patientService->getOne($puuid));
        if (!empty($result)) {
            $serviceResult = $this->appointmentService->getAppointmentsForPatient($result[0]['pid']);
        } else {
            $serviceResult = [];
        }

        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAllForPatient($pid)
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($pid, $data)
    {
        $data['pid'] = $pid;
        $validationResult = $this->appointmentService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->appointmentService->insert($pid, $data);
        return RestControllerHelper::responseHandler(array("id" => $serviceResult), null, 200);
    }

    public function delete($eid)
    {
        try {
            $this->appointmentService->deleteAppointmentRecord($eid);
            $serviceResult = ['message' => 'record deleted'];
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'eid' => $eid]);
            return RestControllerHelper::responseHandler(['message' => 'Failed to delete appointment'], null, 500);
        }
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
