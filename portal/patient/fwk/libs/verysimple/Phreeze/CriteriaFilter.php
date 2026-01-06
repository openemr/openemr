<?php

/**
 * @package verysimple::Phreeze
 */

/**
 * import supporting libraries
 */

/**
 * CriteriaFilter allows arbitrary filtering based on one or more fields
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.2
 */
class CriteriaFilter
{
    const TYPE_SEARCH = 1;

    /**
     * @param string|array $propertyNames comma-delimited string or array of property names (ie haystack)
     * @param string $value search term (ie needle)
     * @param int $type (default CriteriaFilter::TYPE_SEARCH)
     */
    public function __construct(public string|array $propertyNames, public string $value, public int $type = self::TYPE_SEARCH)
    {
    }

    /**
     * Return the "where" portion of the SQL statement (without the where prefix)
     *
     * @param Criteria $criteria the Criteria object to which this filter has been added
     */
    public function GetWhere(Criteria $criteria): string
    {
        if ($this->type != self::TYPE_SEARCH) {
            throw new Exception('Unsupported Filter Type');
        }

        // normalize property names as an array
        $propertyNames = (is_array($this->propertyNames)) ? $this->propertyNames : explode(',', $this->propertyNames);

        $where = ' (';
        $orDelim = '';
        foreach ($propertyNames as $propName) {
            $dbfield = $criteria->GetFieldFromProp($propName);
            $where .= $orDelim . $criteria->Escape($dbfield) . " like " . $criteria->GetQuotedSql($this->value) . "";
            $orDelim = ' or ';
        }

        $where .= ') ';

        return $where;
    }

    /**
     * Return the "order by" portion of the SQL statement (without the order by prefix)
     *
     * @param Criteria $criteria the Criteria object to which this filter has been added
     */
    public function GetOrder(Criteria $criteria): string
    {
        return "";
    }
}
