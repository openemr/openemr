<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Activation;

class DisableAll implements StateProvider
{
    public function isActive(string $name): bool
    {
        return false;
    }
}
