<?php

/**
 * FhirPayloadReader
 *
 * Guarded readers for the nested fragments of an inbound FHIR resource.
 *
 * `FHIRDomainResource::jsonSerialize()` returns the request payload largely as it
 * arrived: the R4 library hydrates the top-level element but not the structures
 * nested inside it, so `$json['code']['coding'][0]['code']` is a chain of reads
 * against values that are only `mixed` as far as the type system is concerned. A
 * client can legally send `"code": "foo"` where a CodeableConcept is expected, and
 * the naive chain then raises a PHP warning (or, on a string, silently indexes a
 * character) instead of being rejected.
 *
 * Every method here reads one level defensively and returns a value of its declared
 * type, so the write services can traverse a payload without repeating `is_array()`
 * at each hop. Absent, malformed, or wrongly-typed fragments collapse to the empty
 * value for the shape rather than throwing -- callers decide whether a missing
 * element is a validation error.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

final class FhirPayloadReader
{
    /**
     * Reads one key off a payload fragment that may not be an array.
     */
    public static function get(mixed $fragment, string|int $key): mixed
    {
        return is_array($fragment) ? ($fragment[$key] ?? null) : null;
    }

    /**
     * Reads one key off a payload fragment, keeping it only when it is a non-empty string.
     */
    public static function getString(mixed $fragment, string|int $key): ?string
    {
        $value = self::get($fragment, $key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Returns a FHIR Reference element's `reference` literal, or null when absent.
     */
    public static function reference(mixed $element): ?string
    {
        return self::getString($element, 'reference');
    }

    /**
     * Extracts the `coding` entries of a FHIR CodeableConcept.
     *
     * Entries that are not arrays are dropped rather than passed on to the callers'
     * offset reads.
     *
     * @return list<array<array-key, mixed>>
     */
    public static function codings(mixed $codeableConcept): array
    {
        $coding = self::get($codeableConcept, 'coding');
        if (!is_array($coding)) {
            return [];
        }
        $entries = [];
        foreach ($coding as $entry) {
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * Returns the first `coding` entry of a FHIR CodeableConcept, or [] when there is none.
     *
     * @return array<array-key, mixed>
     */
    public static function firstCoding(mixed $codeableConcept): array
    {
        return self::codings($codeableConcept)[0] ?? [];
    }

    /**
     * Returns one field of a CodeableConcept's first coding entry, or '' when absent.
     *
     * @param string $field The coding field to read ('code', 'display', 'system', ...)
     */
    public static function firstCodingValue(mixed $codeableConcept, string $field): string
    {
        return self::getString(self::firstCoding($codeableConcept), $field) ?? '';
    }

    /**
     * Returns the `code` of a CodeableConcept's first coding entry, or '' when absent.
     */
    public static function firstCodingCode(mixed $codeableConcept): string
    {
        return self::firstCodingValue($codeableConcept, 'code');
    }

    /**
     * Returns the `code` of the first coding of the first CodeableConcept in a 0..* list.
     *
     * `PractitionerRole.code`, `ServiceRequest.category` and friends are repeating
     * CodeableConcepts; this collapses the common "take the first one" read.
     */
    public static function firstConceptCode(mixed $concepts): string
    {
        return self::firstCodingCode(self::get($concepts, 0));
    }

    /**
     * Re-keys a payload fragment as the string-keyed array the OpenEMR services declare.
     *
     * @return array<string, mixed>
     */
    public static function stringKeyed(mixed $fragment): array
    {
        if (!is_array($fragment)) {
            return [];
        }
        $keyed = [];
        foreach ($fragment as $key => $value) {
            $keyed[(string) $key] = $value;
        }

        return $keyed;
    }

    /**
     * Normalizes a repeating payload fragment into the row list the OpenEMR services declare.
     *
     * @return list<array<string, mixed>>
     */
    public static function rows(mixed $fragment): array
    {
        if (!is_array($fragment)) {
            return [];
        }
        $rows = [];
        foreach ($fragment as $row) {
            if (is_array($row)) {
                $rows[] = self::stringKeyed($row);
            }
        }

        return $rows;
    }
}
