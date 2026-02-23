<?php

/**
 * Isolated tests for UserUpdatedEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events;

use OpenEMR\Events\User\UserUpdatedEvent;
use PHPUnit\Framework\TestCase;

class UserUpdatedEventTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $before = ['id' => 1, 'username' => 'old'];
        $after = ['id' => 1, 'username' => 'new'];
        $event = new UserUpdatedEvent($before, $after);

        $this->assertSame($before, $event->getDataBeforeUpdate());
        $this->assertSame($after, $event->getNewUserData());
    }

    public function testGetSetDataBeforeUpdateRoundTrip(): void
    {
        $event = new UserUpdatedEvent(['a' => 1], ['b' => 2]);
        $newData = ['c' => 3];
        $event->setDataBeforeUpdate($newData);

        $this->assertSame($newData, $event->getDataBeforeUpdate());
    }

    public function testGetSetNewUserDataRoundTrip(): void
    {
        $event = new UserUpdatedEvent(['a' => 1], ['b' => 2]);
        $newData = ['id' => 5, 'username' => 'updated'];
        $event->setNewUserData($newData);

        $this->assertSame($newData, $event->getNewUserData());
    }

    public function testGetUserIdReturnsIdWhenPresent(): void
    {
        $event = new UserUpdatedEvent([], ['id' => 42, 'username' => 'test']);

        $this->assertSame(42, $event->getUserId());
    }

    public function testGetUserIdReturnsNullWhenMissing(): void
    {
        $event = new UserUpdatedEvent([], ['username' => 'test']);

        $this->assertNull($event->getUserId());
    }
}
