<?php

// Note that the portal (api) route is only for patient role
//  (there is a mechanism in place to ensure only patient role can access the portal (api) route)
return array(
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
        RestConfig::apiLog($return);
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
        $return = (new EncounterRestController())->getAll($request->getPatientUUIDString());
        RestConfig::apiLog($return);
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
        $return = (new EncounterRestController())->getOne($request->getPatientUUIDString(), $euuid);
        RestConfig::apiLog($return);
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
        RestConfig::apiLog($return);
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
        RestConfig::apiLog($return);
        return $return;
    }
);
