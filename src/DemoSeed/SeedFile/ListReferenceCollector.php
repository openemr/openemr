<?php

/**
 * Gathers the list values a seed file uses while it's being parsed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final class ListReferenceCollector
{
    /** @var list<ListReference> */
    private array $references = [];

    public function add(string $listId, string $value, string $path): string
    {
        $this->references[] = new ListReference($listId, $value, $path);
        return $value;
    }

    /**
     * @param list<string> $values
     * @return list<string>
     */
    public function addAll(string $listId, array $values, string $path): array
    {
        foreach ($values as $value) {
            $this->add($listId, $value, $path);
        }
        return $values;
    }

    /**
     * @return list<ListReference>
     */
    public function all(): array
    {
        return $this->references;
    }
}
