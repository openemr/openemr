<?php

/**
 * IFhirExportableResourceService defines the methods a Fhir Resource Service must implement in order to be able to
 * export data for the system.  If a Fhir Resource Service is in the correct namespace and implements this service it
 * will be picked up the by export service locator.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;

interface IFhirExportableResourceService
{
    /**
     * Grabs all the objects in my service that match the criteria specified in the ExportJob.  If a
     * $lastResourceIdExported is provided, The service executes the same data collection query it used previously and
     * startes processing at the resource that is immediately after (ordered by date) the resource that matches the id of
     * $lastResourceIdExported.  This allows processing of the service to be resumed or paused.
     * @param ExportStreamWriter $writer Object that writes out to a stream any object that extend the FhirResource object
     * @param ExportJob $job The export job we are processing the request for.  Holds all of the context information needed for the export service.
     * @throws ExportCannotEncodeException Thrown if the resource cannot be properly converted into the right format (ie JSON).
     * @throws ExportWillShutdownException  Thrown if the export is about to be shutdown and all processing must be halted.
     * @throws ExportException  If there is an error in processing the export
     * @return void
     */
    function export(ExportStreamWriter $writer, ExportJob $job, $lastResourceIdExported = null): void;

    /**
     * Returns whether the service supports the system export operation
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---system-level-export
     * @return bool true if this resource service should be called for a system export operation, false otherwise
     */
    function supportsSystemExport();

    /**
     * Returns whether the service supports the group export operation.
     * Note only resources in the Patient compartment SHOULD be returned unless the resource assists in interpreting
     * patient data (such as Organization or Practitioner)
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---group-of-patients
     * @return bool true if this resource service should be called for a group export operation, false otherwise
     */
    function supportsGroupExport();

    /**
     * Returns whether the service supports the all patient export operation
     * Note only resources in the Patient compartment SHOULD be returned unless the resource assists in interpreting
     * patient data (such as Organization or Practitioner)
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---all-patients
     * @return bool true if this resource service should be called for a patient export operation, false otherwise
     */
    function supportsPatientExport();
}
