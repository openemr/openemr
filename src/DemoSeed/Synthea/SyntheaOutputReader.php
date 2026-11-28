<?php

/**
 * Reads a Synthea output directory (ccda/ and fhir/) into SyntheaPatients,
 * in a stable order: sorted by CCDA file name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Synthea;

use OpenEMR\DemoSeed\PostalAddress;

final class SyntheaOutputReader
{
    private const HL7_NAMESPACE = 'urn:hl7-org:v3';
    private const SYNTHEA_AUTHORITY = 'https://github.com/synthetichealth/synthea';

    /**
     * @return list<SyntheaPatient>
     */
    public function read(string $outputDirectory): array
    {
        $directory = rtrim($outputDirectory, '/');
        $ccdaFiles = glob($directory . '/ccda/*.xml');
        if ($ccdaFiles === false || $ccdaFiles === []) {
            throw new SyntheaException('Synthea produced no CCDA files in ' . $directory . '/ccda');
        }
        sort($ccdaFiles, SORT_STRING);

        $patients = [];
        foreach ($ccdaFiles as $ccdaFile) {
            $syntheaId = $this->readSyntheaId($ccdaFile);
            $patients[] = $this->readFhir($syntheaId, $ccdaFile, $this->findFhirFile($directory, $syntheaId));
        }
        return $patients;
    }

    /**
     * Patients Synthea wrote a FHIR file for but no CCDA: its CCDA exporter
     * fails for some patients (seen: "Person does not have insurance at
     * time ..." for a birthday just after the reference date), and those
     * patients can't be imported.
     *
     * @return list<string> FHIR file names without the extension
     */
    public function fhirWithoutCcda(string $outputDirectory): array
    {
        $directory = rtrim($outputDirectory, '/');
        $names = static fn (string $pattern): array => array_map(
            static fn (string $file): string => pathinfo($file, PATHINFO_FILENAME),
            glob($pattern) ?: [],
        );
        $missing = array_values(array_diff($names($directory . '/fhir/*.json'), $names($directory . '/ccda/*.xml')));
        sort($missing, SORT_STRING);
        return $missing;
    }

    public function readSyntheaId(string $ccdaFile): string
    {
        $document = new \DOMDocument();
        if (!$document->load($ccdaFile, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE)) {
            throw new SyntheaException('Could not parse CCDA file ' . $ccdaFile);
        }
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('h', self::HL7_NAMESPACE);
        $query = sprintf(
            '/h:ClinicalDocument/h:recordTarget/h:patientRole/h:id[@assigningAuthorityName="%s"]/@extension',
            self::SYNTHEA_AUTHORITY,
        );
        $nodes = $xpath->query($query);
        $node = $nodes === false ? null : $nodes->item(0);
        $id = $node === null ? '' : trim($node->nodeValue ?? '');
        if ($id === '') {
            throw new SyntheaException('No Synthea patient id in CCDA file ' . $ccdaFile);
        }
        return $id;
    }

    private function findFhirFile(string $directory, string $syntheaId): string
    {
        $matches = glob($directory . '/fhir/*_' . $syntheaId . '.json');
        if ($matches === false || count($matches) !== 1) {
            throw new SyntheaException('Expected one FHIR file for Synthea patient ' . $syntheaId);
        }
        return $matches[0];
    }

    public function readFhir(string $syntheaId, string $ccdaFile, string $fhirFile): SyntheaPatient
    {
        $raw = file_get_contents($fhirFile);
        if ($raw === false) {
            throw new SyntheaException('Could not read FHIR file ' . $fhirFile);
        }
        try {
            $bundle = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SyntheaException('FHIR file is not valid JSON: ' . $fhirFile, 0, $e);
        }
        unset($raw);

        $entries = is_array($bundle) ? ($bundle['entry'] ?? null) : null;
        if (!is_array($entries)) {
            throw new SyntheaException('FHIR file has no bundle entries: ' . $fhirFile);
        }

        $patient = null;
        $latestClaimDate = '';
        $payer = null;
        foreach ($entries as $entry) {
            $resource = is_array($entry) ? ($entry['resource'] ?? null) : null;
            if (!is_array($resource)) {
                continue;
            }
            $type = $resource['resourceType'] ?? null;
            if ($type === 'Patient') {
                $patient = $resource;
            } elseif ($type === 'ExplanationOfBenefit') {
                $created = $resource['created'] ?? null;
                if (is_string($created) && strcmp($created, $latestClaimDate) >= 0) {
                    $latestClaimDate = $created;
                    $payer = $this->insuranceDisplay($resource);
                }
            }
        }
        if ($patient === null) {
            throw new SyntheaException('FHIR file has no Patient: ' . $fhirFile);
        }

        [$firstName, $middleName, $lastName] = $this->officialName($patient, $fhirFile);
        $birthDate = $patient['birthDate'] ?? null;
        $parsedBirthDate = is_string($birthDate)
            ? \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate, new \DateTimeZone('UTC'))
            : false;
        if ($parsedBirthDate === false) {
            throw new SyntheaException('FHIR Patient has no birth date: ' . $fhirFile);
        }
        $sex = $patient['gender'] ?? null;

        return new SyntheaPatient(
            syntheaId: $syntheaId,
            ccdaFile: $ccdaFile,
            fhirFile: $fhirFile,
            firstName: $firstName,
            lastName: $lastName,
            birthDate: $parsedBirthDate,
            sex: is_string($sex) ? $sex : 'unknown',
            maritalStatus: $this->maritalStatus($patient),
            payer: $payer === 'NO_INSURANCE' ? null : $payer,
            address: $this->homeAddress($patient),
            middleName: $middleName,
        );
    }

    /**
     * @param array<mixed> $patient
     */
    private function homeAddress(array $patient): ?PostalAddress
    {
        $addresses = $patient['address'] ?? null;
        $address = is_array($addresses) ? ($addresses[0] ?? null) : null;
        if (!is_array($address)) {
            return null;
        }
        $lines = $address['line'] ?? null;
        $street = is_array($lines) ? implode(' ', array_filter($lines, is_string(...))) : '';
        $city = $address['city'] ?? null;
        $state = $address['state'] ?? null;
        $postalCode = $address['postalCode'] ?? null;
        if ($street === '' || !is_string($city) || !is_string($state) || !is_string($postalCode)) {
            return null;
        }
        return new PostalAddress($street, $city, $state, $postalCode);
    }

    /**
     * @param array<mixed> $patient
     * @return array{string, ?string, string} first, middle (other given names) and family name
     */
    private function officialName(array $patient, string $fhirFile): array
    {
        $names = $patient['name'] ?? null;
        if (is_array($names)) {
            foreach ($names as $name) {
                if (!is_array($name) || ($name['use'] ?? 'official') !== 'official') {
                    continue;
                }
                $given = $name['given'] ?? null;
                $first = is_array($given) ? ($given[0] ?? null) : null;
                $family = $name['family'] ?? null;
                if (is_string($first) && is_string($family)) {
                    $others = array_filter(array_slice($given, 1), static fn (mixed $part): bool => is_string($part) && $part !== '');
                    return [$first, $others === [] ? null : implode(' ', $others), $family];
                }
            }
        }
        throw new SyntheaException('FHIR Patient has no official name: ' . $fhirFile);
    }

    /**
     * @param array<mixed> $patient
     */
    private function maritalStatus(array $patient): ?string
    {
        $maritalStatus = $patient['maritalStatus'] ?? null;
        $codings = is_array($maritalStatus) ? ($maritalStatus['coding'] ?? null) : null;
        $coding = is_array($codings) ? ($codings[0] ?? null) : null;
        $code = is_array($coding) ? ($coding['code'] ?? null) : null;
        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * @param array<mixed> $explanationOfBenefit
     */
    private function insuranceDisplay(array $explanationOfBenefit): ?string
    {
        $insurance = $explanationOfBenefit['insurance'] ?? null;
        $first = is_array($insurance) ? ($insurance[0] ?? null) : null;
        $coverage = is_array($first) ? ($first['coverage'] ?? null) : null;
        $display = is_array($coverage) ? ($coverage['display'] ?? null) : null;
        return is_string($display) && $display !== '' ? $display : null;
    }
}
