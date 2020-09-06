<?php

/**
 * UserCreatedEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\User;

use Symfony\Component\EventDispatcher\Event;

class UserCreatedEvent extends Event
{
    /**
     * This event is triggered after a user has been created, and an assoc
     * array containing the POST of new user data is passed to the event object
     */
    const EVENT_HANDLE = 'user.created';

    private $userData;

    /**
     * UserCreatedEvent constructor.
     * @param $userData
     */
    public function __construct($userData)
    {
        $this->userData = $userData;
    }

    /**
     * @return mixed
     */
    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * @param mixed $userData
     */
    public function setUserData($userData): void
    {
        $this->userData = $userData;
    }
}
