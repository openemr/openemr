<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dermatology Module
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace GeneralDermatology\App;

use RuntimeException;

/**
 * Provides the curated list of favourite ICD-10 diagnosis codes and their
 * associated physical-description options for the general dermatology form.
 *
 * Data is stored in config/diagnosis_codes.json and
 * config/diagnosis_descriptions.json so that it can be maintained without
 * touching PHP source code.
 */
class FavoriteDiagnosisCodes
{
    private const CONFIG_DIR = __DIR__ . '/../../config';

    /** @var array<string, string>|null */
    private static ?array $cachedCodes = null;

    /** @var array<string, list<string>>|null */
    private static ?array $cachedDescriptions = null;

    /**
     * Returns a sorted associative array of ICD-10 code keys mapped to their
     * display labels.
     *
     * @return array<string, string>
     */
    public function codeArray(): array
    {
        if (self::$cachedCodes === null) {
            self::$cachedCodes = self::loadJson('diagnosis_codes.json');
        }
        return self::$cachedCodes;
    }

    /**
     * Returns an associative array of ICD-10 code keys mapped to an ordered
     * list of physical-description strings used for auto-fill suggestions.
     *
     * @return array<string, list<string>>
     */
    public static function diagnosisAndDescription(): array
    {
        if (self::$cachedDescriptions === null) {
            self::$cachedDescriptions = self::loadJson('diagnosis_descriptions.json');
        }
        return self::$cachedDescriptions;
    }

    /**
     * Loads and JSON-decodes a config file from the module config directory.
     *
     * @param  string $filename
     * @return array<string, mixed>
     *
     * @throws RuntimeException when the file is missing or contains invalid JSON.
     */
    private static function loadJson(string $filename): array
    {
        $path = self::CONFIG_DIR . '/' . $filename;

        if (!is_readable($path)) {
            throw new RuntimeException(
                "FavoriteDiagnosisCodes: config file not readable: $path"
            );
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException(
                "FavoriteDiagnosisCodes: failed to read config file: $path"
            );
        }

        $data = json_decode($contents, true);
        if (!is_array($data)) {
            throw new RuntimeException(
                "FavoriteDiagnosisCodes: invalid JSON in config file: $path"
            );
        }

        return $data;
    }
}
