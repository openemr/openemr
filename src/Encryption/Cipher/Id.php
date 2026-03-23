<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

enum Id
{
    case Aes256CbcNoHmac;
    case Aes256CbcHmacSha256;
    case Aes256CbcHmacSha384;
}
