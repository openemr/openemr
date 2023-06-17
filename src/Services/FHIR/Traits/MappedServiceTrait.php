<?php

/**
 * MappedServiceTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Validators\ProcessingResult;

trait MappedServiceTrait
{
    /**
     * @var FhirServiceBase[]
     */
    private $services = [];

    public function addMappedService(FhirServiceBase $service)
    {
        $this->services[] = $service;
    }

    public function getMappedServices()
    {
        return $this->services;
    }

    public function setMappedServices(array $services)
    {
        $validServices = [];
        foreach ($services as $service) {
            if ($service instanceof FhirServiceBase) {
                $validServices[] = $service;
            } else {
                throw new \InvalidArgumentException("Expected service of type " . FhirServiceBase::class . " and instead received class of type " . get_class($service));
            }
        }
        $this->services = $validServices;
    }

    public function searchAllServices($fhirSearchParams, $puuidBind)
    {
        $processingResult = new ProcessingResult();

        /**
         * @var $service BaseService
         */
        foreach ($this->getMappedServices() as $service) {
            $innerResult = $service->getAll($fhirSearchParams, $puuidBind);
            $processingResult->addProcessingResult($innerResult);
            if ($processingResult->hasErrors()) {
                // clear our data out and just return the errors
                $processingResult->clearData();
                return $processingResult;
            }
        }
        return $processingResult;
    }

    public function searchServices(array $services, $fhirSearchParams, $puuidBind)
    {
        $processingResult = new ProcessingResult();
        foreach ($services as $service) {
            $innerResult = $service->getAll($fhirSearchParams, $puuidBind);
            $processingResult->addProcessingResult($innerResult);
            if ($processingResult->hasErrors()) {
                // clear our data out and just return the errors
                $processingResult->clearData();
                return $processingResult;
            }
        }
        return $processingResult;
    }

    public function searchAllServicesWithSupportedFields($fhirSearchParams, $puuidBind)
    {
        $processingResult = new ProcessingResult();
        foreach ($this->getMappedServices() as $service) {
            $filteredParams = $service->getSupportedSearchParams($fhirSearchParams);
            $innerResult = $service->getAll($filteredParams, $puuidBind);
            $processingResult->addProcessingResult($innerResult);
            if ($processingResult->hasErrors()) {
                // clear our data out and just return the errors
                $processingResult->clearData();
                return $processingResult;
            }
        }
        return $processingResult;
    }

    public function getMappedServiceForResourceUuid($resourceUuid)
    {
        $search = ['_id' => $resourceUuid];
        foreach ($this->getMappedServices() as $service) {
            $innerResult = $service->getAll($search);
            if ($innerResult->hasData()) {
                return $service;
            }
        }
    }
}
