<?php

/**
 * FhirDocumentRestController is responsible for downloading documents in the system.  It checks against system access
 * rights for the document as well as whether the document has an expiration date.  If an expired document is found
 * this class cleans it up if it is needed.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use http\Exception\InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\CDADocumentService;
use OpenEMR\Services\FHIR\Document\BaseDocumentDownloader;
use OpenEMR\Services\FHIR\Document\IDocumentDownloader;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\ReferenceSearchField;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class FhirDocumentRestController
{
    /**
     * @var IDocumentDownloader[]  Hash map of mime types to IDocumentDownloader classes
     */
    private $mimeTypeHandlers;

    /**
     * @var BaseDocumentDownloader Default document downloader
     */
    private $defaultMimeTypeHandler;

    public function __construct(HttpRestRequest $request)
    {
        $this->mimeTypeHandlers = [];
        $this->defaultMimeTypeHandler = new BaseDocumentDownloader();
        $this->logger = new SystemLogger();
    }

    /**
     * Given a document and user, attempt to download the document to the calling agent.  Access rights and document
     * expiration are checked against the document.
     * @param $documentId  The document we are requesting to access
     */
    public function downloadDocument($documentId, $patientUuid = null): ResponseInterface
    {
        $document = $this->findDocumentForDocumentId($documentId);
        if (empty($document)) {
            return (new Psr17Factory())->createResponse(StatusCode::NOT_FOUND);
        }

        // run file cleanup requests
        // grab all export db records w/ expired records & delete them

        // return 404 if our document is deleted.
        if ($document->is_deleted()) {
            return (new Psr17Factory())->createResponse(StatusCode::NOT_FOUND);
        }

        // patients need to be able to access their own documents, we expose that here if we have a patientUuid
        if (!empty($patientUuid)) {
            $pid = (new PatientService())->getPidByUuid(UuidRegistry::uuidToBytes($patientUuid));
            // allows for both checking the patient id, and any Information Blocking / Access rules to the document
            if (!$document->can_patient_access($pid)) {
                return (new Psr17Factory())->createResponse(StatusCode::UNAUTHORIZED);
            }
        }

        if (!$document->can_access()) {
            return (new Psr17Factory())->createResponse(StatusCode::UNAUTHORIZED);
        }

        if ($document->has_expired()) {
            // cleanup the document if we haven't already
            try {
                if (!$document->is_deleted()) {
                    $document->process_deleted();
                }
            } catch (\Exception $exception) {
                // we just continue as we still wanto to reject the response
                $this->logger->error(
                    "FhirDocumentRestController->downloadDocument() Failed to delete document with id",
                    ['document' => $documentId, 'username' => $_SESSION['authUser'], 'exception' => $exception->getMessage()]
                );
            }
            // need to return the fact that the document has expired
            return (new Psr17Factory())->createResponse(StatusCode::NOT_FOUND);
        } else {
            // if we have registered mime type handlers we will process them here, otherwise we use the default handler.
            foreach ($this->mimeTypeHandlers as $mimeType => $handler) {
                if ($mimeType === $document->get_mimetype()) {
                    return $handler->downloadDocument($document);
                }
            }
            $this->logger->debug(
                "FhirDocumentRestController->downloadDocument() Sending to default mime type handler",
                ['document' => $documentId, 'username' => $_SESSION['authUser']]
            );
            $response = $this->defaultMimeTypeHandler->downloadDocument($document);
            $this->logger->debug(
                "FhirDocumentRestController->downloadDocument() Response returned",
                ['document' => $documentId, 'username' => $_SESSION['authUser'], 'contentLength' => $response->getHeader("Content-Length")]
            );
            return $response;
        }
    }

    /**
     * Adds a mime type handler that knows how to process and download the given mime type.
     * @param $mimeType
     * @param IDocumentDownloader $handler
     */
    public function addMimeTypeHandler($mimeType, IDocumentDownloader $handler)
    {
        if (!is_string($mimeType)) {
            throw new InvalidArgumentException("invalid mime type");
        }
        $this->mimeTypeHandlers[$mimeType] = $handler;
    }

    private function findDocumentForDocumentId(string $documentId)
    {
        if (Uuid::isValid($documentId)) {
            $document = \Document::getDocumentForUuid($documentId);
        } else {
            // use our integer values
            $document = new \Document($documentId);
        }
        return $document;
    }
}
