<?php

/**
 * AppointmentRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
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

    /**
     * Fetches a single appointment resource by eid.
     * @param $eid - The appointment event id.
     */
    #[OA\Get(
        path: '/api/appointment/{eid}',
        description: 'Retrieves an appointment',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'eid',
                in: 'path',
                description: 'The eid for the appointment.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne($eid)
    {
        $serviceResult = $this->appointmentService->getAppointment($eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Fetches a single appointment for a patient.
     * @param $auuid - The appointment uuid.
     * @param $patientUuid - The patient uuid.
     */
    #[OA\Get(
        path: '/api/patient/{pid}/appointment/{eid}',
        description: 'Retrieves a appointment for a patient',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'eid',
                in: 'path',
                description: 'The eid for the appointment.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    #[OA\Get(
        path: '/portal/patient/appointment/{auuid}',
        description: 'Returns a selected appointment by its uuid.',
        tags: ['standard-patient'],
        parameters: [
            new OA\Parameter(
                name: 'auuid',
                in: 'path',
                description: 'The uuid for the appointment.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOneForPatient($auuid, $patientUuid)
    {
        $serviceResult = $this->appointmentService->search(['puuid' => $patientUuid, 'pc_uuid' => $auuid]);
        $data = ProcessingResult::extractDataArray($serviceResult);
        return RestControllerHelper::responseHandler($data[0] ?? [], null, 200);
    }

    /**
     * Retrieves all appointments.
     */
    #[OA\Get(
        path: '/api/appointment',
        description: 'Retrieves all appointments',
        tags: ['standard'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll()
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient(null);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: '/portal/patient/appointment',
        description: 'Retrieves all appointments for a patient',
        tags: ['standard-patient'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAllForPatientByUuid($puuid)
    {
        $patientService = new PatientService();
        $result = ProcessingResult::extractDataArray($patientService->getOne($puuid));
        $serviceResult = !empty($result) ? $this->appointmentService->getAppointmentsForPatient($result[0]['pid']) : [];

        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Retrieves all appointments for a patient.
     * @param $pid - The patient id.
     */
    #[OA\Get(
        path: '/api/patient/{pid}/appointment',
        description: 'Retrieves all appointments for a patient',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAllForPatient($pid)
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Submits a new appointment for a patient.
     * @param $pid - The patient id.
     * @param $data - The appointment data.
     */
    #[OA\Post(
        path: '/api/patient/{pid}/appointment',
        description: 'Submits a new appointment',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['pc_catid', 'pc_title', 'pc_duration', 'pc_hometext', 'pc_apptstatus', 'pc_eventDate', 'pc_startTime', 'pc_facility', 'pc_billing_location'],
                    properties: [
                        new OA\Property(property: 'pc_catid', description: 'The category of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_title', description: 'The title of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_duration', description: 'The duration of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_hometext', description: 'Comments for the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_apptstatus', description: 'use an option from resource=/api/list/apptstat', type: 'string'),
                        new OA\Property(property: 'pc_eventDate', description: 'The date of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_startTime', description: 'The time of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_facility', description: 'The facility id of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_billing_location', description: 'The billinag location id of the appointment.', type: 'string'),
                        new OA\Property(property: 'pc_aid', description: 'The provider id for the appointment.', type: 'string'),
                    ],
                    example: [
                        'pc_catid' => '5',
                        'pc_title' => 'Office Visit',
                        'pc_duration' => '900',
                        'pc_hometext' => 'Test',
                        'pc_apptstatus' => '-',
                        'pc_eventDate' => '2018-10-19',
                        'pc_startTime' => '09:00',
                        'pc_facility' => '9',
                        'pc_billing_location' => '10',
                        'pc_aid' => '1',
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post($pid, $data)
    {
        $data['pid'] = $pid;
        $validationResult = $this->appointmentService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->appointmentService->insert($pid, $data);
        return RestControllerHelper::responseHandler(["id" => $serviceResult], null, 200);
    }

    /**
     * Deletes an appointment.
     * @param $eid - The appointment event id.
     */
    #[OA\Delete(
        path: '/api/patient/{pid}/appointment/{eid}',
        description: 'Delete a appointment',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'eid',
                in: 'path',
                description: 'The eid for the appointment.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function delete($eid)
    {
        try {
            $this->appointmentService->deleteAppointmentRecord($eid);
            $serviceResult = ['message' => 'record deleted'];
        } catch (\Throwable $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'eid' => $eid]);
            return RestControllerHelper::responseHandler(['message' => 'Failed to delete appointment'], null, 500);
        }
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
