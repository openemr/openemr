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
use OpenEMR\Fixture\Purger\CompositePurger;
use OpenEMR\Fixture\Purger\CompositePurgerFactory;
use OpenEMR\Fixture\RepositoryAwareFixture;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This one testing new fixture instantiated on every setUp
 */
class RepositoryAwareFixtureTest extends TestCase
{
    private CompositePurger $purger;

    private RepositoryAwareFixture $fixture;

    protected function setUp(): void
    {
        $this->purger = CompositePurgerFactory::createPurgeable();
        $this->purger->purge();

        $this->fixture = $this->getFixture();
    }

    protected function tearDown(): void
    {
        $this->purger->restore();
    }

    #[Test]
    public function loadTest(): void
    {
        $this->fixture->load();

        $this->assertCount(2, $this->fixture->getRecords());
        $this->assertEquals(2, UserRepository::getInstance()->count());
    }

    #[Test]
    public function removeFixtureRecordsTest(): void
    {
        $this->fixture->load();
        $this->fixture->removeFixtureRecords();

        $this->assertCount(0, $this->fixture->getRecords());
        $this->assertEquals(0, UserRepository::getInstance()->count());
    }

    private function getFixture(): RepositoryAwareFixture
    {
        return new class (UserRepository::getInstance(), ['users.json']) extends RepositoryAwareFixture {
            protected function getDataDir(): string
            {
                return sprintf('%s/data', __DIR__);
            }
        };
    }
}
