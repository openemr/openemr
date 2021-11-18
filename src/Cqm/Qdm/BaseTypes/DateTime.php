<?php


namespace OpenEMR\Cqm\Qdm\BaseTypes;


class DateTime extends AbstractType implements \JsonSerializable
{
    public $date;

    public function jsonSerialize()
    {
        $formatted = gmdate('Y-m-d\TH:i:s\Z', date('U', strtotime($this->date)));
        return $formatted;
    }
}
