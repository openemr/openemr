<?php

/**
 * In-memory key storage for testing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Encryption\Storage\KeyMaterialId;
use OpenEMR\Encryption\Storage\KeyStorageInterface;
use OutOfBoundsException;
use RuntimeException;

final class InMemoryKeyStorage implements KeyStorageInterface
{
    /** @var array<string, KeyMaterial> */
    private array $keys = [];

    public function getKey(KeyMaterialId $identifier): KeyMaterial
    {
        if (!isset($this->keys[$identifier->id])) {
            throw new OutOfBoundsException("Key not found: {$identifier->id}");
        }
        return $this->keys[$identifier->id];
    }

    public function storeKey(KeyMaterialId $identifier, KeyMaterial $key): void
    {
        if (isset($this->keys[$identifier->id])) {
            throw new RuntimeException("Key already exists: {$identifier->id}");
        }
        $this->keys[$identifier->id] = $key;
    }

    public function has(string $id): bool
    {
        return isset($this->keys[$id]);
    }
}
