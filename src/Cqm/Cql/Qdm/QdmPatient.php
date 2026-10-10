<?php

/**
 * A QDM patient as cqm-models builds it for cqm-execution: its data
 * elements cast by the cqm-models schemas (QdmSchema), and findRecords,
 * the retrieve the CQL engine calls.
 *
 * Attribute values are cast as the cqm-models mongoose types cast them; a
 * value its type rejects is left out, as a failed mongoose cast leaves the
 * attribute unset.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Qdm;

use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Ratio;
use OpenEMR\Cqm\Cql\Types\Tuple;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final class QdmPatient
{
    /** @var list<QdmObject> */
    public readonly array $dataElements;

    /** @var array<string, list<QdmObject|Tuple>> */
    private array $recordCache = [];

    /**
     * @param array<mixed> $patient a QDM patient as JSON-decoded from cqm-models' form
     */
    public function __construct(array $patient)
    {
        $id = $patient['_id'] ?? $patient['id'] ?? null;
        $id = is_scalar($id) ? (string) $id : '';
        // mongoose casts a 24-digit hex _id to an ObjectId, which prints in lower case.
        $this->id = preg_match('/^[0-9a-fA-F]{24}$/', $id) === 1 ? strtolower($id) : $id;
        $birth = $patient['birthDatetime'] ?? null;
        $this->birthDatetime = $birth === null ? null : self::castOrNull('DateTime', $birth);
        $elements = [];
        foreach (is_array($patient['dataElements'] ?? null) ? $patient['dataElements'] : [] as $element) {
            if (is_array($element) && is_string($element['_type'] ?? null)) {
                $elements[] = self::castObject(preg_replace('/^QDM::/', '', $element['_type']) ?? '', $element);
            }
        }
        $this->dataElements = $elements;
    }

    public readonly string $id;

    public readonly mixed $birthDatetime;

    /**
     * The records of a retrieve: profile is the ELM templateId or data
     * type. "Patient" gives the patient's birth date; a Positive or
     * Negative profile keeps the data elements without or with a negation
     * rationale.
     *
     * @return list<QdmObject|Tuple>
     */
    public function findRecords(string $profile): array
    {
        if (array_key_exists($profile, $this->recordCache)) {
            return $this->recordCache[$profile];
        }
        if ($profile === 'Patient') {
            return $this->recordCache[$profile] = [new Tuple(['birthDatetime' => $this->birthDatetime])];
        }
        $stripped = preg_replace('/ *\{[^)]*\} */', '', $profile) ?? $profile;
        $negated = null;
        if (!str_contains($profile, 'PatientCharacteristic')) {
            if (str_contains($stripped, 'Positive')) {
                $stripped = preg_replace('/Positive/', '', $stripped, 1) ?? $stripped;
                $negated = false;
            } elseif (str_contains($stripped, 'Negative')) {
                $stripped = preg_replace('/Negative/', '', $stripped, 1) ?? $stripped;
                $negated = true;
            }
        }
        $records = [];
        foreach ($this->dataElements as $element) {
            if ($element->typeName === $stripped && ($negated === null || $element->isNegated() === $negated)) {
                $records[] = $element;
            }
        }
        return $this->recordCache[$profile] = $records;
    }

    /**
     * A data element, entity or nested object cast by its schema, with
     * its defaults and empty lists filled in.
     *
     * @param array<mixed> $values
     */
    public static function castObject(string $typeName, array $values, bool $nested = false): QdmObject
    {
        $schema = ($nested ? QdmSchema::NESTED : QdmSchema::TYPES)[$typeName] ?? [];
        if (isset($values['_id']) && !isset($values['id']) && !$nested) {
            $values['id'] = $values['_id'];
        }
        if ($typeName === 'EncounterPerformed' && self::truthy($values['clazz'] ?? null)) {
            // cqm-models' EncounterPerformed takes class from clazz, a reserved word in Ruby.
            $values['class'] = $values['clazz'];
        }
        $fields = [];
        foreach ($schema as $name => $kind) {
            if (str_starts_with($kind, '=')) {
                $fields[$name] = array_key_exists($name, $values) ? $values[$name] : substr($kind, 1);
                continue;
            }
            if (!array_key_exists($name, $values)) {
                if (str_starts_with($kind, '[')) {
                    $fields[$name] = [];
                }
                continue;
            }
            try {
                $fields[$name] = self::cast($kind, $values[$name]);
            } catch (\RuntimeException) {
                // A rejected value leaves the attribute unset.
            }
        }
        if ($nested && isset($values['_id']) && is_scalar($values['_id'])) {
            // mongoose's id virtual of a sub-document
            $fields['id'] = (string) $values['_id'];
        }
        return new QdmObject($typeName, $fields, array_key_exists('dataElementCodes', $schema));
    }

    /**
     * Casts a value by a schema kind (see QdmSchema).
     *
     * @throws \UnexpectedValueException when the type rejects the value
     */
    public static function cast(string $kind, mixed $value): mixed
    {
        if (str_starts_with($kind, '[')) {
            $itemKind = substr($kind, 1, -1);
            if ($value === null) {
                return [];
            }
            $list = is_array($value) && array_is_list($value) ? $value : [$value];
            $cast = [];
            foreach ($list as $item) {
                $cast[] = self::cast($itemKind, $item);
            }
            return $cast;
        }
        if (str_starts_with($kind, '@')) {
            return is_array($value) ? self::castObject(substr($kind, 1), $value, true) : null;
        }
        return match ($kind) {
            'Code' => self::castCode($value),
            'DateTime' => self::castDateTime($value),
            'Date' => self::castDate($value),
            'Interval' => self::castInterval($value),
            'Quantity' => self::castQuantity($value),
            'Any' => self::castAny($value),
            'AnyEntity' => self::castEntity($value),
            default => $value,
        };
    }

    private static function castOrNull(string $kind, mixed $value): mixed
    {
        try {
            return self::cast($kind, $value);
        } catch (\RuntimeException) {
            return null;
        }
    }

    private static function castCode(mixed $value): ?Code
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value) && self::truthy($value['code'] ?? null) && self::truthy($value['system'] ?? null)) {
            return new Code(
                self::text($value['code']),
                self::text($value['system']),
                isset($value['version']) ? self::text($value['version']) : null,
                isset($value['display']) ? self::text($value['display']) : null,
            );
        }
        throw new \UnexpectedValueException('Expected a code');
    }

    private static function castDateTime(mixed $value): CqlDateTime
    {
        $dateTime = is_string($value) ? CqlDateTime::fromQdmString($value) : null;
        return $dateTime ?? throw new \UnexpectedValueException('Not a valid DateTime');
    }

    private static function castDate(mixed $value): ?CqlDate
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value) && array_key_exists('year', $value) && array_key_exists('month', $value) && array_key_exists('day', $value)) {
            return new CqlDate(self::intOrNull($value['year']), self::intOrNull($value['month']), self::intOrNull($value['day']));
        }
        $date = is_string($value) ? CqlDate::parse($value) : null;
        return $date ?? throw new \UnexpectedValueException('Not a valid Date');
    }

    private static function castInterval(mixed $value): Interval
    {
        if (!is_array($value)) {
            throw new \UnexpectedValueException('Not an interval');
        }
        $low = $value['low'] ?? null;
        $high = $value['high'] ?? null;
        $lowClosed = isset($value['lowClosed']) && is_bool($value['lowClosed']) ? $value['lowClosed'] : null;
        $highClosed = isset($value['highClosed']) && is_bool($value['highClosed']) ? $value['highClosed'] : null;
        if (is_array($low) && self::truthy($low['unit'] ?? null) && self::truthy($low['value'] ?? null)) {
            $low = self::castQuantity($low);
            if (is_array($high) && self::truthy($high['unit'] ?? null) && self::truthy($high['value'] ?? null)) {
                $high = self::castQuantity($high);
            }
            return new Interval($low, $high, $lowClosed, $highClosed);
        }
        if (self::truthy($low)) {
            $low = self::castDateTime($low);
        }
        if (self::truthy($high)) {
            $high = self::castDateTime($high);
        }
        return new Interval($low, $high, $lowClosed, $highClosed);
    }

    private static function castQuantity(mixed $value): Quantity
    {
        if (!is_array($value) || !array_key_exists('value', $value)) {
            throw new \UnexpectedValueException('Quantity does not have a value');
        }
        if (!array_key_exists('unit', $value)) {
            throw new \UnexpectedValueException('Quantity does not have a unit');
        }
        $number = $value['value'];
        if (is_string($number)) {
            $number = JavaScript::toNumber($number);
        }
        return new Quantity(is_int($number) || is_float($number) ? $number : null, $value['unit'] === null ? null : self::text($value['unit']));
    }

    /**
     * cqm-models' RecursiveCast of an attribute of any type.
     */
    private static function castAny(mixed $value): mixed
    {
        if (is_array($value) && !array_is_list($value)) {
            if (array_key_exists('value', $value) && self::truthy($value['unit'] ?? null)) {
                return self::castQuantity($value);
            }
            if (self::truthy($value['numerator'] ?? null) && self::truthy($value['denominator'] ?? null)) {
                return new Ratio(self::castQuantity($value['numerator']), self::castQuantity($value['denominator']));
            }
            if (self::truthy($value['code'] ?? null) && self::truthy($value['system'] ?? null)) {
                return self::castCode($value);
            }
            if (self::truthy($value['low'] ?? null)) {
                return self::castAnyInterval($value);
            }
            return $value;
        }
        if (is_array($value)) {
            return array_map(self::castAny(...), $value);
        }
        if (is_int($value) || (is_float($value) && is_finite($value))) {
            return $value;
        }
        if (is_string($value) && (JsDate::parses($value) || JsDate::parses("1984-01-01T$value"))) {
            if (str_contains($value, 'T') || str_contains($value, '+')) {
                return CqlDateTime::fromQdmString($value);
            }
            if (str_contains($value, ':')) {
                throw new \LogicException('CQL Time values are not supported');
            }
            return self::castDate($value);
        }
        return $value;
    }

    /**
     * @param array<mixed> $value
     */
    private static function castAnyInterval(array $value): Interval
    {
        $low = $value['low'] ?? null;
        $high = $value['high'] ?? null;
        $lowClosed = isset($value['lowClosed']) && is_bool($value['lowClosed']) ? $value['lowClosed'] : null;
        $highClosed = isset($value['highClosed']) && is_bool($value['highClosed']) ? $value['highClosed'] : null;
        if (is_array($low) && self::truthy($low['unit'] ?? null) && self::truthy($low['value'] ?? null)) {
            $low = self::castQuantity($low);
            if (is_array($high) && self::truthy($high['unit'] ?? null) && self::truthy($high['value'] ?? null)) {
                $high = self::castQuantity($high);
            }
            return new Interval($low, $high, $lowClosed, $highClosed);
        }
        if (is_string($low) && JsDate::parses($low)) {
            $low = CqlDateTime::fromQdmString($low);
        }
        if (is_string($high) && JsDate::parses($high)) {
            $high = CqlDateTime::fromQdmString($high);
        }
        return new Interval($low, $high, $lowClosed, $highClosed);
    }

    private static function castEntity(mixed $value): ?QdmObject
    {
        if ($value === null) {
            return null;
        }
        if (!is_array($value) || !is_string($value['_type'] ?? null)) {
            throw new \UnexpectedValueException('Could not find _type indicator for entity.');
        }
        $type = preg_replace('/^QDM::/', '', $value['_type']) ?? '';
        if (!in_array($type, ['PatientEntity', 'Practitioner', 'CarePartner', 'Organization', 'Location'], true)) {
            throw new \UnexpectedValueException("Could not find entity type $type");
        }
        return self::castObject($type, $value);
    }

    /** JavaScript truthiness of a JSON value. */
    private static function truthy(mixed $value): bool
    {
        return !($value === null || $value === false || $value === 0 || $value === 0.0 || $value === '' || (is_float($value) && is_nan($value)));
    }

    private static function text(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => JavaScript::numberToString($value),
            is_bool($value) => $value ? 'true' : 'false',
            default => throw new \UnexpectedValueException('Not text'),
        };
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }
}
