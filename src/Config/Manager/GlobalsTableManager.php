<?php

declare(strict_types=1);

namespace OpenEMR\Config\Manager;

use OpenEMR\Config\{Key, ManagerInterface};

class GlobalsTableManager implements ManagerInterface
{
    /**
     * @var ?array<string, string>
     */
    private ?array $cache = null;

    public function getCurrentValue(Key $key): mixed
    {
        if ($this->cache === null) {
            // SELECT gl_name, gl_value FROM globals
            // memoize the result
        }
        return $key::cast($this->cache[$key->value]);
    }

    public function setValue(Key $key, mixed $newValue): void
    {
        // upsert globals...
    }
}
