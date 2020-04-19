<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events;

/**
 * Represents a where clause using(?, ?) and it's bound values
 *
 * @package OpenEMR\Events
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class BoundFilter
{
    /**
     * @var string
     *
     * Part of a WHERE filter clause, to be appended to a WHERE clause
     */
    private $filterClause = "1";

    /**
     * @var array
     *
     * Represents the values to be substituted for ?s in the filter clause
     */
    private $boundValues = [];

    /**
     * @return string
     */
    public function getFilterClause()
    {
        return $this->filterClause;
    }

    /**
     * @param string $filterClause
     */
    public function setFilterClause($filterClause)
    {
        $this->filterClause = $filterClause;
    }

    /**
     * @return array
     */
    public function getBoundValues()
    {
        return $this->boundValues;
    }

    /**
     * @param array $boundValues
     */
    public function setBoundValues($boundValues)
    {
        $this->boundValues = $boundValues;
    }

    /**
     * @param string|int $boundValue
     */
    public function addBoundValue($boundValue)
    {
        $this->boundValues[] = $boundValue;
    }
}
