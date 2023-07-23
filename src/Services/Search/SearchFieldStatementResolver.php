<?php

/**
 * SearchFieldStatementResolver is a utility class that takes SearchField's and converts into a SQL SearchQueryFragment
 * with the corresponding SQL statement and bound values that represent that search field.  Nested Composite search
 * fields are traversed and converted into their corresponding values.
 *
 * TODO: adunsulag maybe we can rename this to be SearchFieldQueryConverter  I wonder if that will make more sense to people
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class SearchFieldStatementResolver
{
    const MAX_NESTED_LEVEL = 10;

    /**
     * Given a search field that implements the ISearchField interface, convert the field based upon its type to a full
     * SQL Where Query fragment with its corresponding bound parameterized values.  This is a recursive method as it will
     * traverse any composite search fields up to a heirachical depth of the class constant MAX_NESTED_LEVEL levels.
     * @param ISearchField $field  The field to convert to a SQL SearchQueryFragment
     * @param int $count The current nested count
     * @return SearchQueryFragment
     */
    public static function getStatementForSearchField(ISearchField $field, $count = 0): SearchQueryFragment
    {
        // we allow for more complicated searching by allowing combined search fields but there's a limit to how much
        // we want to allow this to happen.
        if ($count > self::MAX_NESTED_LEVEL) {
            throw new \RuntimeException("Exceeded maximum nested method calls for search fields.");
        }
        if ($field instanceof StringSearchField) {
            return self::resolveStringSearchField($field);
        } else if ($field instanceof DateSearchField) {
            return self::resolveDateField($field);
        } else if ($field instanceof TokenSearchField) {
            return self::resolveTokenField($field);
        } else if ($field instanceof ReferenceSearchField) {
            return self::resolveReferenceField($field);
        } else if ($field instanceof CompositeSearchField) {
            return self::resolveCompositeSearchField($field, $count);
        } else {
            throw new SearchFieldException($field->getName(), "Provided search field type was not implemented");
        }
    }

    /**
     * Given a DateSearchField with a list of SearchFieldComparableValue objects in the search field a SQL query fragment
     * is generated that handles the date field searching.
     * @param DateSearchField $searchField
     * @return SearchQueryFragment
     */
    public static function resolveDateField(DateSearchField $searchField)
    {
        if (empty($searchField->getValues())) {
            throw new SearchFieldException($searchField->getField(), " field does not have a value to search on");
        }

        $clauses = [];
        $searchFragment = new SearchQueryFragment();
        $values = $searchField->getValues();

        /** @var SearchFieldComparableValue $value */
        foreach ($values as $comparableValue) {
            // convert our value to an actual string
            $value = $comparableValue->getValue();
            $lowerBoundDateRange = null;
            $upperBoundDateRange = null;
            $dateSearchString = null;
            $dateFormat = self::getDateFieldFormatForDateType($searchField->getDateType());
            if ($value instanceof \DatePeriod) {
                $lowerBoundDateRange = $value->getStartDate();
                $upperBoundDateRange = $value->getEndDate();
            } else if ($value instanceof \DateTime) {
                // in the future if we want to just have a DateTime value
                $lowerBoundDateRange = $value;
                $upperBoundDateRange = $value;
            } else {
                throw new SearchFieldException($searchField->getField(), "DateSearchField contained value that was not a DatePeriod or DateTime object");
            }

            // make sure we are going to use the local server time adjusted from the timezone the caller requested.
            $lowerBoundDateRange = self::adjustTimezoneToLocalTime($lowerBoundDateRange);
            $upperBoundDateRange = self::adjustTimezoneToLocalTime($upperBoundDateRange);

            switch ($comparableValue->getComparator()) {
                case SearchComparator::LESS_THAN:
                case SearchComparator::ENDS_BEFORE:
                        $operator = "<";
                        $dateSearchString = $lowerBoundDateRange->format($dateFormat);
                    break;
                case SearchComparator::LESS_THAN_OR_EQUAL_TO:
                    // when dealing with an equal to we need to take the upper range of our fuzzy date interval
                    $operator = "<=";
                    $dateSearchString = $upperBoundDateRange->format($dateFormat);
                    break;
                case SearchComparator::GREATER_THAN:
                case SearchComparator::STARTS_AFTER:
                        $operator = ">";
                        $dateSearchString = $upperBoundDateRange->format($dateFormat);
                    break;
                case SearchComparator::GREATER_THAN_OR_EQUAL_TO:
                    // when dealing with an equal to we need to take the lower range of our fuzzy date interval
                    $operator = ">=";
                    $dateSearchString = $lowerBoundDateRange->format($dateFormat);
                    break;
                case SearchComparator::NOT_EQUALS:
                    $operator = "!=";
                    break;
                default:
                    $operator = "=";
                    break;
            }
            // for equality and also inequality (!=) we have to make sure we deal with the fuzzy ranges since search can
            // specify date ranges of just Year, Year+Month, Year+month+day, Year+month+day+hour&minute, Year+month+day+hour&minute+second
            if ($operator === '=') {
                array_push($clauses, $searchField->getField() . ' BETWEEN ? AND ? ');
                $searchFragment->addBoundValue($lowerBoundDateRange->format($dateFormat));
                $searchFragment->addBoundValue($upperBoundDateRange->format($dateFormat));
            } else if ($operator === '!=') {
                // we have to make sure we deal with the fuzzy range when we have an = operator since the user
                // can specify date ranges of just Year, Year+Month, Year+month+day, Year+month+day+hour&minute, Year+month+day+hour&minute+second
                array_push($clauses, $searchField->getField() . ' NOT BETWEEN ? AND ? ');
                $searchFragment->addBoundValue($lowerBoundDateRange->format($dateFormat));
                $searchFragment->addBoundValue($upperBoundDateRange->format($dateFormat));
            } else {
                array_push($clauses, $searchField->getField() . ' ' . $operator . ' ?');
                $searchFragment->addBoundValue($dateSearchString);
            }
        }
        if (count($clauses) > 1) {
            $multipleClause = $searchField->isAnd() ? " AND " : " OR ";
            $searchFragment->setFragment("(" . implode($multipleClause, $clauses) . ")");
        } else {
            $searchFragment->setFragment($clauses[0]);
        }
        return $searchFragment;
    }

    /**
     * Given a composite search field resolve each child field and aggregate into a union or intersection depending on
     * the composite's isAnd setting.
     * @param CompositeSearchField $field The composite field to aggregate.
     * @param $depthCount
     * @return SearchQueryFragment
     */
    public static function resolveCompositeSearchField(CompositeSearchField $field, $depthCount): SearchQueryFragment
    {
        $clauses = [];
        $combinedFragment = new SearchQueryFragment();
        foreach ($field->getChildren() as $searchField) {
            $statement = self::getStatementForSearchField($searchField, $depthCount + 1);
            foreach ($statement->getBoundValues() as $value) {
                $combinedFragment->addBoundValue($value);
            }
            $clauses[] = $statement->getFragment();
        }
        // TODO: stephen do we need to handle OR clauses here for our sub clause?
        $joinType = $field->isAnd() ? " AND " : " OR ";
        $combinedFragment->setFragment("(" . implode($joinType, $clauses) . ")");
        return $combinedFragment;
    }

    /**
     * Converts a reference search field into the appropriate query statement to be executed in the database engine
     *
     * TODO: adunsulag this seems like a lot of duplicate code similar to the resolveTokenField... reference doesn't have
     * the modifiers like the token does so I'm not sure if we keep this duplicative code here or not.
     * @param ReferenceSearchField $searchField
     * @return SearchQueryFragment
     */
    public static function resolveReferenceField(ReferenceSearchField $searchField)
    {
        if (empty($searchField->getValues())) {
            throw new SearchFieldException($searchField->getField(), "field does not have a value to search on");
        }

        $searchFragment = new SearchQueryFragment();
        $values = $searchField->getValues();
        $clauses = [];

        foreach ($values as $value) {
            /** @var ReferenceSearchValue $value  */
            $clauses[] = $searchField->getField() . ' = ?';
            $searchFragment->addBoundValue($value->getId());
        }

        if (count($clauses) > 1) {
            $multipleClause = $searchField->isAnd() ? " AND " : " OR ";
            $searchFragment->setFragment("(" . implode($multipleClause, $clauses) . ")");
        } else {
            $searchFragment->setFragment($clauses[0]);
        }
        return $searchFragment;
    }

    /**
     * Resolves a TokenSearchField to its corresponding value.
     * @param TokenSearchField $searchField
     * @return SearchQueryFragment
     */
    public static function resolveTokenField(TokenSearchField $searchField)
    {
        if (empty($searchField->getValues())) {
            throw new SearchFieldException($searchField->getField(), "field does not have a value to search on");
        }

        $searchFragment = new SearchQueryFragment();
        $modifier = $searchField->getModifier(); // we aren't going to deal with modifiers just yet
        $values = $searchField->getValues();
        $clauses = [];

        foreach ($values as $value) {
            /** @var TokenSearchValue $value  */

            if ($modifier === SearchModifier::MISSING) {
                if ($value->getCode() === false) {
                    // often our tokens get treated as string values so we will do this here also
                    $clauses[] = "(" . $searchField->getField() . " IS NOT NULL AND " . $searchField->getField() . " != '') ";
                } else {
                    // TODO: @adunsulag do we want to compare token values to empty strings... it seems like that would be a missing value but
                    // could we get an inaccurate result here? or will we end up with a case with a number to string conversion on a field
                    // if the value is not a string?
                    $clauses[] = "(" . $searchField->getField() . " IS NULL OR " . $searchField->getField() . " = '') ";
                }
            // if we have other modifiers we would handle them here
            } else {
                $clauses[] = $searchField->getField() . ' = ?';
                // TODO: adunsulag when we better understand Token's we will improve this process of how we resolve the token
                // field to its representative bound value
                $searchFragment->addBoundValue($value->getCode());
            }
        }

        if (count($clauses) > 1) {
            $multipleClause = $searchField->isAnd() ? " AND " : " OR ";
            $searchFragment->setFragment("(" . implode($multipleClause, $clauses) . ")");
        } else {
            $searchFragment->setFragment($clauses[0]);
        }
        return $searchFragment;
    }

    /**
     * Given a search field and any modifier's it may have it converts it to the corresponding SearchQueryFragment
     * @param StringSearchField $searchField
     * @return SearchQueryFragment
     */
    public static function resolveStringSearchField(StringSearchField $searchField)
    {
        if (empty($searchField->getValues())) {
            throw new SearchFieldException($searchField->getField(), "does not have a value to search on");
        }

        $clauses = [];
        $searchFragment = new SearchQueryFragment();
        $modifier = $searchField->getModifier();
        $values = $searchField->getValues();
        foreach ($values as $value) {
            if ($modifier === 'prefix') {
                array_push($clauses, $searchField->getField() . ' LIKE ?');
                $searchFragment->addBoundValue($value . "%");
            } else if ($modifier === 'contains') {
                array_push($clauses, $searchField->getField() . ' LIKE ?');
                $searchFragment->addBoundValue('%' . $value . '%');
            } else if ($modifier === 'exact') {
                // not we may want to grab the specific table collation here in order to improve performance
                // and avoid db casting...
                array_push($clauses, "BINARY " . $searchField->getField() . ' = ?');
                $searchFragment->addBoundValue($value);
            } else if ($modifier == SearchModifier::NOT_EQUALS_EXACT) {
                array_push($clauses, "BINARY " . $searchField->getField() . ' != ?');
                $searchFragment->addBoundValue($value);
            }
        }
        if (count($clauses) > 1) {
            $multipleClause = $searchField->isAnd() ? " AND " : " OR ";
            $searchFragment->setFragment("(" . implode($multipleClause, $clauses) . ")");
        } else {
            $searchFragment->setFragment($clauses[0]);
        }
        return $searchFragment;
    }

    /**
     * Retrieves the date search field date format that should be used for the type of date.
     * @param $dateType
     * @return string
     */
    public static function getDateFieldFormatForDateType($dateType)
    {
        $format = "Y-m-d H:i:s"; // default format is datetime
        if ($dateType == DateSearchField::DATE_TYPE_DATE) {
            $format = "Y-m-d";
        }
        return $format;
    }

    /**
     * Given a datetimeinterface attempt to adjust the timezone component from what was given in the search request
     * to the local timezone that the server is set for so we can make sure we are retrieving the correct data.
     * @param \DateTimeInterface $dateTime
     * @return \DateTimeInterface
     */
    private static function adjustTimezoneToLocalTime(\DateTimeInterface $dateTime)
    {
        if ($dateTime instanceof \DateTimeImmutable || $dateTime instanceof \DateTime) {
            $dateTime->setTimezone(new \DateTimeZone(date('P')));
        }

        // either we return the original date, or we return the locally formatted timezone date.
        return $dateTime;
    }
}
