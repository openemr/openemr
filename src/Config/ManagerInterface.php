<?php

declare(strict_types=1);

namespace OpenEMR\Config;

interface ManagerInterface
{
    /**
     * @template T
     * @param Key<T> $key
     * @return T
     */
    public function getCurrentValue(Key $key): mixed;
}
