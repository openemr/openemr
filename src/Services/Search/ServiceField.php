<?php

/**
 * ServiceField.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class ServiceField
{
    const TYPE_STRING = "string";
    const TYPE_NUMBER = "number";
    const TYPE_UUID = "uuid";

    public function __construct(private readonly string $field, private readonly string $type = self::TYPE_STRING)
    {
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getField(): string
    {
        return $this->field;
    }
}
