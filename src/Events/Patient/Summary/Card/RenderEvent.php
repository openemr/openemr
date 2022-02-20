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

    /**
     * Array holding the prepended data
     *
     * @var array
     */
    private $prependedData = [];

    /**
     * Array holding the appended data
     *
     * @var array
     */
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

    /**
     * Get the name of the card
     *
     * @return string Name of the card
     */
    public function getCard(): string
    {
        return $this->card;
    }

    /**
     * Set the card ID
     *
     * @param string $card Name of the card
     * @return void
     */
    public function setCard(string $card): void
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
    public function addAppendedData(RenderInterface $object, $position = null): void
    {
        $this->modifyArray('appendedData', $object, $position);
    }

    /**
     * Add content to the beginning of a card
     *
     * @param RenderInterface $object
     * @param int|null $position Specific position in array, optional. Defaults to end.
     * @return void
     */
    public function addPrependedData(RenderInterface $object, $position = null): void
    {
        $this->modifyArray('prependedData', $object, $position);
    }

    /**
     * Modify the appended and prepended data array
     *
     * @param string $property Name of the property to modify
     * @param RenderInterface $object The object to add
     * @param int|null $position Specific position in array, optional. Defaults to end
     * @return void
     */
    private function modifyArray(string $property, RenderInterface $object, $position = null): void
    {
        if (property_exists($this, $property)) {
            if (count($this->$property) === 0) {
                $this->$property[] = $object;
            } else {
                $position = $position ?? -1;
                array_splice($this->$property, $position, 0, $object);
            }
        }
    }

    /**
     * Get the data to be appended data
     *
     * @return array
     */
    public function getAppendedInjection(): array
    {
        return $this->appendedData;
    }

    /**
     * Get the data to be prepended data
     *
     * @return array
     */
    public function getPrependedInjection(): array
    {
        return $this->prependedData;
    }
}
