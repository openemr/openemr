<?php

/**
 * OneTimeAuthPolicyTest.
 *
 * Covers the token-consumption policy controls added to OneTimeAuth:
 *   - usageLimitReached(): enforces enforce_onetime_use, max_access_count, and a
 *     hard PIN-attempt cap against the token's access_count (token replay and PIN
 *     brute-force throttling).
 *   - pinMatches(): strict, constant-time PIN comparison that rejects
 *     type-juggling numeric equivalents accepted by a loose (!=) compare.
 *
 * These are pure functions of their arguments, so they run in the isolated suite
 * without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\OneTimeAuth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OneTimeAuthPolicyTest extends TestCase
{
    // ---- usageLimitReached() : replay / brute-force throttling (V1, V2) ----

    public function testOnetimeUseTokenIsRejectedAfterFirstConsumption(): void
    {
        $actions = ['enforce_onetime_use' => true];

        // First use (count 0) is allowed; any subsequent attempt is refused.
        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 0), 'first use must be allowed');
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 1), 'replay must be refused');
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 5), 'later replay must be refused');
    }

    public function testMaxAccessCountIsEnforced(): void
    {
        $actions = ['max_access_count' => 3];

        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 0));
        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 2));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 3), 'at the cap must be refused');
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 4));
    }

    public function testPinTokenIsCappedEvenWithoutOtherFlags(): void
    {
        // No enforce_onetime_use and unlimited max_access_count, but a PIN is
        // required: the hard cap must still bound guessing.
        $actions = ['enforce_auth_pin' => true, 'max_access_count' => 0];

        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, OneTimeAuth::MAX_PIN_ATTEMPTS - 1));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, OneTimeAuth::MAX_PIN_ATTEMPTS));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, OneTimeAuth::MAX_PIN_ATTEMPTS + 10));
    }

    public function testPinAttemptCapIsConfigurable(): void
    {
        // The effective cap is injected by the caller (from the
        // portal_onetime_max_pin_attempts global); the policy method honours it.
        $actions = ['enforce_auth_pin' => true];

        // A stricter cap of 3.
        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 2, 3));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 3, 3));

        // A looser cap of 10.
        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 9, 10));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 10, 10));

        // Default (no explicit limit) falls back to MAX_PIN_ATTEMPTS.
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, OneTimeAuth::MAX_PIN_ATTEMPTS));
    }

    /**
     * Negative test: a token that opts into no limits (the telehealth / standard
     * portal-login / document defaults) stays reusable within its lifetime.
     */
    public function testUnlimitedTokenIsNeverRefusedByPolicy(): void
    {
        $actions = [
            'enforce_onetime_use' => false,
            'enforce_auth_pin' => false,
            'max_access_count' => 0,
        ];

        foreach ([0, 1, 2, 50, 1000] as $count) {
            $this->assertFalse(
                OneTimeAuth::usageLimitReached($actions, $count),
                "unlimited token must remain usable at access_count=$count"
            );
        }
    }

    public function testMissingOrEmptyActionsAreTreatedAsUnlimited(): void
    {
        $this->assertFalse(OneTimeAuth::usageLimitReached([], 0));
        $this->assertFalse(OneTimeAuth::usageLimitReached([], 1000));
    }

    /**
     * The shipped invoice token sets enforce_onetime_use + enforce_auth_pin: the
     * first attempt (success or wrong PIN) consumes it, so replay and brute force
     * are both bounded to a single attempt.
     */
    public function testInvoiceTokenIsSingleUse(): void
    {
        $actions = [
            'enforce_onetime_use' => true,
            'enforce_auth_pin' => true,
            'extend_portal_visit' => false,
        ];

        $this->assertFalse(OneTimeAuth::usageLimitReached($actions, 0));
        $this->assertTrue(OneTimeAuth::usageLimitReached($actions, 1));
    }

    // ---- pinMatches() : strict PIN comparison (V3) ----

    #[DataProvider('typeJugglingBypassProvider')]
    public function testPinMatchesRejectsTypeJugglingEquivalents(string $expected, string $submitted): void
    {
        $this->assertFalse(
            OneTimeAuth::pinMatches($expected, $submitted),
            "wrong PIN '$submitted' must not match '$expected'"
        );
    }

    public static function typeJugglingBypassProvider(): array
    {
        return [
            'leading zero stripped'   => ['012345', '12345'],
            'leading zero stripped 2' => ['056789', '56789'],
            'scientific notation'     => ['123456', '123456e0'],
            'decimal equivalent'      => ['123456', '123456.0'],
            'zero padded'             => ['123456', '0123456'],
            'plain wrong'             => ['123456', '654321'],
        ];
    }

    public function testPinMatchesAcceptsExactPin(): void
    {
        $this->assertTrue(OneTimeAuth::pinMatches('123456', '123456'));
        // Leading-zero PIN must still work for the legitimate exact value.
        $this->assertTrue(OneTimeAuth::pinMatches('012345', '012345'));
    }

    #[DataProvider('nonStringProvider')]
    public function testPinMatchesRejectsNonStringInput(mixed $submitted): void
    {
        $this->assertFalse(OneTimeAuth::pinMatches('123456', $submitted));
    }

    public static function nonStringProvider(): array
    {
        return [
            'null'    => [null],
            'int'     => [123456],
            'float'   => [123456.0],
            'array'   => [['123456']],
            'bool'    => [true],
        ];
    }

    public function testPinMatchesRejectsEmptyString(): void
    {
        $this->assertFalse(OneTimeAuth::pinMatches('123456', ''));
    }
}
