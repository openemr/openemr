<?php

declare(strict_types=1);

namespace OpenEMR\Config\Reader;

use OpenEMR\Config\{Key, ReaderInterface};

class GlobalsTableReader implements ReaderInterface
{
    public function getCurrentValue(Key $key): mixed
    {
        // SELECT * FROM globals WHERE gl_key = ?
        //
        return $key::cast($key->value);
    }
}
