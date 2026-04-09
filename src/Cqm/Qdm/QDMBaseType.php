<?php

namespace OpenEMR\Cqm\Qdm;

use Exception;
use JsonSerializable;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;

/**
 * OpenEMR\Cqm\Qdm\QDMBaseType
 *
 * Base class for generated QDM types. Provides array-based construction
 * for hydrating from external data.
 *
 * @QDM Version 5.6
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
abstract class QDMBaseType implements JsonSerializable
{
    /** @var string (tons of concrete classes need updating to use native type */
    public  $_type = 'QDM::QDMBaseType';
    public ?string $bundleId = null;
    /** @var list<Code> */
    public array $dataElementCodes = [];

    /**
     * @property string|null For backwards compatibility
     */
    public ?string $_id = '';

    /**
     * @property string $id
     */
    public ?string $id = '';

    /**
     * @property BaseTypes\Code $code
     */
    public $code = null;

    /**
     * @property string $patientId
     */
    public $patientId = '';

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.6';

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
