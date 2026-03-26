<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OutOfBoundsException;
use UnexpectedValueException;

readonly class PlaintextKeyInDbKeysTableAdodb implements KeyStorageInterface
{
    public function getKey(string $identifier): KeyMaterial
    {
        $row = QueryUtils::querySingleRow(
            'SELECT value FROM `keys` WHERE name = ?',
            [$identifier],
            log: false,
        );
        if ($row === false) {
            throw new OutOfBoundsException('No key found');
        }
        $result = $row['value'];

        if (!is_string($result)) {
            throw new UnexpectedValueException('Key found, invalid data format');
        }

        $key = base64_decode($result, strict: true);
        if ($key === false) {
            throw new UnexpectedValueException('Key found, malformed');
        }

        return new KeyMaterial($key);
    }

    public function storeKey(string $identifier, KeyMaterial $key): void
    {
        // keys table is (currently) `unique(name)` so we can skip checking if it
        // already exists
        $encoded = base64_encode($key->key);
        // Cannot use sqlInsert, it doesn't have a noLog path
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `keys` (name, value) VALUES (?, ?)',
            [$identifier, $encoded],
            noLog: true,
        );
    }
}
