<?php


namespace OpenEMR\Cqm\Qdm\BaseTypes;


class DateTime extends AbstractType implements \JsonSerializable
{
    public $date;

    public function jsonSerialize()
    {
        return [
            "\$date" => $this->date
        ];
    }
}
