<?php

/**
 * @package OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class DataElement extends AbstractType
{
    public $_type;
    public ?string $bundleId = null;
    /** @var array<Code> */
    public array $dataElementCodes = [];

    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
    }

    public function addCode(Code $code): void
    {
        $this->dataElementCodes[] = $code;
    }
}
