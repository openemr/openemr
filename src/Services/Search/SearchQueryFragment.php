<?php

/**
 * SearchQueryFragment represents the a fragment of a SQL where clause that contains both the SQL statement and the
 * parameterized bound values that will be used in the SQL statement.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class SearchQueryFragment
{
    /**
     * @var string
     */
    private $fragment;

    /**
     * @var mixed[]
     */
    private $boundValues;


    public function __construct($fragment = "", $boundValues = null)
    {
        $this->setFragment($fragment);
        $this->boundValues = is_array($boundValues) ? $boundValues : [];
    }

    /**
     * @return mixed[]
     */
    public function getBoundValues(): array
    {
        return $this->boundValues;
    }

    /**
     * @param mixed $boundValue
     */
    public function addBoundValue($boundValue): void
    {
        $this->boundValues[] = $boundValue;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment(string $fragment): void
    {
        $this->fragment = $fragment;
    }

    public function setQueryFragment(string $fragment, $boundValue)
    {
        $this->setFragment($fragment);
        $this->addBoundValue($boundValue);
    }

    /**
     * Helper statement useful for debugging the query fragment.
     * @return string
     */
    public function __toString()
    {
        return "(fragment=" . $this->getFragment() . ", boundValues=[" . implode(",", $this->boundValues) . "])";
    }
}
