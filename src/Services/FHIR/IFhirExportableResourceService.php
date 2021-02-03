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

interface IFhirExportableResourceService
{
    /**
     * Right now this this just a stub and will be fleshed out when we define more the algorithm for exporting resources
     * @param ExportJob $job
     * @return mixed
     */
    function export(ExportJob $job);
}
