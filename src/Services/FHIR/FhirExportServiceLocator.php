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


use OpenEMR\RestControllers\RestControllerHelper;

class FhirExportServiceLocator
{
    /**
     * @var $restConfig
     */
    private $restConfig;

    /**
     * FhirExportServiceLocator constructor.
     * @param \RestConfig $restConfig
     */
    public function __construct($restConfig)
    {
        $this->restConfig = $restConfig;
    }

    /**
     * Searches through our FHIR capability statement for REST resources that implement the FHIR exportable resource
     * interfaces.  It returns a hashmap of resourceName:string => service:IFhirExportableResourceService.
     * @return IFhirExportableResourceService[]
     */
    public function findExportServices() {
        $resourceRegistry = [];
        $restHelper = new RestControllerHelper();
        $restConfig = $this->restConfig;
        $restCapability = $restHelper->getCapabilityRESTObject($restConfig::$FHIR_ROUTE_MAP);
        $resources = $restCapability->getResource();
        foreach ($resources as $resource) {
            $resourceName = $resource->getType()->getValue();
            $serviceClassName = $restHelper->getFullyQualifiedServiceClassForResource($resourceName);
            if (!empty($serviceClassName)) {
                $service = new $serviceClassName;
                if ($service instanceof IFhirExportableResourceService) {
                    // if service is instance of IFHIRExportableResource
                    $resourceRegistry[$resourceName] = $service;
                }
            }
        }
        return $resourceRegistry;
    }
}