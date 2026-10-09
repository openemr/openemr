<?php

/**
 * Whether a value is of an ELM type, as cql-execution's Context decides it
 * for As, Is and function overload resolution. A QDM data element answers
 * for its own types; other objects match any type that is not a system
 * type, a list or an interval.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Qdm\QdmObject;
use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\Concept;
use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Tuple;

final class TypeMatcher
{
    private const SYSTEM = '{urn:hl7-org:elm-types:r1}';

    /**
     * @param array<mixed>|null $spec an ELM type specifier
     */
    public static function matches(mixed $value, ?array $spec): bool
    {
        return match ($spec['type'] ?? null) {
            'NamedTypeSpecifier' => self::matchesNamed($value, is_string($spec['name'] ?? null) ? $spec['name'] : ''),
            'ListTypeSpecifier' => is_array($value)
                && array_reduce($value, static fn (bool $ok, mixed $item): bool => $ok && self::matches($item, self::spec($spec['elementType'] ?? null)), true),
            'TupleTypeSpecifier' => self::matchesTuple($value, $spec),
            'IntervalTypeSpecifier' => self::matchesInterval($value, self::spec($spec['pointType'] ?? null)),
            'ChoiceTypeSpecifier' => self::matchesChoice($value, $spec['choice'] ?? null),
            default => true,
        };
    }

    /**
     * Whether a specifier names only system types (Is on a value without a
     * type hierarchy needs one).
     *
     * @param array<mixed>|null $spec
     */
    public static function isSystemType(?array $spec): bool
    {
        switch ($spec['type'] ?? null) {
            case 'NamedTypeSpecifier':
                return is_string($spec['name'] ?? null) && str_starts_with($spec['name'], self::SYSTEM);
            case 'ListTypeSpecifier':
                return self::isSystemType(self::spec($spec['elementType'] ?? null));
            case 'TupleTypeSpecifier':
                foreach (is_array($spec['element'] ?? null) ? $spec['element'] : [] as $element) {
                    if (!self::isSystemType(self::spec(is_array($element) ? ($element['elementType'] ?? null) : null))) {
                        return false;
                    }
                }
                return true;
            case 'IntervalTypeSpecifier':
                return self::isSystemType(self::spec($spec['pointType'] ?? null));
            case 'ChoiceTypeSpecifier':
                foreach (is_array($spec['choice'] ?? null) ? $spec['choice'] : [] as $choice) {
                    if (!self::isSystemType(self::spec($choice))) {
                        return false;
                    }
                }
                return true;
            default:
                return false;
        }
    }

    /**
     * @return array<mixed>|null
     */
    public static function spec(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    private static function matchesNamed(mixed $value, string $name): bool
    {
        if ($value === null) {
            return true;
        }
        return match ($name) {
            self::SYSTEM . 'Boolean' => is_bool($value),
            self::SYSTEM . 'Decimal' => is_int($value) || is_float($value),
            self::SYSTEM . 'Integer' => (is_int($value) || is_float($value)) && floor($value) == $value,
            self::SYSTEM . 'String' => is_string($value),
            self::SYSTEM . 'Concept' => $value instanceof Concept,
            self::SYSTEM . 'Code' => $value instanceof Code,
            self::SYSTEM . 'DateTime' => $value instanceof CqlDateTime,
            self::SYSTEM . 'Date' => $value instanceof CqlDate,
            self::SYSTEM . 'Quantity' => $value instanceof Quantity,
            self::SYSTEM . 'Time' => $value instanceof CqlDateTime && $value->isTime(),
            default => self::matchesModelType($value, $name),
        };
    }

    private static function matchesModelType(mixed $value, string $name): bool
    {
        if ($value instanceof QdmObject && $value->isDataElement) {
            return in_array($name, $value->typeHierarchy(), true);
        }
        return !is_array($value) && !$value instanceof Interval;
    }

    /**
     * @param array<mixed> $spec
     */
    private static function matchesTuple(mixed $value, array $spec): bool
    {
        if (!$value instanceof Tuple && !$value instanceof QdmObject) {
            return false;
        }
        foreach (is_array($spec['element'] ?? null) ? $spec['element'] : [] as $element) {
            if (!is_array($element) || !is_string($element['name'] ?? null)) {
                continue;
            }
            [$present, $item] = Scope::ownProperty($value, $element['name']);
            if ($present && !self::matches($item, self::spec($element['elementType'] ?? null))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array<mixed>|null $pointType
     */
    private static function matchesInterval(mixed $value, ?array $pointType): bool
    {
        if ($value === null) {
            // cql-execution reads isInterval of null, a TypeError.
            throw new \UnexpectedValueException('Cannot match null against an interval type');
        }
        return $value instanceof Interval
            && ($value->low === null || self::matches($value->low, $pointType))
            && ($value->high === null || self::matches($value->high, $pointType));
    }

    private static function matchesChoice(mixed $value, mixed $choices): bool
    {
        foreach (is_array($choices) ? $choices : [] as $choice) {
            if (self::matches($value, self::spec($choice))) {
                return true;
            }
        }
        return false;
    }
}
