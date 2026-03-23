<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Fixture;

use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Fixture\Purger\CompositePurger;
use OpenEMR\Fixture\Purger\CompositePurgerFactory;
use OpenEMR\Fixture\RepositoryAwareFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This one testing singleton fixture instantiated once
 * and reused many times - exactly like we usually do during tests
 *
 * So this approach is kind of example how to properly use
 * singleton fixtures with purgers
 */
class SingletonRepositoryAwareFixtureTest extends TestCase
{
    private CompositePurger $purger;

    private SingletonFixture $fixture;

    protected function setUp(): void
    {
        $this->purger = CompositePurgerFactory::createPurgeable();
        $this->purger->purge();

        $this->fixture = SingletonFixture::getInstance();
    }

    protected function tearDown(): void
    {
        $this->purger->restore();
    }

    #[Test]
    #[DataProvider('consecutiveLoadDataProvider')]
    public function consecutiveLoadTest(): void
    {
        $this->fixture->load();

        $this->assertCount(2, $this->fixture->getRecords());
        $this->assertEquals(2, UserRepository::getInstance()->count());
    }

    public static function consecutiveLoadDataProvider(): iterable
    {
        yield [];
        yield [];
        yield [];
    }
}

class SingletonFixture extends RepositoryAwareFixture
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            UserRepository::getInstance(),
            ['users.json'],
        );
    }

    protected function getDataDir(): string
    {
        return sprintf('%s/data', __DIR__);
    }
}
