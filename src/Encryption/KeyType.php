<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

enum KeyType
{
    case Encryption;
    case Hmac;
}
