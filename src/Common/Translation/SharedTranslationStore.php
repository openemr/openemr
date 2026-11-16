<?php

/**
 * Cache of a language's definitions shared by all requests on a server.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

interface SharedTranslationStore
{
    /**
     * @return array<array-key, string>|null The definitions keyed by constant
     *         name, or null when the key is not cached.
     */
    public function fetch(string $key): ?array;

    /**
     * @param array<array-key, string> $definitions
     */
    public function store(string $key, array $definitions, int $ttlSeconds): void;
}
