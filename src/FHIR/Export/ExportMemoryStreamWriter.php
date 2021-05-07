<?php

/**
 * ExportMemoryStreamWriter that writes the export data to a memory stream
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

class ExportMemoryStreamWriter extends ExportStreamWriter
{
    /**
     * The memory stream that we are writing to
     * @var bool|resource
     */
    private $memoryStream;

    /**
     * ExportMemoryStreamWriter creates an ExportStreamWriter and sets our stream to be a memory stream that we can
     * write to.
     * @param \DateTime $shutdownTime  The datetime that this stream should abort any writing to the stream.
     * @throws ExportException Thrown if there is an error in opening the memory stream
     */
    public function __construct(\DateTime $shutdownTime)
    {
        $this->memoryStream = fopen('php://memory', 'rw');
        if ($this->memoryStream === false) {
            throw new ExportException("Failed to open memory stream");
        }

        parent::__construct($this->memoryStream, $shutdownTime);
    }
}
