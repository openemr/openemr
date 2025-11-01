<?php

/**
 * DocumentRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use Nyholm\Psr7\Factory\Psr17Factory;
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

    public function getAllAtPath($pid, $path)
    {
        $serviceResult = $this->documentService->getAllAtPath($pid, $path);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function postWithPath($pid, $path, $fileData)
    {
        $serviceResult = $this->documentService->insertAtPath($pid, $path, $fileData);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

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
