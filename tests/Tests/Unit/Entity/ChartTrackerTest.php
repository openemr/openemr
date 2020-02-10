<?php
declare(strict_types=1);

use OpenEMR\Entities\ChartTracker;
use PHPUnit\Framework\TestCase;

class ChartTrackerTest extends TestCase
{
    /** @test */
    public function can_be_created(): void
    {
        $chartTracker = new ChartTracker();

        self::assertInstanceOf(ChartTracker::class, $chartTracker);
        self::assertNull($chartTracker->getPid());
        self::assertNull($chartTracker->getWhen());
        self::assertNull($chartTracker->getUserId());
        self::assertNull($chartTracker->getLocation());
    }

    /** @test */
    public function can_assign_a_Pid_value(): void
    {
        $pId = 1;
        $chartTracker = new ChartTracker();

        $chartTracker->setPid($pId);

        self::assertEquals($pId, $chartTracker->getPid());
    }

    /** @test */
    public function can_assign_a_When_value(): void
    {
        $whenValue = new DateTime('now');
        $chartTracker = new ChartTracker();

        $chartTracker->setWhen($whenValue);

        self::assertEquals($whenValue, $chartTracker->getWhen());
    }

    /** @test */
    public function can_assign_a_userId(): void
    {
        $userId = 100;
        $chartTracker = new ChartTracker();

        $chartTracker->setUserId($userId);

        self::assertEquals($userId, $chartTracker->getUserId());
    }

    /** @test */
    public function can_assign_a_Location(): void
    {
        $location = 'irrelevant';
        $chartTracker = new ChartTracker();

        $chartTracker->setLocation($location);

        self::assertEquals($location, $chartTracker->getLocation());
    }

    /** @test */
    public function can_be_serialized(): void
    {
        $chartTracker = new ChartTracker();
        $chartTracker->setPid($pId = 1);
        $chartTracker->setWhen($whenValue = new DateTime('2020-01-01'));
        $chartTracker->setUserId($userId = 100);
        $chartTracker->setLocation($location = 'irrelevant');

        $expectedSerializedObject = [
            "pid" => $pId,
            "when" => $whenValue,
            "userId" => $userId,
            "location" => $location,
        ];

        self::assertEquals($expectedSerializedObject, $chartTracker->toSerializedObject());
    }

    /** @test */
    public function can_be_converted_to_string(): void
    {
        $chartTracker = new ChartTracker();
        $chartTracker->setPid($pId = 1);
        $chartTracker->setWhen($whenValue = new DateTime('2020-01-01'));
        $chartTracker->setUserId($userId = 100);
        $chartTracker->setLocation($location = 'irrelevant');

        $expectedString = "pid: '1' date: '2020-01-01' userId: '100' location: 'irrelevant'";

        self::assertEquals($expectedString, $chartTracker->__toString());
    }
}
