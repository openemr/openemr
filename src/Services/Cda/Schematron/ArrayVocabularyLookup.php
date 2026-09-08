<?php

/**
 * ArrayVocabularyLookup - VocabularyLookup backed by an in-memory OID => values map.
 *
 * The generated `schemas/<type>/vocab.php` files return exactly this shape:
 * `[oid => [value, value, ...], ...]`. Load with `require` and construct.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ArrayVocabularyLookup implements VocabularyLookup
{
    /**
     * @param array<string, list<string>> $map
     */
    public function __construct(private array $map)
    {
    }

    public static function fromFile(string $path): self
    {
        $map = require $path;
        if (!is_array($map)) {
            throw new \RuntimeException("vocab file did not return an array: $path");
        }
        /** @var array<string, list<string>> $map */
        return new self($map);
    }

    public function getValuesForOid(string $oid): ?array
    {
        return $this->map[$oid] ?? null;
    }
}
