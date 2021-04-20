<?php
/**
 * TokenSearchField represents a token data type that contains both a system and a code.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;


use OpenEMR\Services\Search\SearchFieldType;

class TokenSearchField extends BasicSearchField
{
    public function __construct($field, $values)
    {
        parent::__construct($field, SearchFieldType::TOKEN, $field, $values);
    }
}