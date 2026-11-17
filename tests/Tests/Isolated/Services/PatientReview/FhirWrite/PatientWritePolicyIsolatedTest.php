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

    /**
     * A site opens each type separately, so a client mode alone never opens a type.
     */
    public function testATypeLeftOffIsRefusedInEveryMode(): void
    {
        foreach (PatientWriteMode::cases() as $mode) {
            $policy = new PatientWritePolicy($mode, [PatientWritableType::QuestionnaireResponse]);

            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Confidential, PatientWritableType::Condition), $mode->value);
        }
    }

    /**
     * A site that has never saved these settings has no rows for them, and must stay closed.
     */
    public function testNothingIsOpenWhenNoSettingsAreStored(): void
    {
        $policy = PatientWritePolicy::fromGlobals(new ParameterBag());

        $this->assertSame([], $policy->advertisedTypes());
        foreach (PatientWritableType::cases() as $type) {
            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Confidential, $type), $type->name);
            $this->assertFalse($policy->allowsWrite(PatientWriteClientType::Public, $type), $type->name);
        }
    }

    /**
     * A bad stored mode must not open writes, even with a type switched on.
     */
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
        // Vital signs is switched on but the API doesn't serve it yet, so it is not advertised.
        $this->assertSame([PatientWritableType::QuestionnaireResponse], $policy->advertisedTypes());
    }

    /**
     * Only a served, enabled type opens the permission gates, and only while the mode is on.
     */
    public function testOnlyAnAdvertisedTypeOpensTheGates(): void
    {
        $on = new PatientWritePolicy(PatientWriteMode::Confidential, PatientWritableType::cases());
        $off = new PatientWritePolicy(PatientWriteMode::Off, PatientWritableType::cases());

        $this->assertTrue($on->opensResource('QuestionnaireResponse'));
        $this->assertFalse($on->opensResource('Observation'), 'not served yet');
        $this->assertFalse($on->opensResource('MedicationRequest'), 'never writable');
        $this->assertFalse($on->opensResource('Patient'), 'never writable');
        $this->assertFalse($off->opensResource('QuestionnaireResponse'));
    }

    /**
     * A write scope is issued only to a client the mode allows.
     */
    public function testAWriteScopeIsGrantedOnlyToAClientTheModeAllows(): void
    {
        $confidentialOnly = new PatientWritePolicy(PatientWriteMode::Confidential, [PatientWritableType::QuestionnaireResponse]);
        $both = new PatientWritePolicy(PatientWriteMode::ConfidentialAndPublic, [PatientWritableType::QuestionnaireResponse]);

        $this->assertTrue($confidentialOnly->grantsWriteScope(PatientWriteClientType::Confidential, 'QuestionnaireResponse'));
        $this->assertFalse($confidentialOnly->grantsWriteScope(PatientWriteClientType::Public, 'QuestionnaireResponse'));
        $this->assertTrue($both->grantsWriteScope(PatientWriteClientType::Public, 'QuestionnaireResponse'));
        $this->assertFalse($both->grantsWriteScope(PatientWriteClientType::Public, 'Condition'));
    }

    /**
     * Each type the API serves needs its approval and read-back in place; this list changes
     * only on purpose.
     */
    public function testOnlyQuestionnaireResponseIsServedByTheApi(): void
    {
        $served = array_values(array_filter(
            PatientWritableType::cases(),
            static fn (PatientWritableType $type): bool => $type->servedByApi(),
        ));

        $this->assertSame([PatientWritableType::QuestionnaireResponse], $served);
    }

    public function testEnabledTypesAreNotAdvertisedWhileTheModeIsOff(): void
    {
        $policy = new PatientWritePolicy(PatientWriteMode::Off, [PatientWritableType::Condition]);

        $this->assertSame([], $policy->advertisedTypes());
    }

    /**
     * Two types sharing a setting would open one whenever the other was switched on.
     */
    public function testEveryTypeHasItsOwnSettingAndAValidResourceType(): void
    {
        $settings = [];
        foreach (PatientWritableType::cases() as $type) {
            $settings[] = $type->setting()->value;
            $this->assertNotSame('', $type->fhirResourceType()->value);
        }

        $this->assertCount(count($settings), array_unique($settings));
    }

    /**
     * Off by default is the safety property of this feature, so a default that opened writes has
     * to fail here. library/globals.inc.php can't be loaded in an isolated test because xl() needs
     * the database, so this reads each entry from the Connectors group in the source.
     */
    public function testEverySettingIsAConnectorsEntryThatDefaultsToOff(): void
    {
        $source = file_get_contents(dirname(__DIR__, 6) . '/library/globals.inc.php');
        $this->assertIsString($source);
        $this->assertSame(1, preg_match("/^    'Connectors' => \[\n(.*?)^    \],\n/ms", $source, $group));

        $mode = $this->settingEntry($group[1], GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE);
        $this->assertStringContainsString("],\n            PatientWriteMode::Off->value,\n", $mode);

        foreach (PatientWritableType::cases() as $type) {
            $entry = $this->settingEntry($group[1], $type->setting());
            $this->assertStringContainsString("'bool',\n            '0',\n", $entry, $type->name);
        }
    }

    /**
     * The source text of one setting's entry in a globals group.
     */
    private function settingEntry(string $group, GlobalConnectorsEnum $setting): string
    {
        // PHP keeps the last of a repeated array key, so a second entry would be the one that applies.
        $this->assertSame(1, substr_count($group, 'GlobalConnectorsEnum::' . $setting->name . '->value =>'), $setting->name);

        $pattern = '/^        GlobalConnectorsEnum::' . $setting->name . '->value => \[\n(.*?)^        \],\n/ms';
        $this->assertSame(1, preg_match($pattern, $group, $entry), $setting->name);

        return $entry[1];
    }
}
