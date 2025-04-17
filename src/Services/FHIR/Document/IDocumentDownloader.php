<?php

/**
 * IDocumentDownloader Response for handling Document downloads.  A class that implements this interface can be used
 * in the FHIR Document endpoints to handle documents, process any additional security or processing requirements.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Document;

use Psr\Http\Message\ResponseInterface;

interface IDocumentDownloader
{
    /**
     * Given a Document object it will return an HTTP response that either includes the entire document or has opened
     * a stream to the document to be sent back to the HTTP requesting agent.
     * @param \Document $document The document object that is to be downloaded.
     * @return ResponseInterface
     */
    function downloadDocument(\Document $document): ResponseInterface;
}
