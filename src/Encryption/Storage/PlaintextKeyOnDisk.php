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
use OutOfBoundsException;
use RuntimeException;
use UnexpectedValueException;

/**
 * @deprecated For backwards compatibility only.
 */
readonly class PlaintextKeyOnDisk implements KeyStorageInterface
{
    public function __construct(private string $storageDir)
    {
    }

    public function getKey(KeyMaterialId $identifier): KeyMaterial
    {
        $path = sprintf('%s/%s', $this->storageDir, $identifier->id);
        if (!file_exists($path)) {
            throw new OutOfBoundsException('Key not found');
        }
        $encoded = file_get_contents($path);
        if ($encoded === false) {
            throw new RuntimeException('Could not read key');
        }
        $decoded = base64_decode($encoded, strict: true);
        if ($decoded === false) {
            throw new UnexpectedValueException('Could not decode key');
        }
        return new KeyMaterial(key: $decoded);
    }

    public function storeKey(KeyMaterialId $identifier, KeyMaterial $key): void
    {
        $path = sprintf('%s/%s', $this->storageDir, $identifier->id);
        if (file_exists($path)) {
            throw new UnexpectedValueException('Key exists, will not overwrite');
        }
        $encoded = base64_encode($key->key);
        $result = file_put_contents($path, $encoded);
        if ($result === false) {
            throw new RuntimeException('Key writing failed');
        }
    }
}
