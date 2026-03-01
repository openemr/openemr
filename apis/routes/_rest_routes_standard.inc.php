<?php

/**
 * Standard API Routes
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\RestControllers\AllergyIntoleranceRestController;
use OpenEMR\RestControllers\AppointmentRestController;
use OpenEMR\RestControllers\ConditionRestController;
use OpenEMR\RestControllers\DocumentRestController;
use OpenEMR\RestControllers\DrugRestController;
use OpenEMR\RestControllers\EmployerRestController;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\RestControllers\ImmunizationRestController;
use OpenEMR\RestControllers\InsuranceCompanyRestController;
use OpenEMR\RestControllers\InsuranceRestController;
use OpenEMR\RestControllers\ListRestController;
use OpenEMR\RestControllers\MessageRestController;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\RestControllers\PractitionerRestController;
use OpenEMR\RestControllers\PrescriptionRestController;
use OpenEMR\RestControllers\ProcedureRestController;
use OpenEMR\RestControllers\ProductRegistrationRestController;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\RestControllers\TransactionRestController;
use OpenEMR\RestControllers\UserRestController;
use OpenEMR\RestControllers\VersionRestController;
use OpenEMR\Services\Search\SearchQueryConfig;
// TODO: Remove this import when the OpenEMR\RestControllers\Config\RestConfig is no longer needed
use OpenEMR\RestControllers\Config\RestConfig;

return [
    "GET /api/facility" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FacilityRestController())->getAll($request, $_GET);
        return $return;
    },
    "GET /api/facility/:fuuid" => function ($fuuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FacilityRestController())->getOne($fuuid, $request);

        return $return;
    },
    "POST /api/facility" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data, $request);

        return $return;
    },
    "PUT /api/facility/:fuuid" => function ($fuuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data, $request);

        return $return;
    },
    "GET /api/patient" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $config = SearchQueryConfig::createConfigFromQueryParams($request->query->all());
        $return = (new PatientRestController())->getAll($request, $request->query->all(), $config);

        return $return;
    },

    "POST /api/patient" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data, $request);

        return $return;
    },

    "PUT /api/patient/:puuid" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data, $request);

        return $return;
    },
    "GET /api/patient/:puuid" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $return = (new PatientRestController())->getOne($puuid, $request);

        return $return;
    },
    "GET /api/patient/:puuid/encounter" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $return = (new EncounterRestController($request->getSession()))->getAll($puuid);

        return $return;
    },

    "POST /api/patient/:puuid/encounter" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->post($puuid, $data, $request);

        return $return;
    },

    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->put($puuid, $euuid, $data);

        return $return;
    },
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $return = (new EncounterRestController($request->getSession()))->getOne($puuid, $euuid);

        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getSoapNotes($pid, $eid);

        return $return;
    },

    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController($request->getSession()))->postVital($pid, $eid, $data);

        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController($request->getSession()))->putVital($pid, $eid, $vid, $data);

        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getVitals($pid, $eid);

        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getVital($pid, $eid, $vid);

        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getSoapNote($pid, $eid, $sid);

        return $return;
    },

    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->postSoapNote($pid, $eid, $data);

        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->putSoapNote($pid, $eid, $sid, $data);

        return $return;
    },
    "GET /api/practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new PractitionerRestController())->getAll($request, $request->query->all());
        return $return;
    },
    "GET /api/practitioner/:pruuid" => function ($pruuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new PractitionerRestController())->getOne($pruuid, $request);
        return $return;
    },
    "POST /api/practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->post($data, $request);
        return $return;
    },
    "PUT /api/practitioner/:pruuid" => function ($pruuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->patch($pruuid, $data, $request);
        return $return;
    },
    "GET /api/medical_problem" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getAll();

        return $return;
    },
    "GET /api/medical_problem/:muuid" => function ($muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getOne($muuid);

        return $return;
    },
    "GET /api/patient/:puuid/medical_problem" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid]);
        return $return;
    },
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid, 'condition_uuid' => $muuid]);

        return $return;
    },

    "POST /api/patient/:puuid/medical_problem" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->post($puuid, $data);

        return $return;
    },
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->put($puuid, $muuid, $data);

        return $return;
    },
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ConditionRestController())->delete($puuid, $muuid);

        return $return;
    },
    "GET /api/allergy" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll();

        return $return;
    },
    "GET /api/allergy/:auuid" => function ($auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getOne($auuid);

        return $return;
    },
    "GET /api/patient/:puuid/allergy" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['puuid' => $puuid]);

        return $return;
    },
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['puuid' => $puuid, 'lists.id' => $auuid]);

        return $return;
    },

    "POST /api/patient/:puuid/allergy" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->post($puuid, $data);

        return $return;
    },
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);

        return $return;
    },
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->delete($puuid, $auuid);

        return $return;
    },
    "GET /api/patient/:pid/medication" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "medication");

        return $return;
    },

    "POST /api/patient/:pid/medication" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "medication", $data);

        return $return;
    },
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $mid, "medication", $data);

        return $return;
    },
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "medication", $mid);

        return $return;
    },
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $mid, "medication");

        return $return;
    },
    "GET /api/patient/:pid/surgery" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "surgery");

        return $return;
    },
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "surgery", $sid);

        return $return;
    },
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $sid, "surgery");

        return $return;
    },

    "POST /api/patient/:pid/surgery" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "surgery", $data);

        return $return;
    },
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $sid, "surgery", $data);

        return $return;
    },
    "GET /api/patient/:pid/dental_issue" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "dental");

        return $return;
    },
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "dental", $did);

        return $return;
    },
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $did, "dental");

        return $return;
    },

    "POST /api/patient/:pid/dental_issue" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "dental", $data);

        return $return;
    },
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $did, "dental", $data);

        return $return;
    },
    "GET /api/patient/:pid/appointment" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getAllForPatient($pid);

        return $return;
    },
    "POST /api/patient/:pid/appointment" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AppointmentRestController())->post($pid, $data);

        return $return;
    },
    "GET /api/appointment" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getAll();

        return $return;
    },
    "GET /api/appointment/:eid" => function ($eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);

        return $return;
    },
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->delete($eid);

        return $return;
    },
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);

        return $return;
    },

    "GET /api/list/:list_name" => function ($list_name, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "lists", "default");
        $return = (new ListRestController())->getOptions($list_name);

        return $return;
    },
    "GET /api/user" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new UserRestController())->getAll($_GET);

        return $return;
    },
    "GET /api/user/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new UserRestController())->getOne($uuid);

        return $return;
    },
    "GET /api/version" => function (HttpRestRequest $request) {
        $return = (new VersionRestController())->getOne();

        return $return;
    },
    "GET /api/product" => function (HttpRestRequest $request) {
        $return = (new ProductRegistrationRestController())->getOne();

        return $return;
    },
    "GET /api/insurance_company" => function (HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getAll();

        return $return;
    },
    "GET /api/insurance_company/:iid" => function ($iid, HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getOne($iid);

        return $return;
    },
    "GET /api/insurance_type" => function (HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getInsuranceTypes();

        return $return;
    },

    "POST /api/insurance_company" => function (HttpRestRequest $request) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->post($data);

        return $return;
    },
    "PUT /api/insurance_company/:iid" => function ($iid, HttpRestRequest $request) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->put($iid, $data);

        return $return;
    },
    "POST /api/patient/:pid/document" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "docs", ['write','addonly']);
        $controller = new DocumentRestController();
        $controller->setSession($request->getSession());
        $return = $controller->postWithPath($pid, $_GET['path'], $_FILES['document'], $_GET['eid']);

        return $return;
    },
    "GET /api/patient/:pid/document" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "docs");
        $return = (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);

        return $return;
    },
    "GET /api/patient/:pid/document/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "docs");
        $return = (new DocumentRestController())->downloadFile($pid, $did);

        return $return;
    },
    "GET /api/patient/:puuid/employer" => function ($puuid, HttpRestRequest $request) {
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            $errorReturn = [
                'validationErrors' => [ 'uuid' => ['Invalid UUID format']]
            ];
            return RestControllerHelper::responseHandler($errorReturn, null, 400);
        }

        $searchParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // For patient portal users, force the UUID to match the authenticated patient.
            $searchParams['puuid'] = $request->getPatientUUIDString();
        } else {
            // For staff users, verify they have permission to view demographic data.
            RestConfig::request_authorization_check($request, "patients", "demo");
            $searchParams['puuid'] = $puuid;
        }

        // Try to get the data. The service layer will handle non-existent UUIDs.
        $return = (new EmployerRestController())->getAll($searchParams);

        return $return;
    },
    "GET /api/patient/:puuid/insurance" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $searchParams = $request->getQueryParams();
        $searchParams['puuid'] = $puuid;
        if ($request->isPatientRequest()) {
            $searchParams['puuid'] = $request->getPatientUUIDString();
        }
        $return = (new InsuranceRestController())->getAll($searchParams);

        return $return;
    },
    'GET /api/patient/:puuid/insurance/$swap-insurance' => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo", 'write');
        if ($request->isPatientRequest()) {
            $puuid = $request->getPatientUUIDString();
        }
        $type = $request->getQueryParam('type');
        $insuranceUuid = $request->getQueryParam('uuid');

        $return = (new InsuranceRestController())->operationSwapInsurance($puuid, $type, $insuranceUuid);

        return $return;
    },
    "GET /api/patient/:puuid/insurance/:uuid" => function ($puuid, $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        if ($request->isPatientRequest()) {
            $puuid = $request->getPatientUUIDString();
        }
        $return = (new InsuranceRestController())->getOne($uuid, $puuid);

        return $return;
    },

    "PUT /api/patient/:puuid/insurance/:insuranceUuid" => function ($puuid, $insuranceUuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo", 'write');
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->put($puuid, $insuranceUuid, $data);

        return $return;
    },
    "POST /api/patient/:puuid/insurance" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo", ['write','addonly']);
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->post($puuid, $data);

        return $return;
    },
    "POST /api/patient/:pid/message" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->post($pid, $data);

        return $return;
    },
    "GET /api/patient/:pid/transaction" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $cont = new TransactionRestController();
        $return = (new TransactionRestController())->GetPatientTransactions($pid);

        return $return;
    },

    "POST /api/patient/:pid/transaction" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new TransactionRestController())->CreateTransaction($pid, $data);

        return $return;
    },
    "PUT /api/transaction/:tid" => function ($tid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new TransactionRestController())->UpdateTransaction($tid, $data);

        return $return;
    },
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->put($pid, $mid, $data);

        return $return;
    },
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $return = (new MessageRestController())->delete($pid, $mid);

        return $return;
    },
    "GET /api/immunization" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ImmunizationRestController())->getAll($_GET);

        return $return;
    },
    "GET /api/immunization/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ImmunizationRestController())->getOne($uuid);

        return $return;
    },
    "GET /api/procedure" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ProcedureRestController())->getAll();

        return $return;
    },
    "GET /api/procedure/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ProcedureRestController())->getOne($uuid);

        return $return;
    },
    "GET /api/drug" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new DrugRestController())->getAll();

        return $return;
    },
    "GET /api/drug/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new DrugRestController())->getOne($uuid);

        return $return;
    },
    "GET /api/prescription" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new PrescriptionRestController())->getAll();

        return $return;
    },
    "GET /api/prescription/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new PrescriptionRestController())->getOne($uuid);

        return $return;
    }
];
