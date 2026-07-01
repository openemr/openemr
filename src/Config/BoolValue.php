<?php

declare(strict_types=1);

namespace OpenEMR\Config;

/**
 * @implements Key<bool>
 */
enum BoolValue: string implements Key
{
    case SimplifiedCopay = 'simplified_copay';
    case UseChargesPanel = 'use_charges_panel';

    public static function cast(string $value): bool
    {
        return (bool)$value;
    }
}
