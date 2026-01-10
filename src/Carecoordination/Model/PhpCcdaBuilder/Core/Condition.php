<?php

/**
 * Condition.php - Conditional logic for CCDA template generation
 *
 * PHP port of oe-blue-button-generate/lib/condition.js
 * Provides condition functions used in existsWhen clauses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core;

class Condition
{
    /**
     * Check if a key exists in input
     * JS: exports.keyExists
     */
    public static function keyExists(string $key): callable
    {
        return fn($input) => self::getDeepValue($input, $key) !== null;
    }

    /**
     * Check if a key does not exist in input
     * JS: exports.keyDoesntExist
     */
    public static function keyDoesntExist(string $key): callable
    {
        return fn($input) => self::getDeepValue($input, $key) === null;
    }

    /**
     * Check if a property equals a specific value
     * JS: exports.propertyEquals
     */
    public static function propertyEquals(string $key, $value): callable
    {
        return function ($input) use ($key, $value) {
            $actual = self::getDeepValue($input, $key);
            return $actual === $value;
        };
    }

    /**
     * Check if a property does not equal a specific value
     * JS: exports.propertyNotEquals
     */
    public static function propertyNotEquals(string $key, $value): callable
    {
        return function ($input) use ($key, $value) {
            $actual = self::getDeepValue($input, $key);
            return $actual !== $value;
        };
    }

    /**
     * Check if a property is not empty
     * JS: exports.propertyNotEmpty
     */
    public static function propertyNotEmpty(string $key): callable
    {
        return function ($input) use ($key) {
            $value = self::getDeepValue($input, $key);
            return !empty($value);
        };
    }

    /**
     * Check if a property value is empty
     * JS: exports.propertyValueEmpty
     */
    public static function propertyValueEmpty(string $key): callable
    {
        return function ($input) use ($key) {
            $value = self::getDeepValue($input, $key);
            return empty($value);
        };
    }

    /**
     * Alias for propertyValueEmpty
     * JS: exports.propertyEmpty
     */
    public static function propertyEmpty(string $key): callable
    {
        return self::propertyValueEmpty($key);
    }

    /**
     * Check if a property value is not empty
     * JS: exports.propertyValueNotEmpty
     */
    public static function propertyValueNotEmpty(string $key): callable
    {
        return function ($input) use ($key) {
            $value = self::getDeepValue($input, $key);
            return !empty($value);
        };
    }

    /**
     * Check if input is a NullFlavor section
     * Returns attributes for nullFlavor if section is empty
     */
    public static function isNullFlavorSection(string $sectionKey): callable
    {
        return function ($input) use ($sectionKey) {
            $data = self::getDeepValue($input, $sectionKey);
            if (empty($data)) {
                return ['nullFlavor' => 'NI'];
            }
            return [];
        };
    }

    /**
     * Combine multiple conditions with AND logic
     * JS: exports.eitherKeyExists (but as AND)
     */
    public static function allTrue(callable ...$conditions): callable
    {
        return function ($input) use ($conditions) {
            foreach ($conditions as $condition) {
                if (!$condition($input)) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * Combine multiple conditions with OR logic
     * JS: exports.eitherKeyExists
     */
    public static function anyTrue(callable ...$conditions): callable
    {
        return function ($input) use ($conditions) {
            foreach ($conditions as $condition) {
                if ($condition($input)) {
                    return true;
                }
            }
            return false;
        };
    }

    /**
     * Negate a condition
     */
    public static function not(callable $condition): callable
    {
        return fn($input) => !$condition($input);
    }

    /**
     * Check if either of the specified keys exists
     * JS: exports.eitherKeyExists
     */
    public static function eitherKeyExists(string ...$keys): callable
    {
        return function ($input) use ($keys) {
            foreach ($keys as $key) {
                if (self::getDeepValue($input, $key) !== null) {
                    return true;
                }
            }
            return false;
        };
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
}
