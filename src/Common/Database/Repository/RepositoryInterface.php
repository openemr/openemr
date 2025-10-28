<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database\Repository;

interface RepositoryInterface
{
    public function getTable(): string;

    public function normalize(array $data): array;

    public function insert(array $data): int;

    public function update(array $data, array $condition): int;

    public function count(): int;

    public function countBy(array $condition): int;

    public function getSingleScalarResultBy(string $column, array $condition): null|string|int;

    public function getSingleColumnResultBy(string $column, array $condition): array;

    public function findOneBy(array $condition): null|array;

    public function findAll(): array;

    public function findBy(array $condition): array;

    public function removeAll(): int;

    public function removeBy(array $condition): int;
}
