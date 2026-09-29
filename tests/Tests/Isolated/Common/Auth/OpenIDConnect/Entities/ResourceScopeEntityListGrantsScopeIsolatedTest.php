<?php

/**
 * Isolated tests for ResourceScopeEntityList::grantsScope() -- union-of-permissions and
 * constraint-aware grant checks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResourceScopeEntityListGrantsScopeIsolatedTest extends TestCase
{
    private const VITALS = 'category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs';
    private const LAB = 'category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory';

    /**
     * @param list<string> $held
     */
    private function grants(array $held, string $candidate): bool
    {
        $validators = (new ScopeValidatorFactory())->buildScopeValidatorArray($held);
        $entity = ScopeEntity::createFromString($candidate);
        $key = $entity->getScopeLookupKey();
        return isset($validators[$key]) && $validators[$key]->grantsScope($entity);
    }

    /**
     * @return array<string, array{list<string>, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function grantProvider(): array
    {
        return [
            // union across split v2 scopes -- the shape ServerScopeListEntity advertises
            'rs + cud grants cruds' => [['user/QuestionnaireResponse.rs', 'user/QuestionnaireResponse.cud'], 'user/QuestionnaireResponse.cruds', true],
            'rs + cud grants crs' => [['user/QuestionnaireResponse.rs', 'user/QuestionnaireResponse.cud'], 'user/QuestionnaireResponse.crs', true],
            'rs alone does not grant cruds' => [['user/QuestionnaireResponse.rs'], 'user/QuestionnaireResponse.cruds', false],
            // v1/v2 are spellings of the same permissions
            'read + cu grants write? no, d missing' => [['patient/Goal.read', 'patient/Goal.cu'], 'patient/Goal.write', false],
            'read + cud grants write' => [['patient/Goal.read', 'patient/Goal.cud'], 'patient/Goal.write', true],
            'rs + write grants cruds' => [['user/Goal.rs', 'user/Goal.write'], 'user/Goal.cruds', true],
            'read grants r' => [['user/Goal.read'], 'user/Goal.r', true],
            'r + s grants read' => [['user/Goal.r', 'user/Goal.s'], 'user/Goal.read', true],
            // contexts never mix
            'patient read does not grant user read' => [['patient/Goal.read'], 'user/Goal.read', false],
            // constraints narrow, never widen
            'category scope does not grant unrestricted' => [['patient/Observation.rs?' . self::VITALS], 'patient/Observation.rs', false],
            'unrestricted grants category scope' => [['patient/Observation.rs'], 'patient/Observation.rs?' . self::VITALS, true],
            'same category grants' => [['patient/Observation.rs?' . self::VITALS], 'patient/Observation.rs?' . self::VITALS, true],
            'other category does not grant' => [['patient/Observation.rs?' . self::VITALS], 'patient/Observation.rs?' . self::LAB, false],
            'v1 unrestricted read grants category rs' => [['patient/Observation.read'], 'patient/Observation.rs?' . self::LAB, true],
            // operations and non-resource scopes keep exact containment
            'operation granted by same operation' => [['system/Patient.$export'], 'system/Patient.$export', true],
            'operation not granted by read' => [['system/Patient.rs'], 'system/Patient.$export', false],
            'openid granted' => [['openid'], 'openid', true],
            // launch and API scopes parse with a "resource" but carry no CRUDS permissions
            'api:oemr granted' => [['api:oemr'], 'api:oemr', true],
            'api:fhir granted' => [['api:fhir'], 'api:fhir', true],
            'launch/patient granted' => [['launch/patient'], 'launch/patient', true],
            'launch/patient not granted by launch' => [['launch'], 'launch/patient', false],
        ];
    }

    /**
     * @param list<string> $held
     */
    #[DataProvider('grantProvider')]
    public function testGrantsScope(array $held, string $candidate, bool $expected): void
    {
        $this->assertSame($expected, $this->grants($held, $candidate));
    }

    /**
     * containsScope() stays constraint-blind on purpose: the resource server relies on a
     * category-restricted token reaching the unconstrained endpoint.
     */
    /**
     * Every scope the server advertises must pass the grant check against the server list,
     * or client registration with that scope is rejected (400).
     */
    public function testEveryServerAdvertisedScopeIsGrantable(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        foreach ([false, true] as $systemScopesEnabled) {
            $serverScopes = new ServerScopeListEntity();
            $serverScopes->setSystemScopesEnabled($systemScopesEnabled);
            $all = array_values(array_filter($serverScopes->getAllSupportedScopesList(), is_string(...)));
            $this->assertNotEmpty($all);
            foreach ($all as $scope) {
                $this->assertTrue($this->grants($all, $scope), "server-advertised scope {$scope} must be grantable");
            }
        }
    }

    public function testContainsScopeRemainsConstraintBlindForRuntime(): void
    {
        $validators = (new ScopeValidatorFactory())->buildScopeValidatorArray(['patient/Observation.rs?' . self::VITALS]);
        $this->assertTrue($validators['patient/Observation']->containsScope(ScopeEntity::createFromString('patient/Observation.rs')));
    }
}
