<?php

/**
 * Tests for the practitioner-attribution authorization policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\PractitionerAttributionPolicy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PractitionerAttributionPolicyIsolatedTest extends TestCase
{
    /**
     * aclCheckCore() reaches the database, so these cases cover the half of the policy that does
     * not: a caller without admin/users may name themselves and nobody else. The privileged half
     * is exercised through the API write tests.
     */
    private function policyForUser(string $authUser, string $authUserId): PractitionerAttributionPolicy
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('authUser', $authUser);
        $session->set('authUserID', $authUserId);

        return new PractitionerAttributionPolicy($session);
    }

    public function testCallerMayAttributeToThemselves(): void
    {
        $policy = $this->policyForUser('', '7');

        $this->assertTrue($policy->mayAttributeTo(7));
        $this->assertTrue($policy->mayAttributeTo('7'));
        $policy->assertMayAttributeTo(7, 'Immunization.performer');
    }

    public function testCallerMayNotAttributeToAnotherPractitioner(): void
    {
        $policy = $this->policyForUser('', '7');

        $this->assertFalse($policy->mayAttributeTo(8));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Immunization.performer attribution to another practitioner requires admin/users');
        $policy->assertMayAttributeTo(8, 'Immunization.performer');
    }

    /**
     * An unauthenticated context must not match a practitioner id by both being empty-ish.
     */
    public function testMissingAuthUserIdNeverMatches(): void
    {
        $policy = $this->policyForUser('', '');

        $this->assertFalse($policy->mayAttributeTo(0));
        $this->assertFalse($policy->mayAttributeTo('0'));
        $this->assertFalse($policy->mayAttributeTo(7));
    }

    public function testNullSessionDeniesAttribution(): void
    {
        $policy = new PractitionerAttributionPolicy(null);

        $this->assertFalse($policy->canAttributeToAnyone());
        $this->assertFalse($policy->mayAttributeTo(7));
    }

    public function testResolveRejectsMalformedUuidBeforeTouchingTheResolver(): void
    {
        $policy = $this->policyForUser('', '7');
        $called = false;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Encounter.participant performer reference is not a valid uuid');
        try {
            $policy->resolveAndAssert(
                'not-a-uuid',
                'Encounter.participant performer',
                static function (string $bytes) use (&$called) {
                    $called = true;
                    return 7;
                }
            );
        } finally {
            $this->assertFalse($called, 'A malformed uuid must not reach the resolver');
        }
    }

    public function testResolveRejectsAnUnresolvableReference(): void
    {
        $policy = $this->policyForUser('', '7');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ServiceRequest.requester reference could not be resolved');
        $policy->resolveAndAssert(
            '8f2b5b9c-0d7a-4a1e-9d3b-6f0a1c2d3e4f',
            'ServiceRequest.requester',
            static fn(string $bytes): false => false
        );
    }

    public function testResolveReturnsTheIdWhenTheCallerIsThePractitioner(): void
    {
        $policy = $this->policyForUser('', '7');

        $resolved = $policy->resolveAndAssert(
            '8f2b5b9c-0d7a-4a1e-9d3b-6f0a1c2d3e4f',
            'MedicationRequest.requester',
            static fn(string $bytes): string => '7'
        );

        $this->assertSame(7, $resolved);
    }

    public function testResolveRejectsAttributionToSomeoneElse(): void
    {
        $policy = $this->policyForUser('', '7');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MedicationRequest.requester attribution to another practitioner requires admin/users');
        $policy->resolveAndAssert(
            '8f2b5b9c-0d7a-4a1e-9d3b-6f0a1c2d3e4f',
            'MedicationRequest.requester',
            static fn(string $bytes): int => 99
        );
    }
}
