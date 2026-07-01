<?php

declare(strict_types=1);

namespace OpenEMR\Config\Manager;

use OpenEMR\Config\{Key, ManagerInterface};

class GlobalsTableManager implements ManagerInterface
{
    public function getCurrentValue(Key $key): mixed
    {
        // SELECT * FROM globals WHERE gl_key = ?
        //
        return $key::cast($key->value);
    }
}
