<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

enum Location
{
    case Documents;

    // More values to come: config, certificates, etc.

    /**
     * Returns the default path for this location, relative to the site directory.
     *
     * @internal - only the storage tooling should call this.
     */
    public function getDefaultPath(): string
    {
        return match ($this) {
            self::Documents => 'documents',
        };
    }
}
