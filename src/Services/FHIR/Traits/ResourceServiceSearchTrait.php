<?php

/**
 * ResourceServiceSearchTrait handles the creating of openemr search parameters for a resource.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\SearchFieldException;

trait ResourceServiceSearchTrait
{
    public function setSearchFieldFactory(FHIRSearchFieldFactory $factory)
    {
        $this->searchFieldFactory = $factory;
    }

    public function getSearchFieldFactory(): FHIRSearchFieldFactory
    {
        return $this->searchFieldFactory;
    }

    /**
     * Given the hashmap of search parameters to values it generates a map of search keys to ISearchField objects that
     * are used to search in the OpenEMR system.  Service classes that extend the base class can override this method
     *
     * to either add search fields or change the functionality of the created ISearchFields.
     *
     * @param $fhirSearchParameters
     * @param $puuidBind The patient unique id if searching in a patient context
     * @return ISearchField[] where the keys are the search fields.
     */
    protected function createOpenEMRSearchParameters($fhirSearchParameters, $puuidBind)
    {
        $oeSearchParameters = array();

        foreach ($fhirSearchParameters as $fhirSearchField => $searchValue) {
            try {
                // format: <field>{:modifier1|:modifier2}={comparator1|comparator2}[value1{,value2}]
                // field is the FHIR search field
                // modifier is the search modifier ie :exact, :contains, etc
                // comparator is used with dates / numbers, ie :gt, :lt
                // values can be comma separated and are treated as an OR condition
                // if the $searchValue is an array then this is treated as an AND condition
                // if $searchValue is an array and individual fields contains comma separated values the and clause takes
                // precedence and ALL values will be UNIONED (AND clause).
                $searchField = $this->createSearchParameterForField($fhirSearchField, $searchValue);
                $oeSearchParameters[$searchField->getName()] = $searchField;
            } catch (\InvalidArgumentException $exception) {
                $message = "The search field argument was invalid, improperly formatted, or could not be parsed. "
                    . " Inner message: " . $exception->getMessage();
                throw new SearchFieldException($fhirSearchField, $message, $exception->getCode(), $exception);
            }
        }

        // we make sure if we are a resource that deals with patient data and we are in a patient bound context that
        // we restrict the data to JUST that patient.
        if (!empty($puuidBind) && $this instanceof IPatientCompartmentResourceService) {
            $searchFactory = $this->getSearchFieldFactory();
            $patientField = $this->getPatientContextSearchField();
            // TODO: @adunsulag not sure if every service will already have a defined binding for the patient... I'm assuming for Patient compartments we would...
            // yet we may need to extend the factory in the future to handle this.
            $oeSearchParameters[$patientField->getName()] = $searchFactory->buildSearchField($patientField->getName(), [$puuidBind]);
        }

        return $oeSearchParameters;
    }

    protected function createSearchParameterForField($fhirSearchField, $searchValue)
    {
        $searchFactory = $this->getSearchFieldFactory();
        if ($searchFactory->hasSearchField($fhirSearchField)) {
            $searchField = $searchFactory->buildSearchField($fhirSearchField, $searchValue);
            return $searchField;
        } else {
            throw new SearchFieldException($fhirSearchField, xlt("This search field does not exist or is not supported"));
        }
    }
}
