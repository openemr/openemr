<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Core\Traits;

use LogicException;
use OpenEMR\Core\Traits\KeyAwareSingletonTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
#[Group('trait')]
#[CoversClass(KeyAwareSingletonTrait::class)]
#[CoversMethod(KeyAwareSingletonTrait::class, 'getInstanceByKey')]
#[CoversMethod(KeyAwareSingletonTrait::class, 'createInstance')]
class KeyAwareSingletonTraitTest extends TestCase
{
    #[Test]
    public function constructTest(): void
    {
        $this->assertNotSame(
            KeyAwareSingletonA::getInstanceByKey(1),
            new KeyAwareSingletonA(1),
        );
    }

    #[Test]
    public function cloneFailedTest(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Cloning of %s is not allowed',
            KeyAwareSingletonA::class,
        ));

        $instance = KeyAwareSingletonA::getInstanceByKey(1);
        clone $instance;
    }

    #[Test]
    public function getInstanceByKeyWithSameKeysReturnsSameInstanceTest(): void
    {
        $a1 = KeyAwareSingletonA::getInstanceByKey(1);
        $a2 = KeyAwareSingletonA::getInstanceByKey(1);

        $this->assertInstanceOf(KeyAwareSingletonA::class, $a1);
        $this->assertInstanceOf(KeyAwareSingletonA::class, $a2);
        $this->assertSame($a1, $a2);
    }

    #[Test]
    public function getInstanceByKeyWithDifferentKeysReturnsDifferentInstanceTest(): void
    {
        $a1 = KeyAwareSingletonA::getInstanceByKey(1);
        $a2 = KeyAwareSingletonA::getInstanceByKey(2);

        $this->assertInstanceOf(KeyAwareSingletonA::class, $a1);
        $this->assertInstanceOf(KeyAwareSingletonA::class, $a2);
        $this->assertNotSame($a1, $a2);
    }

    #[Test]
    public function getInstanceByKeyReturnDifferentInstanceForDifferentClassTest(): void
    {
        $a = KeyAwareSingletonA::getInstanceByKey(1);
        $b = KeyAwareSingletonB::getInstanceByKey(1);
        $c = KeyAwareSingletonC::getInstanceByKey(1);

        $this->assertInstanceOf(KeyAwareSingletonA::class, $a);
        $this->assertInstanceOf(KeyAwareSingletonB::class, $b);
        $this->assertInstanceOf(KeyAwareSingletonC::class, $c);
        $this->assertNotSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertNotSame($b, $c);
    }
}

class KeyAwareSingletonA
{
    /** @use KeyAwareSingletonTrait<int> */
    use KeyAwareSingletonTrait;

    protected static function createInstance($key): static { return new static($key); }
    public function __construct(private readonly int $userId) {}
    public function getUserId(): int { return $this->userId; }
}
class KeyAwareSingletonB extends KeyAwareSingletonA {}
class KeyAwareSingletonC extends KeyAwareSingletonB {}
