<?php

namespace Academe\AuthorizeNet\Payment;

/**
 * TODO: protect the data from var_dump
 */

use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class Track1 extends AbstractModel implements PaymentInterface
{
    protected $objectName = 'trackData';
    protected $trackName = 'track1';

    protected $track;

    public function __construct($track)
    {
        parent::__construct();

        $this->setTrack($track);
    }

    public function jsonSerialize()
    {
        return [
            $this->trackName => $this->getTrack(),
        ];
    }

    // TODO: these setters can include validation.

    protected function setTrack($value)
    {
        $this->track = $value;
    }
}
