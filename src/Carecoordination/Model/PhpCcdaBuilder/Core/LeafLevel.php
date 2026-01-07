<?php

/**
 * LeafLevel.php - Leaf-level template helpers
 *
 * PHP port of oe-blue-button-generate/lib/leafLevel.js
 * Provides leaf-level attribute helpers and input property accessors.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core;

class LeafLevel
{
    /**
     * Reference counter for generating unique IDs
     */
    private static array $referenceCounters = [];

    /**
     * Table reference counter
     */
    private static array $tableReferenceCounters = [];

    /**
     * Return input as-is
     * JS: exports.input
     */
    public static function input($input)
    {
        return $input;
    }

    /**
     * Get a property from input
     * JS: exports.inputProperty
     */
    public static function inputProperty(string $key, $defaultValue = null): callable
    {
        return function ($input) use ($key, $defaultValue) {
            if (is_array($input) && isset($input[$key])) {
                return $input[$key];
            }
            return $defaultValue;
        };
    }

    /**
     * Get a deep property from input using dot notation
     * JS: exports.deepInputProperty
     */
    public static function deepInputProperty(string $path, $defaultValue = null): callable
    {
        return function ($input) use ($path, $defaultValue) {
            $value = self::getDeepValue($input, $path);
            return $value ?? $defaultValue;
        };
    }

    /**
     * Get deep value and format as date
     * JS: exports.deepInputDate
     */
    public static function deepInputDate(string $path, $defaultValue = null): callable
    {
        return function ($input) use ($path, $defaultValue) {
            $value = self::getDeepValue($input, $path);
            if ($value !== null) {
                return Translate::time($value);
            }
            return $defaultValue;
        };
    }

    /**
     * Code attributes
     * JS: exports.code (as static property)
     */
    public static array $code = [
        'code' => [self::class, '_codeValue'],
        'codeSystem' => [self::class, '_codeSystemValue'],
        'codeSystemName' => [self::class, '_codeSystemNameValue'],
        'displayName' => [self::class, '_displayNameValue'],
    ];

    public static function _codeValue($input): ?string
    {
        return $input['code'] ?? null;
    }

    public static function _codeSystemValue($input): ?string
    {
        return $input['code_system'] ?? null;
    }

    public static function _codeSystemNameValue($input): ?string
    {
        return $input['code_system_name'] ?? null;
    }

    public static function _displayNameValue($input): ?string
    {
        return $input['name'] ?? null;
    }

    /**
     * Get code attributes as array of closures
     * Used when leafLevel.code is referenced
     */
    public static function code(): array
    {
        return [
            'code' => fn($input) => $input['code'] ?? null,
            'codeSystem' => fn($input) => $input['code_system'] ?? null,
            'codeSystemName' => fn($input) => $input['code_system_name'] ?? null,
            'displayName' => fn($input) => $input['name'] ?? null,
        ];
    }

    /**
     * Code from name using a code system OID
     * JS: exports.codeFromName = translate.codeFromName;
     */
    public static function codeFromName(string $oid): callable
    {
        return fn($input) => Translate::codeFromName($oid, $input);
    }

    /**
     * Get only the code value from name
     * JS: exports.codeOnlyFromName
     */
    public static function codeOnlyFromName(string $oid, string $key): callable
    {
        return function ($input) use ($oid, $key) {
            if ($input && isset($input[$key])) {
                $result = Translate::codeFromName($oid, $input[$key]);
                return $result['code'] ?? null;
            }
            return null;
        };
    }

    /**
     * Time formatter
     * JS: exports.time = translate.time;
     */
    public static function time($input): ?string
    {
        return Translate::time($input);
    }

    /**
     * Use attribute formatter (e.g., for address use)
     * JS: exports.use
     */
    public static function use(string $key): callable
    {
        return function ($input) use ($key) {
            $value = $input[$key] ?? null;
            if ($value) {
                return Translate::acronymize($value);
            }
            return null;
        };
    }

    /**
     * Type CD constant for xsi:type attribute
     * JS: exports.typeCD = { "xsi:type": "CD" };
     */
    public static array $typeCD = ['xsi:type' => 'CD'];

    /**
     * Type CD - method version for compatibility
     */
    public static function typeCD(): array
    {
        return self::$typeCD;
    }

    /**
     * Type CE constant for xsi:type attribute
     * JS: exports.typeCE = { "xsi:type": "CE" };
     */
    public static array $typeCE = ['xsi:type' => 'CE'];

    /**
     * Type CE - method version for compatibility
     */
    public static function typeCE(): array
    {
        return self::$typeCE;
    }

    /**
     * Acronymize - delegate to Translate
     */
    public static function acronymize(string $value): string
    {
        return Translate::acronymize($value);
    }

    /**
     * Generate next reference ID
     * JS: exports.nextReference
     */
    public static function nextReference(string $prefix): callable
    {
        return function ($input) use ($prefix) {
            if (!isset(self::$referenceCounters[$prefix])) {
                self::$referenceCounters[$prefix] = 0;
            }
            self::$referenceCounters[$prefix]++;
            return '#' . $prefix . self::$referenceCounters[$prefix];
        };
    }

    /**
     * Generate same reference ID (doesn't increment)
     * JS: exports.sameReference
     */
    public static function sameReference(string $prefix): callable
    {
        return function ($input) use ($prefix) {
            $count = self::$referenceCounters[$prefix] ?? 1;
            return '#' . $prefix . $count;
        };
    }

    /**
     * Generate next table reference ID
     * JS: exports.nextTableReference
     */
    public static function nextTableReference(string $prefix): callable
    {
        return function ($input) use ($prefix) {
            if (!isset(self::$tableReferenceCounters[$prefix])) {
                self::$tableReferenceCounters[$prefix] = 0;
            }
            self::$tableReferenceCounters[$prefix]++;
            return $prefix . self::$tableReferenceCounters[$prefix];
        };
    }

    /**
     * Reset reference counters (call at start of new document)
     */
    public static function resetCounters(): void
    {
        self::$referenceCounters = [];
        self::$tableReferenceCounters = [];
    }

    /**
     * Get deep value from array using dot notation
     */
    public static function getDeepValue($input, string $path)
    {
        if (!is_array($input) || empty($path)) {
            return null;
        }

        $keys = explode('.', $path);
        $value = $input;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Boolean value formatter
     * JS: exports.booleanValue
     */
    public static function booleanValue(string $key): callable
    {
        return function ($input) use ($key) {
            $value = $input[$key] ?? null;
            if (in_array($value, [true, 'true', 1, '1'], true)) {
                return 'true';
            }
            if (in_array($value, [false, 'false', 0, '0'], true)) {
                return 'false';
            }
            return null;
        };
    }

    /**
     * Boolean input property - returns 'true' or 'false' string
     * JS: exports.boolInputProperty
     */
    public static function boolInputProperty(string $key): callable
    {
        return function ($input) use ($key) {
            $value = $input[$key] ?? null;
            if (in_array($value, [true, 'true', 1, '1', 'yes'], true)) {
                return 'true';
            }
            if (in_array($value, [false, 'false', 0, '0', 'no'], true)) {
                return 'false';
            }
            return null;
        };
    }

    /**
     * Physical quantity value
     * JS: exports.physicalQuantity
     */
    public static function physicalQuantity(string $valueKey, string $unitKey): callable
    {
        return fn($input) => [
            'value' => $input[$valueKey] ?? null,
            'unit' => $input[$unitKey] ?? null,
        ];
    }

    /**
     * Simple code with fixed code system
     * JS: exports.codeWithSystem
     */
    public static function codeWithSystem(string $codeSystem, string $codeSystemName): callable
    {
        return function ($input) use ($codeSystem, $codeSystemName) {
            if (!is_array($input)) {
                return [
                    'code' => $input,
                    'codeSystem' => $codeSystem,
                    'codeSystemName' => $codeSystemName,
                ];
            }
            return [
                'code' => $input['code'] ?? null,
                'displayName' => $input['name'] ?? $input['displayName'] ?? null,
                'codeSystem' => $codeSystem,
                'codeSystemName' => $codeSystemName,
            ];
        };
    }

    /**
     * Data absent reason
     * JS: exports.dataAbsentReason
     */
    public static function dataAbsentReason(string $reason = 'unknown'): array
    {
        return [
            'nullFlavor' => 'UNK',
            'sdtc:valueSet' => $reason,
        ];
    }
}
