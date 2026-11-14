<?php

/**
 * SMARTSessionTokenContextBuilderTest verifies SMART EHR launch context returned with an access token.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\SMARTSessionTokenContextBuilder;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SMARTSessionTokenContextBuilderTest extends TestCase
{
    public function testExistingAppointmentContextIsUnchanged(): void
    {
        $token = new SMARTLaunchToken('patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_APPOINTMENT_DIALOG);
        $token->setAppointmentUuid('appointment-uuid');

        $context = $this->buildContext($token);

        $fhirContext = $context['fhirContext'];
        $this->assertIsArray($fhirContext);
        $this->assertCount(1, $fhirContext);
        $this->assertInstanceOf(FHIRReference::class, $fhirContext[0]);
        $this->assertSame(
            'Appointment/appointment-uuid',
            (string)$fhirContext[0]->getReference(),
            'Existing appointment fhirContext should remain unchanged'
        );
    }

    public function testAdditionalFhirContextIsAppendedToAppointmentContext(): void
    {
        $token = new SMARTLaunchToken('patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT);
        $token->setAppointmentUuid('appointment-uuid');
        $token->addFhirContextReference('Questionnaire', 'questionnaire-uuid');
        $token->addFhirContextReference('QuestionnaireResponse', 'response-uuid');
        $token->setAppContext('{"workflow":"questionnaire-assessment"}');

        $context = $this->buildContext($token);

        $this->assertSame(
            SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT,
            $context['intent'],
            'Questionnaire assessment intent should be preserved'
        );
        $fhirContext = $context['fhirContext'];
        $this->assertIsArray($fhirContext);
        $this->assertCount(3, $fhirContext);
        $this->assertInstanceOf(FHIRReference::class, $fhirContext[0]);
        $this->assertSame('Appointment/appointment-uuid', (string)$fhirContext[0]->getReference());
        $this->assertSame(
            ['reference' => 'Questionnaire/questionnaire-uuid'],
            $fhirContext[1],
            'Questionnaire context should follow the existing appointment context'
        );
        $this->assertSame(
            ['reference' => 'QuestionnaireResponse/response-uuid'],
            $fhirContext[2],
            'QuestionnaireResponse context should follow the Questionnaire context'
        );
        $this->assertSame(
            '{"workflow":"questionnaire-assessment"}',
            $context['appContext'],
            'SMART appContext should be returned with the launch context'
        );
    }

    public function testPortalLaunchByItsPatientCarriesThePatient(): void
    {
        $token = new SMARTLaunchToken('patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_PORTAL);

        $context = $this->buildContext($token, ['user_id' => 'patient-uuid', 'puuid' => 'patient-uuid']);

        $this->assertSame('patient-uuid', $context['patient'], 'A portal launch hands the app its patient');
        $this->assertSame(SMARTLaunchToken::INTENT_PATIENT_PORTAL, $context['intent']);
    }

    public function testPatientLoginCannotUseALaunchForAnotherPatient(): void
    {
        $token = new SMARTLaunchToken('other-patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_PORTAL);

        $this->assertRefused($token, ['user_id' => 'patient-uuid', 'puuid' => 'patient-uuid'], 'Launch context is not for the logged in patient');
    }

    public function testPatientLoginCannotUseAStaffLaunchForAnotherPatient(): void
    {
        $token = new SMARTLaunchToken('other-patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG);

        $this->assertRefused($token, ['user_id' => 'patient-uuid', 'puuid' => 'patient-uuid'], 'Launch context is not for the logged in patient');
    }

    public function testStaffLoginCannotUseAPortalLaunch(): void
    {
        $token = new SMARTLaunchToken('patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_PORTAL);

        $this->assertRefused($token, ['user_id' => 'staff-user-uuid', 'puuid' => 'patient-uuid'], 'A patient portal launch must be used by its patient');
    }

    public function testStaffLaunchIsUnchangedForStaffLogin(): void
    {
        $token = new SMARTLaunchToken('patient-uuid');
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG);

        $context = $this->buildContext($token, ['user_id' => 'staff-user-uuid']);

        $this->assertSame('patient-uuid', $context['patient'], 'A staff EHR launch keeps the patient it names');
    }

    /**
     * Asserts the launch is refused as an invalid launch context, for the given reason, not some other OAuth failure.
     *
     * @param array<string, string> $sessionValues session values other than the launch
     */
    private function assertRefused(SMARTLaunchToken $token, array $sessionValues, string $expectedMessage): void
    {
        try {
            $this->buildContext($token, $sessionValues);
        } catch (OAuthServerException $e) {
            $this->assertSame('invalid_launch_context', $e->getErrorType());
            $this->assertSame($expectedMessage, $e->getMessage());
            return;
        }
        $this->fail('The launch should have been refused');
    }

    /**
     * @param array<string, string> $sessionValues session values other than the launch
     * @return array<string, mixed>
     */
    private function buildContext(SMARTLaunchToken $token, array $sessionValues = []): array
    {
        $serialized = $token->serialize();
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnCallback(
            static fn(string $name, mixed $default = null): mixed => $name === 'launch' ? $serialized : ($sessionValues[$name] ?? $default)
        );

        $serverConfig = $this->createMock(ServerConfig::class);
        $serverConfig->method('getOauthAuthorizationUrl')->willReturn('https://example.test/oauth2/default');

        $builder = new SMARTSessionTokenContextBuilder($serverConfig, $session);
        /** @var array<string, mixed> $context */
        $context = $builder->getEHRLaunchContext();

        return $context;
    }
}
