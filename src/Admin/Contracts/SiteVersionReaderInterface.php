<?php

/**
 * Site Version Reader Interface
 *
 * Contract for reading site version information from database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Contracts;

use OpenEMR\Admin\ValueObjects\SiteVersion;

interface SiteVersionReaderInterface
{
    /**
     * Read version information from database
     *
     * @throws \OpenEMR\Admin\Exceptions\DatabaseQueryException
     */
    public function readVersion(\mysqli $connection): SiteVersion;

    /**
     * Get site name from database
     *
     * @throws \OpenEMR\Admin\Exceptions\DatabaseQueryException
     */
    public function getSiteName(\mysqli $connection): string;
}
