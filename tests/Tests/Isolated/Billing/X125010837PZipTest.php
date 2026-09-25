<?php

/**
 * Isolated tests for the facility ZIP log lines.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837P;
use PHPUnit\Framework\TestCase;

class X125010837PZipTest extends TestCase
{
    /**
     * A short ZIP is still sent. Billing names the 277CA. Service names that edit and MA114.
     */
    public function testLogNamesMedicareConsequenceForEachLoop(): void
    {
        $billing = X125010837P::BILLING_ZIP_LOG;
        $this->assertStringContainsString('9 digits', $billing);
        $this->assertStringContainsString('277CA', $billing);
        $this->assertStringContainsString('CSC 500', $billing);
        $this->assertStringContainsString('country code', $billing);
        $this->assertStringNotContainsString('MA114', $billing);
        $this->assertStringNotContainsString('Rejecting claim', $billing);

        $service = X125010837P::SERVICE_ZIP_LOG;
        $this->assertStringContainsString('9 digits', $service);
        $this->assertStringContainsString('277CA', $service);
        $this->assertStringContainsString('CSC 500', $service);
        $this->assertStringContainsString('MA114', $service);
        $this->assertStringContainsString('country code', $service);
        $this->assertStringNotContainsString('Rejecting claim', $service);
    }
}
