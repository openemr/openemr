<?php

declare(strict_types=1);

namespace OpenEMR\Plugins\Activation;

class EnableAll implements StateProvider
{
    public function isActive(string $name): bool
    {
        return true;
    }
}
