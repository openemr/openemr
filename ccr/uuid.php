<?php

/*
 * This will return an instance of Ramsey\Uuid\Rfc4122\UuidV4.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Ramsey\Uuid\Uuid;

/**
 * This will return an instance of Ramsey\Uuid\Rfc4122\UuidV4.
 *
 * @return  string  A UUID, made up of 32 hex digits and 4 hyphens.
 */

function getUuid()
{
    $uuid = Uuid::uuid4();
    return $uuid->toString();
}
