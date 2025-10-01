<?php

/**
 * Portal API Routes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\RestControllers\AppointmentRestController;
use OpenEMR\Common\Http\HttpRestRequest;

// Note that the portal (api) route is only for patient role
//  (there is a mechanism in place to ensure only patient role can access the portal (api) route)
return [
    /**
     *  @OA\Get(
     *      path="/portal/patient",
     *      description="Returns the patient.",
     *      tags={"standard-patient"},
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient" => function (HttpRestRequest $request) {
        $return = (new PatientRestController())->getOne($request->getPatientUUIDString());
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/portal/patient/encounter",
     *      description="Returns encounters for the patient.",
     *      tags={"standard-patient"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient/encounter" => function (HttpRestRequest $request) {
        $return = (new EncounterRestController($request->getSession()))->getAll($request->getPatientUUIDString());
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/portal/patient/encounter/{euuid}",
     *      description="Returns a selected encounter by its uuid.",
     *      tags={"standard-patient"},
     *      @OA\Parameter(
     *          name="euuid",
     *          in="path",
     *          description="The uuid for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *          type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient/encounter/:euuid" => function ($euuid, HttpRestRequest $request) {
        $return = (new EncounterRestController($request->getSession()))->getOne($request->getPatientUUIDString(), $euuid);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/portal/patient/appointment",
     *      description="Retrieves all appointments for a patient",
     *      tags={"standard-patient"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient/appointment" => function (HttpRestRequest $request) {
        $return = (new AppointmentRestController())->getAllForPatientByUuid($request->getPatientUUIDString());
        return $return;
    },


    /**
     *  @OA\Get(
     *      path="/portal/patient/appointment/{auuid}",
     *      description="Returns a selected appointment by its uuid.",
     *      tags={"standard-patient"},
     *      @OA\Parameter(
     *          name="auuid",
     *          in="path",
     *          description="The uuid for the appointment.",
     *          required=true,
     *          @OA\Schema(
     *          type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient/appointment/:auuid" => function ($auuid, HttpRestRequest $request) {
        $return = (new AppointmentRestController())->getOneForPatient($auuid, $request->getPatientUUIDString());
        return $return;
    }
];
