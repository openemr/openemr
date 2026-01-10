<?php

/**
 * Translate.php - Translation helper functions
 *
 * PHP port of oe-blue-button-generate/lib/translate.js
 * Provides code translation and formatting utilities.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core;

class Translate
{
    /**
     * Value sets for codeFromName translations
     */
    private static array $valueSets = [
        // Problem Status
        '2.16.840.1.113883.3.88.12.80.68' => [
            'Active' => ['code' => '55561003', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Inactive' => ['code' => '73425007', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Resolved' => ['code' => '413322009', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
        ],
        // Smoking Status
        '2.16.840.1.113883.11.20.9.38' => [
            'Current every day smoker' => ['code' => '449868002', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Current some day smoker' => ['code' => '428041000124106', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Former smoker' => ['code' => '8517006', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Never smoker' => ['code' => '266919005', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Smoker, current status unknown' => ['code' => '77176002', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Unknown if ever smoked' => ['code' => '266927001', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Heavy tobacco smoker' => ['code' => '428071000124103', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
            'Light tobacco smoker' => ['code' => '428061000124105', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED CT'],
        ],
        // Administrative Gender
        '2.16.840.1.113883.5.1' => [
            'Male' => ['code' => 'M', 'codeSystem' => '2.16.840.1.113883.5.1', 'codeSystemName' => 'AdministrativeGender'],
            'Female' => ['code' => 'F', 'codeSystem' => '2.16.840.1.113883.5.1', 'codeSystemName' => 'AdministrativeGender'],
            'Undifferentiated' => ['code' => 'UN', 'codeSystem' => '2.16.840.1.113883.5.1', 'codeSystemName' => 'AdministrativeGender'],
        ],
        // Age Unit
        '2.16.840.1.113883.11.20.9.21' => [
            'min' => ['code' => 'min', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'h' => ['code' => 'h', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'd' => ['code' => 'd', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'wk' => ['code' => 'wk', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'mo' => ['code' => 'mo', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'a' => ['code' => 'a', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'year' => ['code' => 'a', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'years' => ['code' => 'a', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'month' => ['code' => 'mo', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'months' => ['code' => 'mo', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'week' => ['code' => 'wk', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'weeks' => ['code' => 'wk', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'day' => ['code' => 'd', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'days' => ['code' => 'd', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'hour' => ['code' => 'h', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'hours' => ['code' => 'h', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'minute' => ['code' => 'min', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
            'minutes' => ['code' => 'min', 'codeSystem' => '2.16.840.1.113883.6.8', 'codeSystemName' => 'UCUM'],
        ],
        // Observation Interpretation
        '2.16.840.1.113883.5.83' => [
            'H' => ['code' => 'H', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'High'],
            'L' => ['code' => 'L', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'Low'],
            'N' => ['code' => 'N', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'Normal'],
            'A' => ['code' => 'A', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'Abnormal'],
            'HH' => ['code' => 'HH', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'Critical High'],
            'LL' => ['code' => 'LL', 'codeSystem' => '2.16.840.1.113883.5.83', 'codeSystemName' => 'ObservationInterpretation', 'displayName' => 'Critical Low'],
        ],
        // Act Reason (for immunization refusal)
        '2.16.840.1.113883.5.8' => [
            'IMMUNE' => ['code' => 'IMMUNE', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'MEDPREC' => ['code' => 'MEDPREC', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'OSTOCK' => ['code' => 'OSTOCK', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'PATOBJ' => ['code' => 'PATOBJ', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'PHILISOP' => ['code' => 'PHILISOP', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'RELIG' => ['code' => 'RELIG', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'VACEFF' => ['code' => 'VACEFF', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
            'VACSAF' => ['code' => 'VACSAF', 'codeSystem' => '2.16.840.1.113883.5.8', 'codeSystemName' => 'ActReason'],
        ],
    ];

    /**
     * Translate code from name using value set
     */
    public static function codeFromName(string $oid, $input): array
    {
        if (!$input) {
            return [];
        }

        $name = is_array($input) ? ($input['name'] ?? $input['value'] ?? '') : (string)$input;

        if (isset(self::$valueSets[$oid][$name])) {
            $result = self::$valueSets[$oid][$name];
            $result['displayName'] = $name;
            return $result;
        }

        // Return input as-is if not found in value set
        if (is_array($input)) {
            return [
                'code' => $input['code'] ?? null,
                'codeSystem' => $input['code_system'] ?? $oid,
                'codeSystemName' => $input['code_system_name'] ?? null,
                'displayName' => $input['name'] ?? null,
            ];
        }

        return [
            'code' => $name,
            'codeSystem' => $oid,
            'displayName' => $name,
        ];
    }

    /**
     * Translate time/date input to HL7 format
     */
    public static function time($input): ?string
    {
        if (!$input) {
            return null;
        }

        // If it's already a properly formatted string
        if (is_string($input)) {
            // Check if it's already in HL7 format (YYYYMMDD or YYYYMMDDHHMMSS)
            if (preg_match('/^\d{8,14}$/', $input)) {
                return $input;
            }

            // Try to parse as date
            $timestamp = strtotime($input);
            if ($timestamp !== false) {
                return date('YmdHis', $timestamp);
            }

            return $input;
        }

        // If it's an array with date/precision
        if (is_array($input)) {
            $date = $input['date'] ?? $input['point'] ?? null;
            $precision = $input['precision'] ?? null;

            if ($date) {
                return self::formatDate($date, $precision);
            }
        }

        return null;
    }

    /**
     * Format date with optional precision
     */
    public static function formatDate($date, $precision = null): ?string
    {
        if (!$date) {
            return null;
        }

        $timestamp = is_numeric($date) ? $date : strtotime((string) $date);
        if ($timestamp === false) {
            return is_string($date) ? $date : null;
        }

        return match ($precision) {
            'year' => date('Y', $timestamp),
            'month' => date('Ym', $timestamp),
            'day' => date('Ymd', $timestamp),
            'hour' => date('YmdH', $timestamp),
            'minute' => date('YmdHi', $timestamp),
            default => date('YmdHis', $timestamp),
        };
    }

    /**
     * Acronymize address use
     */
    public static function acronymize($value): ?string
    {
        if (!$value) {
            return null;
        }

        $map = [
            'home' => 'HP',
            'work' => 'WP',
            'mobile' => 'MC',
            'primary home' => 'HP',
            'vacation home' => 'HV',
            'workplace' => 'WP',
            'public' => 'PUB',
            'bad address' => 'BAD',
            'temporary' => 'TMP',
            'direct' => 'DIR',
            'confidential' => 'CONF',
        ];

        $lower = strtolower(trim((string) $value));
        return $map[$lower] ?? strtoupper((string) $value);
    }

    /**
     * Translate code attributes from input
     */
    public static function code($input): array
    {
        if (!$input || !is_array($input)) {
            return [];
        }

        return array_filter([
            'code' => $input['code'] ?? null,
            'codeSystem' => $input['code_system'] ?? null,
            'codeSystemName' => $input['code_system_name'] ?? null,
            'displayName' => $input['name'] ?? null,
        ]);
    }

    /**
     * Transform name for usRealmName
     */
    public static function name($input): ?array
    {
        if (!$input) {
            return null;
        }

        // If already in correct format
        if (isset($input['family']) || isset($input['given'])) {
            return $input;
        }

        // If it's a simple string
        if (is_string($input)) {
            $parts = explode(' ', $input);
            return [
                'given' => [array_shift($parts)],
                'family' => implode(' ', $parts) ?: null,
            ];
        }

        // Try to extract from common formats
        $result = [];

        if (isset($input['last']) || isset($input['last_name'])) {
            $result['family'] = $input['last'] ?? $input['last_name'];
        }

        if (isset($input['first']) || isset($input['first_name'])) {
            $given = [$input['first'] ?? $input['first_name']];
            if (isset($input['middle']) || isset($input['middle_name'])) {
                $given[] = $input['middle'] ?? $input['middle_name'];
            }
            $result['given'] = $given;
        }

        if (isset($input['prefix'])) {
            $result['prefix'] = $input['prefix'];
        }

        if (isset($input['suffix'])) {
            $result['suffix'] = $input['suffix'];
        }

        return $result ?: $input;
    }

    /**
     * Transform telecom
     */
    public static function telecom($input): ?array
    {
        if (!$input) {
            return null;
        }

        if (is_string($input)) {
            return ['value' => $input];
        }

        if (!is_array($input)) {
            return null;
        }

        $result = [];

        // Handle phone/email/fax
        if (isset($input['phone']) || isset($input['number'])) {
            $number = $input['phone'] ?? $input['number'];
            // Ensure number is a string
            if (is_array($number)) {
                $number = $number[0] ?? '';
            }
            $number = (string)$number;
            if ($number && !str_starts_with($number, 'tel:')) {
                $number = 'tel:' . preg_replace('/[^0-9+]/', '', $number);
            }
            $result['value'] = $number;
            $result['use'] = $input['use'] ?? 'WP';
        } elseif (isset($input['email'])) {
            $email = $input['email'];
            if (is_array($email)) {
                $email = $email[0] ?? '';
            }
            $email = (string)$email;
            if ($email && !str_starts_with($email, 'mailto:')) {
                $email = 'mailto:' . $email;
            }
            $result['value'] = $email;
        } elseif (isset($input['value'])) {
            $value = $input['value'];
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }
            $result['value'] = (string)$value;
            if (isset($input['use'])) {
                $result['use'] = $input['use'];
            }
        }

        return $result ?: $input;
    }
}
