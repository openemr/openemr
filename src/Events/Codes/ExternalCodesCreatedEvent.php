<?php

/**
 * ExternalCodesEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2021-2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Codes;

use Symfony\Component\EventDispatcher\Event;

class ExternalCodesCreatedEvent extends Event
{
    /**
     * This event is triggered after the external codes array has been created.
     */
    const EVENT_HANDLE = 'external_codes.register';

    private $externalCodeData;

    /**
     * ExternalCodesEvent constructor.
     * @param $externalCodes
     */
    public function __construct($externalCodeData)
    {
        $this->externalCodeData = $externalCodeData;
    }

    /**
     * @return mixed
     */
    public function getExternalCodeData()
    {
        return $this->externalCodeData;
    }

    /**
     * @param mixed $externalCodeData
     */
    public function setExternalCodeData($externalCodeData): void
    {
        $this->externalCodeData = $externalCodeData;
    }
}
