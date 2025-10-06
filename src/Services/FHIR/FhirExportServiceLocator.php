<?php

/**
 * FhirExportServiceLocator locates all of the Fhir Resource Services that support exporting data in a server export
 * operation.  This makes it possible for any FHIR resource to be exportable by implementing the appropriate interfaces
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;

class FhirExportServiceLocator
{
    private FhirServiceLocator $innerLocator;
    /**
     * FhirExportServiceLocator constructor.
     */
    public function __construct(FhirServiceLocator $serviceLocator)
    {
        $this->innerLocator = $serviceLocator;
    }

    /**
     * Searches through our FHIR capability statement for REST resources that implement the FHIR exportable resource
     * interfaces.  It returns a hashmap of resourceName:string => service:IFhirExportableResourceService.
     * @return IFhirExportableResourceService[]
     */
    public function findExportServices()
    {
        return $this->innerLocator->findServices(IFhirExportableResourceService::class);
    }
}
