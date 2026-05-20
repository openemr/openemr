<?php

/**
 * Isolated tests for AppointmentsPage::getEligibilitySummary().
 *
 * The summary parser turns a stored individual_json column (a serialized
 * piece of the ClaimRev eligibility response) into a list of stdClass
 * summaries the calendar / appointments page renders. Tests pin down:
 *
 *  - null / empty / malformed JSON returns null (so the caller's guard
 *    chain `$summaries !== null && $summaries !== []` works)
 *  - missing 'eligibility' returns null
 *  - non-iterable 'eligibility' returns null (was a real upstream bug
 *    when ClaimRev returned an object instead of an array of results)
 *  - a single eligibility entry produces a stdClass with the four
 *    expected properties (status, payerName, subscriberId, insuranceType)
 *  - missing properties default to '' (so the calendar template doesn't
 *    crash on partial responses)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\AppointmentsPage;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/AppointmentsPage.php';

class AppointmentsPageTest extends TestCase
{
    public function testNullJsonReturnsNull(): void
    {
        $this->assertNull(AppointmentsPage::getEligibilitySummary(null));
    }

    public function testEmptyJsonReturnsNull(): void
    {
        $this->assertNull(AppointmentsPage::getEligibilitySummary(''));
    }

    public function testMalformedJsonReturnsNull(): void
    {
        // json_decode returns null on parse error.
        $this->assertNull(AppointmentsPage::getEligibilitySummary('{ broken'));
    }

    public function testJsonRootScalarReturnsNull(): void
    {
        // json_decode of "42" produces an int, not an object.
        $this->assertNull(AppointmentsPage::getEligibilitySummary('42'));
    }

    public function testMissingEligibilityPropertyReturnsNull(): void
    {
        $this->assertNull(AppointmentsPage::getEligibilitySummary((string) json_encode([
            'someOtherField' => 'value',
        ])));
    }

    public function testNonIterableEligibilityReturnsNull(): void
    {
        // Older ClaimRev responses sometimes had eligibility as an object
        // rather than a list — if it's not iterable, return null instead
        // of crashing the foreach.
        $this->assertNull(AppointmentsPage::getEligibilitySummary((string) json_encode([
            'eligibility' => 'not an array',
        ])));
    }

    public function testSingleEligibilityProducesSummary(): void
    {
        $json = (string) json_encode([
            'eligibility' => [
                [
                    'status' => 'Active Coverage',
                    'subscriberId' => 'MEM-12345',
                    'insuranceType' => 'PPO',
                    'payerInfo' => [
                        'payerName' => 'Acme Health',
                    ],
                ],
            ],
        ]);

        $summaries = AppointmentsPage::getEligibilitySummary($json);

        $this->assertIsArray($summaries);
        $this->assertCount(1, $summaries);
        $this->assertSame('Active Coverage', $summaries[0]->status);
        $this->assertSame('MEM-12345', $summaries[0]->subscriberId);
        $this->assertSame('PPO', $summaries[0]->insuranceType);
        $this->assertSame('Acme Health', $summaries[0]->payerName);
    }

    public function testMissingFieldsDefaultToEmptyString(): void
    {
        // Partial response — only status set; the other three properties
        // must still be present as '' so the calendar template doesn't
        // crash on undefined reads.
        $json = (string) json_encode([
            'eligibility' => [
                ['status' => 'Inactive'],
            ],
        ]);

        $summaries = AppointmentsPage::getEligibilitySummary($json);

        $this->assertIsArray($summaries);
        $this->assertSame('Inactive', $summaries[0]->status);
        $this->assertSame('', $summaries[0]->payerName);
        $this->assertSame('', $summaries[0]->subscriberId);
        $this->assertSame('', $summaries[0]->insuranceType);
    }

    public function testMultipleEligibilitiesProduceMultipleSummaries(): void
    {
        $json = (string) json_encode([
            'eligibility' => [
                ['status' => 'Active Coverage', 'subscriberId' => 'A'],
                ['status' => 'Inactive', 'subscriberId' => 'B'],
                ['status' => 'Pending', 'subscriberId' => 'C'],
            ],
        ]);

        $summaries = AppointmentsPage::getEligibilitySummary($json);

        $this->assertIsArray($summaries);
        $this->assertCount(3, $summaries);
        $this->assertSame('A', $summaries[0]->subscriberId);
        $this->assertSame('B', $summaries[1]->subscriberId);
        $this->assertSame('C', $summaries[2]->subscriberId);
    }

    public function testNonObjectEntriesAreSkipped(): void
    {
        // If an eligibility entry is a scalar (malformed), skip it
        // rather than crash on the property_exists chain.
        $json = (string) json_encode([
            'eligibility' => [
                'malformed entry',
                ['status' => 'Active Coverage'],
                42,
            ],
        ]);

        $summaries = AppointmentsPage::getEligibilitySummary($json);

        // Only the well-formed middle entry survives.
        $this->assertIsArray($summaries);
        $this->assertCount(1, $summaries);
        $this->assertSame('Active Coverage', $summaries[0]->status);
    }

    public function testPayerNameFromMalformedPayerInfoDefaultsToEmpty(): void
    {
        $json = (string) json_encode([
            'eligibility' => [
                [
                    'status' => 'Active Coverage',
                    'payerInfo' => 'not an object',
                ],
            ],
        ]);

        $summaries = AppointmentsPage::getEligibilitySummary($json);

        $this->assertIsArray($summaries);
        $this->assertSame('', $summaries[0]->payerName);
    }
}
