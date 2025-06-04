<?php

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class Interval extends AbstractType
{
    /**
     * @var DateTime
     */
    public $low;

    /**
     * @var DateTime
     */
    public $high;

    /**
     * @var bool
     */
    public $lowClosed;

    /**
     * @var bool
     */
    public $highClosed;
}
