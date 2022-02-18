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

use OpenEMR\Events\Patient\Summary\Card\RenderInterface;
use Symfony\Component\EventDispatcher\Event;

class RenderEvent extends Event
{
    /**
     * The patiemtSummaryCard.render event occurs when card on the Patient
     * Demographics screen is rendered.
     */
    const EVENT_HANDLE = 'patientSummaryCard.render';

    /**
     * ID of the card being rendered
     *
     * @var string
     */
    private $card;

    private $prependedData = [];

    private $appendedData = [];

    /**
     * UpdateEvent constructor.
     *
     * @param string $cardID The ID of the card being rendered
     */
    public function __construct(string $cardID)
    {
        $this->setCard($cardID);
    }

    public function getCard()
    {
        return $this->card;
    }

    public function setCard(string $card)
    {
        $this->card = $card;
    }

    /**
     * Add content to the end of a card
     *
     * @param RenderInterface $object
     * @param int|null $position Specific position in array, optional. Defaults to end.
     * @return void
     */
    public function addAppendedData(RenderInterface $object, $position = null)
    {
        if (count($this->appendedData) === 0) {
            $this->appendedData[] = $object;
        } else {
            $position = $position ?? -1;
            array_splice($this->appendedData, $position, 0, $object);
        }
    }

    /**
     * Add content to the beginning of a card
     *
     * @param RenderInterface $object
     * @param int|null $position Specific position in array, optional. Defaults to end.
     * @return void
     */
    public function addPrependedData(RenderInterface $object, $position = null)
    {
        if (count($this->prependedData) === 0) {
            $this->prependedData[] = $object;
        } else {
            $position = $position ?? -1;
            array_splice($this->prependedData, $position, 0, $object);
        }
    }

    public function getAppendedInjection()
    {
        return $this->appendedData;
    }

    public function getPrependedInjection()
    {
        return $this->prependedData;
    }

}
