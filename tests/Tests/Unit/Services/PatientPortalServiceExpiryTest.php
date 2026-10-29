<?php

/**
 * The one-time document link message states the link's real expiry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Services;

use OpenEMR\Services\PatientPortalService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientPortalServiceExpiryTest extends TestCase
{
    /**
     * @return array<string, array{?string, string}>
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
        ];
    }

    #[DataProvider('periods')]
    public function testDescribeExpiryUsesTheActualPeriod(?string $period, string $expected): void
    {
        $this->assertSame($expected, PatientPortalService::describeExpiry($period));
    }
}
