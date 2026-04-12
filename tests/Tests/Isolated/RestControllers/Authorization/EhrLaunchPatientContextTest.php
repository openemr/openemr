<?php

/**
 * EhrLaunchPatientContextTest - Verify patient UUID from a SMART launch token
 * propagates into the API session during the EHR launch skip-auth flow.
 *
 * The processAuthorizeFlowForLaunch() method in AuthorizationController is
 * private and deeply coupled to the OAuth server, database, and session
 * infrastructure. This test exercises the specific contract introduced by the
 * fix: deserializing a launch token and transferring its patient UUID into the
 * API session data that ultimately reaches saveTrustedUser().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Crypto\KeySource;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\TestCase;

class EhrLaunchPatientContextTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset first so a prior test failure can't leak an override.
        ServiceContainer::reset();

        // Provide a no-op crypto implementation so SMARTLaunchToken can
        // serialize/deserialize without database-backed encryption keys.
        $crypto = new class implements CryptoInterface {
            public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
            {
                // Use reversible encoding instead of real encryption
                return 'ENC:' . ($value ?? '');
            }

            public function decryptStandard(?string $value, KeySource $keySource = KeySource::Drive, ?int $minimumVersion = null): false|string
            {
                if ($value === null || !str_starts_with($value, 'ENC:')) {
                    return false;
                }
                return substr($value, 4);
            }

            public function cryptCheckStandard(?string $value): bool
            {
                return $value !== null && str_starts_with($value, 'ENC:');
            }
        };
        ServiceContainer::override(CryptoInterface::class, $crypto);
    }

    protected function tearDown(): void
    {
        ServiceContainer::reset();
    }

    /**
     * Verify that a serialized launch token containing a patient UUID
     * results in puuid being set in the API session data.
     *
     * This mirrors the logic in processAuthorizeFlowForLaunch():
     *   $launchToken = SMARTLaunchToken::deserializeToken($launch);
     *   ...
     *   $patientUuid = $launchToken->getPatient();
     *   if ($patientUuid) {
     *       $apiSession['puuid'] = $patientUuid;
     *   }
     */
    public function testPatientUuidFromLaunchTokenIsPreservedInApiSession(): void
    {
        $patientUuid = '9a7b8c6d-1234-5678-abcd-ef0123456789';
        $encounterId = 'enc-9999-8888-7777';

        $token = new SMARTLaunchToken($patientUuid, $encounterId);
        $serialized = $token->serialize();

        // Deserialize exactly as processAuthorizeFlowForLaunch does
        $launchToken = SMARTLaunchToken::deserializeToken($serialized);

        // Build apiSession the same way the method does
        $apiSession = [
            'site_id' => 'default',
            'authRequestSerial' => '{}',
        ];
        $apiSession['launch'] = $serialized;

        // This is the fix: extract patient UUID from the launch token
        $extractedPatientUuid = $launchToken->getPatient();
        if ($extractedPatientUuid) {
            $apiSession['puuid'] = $extractedPatientUuid;
        }

        $apiSession['client_id'] = 'test-client';
        $apiSession['user_id'] = 'user-uuid-1234';
        $apiSession['scopes'] = 'launch openid';
        $apiSession['persist_login'] = 0;

        // Verify the patient UUID is present in the session
        $this->assertArrayHasKey('puuid', $apiSession, 'API session must contain puuid when launch token has a patient');
        $this->assertSame($patientUuid, $apiSession['puuid'], 'puuid must match the patient UUID from the launch token');

        // Verify the JSON-encoded session cache (passed to saveTrustedUser) contains puuid
        $sessionCache = json_encode($apiSession, JSON_THROW_ON_ERROR);
        $decoded = json_decode($sessionCache, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        $this->assertSame($patientUuid, $decoded['puuid'], 'JSON session cache must preserve puuid');
    }

    /**
     * Verify that when a launch token has no patient UUID, puuid is NOT set
     * in the API session (no spurious null/empty value).
     */
    public function testApiSessionOmitsPuuidWhenLaunchTokenHasNoPatient(): void
    {
        $token = new SMARTLaunchToken(null, 'enc-1111-2222');
        $serialized = $token->serialize();

        $launchToken = SMARTLaunchToken::deserializeToken($serialized);

        $apiSession = [
            'site_id' => 'default',
        ];
        $apiSession['launch'] = $serialized;

        // Apply the same conditional from processAuthorizeFlowForLaunch
        $patientUuid = $launchToken->getPatient();
        if ($patientUuid) {
            $apiSession['puuid'] = $patientUuid;
        }

        $apiSession['client_id'] = 'test-client';
        $apiSession['user_id'] = 'user-uuid-1234';

        $this->assertArrayNotHasKey('puuid', $apiSession, 'API session must not contain puuid when launch token has no patient');
    }

    /**
     * Verify that a launch token with only a patient (no encounter) still
     * produces a valid puuid in the session.
     */
    public function testPatientOnlyLaunchTokenSetsPuuid(): void
    {
        $patientUuid = 'patient-only-uuid-5678';

        $token = new SMARTLaunchToken($patientUuid);
        $serialized = $token->serialize();
        $launchToken = SMARTLaunchToken::deserializeToken($serialized);

        $apiSession = [];
        $patientFromToken = $launchToken->getPatient();
        if ($patientFromToken) {
            $apiSession['puuid'] = $patientFromToken;
        }

        $this->assertArrayHasKey('puuid', $apiSession, 'puuid must be set from a patient-only launch token');
        $this->assertSame($patientUuid, $apiSession['puuid'], 'puuid must match the original patient UUID');
    }
}
