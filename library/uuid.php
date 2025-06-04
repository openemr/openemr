<?php

/**
 * function that is used by the automated uuid creation service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Uuid\UuidRegistry;

function autoPopulateAllMissingUuids()
{
    UuidRegistry::populateAllMissingUuids();
}
