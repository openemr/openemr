<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Qdm\BaseTypes;

use Exception;
use JsonSerializable;

/**
 * Base class for QDM data elements (generated types like Diagnosis, EncounterPerformed, etc.)
 *
 * Uses array-based construction to hydrate from external data.
 */
class DataElement implements JsonSerializable
{
    public mixed $_type = null;
    public ?string $bundleId = null;
    /** @var list<Code> */
    public array $dataElementCodes = [];

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            } else {
                throw new Exception("Property {$property} does not exist on " . static::class);
            }
        }
    }

    public function addCode(Code $code): void
    {
        $this->dataElementCodes[] = $code;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
