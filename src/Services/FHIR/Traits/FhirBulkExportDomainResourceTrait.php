<?php
/**
 * FhirBulkExportDomainResourceTrait implements a simple export method that can be incorporated into classes to quickly
 * allow FHIR bulk export of resources.  Advanced searching or filtering on the exported resources is not supported and
 * should be implemented directly in the class rather than using this trait.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;


use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\IResourceReadableService;

trait FhirBulkExportDomainResourceTrait
{
    /**
     * Grabs all the objects in my service that match the criteria specified in the ExportJob.  If a
     * $lastResourceIdExported is provided, The service executes the same data collection query it used previously and
     * startes processing at the resource that is immediately after (ordered by date) the resource that matches the id of
     * $lastResourceIdExported.  This allows processing of the service to be resumed or paused.
     * @param ExportStreamWriter $writer Object that writes out to a stream any object that extend the FhirResource object
     * @param ExportJob $job The export job we are processing the request for.  Holds all of the context information needed for the export service.
     * @return void
     * @throws ExportWillShutdownException  Thrown if the export is about to be shutdown and all processing must be halted.
     * @throws ExportException  If there is an error in processing the export
     * @throws ExportCannotEncodeException Thrown if the resource cannot be properly converted into the right format (ie JSON).
     */
    public function export(ExportStreamWriter $writer, ExportJob $job, $lastResourceIdExported = null): void
    {
        if (!($this instanceof IResourceReadableService))
        {
            // we need to ensure we only get called in a method that implements the getAll method.
            throw new \BadMethodCallException("Trait can only be used in classes that implement the " . IResourceReadableService::class . " interface");
        }
        $searchParams = [];
        $processingResult = $this->getAll($searchParams);
        $records = $processingResult->getData();
        foreach ($records as $record) {
            if (!($record instanceof FHIRDomainResource)) {
                throw new ExportException(self::class . " returned records that are not a valid fhir resource type for this class", 0, $lastResourceIdExported);
            }
            $writer->append($record);
            $lastResourceIdExported = $record->getId();
        }
    }
}