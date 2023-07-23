<?php

/**
 * ExportStreamWriter decorates a php stream and converts FHIRResource objects into the ndjson format and pushes it out
 * to the provided stream.  The writer will abort if the current system time exceeds the shutdown time specified for
 * the writer.  This allows the script to be processed in an asynchronous fashion.  The Writer tracks the last processed
 * fhir resource which can be used by callers to resume or retry a resource in the stream.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRResource;

class ExportStreamWriter
{
    /**
     * @var bool true if we have written at least one byte of data to it, false otherwise
     */
    private $hasWrittenBytes = false;

    /**
     * @var resource
     */
    private $stream;

    /**
     * The last resource identifier that was processed by this export writer.  This allows callers to resume / retry
     * @var string
     */
    private $lastProcessedId;

    /**
     * @var \DateTime
     */
    private $shutdownTime;

    /**
     * @var int The number of records / lines written
     */
    private $recordsWritten;

    public function __construct($stream, \DateTime $shutdownTime)
    {
        $this->stream = $stream;
        $this->shutdownTime = $shutdownTime->getTimestamp();
        $this->recordsWritten = 0;
    }

    /**
     * Takes a FHIR resource and encodes it into the ndjson format expected for the FHIR $export operation.  Will
     * continue allow appending of resources until the shutdown time for the export has been reached
     * @param FHIRResource $resource the resource we are exporting
     * @throws ExportCannotEncodeException Thrown if the resource cannot be converted into the ndjson format
     * @throws ExportWillShutdownException Thrown if the maximum export time has reached or the writer detects the process is about to end
     * @throws ExportException Thrown if any other error in attempting to export the data occurs
     */
    public function append(FHIRResource $resource)
    {
        if ($this->willShutdown()) {
            throw new ExportWillShutdownException("Export time has exceeded shutdown limit", 0, $this->lastProcessedId);
        }

        try {
            $data = json_encode($resource, JSON_THROW_ON_ERROR);

            // need to make sure we don't have a newline on the last record
            if ($this->hasWrittenBytes) {
                fputs($this->stream, "\n");
                fputs($this->stream, $data);
            } else {
                fputs($this->stream, $data);
                $this->hasWrittenBytes = true;
            }


            $this->incrementRecordCount();
            $this->lastProcessedId = $resource->getId();
            if ($this->willShutdown()) {
                (new SystemLogger())->debug(
                    "ExportStreamWriter->append() reached shutdown time limit for export",
                    ['lastProcessedId' => $this->lastProcessedId, 'resource' => $resource->get_fhirElementName()]
                );

                throw new ExportWillShutdownException("Export time has exceeded shutdown limit", 0, $this->lastProcessedId);
            }
        } catch (\JsonException $exception) {
            throw new ExportCannotEncodeException("Failed to encode resource for export", 0, $this->lastProcessedId, $exception);
        } catch (\Exception $exception) {
            throw new ExportException("Unknown error in writing to stream", 0, $this->lastProcessedId, $exception);
        }
    }

    /**
     * Flush any pending / buffered data out to the stream
     */
    public function flush()
    {
        fflush($this->stream);
    }

    /**
     * Retrieves all of the contents of the stream
     * @return string The contents contained in the stream
     * @throws ExportException Thrown if the stream contents cannot be read.
     */
    public function getContents()
    {
        $this->flush();

        $res = rewind($this->stream);
        if ($res === false) {
            throw new ExportException("Failed to get stream contents");
        }
        $result = stream_get_contents($this->stream);
        if ($result === false) {
            throw new ExportException("Failed to get stream contents");
        }
        return $result;
    }

    /**
     * Closes the stream.
     * @return bool true if the stream closed, false otherwise
     */
    public function close()
    {
        // do we want to error out if the stream can't be closed?
        $closed = fclose($this->stream);
        return $closed;
    }

    /**
     * Checks if the max execution time for this writer has been reached and the writer will shut down.
     * @return bool
     */
    public function willShutdown()
    {
        // TODO: we could register a shutdown function and check against that here if we wanted to truly ensure a
        // proper stream writing.
        $diff = $this->shutdownTime - time();
        return $diff <= 0;
    }

    /**
     * Retrieves the number of FHIR resource records that have been exported by this writer.
     * @return int
     */
    public function getRecordsWritten()
    {
        return $this->recordsWritten;
    }

    /**
     * Increments the number of records that have been written by this exporter
     */
    protected function incrementRecordCount()
    {
        $this->recordsWritten++;
    }
}
