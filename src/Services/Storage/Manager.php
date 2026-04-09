<?php

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

use League\Flysystem\FilesystemOperator;

class Manager
{
    public function getStorage(Location $location): FilesystemOperator
    {
    }
}
