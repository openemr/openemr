<?php

/**
 * Test for EventAuditLogger breakglass user functionality using fixtures
 *
 * @package   OpenEMR\Tests\Common\Logging
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Logging;

use OpenEMR\Common\Logging\EventAuditLogger;
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
     * Test isBreakglassUser method with FixtureManager to cover line 995
     * This test uses proper FixtureManager pattern to set up GACL data
     */
    public function testIsBreakglassUserWithFixtureManager(): void
    {
        // Install GACL breakglass fixtures
        $insertCount = $this->gaclFixtureManager->installFixtures();
        $this->assertGreaterThan(0, $insertCount, 'GACL fixtures should be installed');

        // Get EventAuditLogger singleton instance
        $eventAuditLogger = EventAuditLogger::instance();

        // Use reflection to access private properties and methods
        $reflectionClass = new \ReflectionClass($eventAuditLogger);

        // Reset breakglassUser property to null to force re-evaluation
        $breakglassProperty = $reflectionClass->getProperty('breakglassUser');
        $breakglassProperty->setValue($eventAuditLogger, null);

        // Access the protected isBreakglassUser method
        $reflectionMethod = $reflectionClass->getMethod('isBreakglassUser');

        // Test breakglass user - should execute line 995: $this->breakglassUser = true;
        $result = $reflectionMethod->invoke($eventAuditLogger, 'testbreakglassuser');

        // Verify line 995 was executed by checking the property value
        $finalValue = $breakglassProperty->getValue($eventAuditLogger);
        $this->assertTrue($finalValue === true, 'Line 995 should set breakglassUser to true');
        $this->assertTrue($result, 'Breakglass user should return true');

        // Test non-breakglass user (reset first)
        $breakglassProperty->setValue($eventAuditLogger, null);
        $result2 = $reflectionMethod->invoke($eventAuditLogger, 'normaluser');
        $this->assertFalse($result2, 'Normal user should return false');
    }
}
