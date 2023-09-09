<?php

/**
 * SearchFieldOrder represents a field and its sort order for a search query.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class SearchFieldOrder
{
    private string $field;

    private bool $ascending;

    public function __construct(string $field, bool $ascending)
    {
        $this->field = $field;
        $this->ascending = $ascending;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return bool
     */
    public function isAscending(): bool
    {
        return $this->ascending;
    }
}
