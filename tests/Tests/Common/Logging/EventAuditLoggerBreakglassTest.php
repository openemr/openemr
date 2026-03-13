<?php

/**
 * Test for BreakglassChecker functionality using fixtures
 *
 * @package   OpenEMR\Tests\Common\Logging
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Logging;

use OpenEMR\Common\Database\DatabaseConnectionFactory;
use OpenEMR\Common\Logging\BreakglassChecker;
use OpenEMR\Tests\Fixtures\GaclFixtureManager;
use PHPUnit\Framework\TestCase;

final class EventAuditLoggerBreakglassTest extends TestCase
{
    private GaclFixtureManager $gaclFixtureManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gaclFixtureManager = new GaclFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->gaclFixtureManager->removeFixtures();
        parent::tearDown();
    }

    /**
     * Test isBreakglassUser method with FixtureManager
     * This test uses proper FixtureManager pattern to set up GACL data
     */
    public function testIsBreakglassUserWithFixtureManager(): void
    {
        // Install GACL breakglass fixtures
        $insertCount = $this->gaclFixtureManager->installFixtures();
        $this->assertGreaterThan(0, $insertCount, 'GACL fixtures should be installed');

        // Create a BreakglassChecker with a real database connection
        $conn = DatabaseConnectionFactory::createDbal();
        $checker = new BreakglassChecker($conn);

        // Test breakglass user
        $result = $checker->isBreakglassUser('testbreakglassuser');
        $this->assertTrue($result, 'Breakglass user should return true');

        // Test non-breakglass user
        $result2 = $checker->isBreakglassUser('normaluser');
        $this->assertFalse($result2, 'Normal user should return false');
    }
}
