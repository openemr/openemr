<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Storage;

use OpenEMR\Encryption\Keys\KeyMaterial;

interface KeyStorageInterface
{
    public function getKey(KeyMaterialId $identifier): KeyMaterial;

    /**
     * This should throw an exception if storing the key failed.
     *
     * It MUST NOT overwrite an existing key.
     */
    public function storeKey(KeyMaterialId $identifier, KeyMaterial $key): void;
}
