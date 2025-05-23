<?php

/**
 * NumberSearchField.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class NumberSearchField extends BasicSearchField
{
    public function __construct($field, $values, $searchModifier = null)
    {
        parent::__construct($field, SearchFieldType::NUMBER, $field, $values, $searchModifier);
    }
}
