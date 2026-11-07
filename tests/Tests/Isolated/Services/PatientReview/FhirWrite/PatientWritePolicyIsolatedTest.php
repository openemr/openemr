<?php

/**
 * Isolated tests for the patient app write policy: which clients and record types a site allows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class PatientWritePolicyIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{PatientWriteMode, PatientWriteClientType, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function modeAndClientProvider(): array
    {
        return [
            'off refuses confidential' => [PatientWriteMode::Off, PatientWriteClientType::Confidential, false],
            'off refuses public' => [PatientWriteMode::Off, PatientWriteClientType::Public, false],
            'confidential allows confidential' => [PatientWriteMode::Confidential, PatientWriteClientType::Confidential, true],
            'confidential refuses public' => [PatientWriteMode::Confidential, PatientWriteClientType::Public, false],
            'both allows confidential' => [PatientWriteMode::ConfidentialAndPublic, PatientWriteClientType::Confidential, true],
            'both allows public' => [PatientWriteMode::ConfidentialAndPublic, PatientWriteClientType::Public, true],
        ];
    }

    #[DataProvider('modeAndClientProvider')]
    public function testModeDecidesWhichClientsMayWriteAnEnabledType(
        PatientWriteMode $mode,
        PatientWriteClientType $client,
        bool $expected,
    ): void {
        $policy = new PatientWritePolicy($mode, [PatientWritableType::QuestionnaireResponse]);

        $this->assertSame($expected, $policy->allowsWrite($client, PatientWritableType::QuestionnaireResponse));
    }

    public function testATypeLeftOffIsRefusedInEveryMode(): void
    {
        foreach (PatientWriteMode::cases() as $mode) {
            $policy = new PatientWritePolicy($mode, [PatientWritableType::QuestionnaireResponse]);

            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Confidential, PatientWritableType::Condition), $mode->value);
        }
    }

    public function testNothingIsOpenWhenNoSettingsAreStored(): void
    {
        $policy = PatientWritePolicy::fromGlobals(new ParameterBag());

        $this->assertSame([], $policy->advertisedTypes());
        foreach (PatientWritableType::cases() as $type) {
            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Confidential, $type), $type->name);
            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Public, $type), $type->name);
        }
    }

    public function testAnUnrecognizedStoredModeIsTreatedAsOff(): void
    {
        $policy = PatientWritePolicy::fromGlobals(new ParameterBag([
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE->value => 'everyone',
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_CONDITION->value => '1',
        ]));

        $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Confidential, PatientWritableType::Condition));
        $this->assertSame([], $policy->advertisedTypes());
    }

    public function testStoredSettingsChooseTheModeAndTypes(): void
    {
        $policy = PatientWritePolicy::fromGlobals(new ParameterBag([
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE->value => PatientWriteMode::ConfidentialAndPublic->value,
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_QUESTIONNAIRE_RESPONSE->value => '1',
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_VITAL_SIGNS->value => '1',
            GlobalConnectorsEnum::FHIR_PATIENT_WRITE_ALLERGY_INTOLERANCE->value => '0',
        ]));

        $this->assertTrue($policy->allowsWrite(PatientWriteClientType::Public, PatientWritableType::VitalSignObservation));
        $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Public, PatientWritableType::AllergyIntolerance));
        $this->assertSame(
            [PatientWritableType::QuestionnaireResponse, PatientWritableType::VitalSignObservation],
            $policy->advertisedTypes(),
        );
    }

    public function testEnabledTypesAreNotAdvertisedWhileTheModeIsOff(): void
    {
        $policy = new PatientWritePolicy(PatientWriteMode::Off, [PatientWritableType::Condition]);

        $this->assertSame([], $policy->advertisedTypes());
    }

    public function testEveryTypeHasItsOwnSettingAndAValidResourceType(): void
    {
        $settings = [];
        foreach (PatientWritableType::cases() as $type) {
            $settings[] = $type->setting()->value;
            $this->assertNotSame('', $type->fhirResourceType()->value);
        }

        $this->assertCount(count($settings), array_unique($settings));
    }

    public function testEverySettingAppearsInTheGlobalsPageDefinitions(): void
    {
        $source = file_get_contents(dirname(__DIR__, 6) . '/library/globals.inc.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE->value', $source);
        foreach (PatientWritableType::cases() as $type) {
            $this->assertStringContainsString('GlobalConnectorsEnum::' . $type->setting()->name . '->value', $source, $type->name);
        }
    }
}
