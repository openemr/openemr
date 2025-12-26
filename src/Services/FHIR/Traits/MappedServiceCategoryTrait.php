<?php

/*
 * MappedServiceCategoryTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;

trait MappedServiceCategoryTrait
{
    use MappedServiceTrait;

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
}
