<?php

/**
 * FHIRSearchFieldFactory.php Represents an object factory that creates Search Fields that are used by FHIR services
 * to process searching within the OpenEMR system.  Given a FhirSearchParameterDefinition it will construct the corresponding
 * search field that can be used by the system.
 *
 * Types of fields constructed are defined in the FhirSearchParameterType class.
 * @see OpenEMR\FHIR\FhirSearchParameterType
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Services\FHIR\FhirUrlResolver;
use OpenEMR\Services\Search\SearchFieldType;

class FHIRSearchFieldFactory
{
    /**
     * @var FhirUrlResolver
     */
    private $fhirUrlResolver;

    /**
     * FHIRSearchFieldFactory constructor.
     * @param FhirSearchParameterDefinition[] $resourceSearchParameters
     * @throws \InvalidArgumentException if $searchFieldDefinitions are not an instance of FhirSearchParameterDefinition
     */
    public function __construct(private array $resourceSearchParameters)
    {
        foreach ($this->resourceSearchParameters as $key => $definition) {
            if (!$definition instanceof FhirSearchParameterDefinition) {
                throw new \InvalidArgumentException("Search parameter contains invalid class definition " . $key);
            }
        }
    }

    public function setFhirUrlResolver(FhirUrlResolver $urlResolver): void
    {
        $this->fhirUrlResolver = $urlResolver;
    }

    public function getFhirUrlResolver(): FhirUrlResolver
    {
        return $this->fhirUrlResolver;
    }

    /**
     * @param $fhirSearchField
     * @param FhirSearchParameterDefinition $definition
     * @return void
     */
    public function setSearchFieldDefinition(string $fhirSearchField, FhirSearchParameterDefinition $definition)
    {
        $this->resourceSearchParameters[$fhirSearchField] = $definition;
    }

    /**
     * Checks whethere the factory has a search definition for the passed in search field name
     * @param $fhirSearchField
     * @return bool
     */
    public function hasSearchField($fhirSearchField): bool
    {
        $fieldName = $this->extractSearchFieldName($fhirSearchField);
        return isset($this->resourceSearchParameters[$fieldName]);
    }

    public function getSearchFieldDefinition($fhirSearchField): FhirSearchParameterDefinition
    {
        return $this->resourceSearchParameters[$fhirSearchField];
    }

    /**
     * Factory method to build a search field using the factory's search field definitions.
     * @param $fhirSearchField string The passed in parameter name for the search field the user agent sent.  Can contain search modifiers
     * @param mixed $fhirSearchValues The search value the user agent sent for the $fhirSearchField, or an array of them
     * @throws \InvalidArgumentException If the factory does not have a search definition for $fhirSearchField
     * @return CompositeSearchField|DateSearchField|ReferenceSearchField|StringSearchField|TokenSearchField
     */
    public function buildSearchField($fhirSearchField, mixed $fhirSearchValues)
    {

        if (!$this->hasSearchField($fhirSearchField)) {
            throw new \InvalidArgumentException("Search definition not found for passed in field " . $fhirSearchField);
        }

        $fieldName = $this->extractSearchFieldName($fhirSearchField);
        $searchDefinition = $this->resourceSearchParameters[$fieldName];
        $fhirSearchValues = $this->parseSearchValues($searchDefinition->getType(), $fhirSearchValues);

        // a FHIR composite field can be composed of multiple FHIR fields, we send the lookup table so we can grab them
        if ($searchDefinition->getType() === SearchFieldType::COMPOSITE) {
            $searchField = $this->buildFHIRCompositeField($searchDefinition, $fhirSearchField, $fhirSearchValues);
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
     * Splits a request value into its OR values on each comma that is not escaped (\,). A repeated parameter arrives
     * as an array and its values are not split. Token and composite values keep their escapes until they are split on
     * | or $; all other values are unescaped here.
     * @see https://hl7.org/fhir/R4/search.html#escaping
     * @return array<mixed>
     */
    private function parseSearchValues(string $type, mixed $fhirSearchValues): array
    {
        $values = match (true) {
            is_string($fhirSearchValues) => FhirSearchValueEscape::split($fhirSearchValues, ','),
            is_array($fhirSearchValues) => $fhirSearchValues,
            $fhirSearchValues === null => [],
            default => [$fhirSearchValues],
        };
        if ($type === SearchFieldType::TOKEN || $type === SearchFieldType::COMPOSITE) {
            return $values;
        }
        return array_map(
            static fn(mixed $value): mixed => is_string($value) ? FhirSearchValueEscape::unescape($value) : $value,
            $values
        );
    }

    /**
     * Given a user provided search field that may contain search modifiers we extract the search name from the field.
     * @param $fhirSearchField
     * @return string
     */
    private function extractSearchFieldName($fhirSearchField): string
    {
        $fieldNameWithModifiers = explode(":", (string) $fhirSearchField);
        $fieldName = $fieldNameWithModifiers[0];
        return $fieldName;
    }

    /**
     * Creates a ISearchField for the given type
     * @param string $type a value from SearchFieldType
     * @param string|ServiceField $field The name of the search field or a service field definition
     * @param array<mixed> $fhirSearchValues The values that will be searched on, as parseSearchValues() returns them
     * @param string[] $modifiers Any search modifiers such as :exact or :contains
     * @return DateSearchField|ReferenceSearchField|StringSearchField|TokenSearchField
     */
    private function createFieldForType(string $type, string|ServiceField $field, array $fhirSearchValues, ?array $modifiers = null)
    {
        // we currently only support a single modifier, not going to support multiple modifiers right now in the system
        $modifier = is_array($modifiers) ? array_pop($modifiers) : null;

        $isUUID = false;
        if ($field instanceof ServiceField) {
            $fieldName = $field->getField();
            $isUUID = $field->getType() == ServiceField::TYPE_UUID;
        } else {
            $fieldName = $field;
        }

        if ($type == SearchFieldType::TOKEN) {
            return $this->createTokenSearchField($fieldName, $fhirSearchValues, $modifier, $isUUID);
        } elseif ($type == SearchFieldType::URI) {
            throw new \BadMethodCallException("URI Search Parameter not implemented yet");
        } elseif ($type == SearchFieldType::DATE) {
            return $this->createDateSearchField($fieldName, $fhirSearchValues, DateSearchField::DATE_TYPE_DATE, $modifier);
        } elseif ($type == SearchFieldType::DATETIME) {
            return $this->createDateSearchField($fieldName, $fhirSearchValues, DateSearchField::DATE_TYPE_DATETIME, $modifier);
        } elseif ($type == SearchFieldType::NUMBER) {
            throw new \BadMethodCallException("Number search parameter not implemented yet");
        } elseif ($type == SearchFieldType::REFERENCE) {
            return $this->createReferenceFieldType($fieldName, $fhirSearchValues, $modifier, $isUUID);
        } else {
            // default is a string token
            return new StringSearchField($fieldName, $fhirSearchValues, $modifier, isLiteral: true);
        }
    }

    /**
     * A date the date parser rejects is the request's syntax error, not a search that matches nothing. Three cases
     * fail as they did before instead: an empty value (R4 search: "Empty parameters are not an error"), a
     * modifier, such as :missing, which date parameters don't support here, so the value was never meant as a date,
     * and the ap prefix before a well-formed date, which R4 defines but the date parser doesn't support.
     * @param array<mixed> $fhirSearchValues
     * @throws SearchParameterSyntaxException if a non-empty value isn't an ISO 8601 date with an optional comparator
     */
    private function createDateSearchField(string $fieldName, array $fhirSearchValues, string $dateType, ?string $modifier): DateSearchField
    {
        try {
            return new DateSearchField($fieldName, $fhirSearchValues, $dateType);
        } catch (\InvalidArgumentException $exception) {
            if ($modifier !== null && $modifier !== '') {
                throw $exception;
            }
            foreach ($fhirSearchValues as $value) {
                if (!is_string($value) || trim($value) === '') {
                    continue;
                }
                try {
                    new DateSearchField($fieldName, [$value], $dateType);
                } catch (\InvalidArgumentException $valueException) {
                    if ($this->isApproximateDate($fieldName, $value, $dateType)) {
                        continue;
                    }
                    throw new SearchParameterSyntaxException(
                        $fieldName,
                        $valueException->getMessage(),
                        $valueException->getCode(),
                        $valueException,
                        'must be a FHIR date or dateTime, such as 2020-01-31 or 2020-01-31T13:45:00Z.',
                    );
                }
            }
            throw $exception;
        }
    }

    /**
     * Whether the value is the ap prefix before a date the parser accepts: a valid R4 search this server doesn't
     * support, rather than a malformed one.
     */
    private function isApproximateDate(string $fieldName, string $value, string $dateType): bool
    {
        if (!str_starts_with($value, SearchComparator::APROXIMATELY_SAME)) {
            return false;
        }
        try {
            new DateSearchField($fieldName, [substr($value, strlen(SearchComparator::APROXIMATELY_SAME))], $dateType);
            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @param array<mixed> $fhirSearchValues
     */
    private function createTokenSearchField(string $fieldName, array $fhirSearchValues, ?string $modifier, bool $isUUID): TokenSearchField
    {
        $token = new TokenSearchField($fieldName, $fhirSearchValues, $isUUID);
        if (!empty($modifier)) {
            $token->setModifier($modifier);
        }
        // TokenSearchField reads a string without FHIR escapes, so read system|code again with them
        $token->setValues(array_map(
            static fn(mixed $value): mixed => is_string($value)
                ? TokenSearchValue::buildFromEscapedFHIRString($value, $token->isUuid() === true)
                : $value,
            $fhirSearchValues
        ));
        return $token;
    }

    /**
     * @param array<mixed> $fhirSearchValues
     */
    private function createReferenceFieldType(string $fieldName, array $fhirSearchValues, ?string $modifiers, bool $isUUID): ReferenceSearchField
    {
        $normalizedValues = [];
        foreach ($fhirSearchValues as $searchValue) {
            if (is_string($searchValue) && str_contains($searchValue, '://')) {
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
     * @throws \BadMethodCallException if the FhirUrlResolver is not setup for this class
     * @throws \InvalidArgumentException if the URL does not match the server base URL
     * @return string
     */
    private function resolveReferenceRelativeUrl(string $urlToResolve): string
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
     * @param array<mixed> $fhirSearchValues The values that will be searched on for each of the composite fields.
     */
    private function createCompositeFieldForMultipleMappedFields(FhirSearchParameterDefinition $definition, $fhirSearchField, array $fhirSearchValues): CompositeSearchField
    {
        $isAnd = false; // when we are building our composite field here we want the UNION of values since the internal
        // we want to search across all of the mapped OpenEMR columns which is an intersection(logical OR) rather than
        // the logical AND of everything.
        $composite = new CompositeSearchField($definition->getName(), $fhirSearchValues, $isAnd);
        $modifiers = $this->extractFieldModifiers($fhirSearchField);
        foreach ($definition->getMappedFields() as $field) {
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
     * @return list<string>
     */
    private function extractFieldModifiers($fhirSearchField): array
    {
        $fieldNameWithModifiers = explode(":", (string) $fhirSearchField);
        $fieldName = $fieldNameWithModifiers[0];
        array_shift($fieldNameWithModifiers); // grab our modifiers
        return $fieldNameWithModifiers;
    }

    /**
     * Creates a composite search field (searching across multiple fhir sub-fields such as gender and birthdate in a patient)
     * @param FhirSearchParameterDefinition $definition  The definition for this FHIR composite search field
     * @param $fhirSearchField The name of the search field
     * @param $fhirSearchValues The values that were sent by the calling user agent.
     */
    private function buildFHIRCompositeField(FhirSearchParameterDefinition $definition, $fhirSearchField, $fhirSearchValues): CompositeSearchField
    {

        $composite = new CompositeSearchField($definition->getName(), $fhirSearchValues);

        foreach ($definition->getMappedFields() as $fieldName) {
            if (isset($this->resourceSearchParameters[$fieldName])) {
                $childDefinition = $this->resourceSearchParameters[$fieldName];
                // for now let's treat everything as a string...
                // we won't give any modifier here for now
                // not sure how we handle modifiers here...
                $childField = $this->buildSearchField($childDefinition, $fieldName);
                $composite->addChild($childField);
            }
        }

        return $composite;
    }
}
