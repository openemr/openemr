<?php

/**
 * A measure's parity fixture: generated QDM patients and the results the
 * reference engine (cqm-execution) computed for them. The PHP CQM engine is
 * expected to reproduce these results. capture.js in this directory writes
 * the fixtures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Parity;

final readonly class ParityFixture
{
    /**
     * @param list<array<mixed>> $patients QDM patients exactly as the engine receives them
     * @param array<string, array<string, PopulationSetResult>> $results patient id => population set or stratification id => result
     */
    public function __construct(
        public string $measure,
        public string $hqmfId,
        public string $reportingYear,
        public string $engine,
        public ?string $engineError,
        public array $patients,
        public array $results,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException("Unable to read parity fixture $path");
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \UnexpectedValueException("Parity fixture $path is not an object");
        }

        $patients = [];
        $patientList = $data['patients'] ?? null;
        if (!is_array($patientList) || !array_is_list($patientList)) {
            throw new \UnexpectedValueException("Parity fixture $path has no patient list");
        }
        foreach ($patientList as $patient) {
            if (!is_array($patient)) {
                throw new \UnexpectedValueException("Parity fixture $path has a malformed patient");
            }
            $patients[] = $patient;
        }

        $results = [];
        $resultMap = $data['results'] ?? null;
        if (!is_array($resultMap)) {
            throw new \UnexpectedValueException("Parity fixture $path has no results");
        }
        foreach ($resultMap as $patientId => $byKey) {
            if (!is_string($patientId) || !is_array($byKey)) {
                throw new \UnexpectedValueException("Parity fixture $path has malformed results");
            }
            $results[$patientId] = [];
            foreach ($byKey as $key => $result) {
                if (!is_string($key) || !is_array($result)) {
                    throw new \UnexpectedValueException("Parity fixture $path has a malformed result for $patientId");
                }
                $results[$patientId][$key] = PopulationSetResult::fromArray($result);
            }
        }

        $engineError = $data['engineError'] ?? null;
        if ($engineError !== null && !is_string($engineError)) {
            throw new \UnexpectedValueException("Parity fixture $path has a malformed engineError");
        }

        return new self(
            self::requireString($data, 'measure', $path),
            self::requireString($data, 'hqmfId', $path),
            self::requireString($data, 'reportingYear', $path),
            self::requireString($data, 'engine', $path),
            $engineError,
            $patients,
            $results,
        );
    }

    /**
     * The id the engine keys a patient's results by.
     *
     * @param array<mixed> $patient
     */
    public static function patientId(array $patient): string
    {
        $id = $patient['_id'] ?? null;
        if (!is_string($id)) {
            throw new \UnexpectedValueException('A parity patient has no _id');
        }
        return $id;
    }

    /**
     * @param array<mixed> $data
     */
    private static function requireString(array $data, string $key, string $path): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value)) {
            throw new \UnexpectedValueException("Parity fixture $path has no $key");
        }
        return $value;
    }
}
