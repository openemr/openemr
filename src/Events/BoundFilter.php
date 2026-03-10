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
    private string $filterClause = "1";

    /**
     * @var array<int, string|int>
     *
     * Represents the values to be substituted for ?s in the filter clause
     */
    private array $boundValues = [];

    public function getFilterClause(): string
    {
        return $this->filterClause;
    }

    public function setFilterClause(string $filterClause): void
    {
        $this->filterClause = $filterClause;
    }

    /**
     * @return array<int, string|int>
     */
    public function getBoundValues(): array
    {
        return $this->boundValues;
    }

    /**
     * @param array<int, string|int> $boundValues
     */
    public function setBoundValues(array $boundValues): void
    {
        $this->boundValues = $boundValues;
    }

    public function addBoundValue(string|int $boundValue): void
    {
        $this->boundValues[] = $boundValue;
    }
}
