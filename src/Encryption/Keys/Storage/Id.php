<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys\Storage;

enum Id
{
    case PlaintextDisk;
    case PlaintextDatabase;
    // enc on disk w/ backing in db
}
