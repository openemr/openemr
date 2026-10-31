<?php

/**
 * The one-time document link message states the link's real expiry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    James DuRant <jdurant@ad.unc.edu>
 * @copyright Copyright (c) 2026 James DuRant <jdurant@ad.unc.edu>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Services;

use OpenEMR\Services\PatientPortalService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientPortalServiceExpiryTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     */
    public static function periods(): array
    {
        return [
            'default document period' => ['PT60M', '60 minutes'],
            'one hour' => ['PT1H', '1 hour'],
            'invoice period' => ['P14D', '14 days'],
            'combined' => ['P2DT3H', '2 days 3 hours'],
            'missing' => [null, 'a limited time'],
            'empty' => ['', 'a limited time'],
            'invalid' => ['not-an-interval', 'a limited time'],
            'zero' => ['PT0M', 'a limited time'],
            'seconds only' => ['PT30S', '30 seconds'],
            'minutes and seconds' => ['PT1M30S', '1 minute 30 seconds'],
            'not a string' => [60, 'a limited time'],
        ];
    }

    public function testDocumentLineStatesTheRequestedPeriodNotFortyEightHours(): void
    {
        $line = PatientPortalService::oneTimeDocumentLine('Intake Form', 'PT60M');
        $this->assertStringContainsString('"Intake Form"', $line);
        $this->assertStringContainsString('valid for 60 minutes', $line);
        $this->assertStringNotContainsString('48 hours', $line);
    }

    #[DataProvider('periods')]
    public function testDescribeExpiryUsesTheActualPeriod(mixed $period, string $expected): void
    {
        $this->assertSame($expected, PatientPortalService::describeExpiry($period));
    }
}
