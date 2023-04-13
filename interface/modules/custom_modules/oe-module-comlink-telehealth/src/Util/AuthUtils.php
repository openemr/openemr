<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Util;

class AuthUtils
{
    public static function getFormattedPassword($password)
    {
        $hash = hash('sha256', $password);
        return $hash;
    }
}
