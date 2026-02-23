<?php

/**
 * Isolated tests for CodeTypeInstalledEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events;

use OpenEMR\Events\Codes\CodeTypeInstalledEvent;
use PHPUnit\Framework\TestCase;

class CodeTypeInstalledEventTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $details = ['version' => '2024', 'count' => 100];
        $event = new CodeTypeInstalledEvent('ICD10', $details);

        $this->assertSame('ICD10', $event->getCodeType());
        $this->assertSame($details, $event->getDetails());
    }

    public function testGetSetCodeTypeRoundTrip(): void
    {
        $event = new CodeTypeInstalledEvent('ICD10', []);
        $event->setCodeType('CPT4');

        $this->assertSame('CPT4', $event->getCodeType());
    }

    public function testGetSetDetailsRoundTrip(): void
    {
        $event = new CodeTypeInstalledEvent('ICD10', ['a' => 1]);
        $newDetails = ['b' => 2, 'c' => 3];
        $event->setDetails($newDetails);

        $this->assertSame($newDetails, $event->getDetails());
    }

    public function testSetCodeTypeReturnsFluent(): void
    {
        $event = new CodeTypeInstalledEvent('ICD10', []);
        $result = $event->setCodeType('CPT4');

        $this->assertSame($event, $result);
    }

    public function testSetDetailsReturnsFluent(): void
    {
        $event = new CodeTypeInstalledEvent('ICD10', []);
        $result = $event->setDetails(['x' => 1]);

        $this->assertSame($event, $result);
    }
}
