<?php

/**
 * ServiceField.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Uuid\UuidRegistry;

class ServiceField
{
    private $field;
    private $type;

    const TYPE_STRING = "string";
    const TYPE_NUMBER = "number";
    const TYPE_UUID = "uuid";

    public function __construct($field, $type = self::TYPE_STRING)
    {
        $this->field = $field;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }
}
