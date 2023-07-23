<?php

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class Quantity extends AbstractType
{
    public $value;
    public $unit;
    public $_type = 'QDM::Quantity';
}
