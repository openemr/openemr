<?php

/**
 * DocumentRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\PatientService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DocumentRestController
{
    private readonly DocumentService $documentService;
    private readonly PatientService $patientService;

    public function __construct(?DocumentService $documentService = null, ?PatientService $patientService = null)
    {
        $this->documentService = $documentService ?? new DocumentService();
        $this->patientService = $patientService ?? new PatientService();
    }

    /**
     * Every document endpoint is scoped to a patient, so a pid that does not resolve to a patient
     * is a bad request rather than an empty result. Without this check a document can be uploaded
     * against a pid that has no patient, leaving a row that no patient chart will ever surface.
     */
    private function isValidPid(mixed $pid): bool
    {
        if (!is_scalar($pid)) {
            return false;
        }

        return $this->patientService->getUuid((string)$pid) !== false;
    }

    private function invalidPidResponse(): Response
    {
        return RestControllerHelper::responseHandler(
            ['validationErrors' => ['pid' => ['Invalid pid']]],
            null,
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Retrieves all file information of documents from a category for a patient.
     */
    #[OA\Get(
        path: '/api/patient/{pid}/document',
        description: 'Retrieves all file information of documents from a category for a patient',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'path',
                in: 'query',
                description: 'The category of the documents.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'eid',
                in: 'query',
                description: 'The Encounter ID (optional) the document is assigned to',
                required: false,
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
    public function getAllAtPath($pid, $path)
    {
        if (!$this->isValidPid($pid)) {
            return $this->invalidPidResponse();
        }

        $serviceResult = $this->documentService->getAllAtPath($pid, $path);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Submits a new patient document.
     */
    #[OA\Post(
        path: '/api/patient/{pid}/document',
        description: 'Submits a new patient document',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'path',
                in: 'query',
                description: 'The category of the document.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'document',
                            description: 'document',
                            type: 'string',
                            format: 'binary'
                        ),
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
    public function postWithPath($pid, $path, $fileData, $eid)
    {
        if (!$this->isValidPid($pid)) {
            return $this->invalidPidResponse();
        }

        // insertAtPath() reads tmp_name and name straight off the upload, so the upload is
        // checked before it gets that far. PHP populates those two keys even when the upload
        // failed -- a partial transfer carries UPLOAD_ERR_PARTIAL alongside whatever bytes did
        // arrive, and a missing or oversized file leaves tmp_name as an empty string -- so the
        // error code has to be honoured or a truncated file is stored as though it were whole.
        $upload = is_array($fileData) ? $fileData : [];
        $tmpName = $upload['tmp_name'] ?? null;
        $name = $upload['name'] ?? null;
        // a caller that built the array itself rather than handing over a $_FILES entry has no
        // error key, and there is no failed upload to report in that case.
        $uploadError = $upload['error'] ?? UPLOAD_ERR_OK;
        if (
            $uploadError !== UPLOAD_ERR_OK
            || !is_string($tmpName) || $tmpName === ''
            || !is_string($name) || $name === ''
            || !is_file($tmpName)
        ) {
            return RestControllerHelper::responseHandler(
                ['validationErrors' => ['document' => ['A valid document file is required']]],
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        $serviceResult = $this->documentService->insertAtPath(
            $pid,
            $path,
            ['tmp_name' => $tmpName, 'name' => $name],
            $eid
        );
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    /**
     * Downloads a document for a patient.
     */
    #[OA\Get(
        path: '/api/patient/{pid}/document/{did}',
        description: 'Retrieves a document for a patient',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The pid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'did',
                in: 'path',
                description: 'The id for the patient document.',
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
    public function downloadFile($pid, $did)
    {
        if (!$this->isValidPid($pid)) {
            return $this->invalidPidResponse();
        }

        $results = $this->documentService->getFile($pid, $did);

        if (!empty($results)) {
            $response = new BinaryFileResponse($results['file'], Response::HTTP_OK, [], true);
            $response->setContentDisposition('attachment', $results['filename']);
            // we no longer use pre-check and post-check headers as they are not needed and microsoft even discourages
            // their use at this point.
            $response->setCache([
                'must_revalidate' => true
            ]);
            // this used to be Expires: 0 but that is not recommended anymore, we set it to be 1 hour ago so that
            // the browser will not cache the file.
            $response->setExpires(new \DateTimeImmutable("-1 HOUR"));
            return $response;
        } else {
            // TODO: @adunsulag we should return a 404 here if the file does not exist... but prior behavior was to return a 400
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }
    }

    public function setSession(SessionInterface $getSession)
    {
        $this->documentService->setSession($getSession);
    }
}
