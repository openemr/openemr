<?php

/**
 * CompositeSearchField implements the ISearchField interface and represents a search that covers more than one search
 * field.  The class is heirarchical in that it has a one to many child relationship that can represent multiple complex
 * search operations up to 10 levels deep of unions or intersections.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Services\Search\SearchFieldType;

class CompositeSearchField implements ISearchField
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $field;

    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @var ISearchField[]
     */
    private $children;

    /**
     * @var boolean Whether the composite fields should be treated as a logical AND (intersection)
     * or a logical OR (UNION)
     */
    private $isAnd;

    public function __construct($name, $values, $isAnd = true)
    {
        $this->name = $name;
        $this->field = $name; // we will give the field the same name as our name.
        $this->values = $values;
        $this->children = [];
        $this->isAnd = $isAnd === true;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param mixed[] $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function addValue($value)
    {
        $this->values[] = $value;
    }

    public function getType()
    {
        return SearchFieldType::COMPOSITE;
    }

    /**
     * @return ISearchField[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param ISearchField[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(ISearchField $child)
    {
        $this->children[] = $child;
    }

    public function getField()
    {
        return $this->field;
    }

    public function isAnd()
    {
        return $this->isAnd;
    }

    /**
     * Useful for debugging, you can echo the object to see its values.
     * @return string
     */
    public function __toString()
    {
        $values = $this->getValues ?? [];
        $children = $this->getChildren() ?? [];

        return "(field=" . $this->getField() . ",type=" . $this->getType()
            . ",values=[" . implode(",", $values) . "],children=[{" . implode("},{", $children) . "}])";
    }
}
