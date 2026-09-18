<?php

/**
 * ResourceServiceSearchTrait handles the creating of openemr search parameters for a resource.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use InvalidArgumentException;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldOrder;

trait ResourceServiceSearchTrait
{
    /**
     * @var FHIRSearchFieldFactory
     */
    private FHIRSearchFieldFactory $searchFieldFactory;

    public function setSearchFieldFactory(FHIRSearchFieldFactory $factory): void
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
     * @param array $fhirSearchParameters
     * @param string|null $puuidBind The patient unique id if searching in a patient context
     * @return ISearchField[] where the keys are the search fields.
     */
    protected function createOpenEMRSearchParameters(array $fhirSearchParameters, ?string $puuidBind = null): array
    {
        $oeSearchParameters = [];

        $specialColumns = ['_sort' => '', '_count' => '', '_offset' => ''];
        $hasSort = false;

        foreach ($fhirSearchParameters as $fhirSearchField => $searchValue) {
            try {
                if (isset($specialColumns[$fhirSearchField])) {
                    $config = $oeSearchParameters['_config'] ?? [];
                    if ($fhirSearchField == '_sort') {
                        $hasSort = true;
                    }
                    $config[$fhirSearchField] = $searchValue;
                    $oeSearchParameters['_config'] = $config;
                    continue;
                }
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
            } catch (InvalidArgumentException $exception) {
                $message = "The search field argument was invalid, improperly formatted, or could not be parsed. "
                    . " Inner message: " . $exception->getMessage();
                throw new SearchFieldException($fhirSearchField, $message, $exception->getCode(), $exception);
            }
        }

        // now that we've created all of our fields, let's go through and create our sort
        if ($hasSort) {
            $oeSearchParameters['_config']['_sort'] = $this->createSortParameter($fhirSearchParameters['_sort']);
        }

        // Patient-compartment enforcement. If a patient-scope bind is present,
        // the service MUST declare IPatientCompartmentResourceService (or
        // INonPatientCompartmentResourceService for opt-out). Non-declaring
        // services throw so the outer getAll() logs a SearchFieldException
        // and returns an empty result rather than returning another patient's
        // data.
        if (!empty($puuidBind)) {
            if ($this instanceof IPatientCompartmentResourceService) {
                $searchFactory = $this->getSearchFieldFactory();
                $patientField = $this->getPatientContextSearchField();
                $oeSearchParameters[$patientField->getName()] = $searchFactory->buildSearchField($patientField->getName(), [$puuidBind]);
            } elseif (!($this instanceof INonPatientCompartmentResourceService)) {
                throw new SearchFieldException(
                    'patient',
                    'Patient-scoped access to this resource is not permitted.'
                );
            }
            // Non-patient-compartment services drop the bind (patient tokens
            // legitimately reach these endpoints per FHIR spec; the bind is a
            // no-op there).
        }

        return $oeSearchParameters;
    }

    private function createSortParameter($sort): array
    {
        $newSortOrder = [];
        $sortFields = explode(',', (string) $sort);
        $searchFactory = $this->getSearchFieldFactory();
        foreach ($sortFields as $sortField) {
            $isDescending = ($sortField[0] ?? '') === '-';
            if ($isDescending) {
                $sortField = substr($sortField, 1);
            }

            // TODO: @adunsulag would it be more efficient to just build the
            if ($searchFactory->hasSearchField($sortField)) {
                $definition = $searchFactory->getSearchFieldDefinition($sortField);
                $mappedFields = $definition->getMappedFields();
                foreach ($mappedFields as $field) {
                    $newSortOrder[] = new SearchFieldOrder($field, !$isDescending);
                }
            }
        }
        return $newSortOrder;
    }

    protected function createSearchParameterForField($fhirSearchField, $searchValue): ISearchField
    {
        $searchFactory = $this->getSearchFieldFactory();
        if ($searchFactory->hasSearchField($fhirSearchField)) {
            return $searchFactory->buildSearchField($fhirSearchField, $searchValue);
        } else {
            throw new SearchFieldException($fhirSearchField, xlt("This search field does not exist or is not supported"));
        }
    }
}
