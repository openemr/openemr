<?php

/**
 * FhirServiceLocator.php  This class locates fhir service classes for the fhir resources.  Note that because it leverages
 * the Capability statement this can take 500-900ms to execute and should be used with caution.
 * TODO: @adunsulag Look at changing up this class to not use the capability statement or optimize that class to be more performant.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Utils;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;

class FhirServiceLocator
{
    /**
     * @var $restConfig
     */
    private $restConfig;

    /**
     * FhirExportServiceLocator constructor.
     * @param \RestConfig $restConfig
     * $type is the FQDN of a class or interface... IE type should resolve to the ::class property of a class or interface
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
    public function findServices($type)
    {
        if (empty($type) || !(class_exists($type) || interface_exists($type))) {
            throw new \InvalidArgumentException('$type must be a valid class or instance');
        }

        $resourceRegistry = [];
        $restHelper = new RestControllerHelper();
        $restConfig = $this->restConfig;
        $restCapability = $restHelper->getCapabilityRESTObject($restConfig::$FHIR_ROUTE_MAP);
        $resources = $restCapability->getResource();
        foreach ($resources as $resource) {
            $resourceName = $resource->getType()->getValue();
            $serviceClassName = $restHelper->getFullyQualifiedServiceClassForResource($resourceName);
            if (!empty($serviceClassName)) {
                $service = new $serviceClassName();
                if ($service instanceof $type) {
                    // if service is instance of IFHIRExportableResource
                    $resourceRegistry[$resourceName] = $service;
                }
            }
        }
        return $resourceRegistry;
    }
}
