<?php

/**
 * UserUpdatedEvent
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\User;

use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    /**
     * This event is triggered after a user has been updated, and an assoc
     * array containing the POST of new user data is passed to the event object
     */
    const EVENT_HANDLE = 'user.updated';

    /**
     * UserUpdatedEvent constructor.
     * @param array<string, mixed> $dataBeforeUpdate
     * @param array<string, mixed> $newUserData
     */
    public function __construct(private array $dataBeforeUpdate, private array $newUserData)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataBeforeUpdate(): array
    {
        return $this->dataBeforeUpdate;
    }

    /**
     * @param array<string, mixed> $dataBeforeUpdate
     */
    public function setDataBeforeUpdate(array $dataBeforeUpdate): void
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
    }

    /**
     * @return array<string, mixed>
     */
    public function getNewUserData(): array
    {
        return $this->newUserData;
    }

    /**
     * @param array<string, mixed> $newUserData
     */
    public function setNewUserData(array $newUserData): void
    {
        $this->newUserData = $newUserData;
    }

    public function getUserId(): mixed
    {
        return $this->newUserData['id'] ?? null;
    }
}
