<?php
/**
 * BasicSearchField implements the ISearchField interface and provides a basic class implementation of all of the search
 * field functionality that child classes can leverage to quickly implement new types of search fields.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;


use OpenEMR\Services\Search\SearchFieldType;

class BasicSearchField implements ISearchField
{
    private $field;
    private $name;
    private $modifier;
    private $values;
    private $isAnd;

    /**
     * BasicSearchField constructor.
     * @param $name The documented name of this search field.  Can be the same as field name but is not required to be.
     * @param $type The type of
     * @param $field
     * @param $values
     * @param null $modifier
     * @param bool $isAnd
     */
    public function __construct($name, $type, $field, $values, $modifier = null, $isAnd = true)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setField($field);
        $this->setModifier($modifier);
        $this->setIsAnd($isAnd);
        $values = $values ?? [];
        $values = is_array($values) ? $values : [$values];
        $this->setValues($values);
    }

    public function getName()
    {
        return $this->name;
    }

    protected function setName($name) {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function setType($type) {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     * @return BasicSearchField
     */
    protected function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @param string $modifier
     * @return BasicSearchField
     */
    protected function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @return null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param mixed[] $values
     * @return BasicSearchField
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Returns whether the array of values that this search field can have should be logically intersected(AND) or logically
     * unioned(OR).
     * @return bool
     */
    public function isAnd(): bool
    {
        return $this->isAnd;
    }

    /**
     * @param bool $isAnd
     * @return BasicSearchField
     */
    protected function setIsAnd(bool $isAnd): ISearchField
    {
        $this->isAnd = $isAnd;
        return $this;
    }
}