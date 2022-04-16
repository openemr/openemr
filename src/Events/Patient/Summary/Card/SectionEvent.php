<?php

/**
 *
 */

namespace OpenEMR\Events\Patient\Summary\Card;

use DomainException;
use OpenEMR\Events\PatientDemographics\ViewEvent;
use Symfony\Component\EventDispatcher\Event;

class SectionEvent extends Event
{
    /**
     * The checkViewAuth event occurs when a user attempts to view a
     * patient record from the demographics screen
     */
    const EVENT_HANDLE = 'section.render';

    /**
     * The section being rendered
     *
     * @var string
     */
    private $section;

    /**
     * Array of CardInterface objects
     *
     * @var string $section The section name
     */
    private $cards;

    public function __construct(string $section)
    {
        $this->cards = [];
        $this->section = $section;
    }

    /**
     * Return the section name being rendered
     *
     * @return string
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     * Return an array of CardInterface objects
     *
     * @return array
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Add a new Card to the Card Array.
     *
     * @param CardInterface $card
     * @param null|int $position Define a specific position in array. Null to append, 0 to prepend
     * @return void
     */
    public function addCard(CardInterface $card, $position = null): void
    {
        $currentCards = $this->getCardIdentifiers();
        if (in_array($card->getIdentifier(), $currentCards)) {
            throw new DomainException("Card {$card->getIdentifier()} is not unique in current list");
        }

        // @todo ensure position is an integer or null

        array_splice($this->cards, $position ?? -1, 0, $card);
    }

    /**
     * Private function returning an array of card identifiers
     *
     * @return array
     */
    private function getCardIdentifiers(): array
    {
        $_idArr = [];

        if (count($this->cards) === 0) {
            return $_idArr;
        }

        foreach ($this->cards as $card) {
            if (!$card instanceof CardInterface) {
                $objtype = get_class($card);
                throw new \UnexpectedValueException("Expecting an object implementing CardInterface. Received {$objtype}");
            }
            $_idArr[] = $card->getIdentifier();
        }
        return $_idArr;
    }
}
