<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Util;

class TelehealthAuthUtils
{
    public static function getFormattedPassword($password): string
    {
        $hash = hash('sha256', (string) $password);
        return $hash;
    }
}
