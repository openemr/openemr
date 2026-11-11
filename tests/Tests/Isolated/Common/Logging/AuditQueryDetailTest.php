<?php

/**
 * SELECT audit detail defaults to one summary per user action.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\AuditQueryDetail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuditQueryDetailTest extends TestCase
{
    /**
     * @return array<string, array{string, AuditQueryDetail}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedValueProvider(): array
    {
        return [
            'summary' => ['request', AuditQueryDetail::Request],
            'every statement' => ['statement', AuditQueryDetail::Statement],
            'empty' => ['', AuditQueryDetail::Request],
            'unrecognized' => ['every-row', AuditQueryDetail::Request],
        ];
    }

    #[DataProvider('storedValueProvider')]
    public function testStoredValue(string $stored, AuditQueryDetail $detail): void
    {
        $this->assertSame($detail, AuditQueryDetail::fromGlobal($stored));
    }

    public function testAnOmittedDetailIsOneSummary(): void
    {
        $config = new AuditConfig(true, false, true, false, []);

        $this->assertSame(AuditQueryDetail::Request, $config->queryDetail);
    }
}
