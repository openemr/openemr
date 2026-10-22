<?php

/**
 * REST routes for the FHIR Task resource: read, search, create and update,
 * through the generic FHIR controller, with their API documentation. Responses
 * carry the task's version as an ETag, and an update may send it back as
 * If-Match so that it fails (412) if someone changed the task since.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\FHIR\UtilsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirTaskRestController
{
    private readonly FhirTaskService $service;

    public function __construct(private readonly HttpRestRequest $request, private readonly OEGlobalsBag $globalsBag)
    {
        $this->service = new FhirTaskService();
    }

    private function controller(): FhirGenericRestController
    {
        $controller = new FhirGenericRestController($this->request, $this->service, $this->globalsBag);
        $controller->setExpectedResourceType('Task');
        $controller->addAclRestrictions('patients', 'med');
        return $controller;
    }

    /**
     * The version in an If-Match header (W/"3" or "3"), null when there is none or it is
     * "*", false when it is something else.
     */
    public static function ifMatchVersion(?string $header): int|false|null
    {
        if ($header === null || trim($header) === '' || trim($header) === '*') {
            return null;
        }
        return preg_match('/^\s*(?:W\/)?"(\d+)"\s*$/', $header, $m) === 1 ? (int)$m[1] : false;
    }

    /**
     * The task's version, from the resource in a response body, as its ETag. A client can read
     * the same version from meta.versionId: OpenEMR's Docker image removes ETag headers
     * ("Header unset ETag" in its Apache configuration).
     */
    public static function withETag(Response $response): Response
    {
        $body = json_decode((string)$response->getContent(), true);
        if (!is_array($body) || ($body['resourceType'] ?? null) !== 'Task') {
            return $response;
        }
        $meta = $body['meta'] ?? null;
        $version = is_array($meta) ? ($meta['versionId'] ?? null) : null;
        if (is_string($version) && $version !== '') {
            $response->headers->set('ETag', 'W/"' . $version . '"');
        }
        return $response;
    }

    private static function outcome(string $code, string $message, int $status): Response
    {
        return RestControllerHelper::responseHandler(UtilsService::createOperationOutcomeResource('error', $code, $message), null, $status);
    }

    #[OA\Get(
        path: '/fhir/Task/{uuid}',
        description: 'Returns a single Task resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Task resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'id' => '9f1c2a5e-3b7d-4c8e-a1f0-2d6b8e4c7a19',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2026-10-15T14:00:00+00:00',
                            ],
                            'resourceType' => 'Task',
                            'status' => 'requested',
                            'intent' => 'order',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/CodeSystem/task-code',
                                        'code' => 'fulfill',
                                        'display' => 'fulfill',
                                    ],
                                ],
                            ],
                            'focus' => [
                                'reference' => 'ServiceRequest/95e9d3fb-fe7b-448a-aa60-d40b11b486a5',
                                'type' => 'ServiceRequest',
                            ],
                            'for' => [
                                'reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf',
                                'type' => 'Patient',
                            ],
                            'authoredOn' => '2026-10-15T14:00:00+00:00',
                            'lastModified' => '2026-10-15T14:00:00+00:00',
                            'requester' => [
                                'reference' => 'Practitioner/9b6a5d3f-2c1e-4f8a-b7d0-1e3c5a7f9b2d',
                                'type' => 'Practitioner',
                            ],
                            'owner' => [
                                'reference' => 'Organization/95f0a1c2-7d3e-4b5f-8a9c-0e1d2f3a4b5c',
                                'type' => 'Organization',
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $uuid): Response
    {
        return self::withETag($this->controller()->getOne($uuid));
    }

    #[OA\Get(
        path: '/fhir/Task',
        description: 'Returns a list of Task resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the Task resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: '_lastUpdated',
                in: 'query',
                description: 'Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'patient',
                in: 'query',
                description: 'The uuid for the patient the task is for.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'The status of the Task resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'code',
                in: 'query',
                description: 'The code of the Task resource, for example fulfill (http://hl7.org/fhir/CodeSystem/task-code), complete-questionnaire (http://hl7.org/fhir/uv/sdc/CodeSystem/temp), or make-contact, review-material and general-information (http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes).',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'focus',
                in: 'query',
                description: 'The resource the task acts on, such as ServiceRequest/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'owner',
                in: 'query',
                description: 'Who is responsible for the task, such as Organization/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'requester',
                in: 'query',
                description: 'Who asked for the task, such as Practitioner/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'part-of',
                in: 'query',
                description: 'The parent task, such as Task/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'authored-on',
                in: 'query',
                description: 'When the task was created.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'meta' => [
                                'lastUpdated' => '2026-10-15T14:00:00',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/Task',
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(): JsonResponse|Response
    {
        return $this->controller()->getAll();
    }

    #[OA\Post(
        path: '/fhir/Task',
        description: "Creates a Task. The server sets id, meta and lastModified, and authoredOn when it is left out. Task.for must name the patient; code is from http://hl7.org/fhir/CodeSystem/task-code, or is one SDOH Clinical Care's TaskForPatient uses (complete-questionnaire, make-contact, review-material, general-information); focus may name a ServiceRequest or Questionnaire; owner an Organization, Practitioner, PractitionerRole, the task's Patient or a RelatedPerson of theirs, and requester an Organization, Practitioner, PractitionerRole or Patient; partOf one task for the same patient. Each input is a canonical, url, string, markdown, CodeableConcept, or a reference to a Questionnaire, DocumentReference, Organization, Practitioner or PractitionerRole; each output a string, markdown, CodeableConcept, or a reference to a QuestionnaireResponse, Observation, Procedure or DocumentReference. References into the patient's record must be the task's patient's.",
        tags: ['fhir'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/fhir+json',
                schema: new OA\Schema(
                    example: [
                        'resourceType' => 'Task',
                        'status' => 'requested',
                        'intent' => 'order',
                        'code' => ['coding' => [['system' => 'http://hl7.org/fhir/CodeSystem/task-code', 'code' => 'fulfill']]],
                        'for' => ['reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf'],
                        'focus' => ['reference' => 'ServiceRequest/95e9d3fb-fe7b-448a-aa60-d40b11b486a5'],
                        'owner' => ['reference' => 'Organization/95f0a1c2-7d3e-4b5f-8a9c-0e1d2f3a4b5c'],
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: '201', description: 'The Task as saved, with its version as an ETag.'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(): Response
    {
        $data = RestControllerHelper::parseJsonRequestBody($this->request, true);
        if ($data instanceof Response) {
            return $data;
        }
        return self::withETag($this->controller()->post($data));
    }

    #[OA\Put(
        path: '/fhir/Task/{uuid}',
        description: "Updates a Task. Task.for can't change, and the inputs and outputs in the body replace the task's. A task that is completed, cancelled, failed or rejected can only become entered-in-error, and an entered-in-error task can't change status. Send the version from the ETag as If-Match to have the update refused (412) if the task changed since you read it.",
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', description: 'The uuid for the Task resource.', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'If-Match', in: 'header', description: 'The version the task must still have, as W/"{versionId}".', required: false, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/fhir+json',
                schema: new OA\Schema(
                    example: [
                        'resourceType' => 'Task',
                        'id' => '9f1c2a5e-3b7d-4c8e-a1f0-2d6b8e4c7a19',
                        'status' => 'completed',
                        'intent' => 'order',
                        'code' => ['coding' => [['system' => 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp', 'code' => 'complete-questionnaire']]],
                        'for' => ['reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf'],
                        'focus' => ['reference' => 'Questionnaire/96a1b2c3-d4e5-4f60-8a7b-9c0d1e2f3a4b'],
                        'owner' => ['reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf'],
                        'input' => [[
                            'type' => ['coding' => [['system' => 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp', 'code' => 'questionnaire']]],
                            'valueCanonical' => 'https://localhost:9300/apis/default/fhir/Questionnaire/96a1b2c3-d4e5-4f60-8a7b-9c0d1e2f3a4b',
                        ]],
                        'output' => [[
                            'type' => ['coding' => [['system' => 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp', 'code' => 'questionnaire-response']]],
                            'valueReference' => ['reference' => 'QuestionnaireResponse/96b2c3d4-e5f6-4a71-9b8c-0d1e2f3a4b5c'],
                        ]],
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: '200', description: 'The Task as saved, with its new version as an ETag.'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
            new OA\Response(response: '412', description: 'If-Match named a version the task no longer has.'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function put(string $uuid): Response
    {
        // A patient token is refused by the generic controller (403) before anything is read.
        if (!$this->request->isPatientRequest()) {
            RestConfig::request_authorization_check($this->request, 'patients', 'med');
            $expected = self::ifMatchVersion($this->request->headers->get('If-Match'));
            if ($expected === false) {
                return self::outcome('invalid', 'If-Match must be a version as W/"{versionId}"', 400);
            }
            // The service compares it with the stored version, after the compartment check and its
            // own checks of the body, so a version is only ever told to a caller that may write the task.
            $this->service->expectVersion($expected);
        }
        $data = RestControllerHelper::parseJsonRequestBody($this->request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $response = $this->controller()->put($uuid, $data);
        if ($this->service->hadVersionConflict()) {
            return self::outcome('conflict', 'The task changed since it was read: read it again, then update it with If-Match set to its new version', 412);
        }
        return self::withETag($response);
    }
}
