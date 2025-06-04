<?php

/**
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Juggernaut Systems Express, <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023 Juggernaut Systems Express, <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use Symfony\Contracts\EventDispatcher\Event;

class QuestLabTransmitEvent extends Event
{
    /**
     * This event is triggered when a lab is to be transmitted
     */
    const EVENT_LAB_TRANSMIT = 'lab.transmit';

    /**
     * This event is triggered when a lab requisition form is returned from Quest
     * Requisition form has to be enabled in the globals
     */
    const EVENT_LAB_POST_ORDER_LOAD = 'lab.post_order_load';

    private string $order;
    public function __construct($hl7)
    {
        if (is_string($hl7)) {
            $this->order = $hl7;
        }
    }

    public function getOrder(): string
    {
        return $this->order;
    }
}
