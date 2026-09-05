<?php

/**
 * Isolated tests for `Rainforest\Api::ensureEncountersSumToAmount`.
 *
 * The validator runs before any network traffic to Rainforest so that a
 * payin_config whose per-encounter amounts do not sum to the authorized
 * payment amount cannot be created at all. Combined with the composite-key
 * binding check in `GetPayinComponentParameters::parseRawRequest`, this
 * prevents creation of a payin_config where the total amount matches the
 * authorization but the per-encounter distribution attributes credit to
 * encounters the caller chose.
 *
 * The method is private and reached only through `getPaymentComponentParameters`
 * which needs an injected Rainforest API client. That code path is not
 * currently reachable from an isolated (no-network) test, so this file uses
 * reflection to invoke the private method directly. The invariant it tests —
 * a summed-amount mismatch is rejected before any external state changes —
 * is the load-bearing contract; if that changes shape the reflection call
 * fails and this test is the alarm.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PaymentProcessing\Rainforest;

use InvalidArgumentException;
use Money\{
    Currency,
    Money,
};
use OpenEMR\PaymentProcessing\Rainforest\Api;
use OpenEMR\PaymentProcessing\Rainforest\EncounterData;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ApiEnsureEncountersSumToAmountIsolatedTest extends TestCase
{
    private ReflectionMethod $ensure;

    protected function setUp(): void
    {
        $this->ensure = new ReflectionMethod(Api::class, 'ensureEncountersSumToAmount');
    }

    public function testAcceptsExactMatchOnSingleEncounter(): void
    {
        // No exception — sum matches to the cent.
        $this->ensure->invoke(
            null,
            new Money('1000', new Currency('USD')),
            [$this->encounter('1000')],
        );
        $this->addToAssertionCount(1);
    }

    public function testAcceptsExactMatchOnMultipleEncounters(): void
    {
        // 400 + 350 + 250 = 1000 cents.
        $this->ensure->invoke(
            null,
            new Money('1000', new Currency('USD')),
            [
                $this->encounter('400'),
                $this->encounter('350'),
                $this->encounter('250'),
            ],
        );
        $this->addToAssertionCount(1);
    }

    public function testRejectsSumBelowAuthorizedAmount(): void
    {
        // 500 + 400 = 900, but authorization is 1000 cents. Would silently
        // credit less than the patient paid; reject to keep the recorder in
        // sync with the gateway.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not match payment amount');
        $this->ensure->invoke(
            null,
            new Money('1000', new Currency('USD')),
            [
                $this->encounter('500'),
                $this->encounter('400'),
            ],
        );
    }

    public function testRejectsSumAboveAuthorizedAmount(): void
    {
        // 500 + 600 = 1100, but authorization is 1000 cents. Would let the
        // recorder credit more than the gateway authorized.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not match payment amount');
        $this->ensure->invoke(
            null,
            new Money('1000', new Currency('USD')),
            [
                $this->encounter('500'),
                $this->encounter('600'),
            ],
        );
    }

    public function testRejectsEmptyEncountersAgainstNonZeroAmount(): void
    {
        // Sum of zero encounters is 0; a nonzero authorization must not be
        // acceptable with an empty distribution. Additional check on top of
        // the parseRawRequest check, which shouldn't reach here with []; if
        // the caller ever changes, this stays a rejection guard.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not match payment amount');
        $this->ensure->invoke(
            null,
            new Money('1000', new Currency('USD')),
            [],
        );
    }

    public function testErrorMessageIncludesDifferenceForOperatorDiagnosis(): void
    {
        // The exception message is the only telemetry an operator has when
        // debugging a rejected payin_config; pin the shape so a future
        // refactor cannot silently drop the difference figure.
        try {
            $this->ensure->invoke(
                null,
                new Money('1000', new Currency('USD')),
                [$this->encounter('900')],
            );
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('900 USD', $e->getMessage());
            $this->assertStringContainsString('1000 USD', $e->getMessage());
            $this->assertStringContainsString('100 USD', $e->getMessage());
        }
    }

    /**
     * @param numeric-string $cents
     */
    private function encounter(string $cents): EncounterData
    {
        return new EncounterData(
            id: '1',
            code: '99213',
            codeType: 'CPT4',
            amount: new Money($cents, new Currency('USD')),
        );
    }
}
