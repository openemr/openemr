<?php

/**
 * Value set expansions by OID and version, from a measure's value sets in
 * the form cqm-execution receives them; cql-execution's CodeService with
 * cqm-execution's valueSetsForCodeService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\ValueSet;

final class CodeService
{
    /**
     * Expansions by OID; each a list, since a version that looks like a
     * number would not survive as an array key.
     *
     * @var array<string, list<ValueSet>>
     */
    private array $valueSets = [];

    /**
     * @param list<array<mixed>> $valueSets value sets with oid, version and concepts (code, code_system_oid)
     */
    public function __construct(array $valueSets)
    {
        /** @var array<string, list<array{string, list<Code>}>> $byOid */
        $byOid = [];
        foreach ($valueSets as $valueSet) {
            $oid = $valueSet['oid'] ?? null;
            $concepts = $valueSet['concepts'] ?? null;
            if (!is_string($oid) || !is_array($concepts)) {
                continue;
            }
            $version = $valueSet['version'] ?? '';
            $version = $version === 'N/A' || !is_scalar($version) ? '' : (string) $version;
            $codes = [];
            foreach ($concepts as $concept) {
                if (!is_array($concept) || !is_scalar($concept['code'] ?? null)) {
                    continue;
                }
                $system = $concept['code_system_oid'] ?? null;
                $codes[] = new Code((string) $concept['code'], is_scalar($system) ? (string) $system : null, $version);
            }
            $byOid[$oid] ??= [];
            $merged = false;
            foreach ($byOid[$oid] as $i => [$existingVersion, $existingCodes]) {
                if ($existingVersion === $version) {
                    $byOid[$oid][$i] = [$version, [...$existingCodes, ...$codes]];
                    $merged = true;
                }
            }
            if (!$merged) {
                $byOid[$oid][] = [$version, $codes];
            }
        }
        foreach ($byOid as $oid => $versions) {
            foreach ($versions as [$version, $codes]) {
                $this->valueSets[$oid][] = new ValueSet($oid, $version, $codes);
            }
        }
    }

    /**
     * The expansion of a value set; without a version, the one with the
     * highest version string (the last of equals).
     */
    public function findValueSet(string $oid, ?string $version): ?ValueSet
    {
        if ($version !== null) {
            foreach ($this->valueSets[$oid] ?? [] as $valueSet) {
                if ($valueSet->version === $version) {
                    return $valueSet;
                }
            }
            return null;
        }
        $found = null;
        foreach ($this->valueSets[$oid] ?? [] as $valueSet) {
            if ($found === null || !(strcmp((string) $found->version, (string) $valueSet->version) > 0)) {
                $found = $valueSet;
            }
        }
        return $found;
    }
}
