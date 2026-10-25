<?php

declare(strict_types=1);

namespace GeneralDermatology\Catalog;

use GeneralDermatology\App\ProcedurePlans;

/**
 * Single access point for procedure plan keys and display labels.
 */
final class ProcedureCatalog
{
    /** @var array<string, string>|null */
    private static ?array $plans = null;

    /** @var array<string, string>|null */
    private static ?array $displayValues = null;

    /**
     * @return array<string, string> key => display label
     */
    public static function plans(): array
    {
        if (self::$plans === null) {
            self::$plans = (new ProcedurePlans())->getPlans();
        }

        return self::$plans;
    }

    /**
     * @return array<string, string>
     */
    public static function displayValues(): array
    {
        if (self::$displayValues === null) {
            self::$displayValues = (new ProcedurePlans())->getDisplayValues();
        }

        return self::$displayValues;
    }

    public static function labelFor(string $procedureKey): string
    {
        $plans = self::plans();
        if (isset($plans[$procedureKey])) {
            return $plans[$procedureKey];
        }

        // Case-insensitive fallback
        foreach ($plans as $key => $label) {
            if (strcasecmp($key, $procedureKey) === 0) {
                return $label;
            }
        }

        return $procedureKey;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::plans());
    }

    public static function isKnown(string $procedureKey): bool
    {
        if ($procedureKey === '') {
            return false;
        }
        foreach (self::keys() as $key) {
            if (strcasecmp($key, $procedureKey) === 0) {
                return true;
            }
        }

        return false;
    }
}
