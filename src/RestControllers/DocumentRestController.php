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

use Nyholm\Psr7\Factory\Psr17Factory;
use OpenApi\Attributes as OA;
use OpenEMR\Services\DocumentService;
use OpenEMR\RestControllers\RestControllerHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DocumentRestController
{
    private $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
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
        $serviceResult = $this->documentService->insertAtPath($pid, $path, $fileData, $eid);
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
