<?php

/**
 * Isolated tests for HttpRestRequest::requestHasUnconstrainedScopeEntity(), used by endpoints
 * that cannot narrow their output by a scope constraint (bulk export).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HttpRestRequestUnconstrainedScopeIsolatedTest extends TestCase
{
    private const LAB = 'category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory';
    private const SOCIAL = 'category=http://terminology.hl7.org/CodeSystem/observation-category|social-history';

    /**
     * @return array<string, array{list<string>, string, bool, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scopeProvider(): array
    {
        return [
            // Baseline matrix: a single system/Observation scope held against a
            // system/Observation.read lookup. Covers no-scopes, resource mismatch,
            // and the constrained/unconstrained/mixed branches of the matching key.
            'unconstrained v1 read' => [['system/Observation.read'], 'system/Observation.read', true, true],
            'unconstrained v2 rs' => [['system/Observation.rs'], 'system/Observation.read', true, true],
            'category-only scope' => [['system/Observation.rs?' . self::LAB], 'system/Observation.read', true, false],
            'category scope beside unconstrained' => [['system/Observation.rs?' . self::LAB, 'system/Observation.rs'], 'system/Observation.read', true, true],
            'other resource only' => [['system/Condition.rs'], 'system/Observation.read', false, false],
            'no scopes' => [[], 'system/Observation.read', false, false],

            // getAllContainedScopesForScopeEntity() short-circuits to [] when the
            // required scope's lookup key is absent, so a token scope on a sibling
            // context cannot authorize a different context's bulk export.
            'context mismatch user vs system' => [['user/Observation.rs'], 'system/Observation.read', false, false],
            'context mismatch patient vs system' => [['patient/Observation.rs'], 'system/Observation.read', false, false],

            // Two different constrained scopes for the same resource, no unconstrained
            // sibling. Each entry fails hasConstraints() === false, so the loop
            // returns false even though the token legitimately grants read.
            'two constrained scopes, none unconstrained' => [
                ['system/Observation.rs?' . self::LAB, 'system/Observation.rs?' . self::SOCIAL],
                'system/Observation.read',
                true,
                false,
            ],

            // Context parity: patient and user contexts flow through the same
            // branches as system. Each confirms unconstrained-wins and
            // constrained-only loses on the non-system contexts.
            'patient unconstrained' => [['patient/Observation.rs'], 'patient/Observation.read', true, true],
            'patient constrained only' => [['patient/Observation.rs?' . self::LAB], 'patient/Observation.read', true, false],
            'user unconstrained' => [['user/Observation.rs'], 'user/Observation.read', true, true],
            'user constrained only' => [['user/Observation.rs?' . self::LAB], 'user/Observation.read', true, false],

            // v1 write spelling (CUD) is unconstrained and must satisfy a required
            // v1 write check. Guards against a regression where only the v2 CRUDS
            // flags are consulted.
            'v1 write unconstrained' => [['system/Patient.write'], 'system/Patient.write', true, true],

            // Order independence: unconstrained listed first vs last against the
            // same constrained sibling. Both must resolve the same way, since the
            // method is a plain any-unconstrained-wins scan.
            'unconstrained listed first' => [['system/Observation.rs', 'system/Observation.rs?' . self::LAB], 'system/Observation.read', true, true],
            'unconstrained listed last' => [['system/Observation.rs?' . self::LAB, 'system/Observation.rs'], 'system/Observation.read', true, true],

            // v1 permission containment: holding read must NOT satisfy a required
            // write even when the resource / context / unconstrained-ness all
            // align. Exercises the v1-write branch of ScopeEntity::containsScope
            // separately from the v2 CRUDS branch covered below.
            'v1 read token vs v1 write required' => [['system/Patient.read'], 'system/Patient.write', false, false],
        ];
    }

    /**
     * @param list<string> $tokenScopes
     */
    #[DataProvider('scopeProvider')]
    public function testUnconstrainedScopeIsRequired(array $tokenScopes, string $required, bool $hasScope, bool $hasUnconstrained): void
    {
        $request = HttpRestRequest::create('/fhir/$export', 'GET');
        $request->setAccessTokenScopeValidationArray((new ScopeValidatorFactory())->buildScopeValidatorArray($tokenScopes));
        $scope = ScopeEntity::createFromString($required);

        $this->assertSame($hasScope, $request->requestHasScopeEntity($scope), 'constraint-blind check');
        $this->assertSame($hasUnconstrained, $request->requestHasUnconstrainedScopeEntity($scope));
    }

    /**
     * A freshly-created request has an empty access-token scope map. The method
     * must not throw and must report "no unconstrained scope" rather than
     * defaulting open.
     */
    public function testEmptyScopeMapReturnsFalse(): void
    {
        $request = HttpRestRequest::create('/fhir/$export', 'GET');
        $scope = ScopeEntity::createFromString('system/Observation.read');

        $this->assertFalse($request->requestHasUnconstrainedScopeEntity($scope));
    }

    /**
     * The ".c" permission does not satisfy a required ".read". Confirms that
     * requestHasUnconstrainedScopeEntity() delegates permission containment
     * (via containsScope()) rather than treating any lookup-key hit as a pass.
     * The scope IS unconstrained; the "no" is driven by the permission mismatch.
     */
    public function testUnconstrainedScopeWithInsufficientPermissionReturnsFalse(): void
    {
        $request = HttpRestRequest::create('/fhir/$export', 'GET');
        $request->setAccessTokenScopeValidationArray(
            (new ScopeValidatorFactory())->buildScopeValidatorArray(['system/Observation.c'])
        );
        $scope = ScopeEntity::createFromString('system/Observation.read');

        $this->assertFalse($request->requestHasUnconstrainedScopeEntity($scope));
    }
}
