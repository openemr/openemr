<?php

/**
 * This file is part of OpenEMR.
 *
 * @link      https://github.com/openemr/openemr/tree/master
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @package   OpenEMR\Events\Patient\Summary\Card
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 */

namespace OpenEMR\Events\Patient\Summary\Card;

use Symfony\Component\EventDispatcher\Event;

class RenderEvent extends Event
{
    /**
     * The checkViewAuth event occurs when a user attempts to view a
     * patient record from the demographics screen
     */
    const EVENT_HANDLE = 'patientSummaryCard.render';

    private $card;

    private $content = [];

    /**
     * UpdateEvent constructor.
     *
     * @param string $cardID The ID of the card being rendered
     */
    public function __construct(string $cardID)
    {
        $this->card = $cardID;
    }

    public function getCard()
    {
        return $this->card;
    }

    public function setCard(string $card)
    {
        $this->card = $card;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(array $content)
    {
        $this->content = $content;
    }

    public function appendContent(array $content)
    {
        array_push($this->content, $content);
    }

    public function prependContent(array $content)
    {
        array_unshift($this->content, $content);
    }

}
