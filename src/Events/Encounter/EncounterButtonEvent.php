<?php

/**
 * package   OpenEMR
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @Copyright 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Encounter;

use Symfony\Contracts\EventDispatcher\Event;

class EncounterButtonEvent extends Event
{
    /**
     * This event is fired where a button it to be rendered in the encounter form.
     */
    const BUTTON_RENDER = 'button.render';

    private $button;

    public function setButton($button): void
    {
        $this->button = $button;
    }

    public function displayButton()
    {
        return $this->button;
    }
}
