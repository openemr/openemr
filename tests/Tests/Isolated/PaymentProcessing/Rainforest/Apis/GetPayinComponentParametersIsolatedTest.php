<?php

/**
 * Isolated tests for the `GetPayinComponentParameters::selectPatientId`
 * trust-boundary invariant.
 *
 * The Rainforest portal setup endpoint accepts an authenticated portal-patient
 * session AND a JSON body with a `patientId` field. Historically the endpoint
 * signed the body's `patientId` straight into the Rainforest `payin_config`
 * metadata — the webhook processor later attributed the credit to whatever
 * patient the body named. `selectPatientId` centralizes the rule: a trusted
 * caller-supplied id wins over the body's claim, and if neither is present
 * the call is rejected before any network traffic to the gateway.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PaymentProcessing\Rainforest\Apis;

use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class GetPayinComponentParametersIsolatedTest extends TestCase
{
    // ------- Trusted caller wins over body -------

    public function testTrustedPatientIdOverridesBodyPatientId(): void
    {
        // Attacker sends `patientId: 999` in the body; the portal endpoint
        // has already resolved the session pid to `42`. The trusted value
        // must win.
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '42',
            bodyPatientId: '999',
        );

        $this->assertSame('42', $picked);
    }

    public function testTrustedPatientIdWinsEvenWhenBodyIsIdenticalString(): void
    {
        // Regression sentinel: identical strings shouldn't reveal a code
        // path that reads from the body preferentially.
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '42',
            bodyPatientId: '42',
        );

        $this->assertSame('42', $picked);
    }

    public function testTrustedPatientIdWinsWhenBodyIsNull(): void
    {
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '42',
            bodyPatientId: null,
        );

        $this->assertSame('42', $picked);
    }

    public function testTrustedPatientIdWinsWhenBodyIsEmptyString(): void
    {
        // Empty-string body still gets overridden — an empty patientId
        // would otherwise flow through and mis-attribute at recorder time.
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '42',
            bodyPatientId: '',
        );

        $this->assertSame('42', $picked);
    }

    // ------- Fallback to body when no trusted id provided -------

    public function testFallsBackToBodyWhenTrustedIsNull(): void
    {
        // Legacy caller shape kept for backward compatibility. Only the
        // portal endpoint should be able to reach here in real code; any
        // new caller should pass a trusted id.
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: null,
            bodyPatientId: '77',
        );

        $this->assertSame('77', $picked);
    }

    public function testFallsBackToBodyWhenTrustedIsEmptyString(): void
    {
        // An empty-string trusted id is treated as "not provided" and
        // falls through to the body — the portal endpoint refuses the
        // request before reaching this method if session pid is empty,
        // but this keeps the contract predictable if a future caller
        // passes an empty session-derived string by mistake.
        $picked = GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '',
            bodyPatientId: '77',
        );

        $this->assertSame('77', $picked);
    }

    // ------- No source available: throws -------

    public function testThrowsWhenNeitherTrustedNorBodyProvideAValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('patientId must be supplied');

        GetPayinComponentParameters::selectPatientId(
            trustedPatientId: null,
            bodyPatientId: null,
        );
    }

    public function testThrowsWhenBothTrustedAndBodyAreEmpty(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('patientId must be supplied');

        GetPayinComponentParameters::selectPatientId(
            trustedPatientId: '',
            bodyPatientId: '',
        );
    }
}
