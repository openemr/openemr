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

enum MessageFormat
{
    // Existing format as of March 2026: the "format" id is also a key id
    case ImplicitKey;
    // Future: ExplicitKey
    // Maybe future: Plaintext

    const LATEST = self::ImplicitKey;
}
