<?php

/**
 * A QDM data element, entity or component as cqm-models hands it to the
 * CQL engine: its attributes cast to CQL values, its schema defaults
 * filled in, and absent attributes left out (undefined, not null).
 *
 * A data element also answers code (its first code), getCode (all of
 * them) and the type tests of the ELM Is and As operators.
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

final readonly class QdmObject
{
    /**
     * @param string $typeName the QDM type without its QDM:: prefix, e.g. EncounterPerformed
     * @param array<string, mixed> $fields the attributes present, in schema order
     */
    public function __construct(
        public string $typeName,
        public array $fields,
        public bool $isDataElement,
    ) {
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->fields) || ($this->isDataElement && ($name === 'code' || $name === 'getCode'));
    }

    /**
     * An attribute; code is the data element's first code. Null when absent.
     */
    public function get(string $name): mixed
    {
        if ($this->isDataElement && $name === 'code') {
            return $this->code();
        }
        if ($this->isDataElement && $name === 'getCode') {
            return $this->getCode();
        }
        return $this->fields[$name] ?? null;
    }

    public function code(): ?Code
    {
        $codes = $this->fields['dataElementCodes'] ?? null;
        $first = is_array($codes) ? ($codes[0] ?? null) : null;
        return $first instanceof Code ? $first : null;
    }

    /**
     * @return list<Code>|null
     */
    public function getCode(): ?array
    {
        $codes = $this->fields['dataElementCodes'] ?? null;
        if (!is_array($codes)) {
            return null;
        }
        return array_values(array_filter($codes, static fn (mixed $code): bool => $code instanceof Code));
    }

    public function isNegated(): bool
    {
        // JavaScript truthiness of negationRationale.
        return ($this->fields['negationRationale'] ?? null) !== null;
    }

    /**
     * The ELM type names this data element is, most specific first.
     *
     * @return list<string>
     */
    public function typeHierarchy(): array
    {
        $version = $this->fields['qdmVersion'] ?? '5.6';
        $version = preg_replace('/\./', '_', is_string($version) ? $version : '5.6', 1) ?? '5_6';
        $prefix = $this->isNegated() ? 'Negative' : 'Positive';
        return [
            "{urn:healthit-gov:qdm:v$version}$prefix{$this->typeName}",
            "{urn:healthit-gov:qdm:v$version}{$this->typeName}",
            '{urn:hl7-org:elm-types:r1}Tuple',
            '{urn:hl7-org:elm-types:r1}Any',
        ];
    }
}
