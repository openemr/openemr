<?php

/**
 * FHIRSearchFieldFactory.php Represents an object factory that creates Search Fields that are used by FHIR services
 * to process searching within the OpenEMR system.  Given a FhirSearchParameterDefinition it will construct the corresponding
 * search field that can be used by the system.
 *
 * Types of fields constructed are defined in the FhirSearchParameterType class.
 * @see OpenEMR\FHIR\FhirSearchParameterType
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use Laminas\Mvc\Exception\BadMethodCallException;
use OpenEMR\Services\FHIR\FhirUrlResolver;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSearchParameter;

class FHIRSearchFieldFactory
{
    /**
     * @var FhirSearchParameterDefinition[];
     */
    private $resourceSearchParameters;

    /**
     * @var FhirUrlResolver
     */
    private $fhirUrlResolver;

    /**
     * FHIRSearchFieldFactory constructor.
     * @param FhirSearchParameterDefinition[] $searchFieldDefinitions
     * @throws \InvalidArgumentException if $searchFieldDefinitions are not an instance of FhirSearchParameterDefinition
     */
    public function __construct(array $searchFieldDefinitions)
    {
        $this->resourceSearchParameters = $searchFieldDefinitions;
        foreach ($searchFieldDefinitions as $key => $definition) {
            if (!$definition instanceof FhirSearchParameterDefinition) {
                throw new \InvalidArgumentException("Search parameter contains invalid class definition " . $key);
            }
        }
    }

    public function setFhirUrlResolver(FhirUrlResolver $urlResolver)
    {
        $this->fhirUrlResolver = $urlResolver;
    }

    public function getFhirUrlResolver(): FhirUrlResolver
    {
        return $this->fhirUrlResolver;
    }

    /**
     * Checks whethere the factory has a search definition for the passed in search field name
     * @param $fhirSearchField
     * @return bool
     */
    public function hasSearchField($fhirSearchField)
    {
        $fieldName = $this->extractSearchFieldName($fhirSearchField);
        return isset($this->resourceSearchParameters[$fieldName]);
    }

    /**
     * Factory method to build a search field using the factory's search field definitions.
     * @param $fhirSearchField The passed in parameter name for the search field the user agent sent.  Can contain search modifiers
     * @param $fhirSearchValues The array of search values the user agent sent for the $fhirSearchField
     * @throws \InvalidArgumentException If the factory does not have a search definition for $fhirSearchField
     * @return CompositeSearchField|DateSearchField|StringSearchField|TokenSearchField
     */
    public function buildSearchField($fhirSearchField, $fhirSearchValues)
    {

        if (!$this->hasSearchField($fhirSearchField)) {
            throw new \InvalidArgumentException("Search definition not found for passed in field " . $fhirSearchField);
        }

        $fieldName = $this->extractSearchFieldName($fhirSearchField);
        $searchDefinition = $this->resourceSearchParameters[$fieldName];

        // a FHIR composite field can be composed of multiple FHIR fields, we send the lookup table so we can grab them
        if ($searchDefinition->getType() === SearchFieldType::COMPOSITE) {
            $searchField = $this->buildFhirCompositeField($searchDefinition, $fhirSearchField, $fhirSearchValues);
        } else {
            $mappedFields = $searchDefinition->getMappedFields();
            if (count($mappedFields) > 1) {
                // create a composite field
                $searchField = $this->createCompositeFieldForMultipleMappedFields($searchDefinition, $fhirSearchField, $fhirSearchValues);
            } else {
                // create a regular field
                $modifiers = $this->extractFieldModifiers($fhirSearchField);

                $searchField = $this->createFieldForType($searchDefinition->getType(), $mappedFields[0], $fhirSearchValues, $modifiers);
            }
        }
        return $searchField;
    }

    /**
     * Given a user provided search field that may contain search modifiers we extract the search name from the field.
     * @param $fhirSearchField
     * @return string
     */
    private function extractSearchFieldName($fhirSearchField)
    {
        $fieldNameWithModifiers = explode(":", $fhirSearchField);
        $fieldName = $fieldNameWithModifiers[0];
        return $fieldName;
    }

    /**
     * Creates a ISearchField for the given type
     * @param $type a value from SearchFieldType
     * @param $field string|ServiceField The name of the search field or a service field definition
     * @param $fhirSearchValues The values that will be searched on
     * @param string[] $modifiers Any search modifiers such as :exact or :contains
     * @return DateSearchField|StringSearchField|TokenSearchField
     */
    private function createFieldForType($type, $field, $fhirSearchValues, $modifiers = null)
    {
        // we currently only support a single modifier, not going to support multiple modifiers right now in the system
        $modifier = is_array($modifiers) ? array_pop($modifiers) : null;

        // need to handle the fact that we can have multiple OR values that are separated in CSV format.
        if (is_string($fhirSearchValues) && strpos($fhirSearchValues, ',') !== false) {
            $fhirSearchValues = explode(',', $fhirSearchValues);
        }

        $isUUID = false;
        if ($field instanceof ServiceField) {
            $fieldName = $field->getField();
            $isUUID = $field->getType() == ServiceField::TYPE_UUID ? true : false;
        } else {
            $fieldName = $field;
        }

        if ($type == SearchFieldType::TOKEN) {
            return $this->createTokenSearchField($fieldName, $fhirSearchValues, $modifier, $isUUID);
        } else if ($type == SearchFieldType::URI) {
            throw new \BadMethodCallException("URI Search Parameter not implemented yet");
        } else if ($type == SearchFieldType::DATE) {
            return new DateSearchField($fieldName, $fhirSearchValues, DateSearchField::DATE_TYPE_DATE);
        } else if ($type == SearchFieldType::DATETIME) {
            return new DateSearchField($fieldName, $fhirSearchValues, DateSearchField::DATE_TYPE_DATETIME);
        } else if ($type == SearchFieldType::NUMBER) {
            throw new \BadMethodCallException("Number search parameter not implemented yet");
        } else if ($type == SearchFieldType::REFERENCE) {
            return $this->createReferenceFieldType($fieldName, $fhirSearchValues, $modifier, $isUUID);
        } else {
            // default is a string token
            return new StringSearchField($fieldName, $fhirSearchValues, $modifier);
        }
    }
    private function createTokenSearchField($fieldName, $fhirSearchValues, $modifier, $isUUID)
    {
        $token = new TokenSearchField($fieldName, $fhirSearchValues, $isUUID);
        if (!empty($modifier)) {
            $token->setModifier($modifier);
        }
        return $token;
    }

    private function createReferenceFieldType($fieldName, $fhirSearchValues, $modifiers, $isUUID)
    {
        $referenceOptions = $this->resourceSearchParameters[$fieldName] ?? [];

        $values = $fhirSearchValues ?? [];
        $values = is_array($values) ? $values : [$values];

        $normalizedValues = [];
        foreach ($values as $searchValue) {
            if (strpos($searchValue, '://') !== false) {
                $url = $this->resolveReferenceRelativeUrl($searchValue);
                $normalizedValues[] = $url;
            } else {
                $normalizedValues[] = $searchValue;
            }
        }
        return new ReferenceSearchField($fieldName, $normalizedValues, $isUUID);
    }

    /**
     * Returns the relative url
     * @param $urlToResolve
     * @throws BadMethodCallException if the FhirUrlResolver is not setup for this class
     * @throws \InvalidArgumentException if the URL does not match the server base URL
     * @return string
     */
    private function resolveReferenceRelativeUrl($urlToResolve)
    {
        if (empty($this->getFhirUrlResolver())) {
            throw new \BadMethodCallException("FHIR URL Resolver is not properly setup.  This is a developer error");
        }
        $url = $this->getFhirUrlResolver()->getRelativeUrl($urlToResolve);
        if (empty($url)) {
            throw new \InvalidArgumentException("URL does not match fhir server or relative url for reference could not be found");
        }
        return $url;
    }

    /**
     * If a single search field has multiple mapped fields we create a composite field that is the union (logical OR) of
     * those values.  This field will return a value if ANY of the mapped fields has the $fhirSearchValues in it.
     * @param FhirSearchParameterDefinition $definition The search definition object
     * @param $fhirSearchField The name of the search definition
     * @param $fhirSearchValues The values that will be searched on for each of the composite fields.
     * @return CompositeSearchField
     */
    private function createCompositeFieldForMultipleMappedFields(FhirSearchParameterDefinition $definition, $fhirSearchField, $fhirSearchValues)
    {
        $isAnd = false; // when we are building our composite field here we want the UNION of values since the internal
        // we want to search across all of the mapped OpenEMR columns which is an intersection(logical OR) rather than
        // the logical AND of everything.
        $composite = new CompositeSearchField($definition->getName(), $fhirSearchValues, $isAnd);
        $modifiers = $this->extractFieldModifiers($fhirSearchField);
        foreach ($definition->getMappedFields() as $key => $field) {
            // for token types we want to make if we have a system we are only going to the key

            // for now let's treat everything as a string...
            // we won't give any modifier here for now
            $childField = $this->createFieldForType($definition->getType(), $field, $fhirSearchValues, $modifiers);
            $composite->addChild($childField);
        }
        return $composite;
    }

    /**
     * Given a search field that may or may not contain FHIR modifiers (noted by a : after the field name) it will remove
     * all the modifiers and return them as an array of strings to the caller.
     * @param $fhirSearchField
     * @return array
     */
    private function extractFieldModifiers($fhirSearchField)
    {
        $fieldNameWithModifiers = explode(":", $fhirSearchField);
        $fieldName = $fieldNameWithModifiers[0];
        array_shift($fieldNameWithModifiers); // grab our modifiers
        return $fieldNameWithModifiers;
    }

    /**
     * Creates a composite search field (searching across multiple fhir sub-fields such as gender and birthdate in a patient)
     * @param FhirSearchParameterDefinition $definition  The definition for this FHIR composite search field
     * @param $fhirSearchField The name of the search field
     * @param $fhirSearchValues The values that were sent by the calling user agent.
     * @return CompositeSearchField  The created composite search field.
     */
    private function buildFHIRCompositeField(FhirSearchParameterDefinition $definition, $fhirSearchField, $fhirSearchValues)
    {

        $composite = new CompositeSearchField($definition->getName(), $fhirSearchValues);

        foreach ($definition->getMappedFields() as $fieldName) {
            if (isset($this->resourceSearchParameters[$fieldName])) {
                $childDefinition = $this->resourceSearchParameters[$fieldName];
                // for now let's treat everything as a string...
                // we won't give any modifier here for now
                // not sure how we handle modifiers here...
                $childField = $this->buildSearchField($childDefinition, $fieldName, $fhirSearchValues);
                $composite->addChild($childField);
            }
        }

        return $composite;
    }
}
