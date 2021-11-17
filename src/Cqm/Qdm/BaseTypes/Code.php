<?php

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class Code extends AbstractType
{
    public $code;
    public $system;
    public $display = null; // Not required
    public $version = null; // Not required
    public $_type = "QDM::Code";
}
