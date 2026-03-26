<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

enum MessageFormat: int
{
    // Existing formats as of March 2026: these all mapped 1:1 with key ids and
    // versions
    case v1 = 1;
    case v2 = 2;
    case v3 = 3;
    case v4 = 4;
    case v5 = 5;
    case v6 = 6;
    case v7 = 7;
    // Future: v8 will allow for actual key versioning without additional code
    // changes. It will get different handling in Message.

    const LATEST = self::v7;
}
