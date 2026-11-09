<?php

/**
 * Isolated tests for patient app write scopes: offered only for a type a site opened, and
 * issued only to a kind of client the site's mode allows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Repositories;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use PHPUnit\Framework\TestCase;

class ScopeRepositoryPatientWriteIsolatedTest extends TestCase
{
    private const SCOPES = ['patient/QuestionnaireResponse.rs', 'patient/QuestionnaireResponse.cu'];

    /** @var array<string, mixed> */
    private array $saved = [];

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach ([GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE, GlobalConnectorsEnum::FHIR_PATIENT_WRITE_QUESTIONNAIRE_RESPONSE] as $setting) {
            $this->saved[$setting->value] = $globals->get($setting->value);
        }
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach ($this->saved as $name => $value) {
            $globals->set($name, $value);
        }
    }

    private function siteSettings(PatientWriteMode $mode): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->set(GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE->value, $mode->value);
        $globals->set(GlobalConnectorsEnum::FHIR_PATIENT_WRITE_QUESTIONNAIRE_RESPONSE->value, '1');
    }

    /**
     * @return list<string>
     */
    private function finalized(bool $confidential): array
    {
        $client = new ClientEntity();
        $client->setIdentifier('app');
        $client->setIsConfidential($confidential);
        $client->setScopes(self::SCOPES);
        $scopes = (new ScopeRepository())->finalizeScopes(
            array_map(ScopeEntity::createFromString(...), self::SCOPES),
            'authorization_code',
            $client,
        );
        return array_values(array_map(static fn ($scope): string => $scope->getIdentifier(), $scopes));
    }

    public function testNoPatientWriteScopeIsOfferedUntilATypeIsOpened(): void
    {
        $list = new ServerScopeListEntity();
        $this->assertNotContains('patient/QuestionnaireResponse.cu', $list->fhirResourceScopesV2());

        $list->setPatientWriteResources(['QuestionnaireResponse']);
        $scopes = $list->fhirResourceScopesV2();
        $this->assertContains('patient/QuestionnaireResponse.cu', $scopes);
        $this->assertNotContains('patient/Observation.cu', $scopes);
        $this->assertNotContains('patient/QuestionnaireResponse.cud', $scopes, 'an app cannot delete');
    }

    public function testAPublicAppGetsNoWriteScopeWhenOnlyConfidentialAppsMayWrite(): void
    {
        $this->siteSettings(PatientWriteMode::Confidential);

        $this->assertSame(self::SCOPES, $this->finalized(true));
        $this->assertSame(['patient/QuestionnaireResponse.rs'], $this->finalized(false));
    }

    public function testBothKindsOfAppGetTheWriteScopeWhenTheModeAllowsBoth(): void
    {
        $this->siteSettings(PatientWriteMode::ConfidentialAndPublic);

        $this->assertSame(self::SCOPES, $this->finalized(false));
    }

    public function testNoAppGetsTheWriteScopeWhileTheModeIsOff(): void
    {
        $this->siteSettings(PatientWriteMode::Off);

        $this->assertSame(['patient/QuestionnaireResponse.rs'], $this->finalized(true));
    }
}
