<?php

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

enum Location
{
    case Documents;

    // More values to come: config, certificates, etc.
}
