<?php

declare(strict_types=1);

namespace OpenEMR\Config;

use BackedEnum;

/**
 * @template T
 */
interface Key extends BackedEnum
{
    /**
     * @return T
     */
    public static function cast(string $value): mixed;
}
