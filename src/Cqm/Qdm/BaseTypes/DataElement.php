<?php

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class DataElement extends AbstractType
{
    public $_type;
    public $bundleId;
    public $dataElementCodes = [];

    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
    }

    public function addCode(Code $code)
    {
        $this->dataElementCodes[] = $code;
    }
}
