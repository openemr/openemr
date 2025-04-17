<?php

/**
 * BaseDocumentDownloader implements the IDocumentDownloader interface to download a Document object into an HTTP Response
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Document;

use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseDocumentDownloader implements IDocumentDownloader
{
    /**
     * GMT date format per RFC 2616
     */
    const EXPIRES_HEADER_DATE_TIME_FORMAT = 'D, d M Y H:i:s \G\M\T';

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * BaseDocumentDownloader constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new SystemLogger();
    }

    /**
     * Given a Document object it will return an HTTP response that either includes the entire document or has opened
     * a stream to the document to be sent back to the HTTP requesting agent.
     * @param \Document $document The document object that is to be downloaded.
     * @return ResponseInterface
     */
    public function downloadDocument(\Document $document): ResponseInterface
    {
        if (!empty($document->get_date_expires())) {
            $dateTime = \DateTime::createFromFormat(\Document::EXPIRES_DATE_FORMAT, $document->get_date_expires());
            $expires = $dateTime->format(self::EXPIRES_HEADER_DATE_TIME_FORMAT);
        } else {
            $expires = 0;
        }
        // if we wanted this to be super efficient for document processing we should create a StreamInterface
        // and pass the response body with a Stream, that way we can send bytes to the client as the stream reads them
        // improving memory performance
        // @see Psr\Http\Message\StreamInterface

        $documentData = $document->get_data();
        $psr17 = new Psr17Factory();

        // when creating a stream from a string for some reason we have to rewind to the beginning of the stream
        // which seems very odd.  This behavior was tested on Feb 4th, 2021
        $stream = $psr17->createStream($documentData);
        $stream->rewind();
        $this->logger->debug(
            "BaseDocumentDownloader->downloadDocument() Document data",
            ['id' => $document->get_id(), 'data' => $documentData,
            'is_encrypted' => $document->is_encrypted()]
        );

        return $psr17->createResponse(StatusCode::OK)
            ->withAddedHeader('Content-Description', 'File Transfer')
            ->withAddedHeader('Content-Type', $document->get_mimetype())
            ->withAddedHeader('Content-Disposition', 'attachment; filename=' . $document->get_name())
            ->withAddedHeader('Expires', $expires)
            ->withAddedHeader('Cache-Control', 'must-revalidate; post-check=0; pre-check=0')
            ->withAddedHeader('Pragma', 'public')
            ->withAddedHeader('Content-Length', $document->get_size())
            ->withBody($stream);
    }
}
