<?php

/**
 * In-memory SharedTranslationStore for tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\SharedTranslationStore;

final class InMemoryTranslationStore implements SharedTranslationStore
{
    /** @var array<string, array{array<array-key, string>, int}> Definitions and TTL per key */
    public array $entries = [];

    public function fetch(string $key): ?array
    {
        return $this->entries[$key][0] ?? null;
    }

    public function store(string $key, array $definitions, int $ttlSeconds): void
    {
        $this->entries[$key] = [$definitions, $ttlSeconds];
    }
}
