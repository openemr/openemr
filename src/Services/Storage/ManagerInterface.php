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

use League\Flysystem\FilesystemOperator;

/**
 * Read-only accessor for storage filesystems.
 *
 * Consumers should depend on this interface, not the concrete Manager.
 */
interface ManagerInterface
{
    public function getStorage(Location $location): FilesystemOperator;
}
