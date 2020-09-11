<?php

/**
 * UserUpdatedEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\User;

use Symfony\Component\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    /**
     * This event is triggered after a user has been updated, and an assoc
     * array containing the POST of new user data is passed to the event object
     */
    const EVENT_HANDLE = 'user.updated';

    private $dataBeforeUpdate;
    private $newUserData;

    /**
     * UserUpdatedEvent constructor.
     * @param $dataBeforeUpdate
     * @param $newUserData
     */
    public function __construct($dataBeforeUpdate, $newUserData)
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
        $this->newUserData = $newUserData;
    }

    /**
     * @return mixed
     */
    public function getDataBeforeUpdate()
    {
        return $this->dataBeforeUpdate;
    }

    /**
     * @param mixed $dataBeforeUpdate
     */
    public function setDataBeforeUpdate($dataBeforeUpdate): void
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
    }

    /**
     * @return mixed
     */
    public function getNewUserData()
    {
        return $this->newUserData;
    }

    /**
     * @param mixed $newUserData
     */
    public function setNewUserData($newUserData): void
    {
        $this->newUserData = $newUserData;
    }
}
