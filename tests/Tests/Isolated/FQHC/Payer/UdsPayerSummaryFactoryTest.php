<?php

/**
 * Isolated tests for the UDS payer summary factory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Payer;

use OpenEMR\FQHC\Payer\PatientPrimaryInsurance;
use OpenEMR\FQHC\Payer\UdsPayerSummaryFactory;
use PHPUnit\Framework\TestCase;

final class UdsPayerSummaryFactoryTest extends TestCase
{
    private UdsPayerSummaryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new UdsPayerSummaryFactory();
    }

    public function testNoCoverageIsNoneUninsured(): void
    {
        $summary = $this->factory->create(null);

        self::assertFalse($summary->hasCoverage);
        self::assertTrue($summary->classified);
        self::assertSame('None / uninsured', $summary->categoryLabel);
        self::assertNull($summary->planName);
    }

    public function testRecognisedCoverageMapsToBucket(): void
    {
        $summary = $this->factory->create(new PatientPrimaryInsurance('State Medicaid', 3));

        self::assertTrue($summary->hasCoverage);
        self::assertTrue($summary->classified);
        self::assertSame('Medicaid', $summary->categoryLabel);
        self::assertSame('State Medicaid', $summary->planName);
    }

    public function testUnrecognisedCoverageIsUnclassified(): void
    {
        $summary = $this->factory->create(new PatientPrimaryInsurance('Mystery Plan', 999));

        self::assertTrue($summary->hasCoverage);
        self::assertFalse($summary->classified);
        self::assertSame('Unclassified', $summary->categoryLabel);
        self::assertSame('Mystery Plan', $summary->planName);
    }
}
