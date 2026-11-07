<?php

/**
 * FHIR write operations recorded in the API audit ledger.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

enum ApiWriteAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
