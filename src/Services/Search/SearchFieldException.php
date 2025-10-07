<?php

/**
 * SearchArgumentException.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use Throwable;

class SearchFieldException extends \InvalidArgumentException
{
    /**
     * @param string $field The name of the field that the exception was triggered on
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(
        private $field,
        $message = "",
        $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
