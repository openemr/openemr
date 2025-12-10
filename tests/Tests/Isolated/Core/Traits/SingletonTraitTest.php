<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Core\Traits;

use Error;
use LogicException;
use OpenEMR\Core\Traits\SingletonTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
#[CoversClass(SingletonTrait::class)]
#[CoversMethod(SingletonTrait::class, '__construct')]
#[CoversMethod(SingletonTrait::class, '__clone')]
#[CoversMethod(SingletonTrait::class, 'getInstance')]
#[CoversMethod(SingletonTrait::class, 'createInstance')]
class SingletonTraitTest extends TestCase
{
    #[Test]
    public function cloneFailedTest(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Cloning of %s is not allowed',
            SingletonA::class,
        ));

        $instance = SingletonA::getInstance();
        clone $instance;
    }

    #[Test]
    public function getInstanceReturnsSameInstanceTest(): void
    {
        $a1 = SingletonA::getInstance();
        $a2 = SingletonA::getInstance();

        $this->assertInstanceOf(SingletonA::class, $a1);
        $this->assertInstanceOf(SingletonA::class, $a2);
        $this->assertSame($a1, $a2);
    }

    #[Test]
    public function getInstanceReturnDifferentInstanceForDifferentClassTest(): void
    {
        $a = SingletonA::getInstance();
        $b = SingletonB::getInstance();
        $c = SingletonC::getInstance();

        $this->assertInstanceOf(SingletonA::class, $a);
        $this->assertInstanceOf(SingletonB::class, $b);
        $this->assertInstanceOf(SingletonC::class, $c);
        $this->assertNotSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertNotSame($b, $c);
    }
}

class SingletonA
{
    use SingletonTrait;
}

class SingletonB extends SingletonA {}

class SingletonC extends SingletonB
{
    protected function __construct(private readonly string $argument) {}

    protected static function createInstance(): static {
        return new self('argument');
    }
}
