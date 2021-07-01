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
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

trait MappedServiceTrait
{
    /**
     * @var FhirServiceBase[]
     */
    private $services = [];

    public function addMappedService($service)
    {
        $this->services[] = $service;
    }

    public function getMappedServices()
    {
        return $this->services;
    }

    public function setMappedServices(array $services)
    {
        $this->services = $services;
    }

    public function getServiceForCode(TokenSearchField $field, $defaultCode)
    {
        // shouldn't ever hit the default but we have it there just in case.
        $values = $field->getValues() ?? [new TokenSearchValue($defaultCode)];
        $searchCode = $values[0]->getCode();

        // we only grab the first one as we assume each service only supports a single LOINC observation code
        foreach ($this->getMappedServices() as $service) {
            if ($service->supportsCode($searchCode)) {
                return $service;
            }
        }
        throw new SearchFieldException($field->getField(), "Invalid or unsupported code");
    }

    public function getServiceForCategory(TokenSearchField $category, $defaultCategory): FhirServiceBase
    {
        // let the field parse our category
        $values = $category->getValues() ?? [new TokenSearchValue($defaultCategory)];
        foreach ($values as $value) {
            // we only search the first one
            $parsedCategory = $value->getCode();
            foreach ($this->getMappedServices() as $service) {
                if ($service->supportsCategory($parsedCategory)) {
                    return $service;
                }
            }
        }
        throw new SearchFieldException("category", "Invalid or unsupported category");
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
}
