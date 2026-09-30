<?php

/**
 * Isolated tests for HttpRestRequest::requestHasUnconstrainedScopeEntity(), used by endpoints
 * that cannot narrow their output by a scope constraint (bulk export).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
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

    /**
     * @return array<string, array{list<string>, string, bool, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scopeProvider(): array
    {
        return [
            'unconstrained v1 read' => [['system/Observation.read'], 'system/Observation.read', true, true],
            'unconstrained v2 rs' => [['system/Observation.rs'], 'system/Observation.read', true, true],
            'category-only scope' => [['system/Observation.rs?' . self::LAB], 'system/Observation.read', true, false],
            'category scope beside unconstrained' => [['system/Observation.rs?' . self::LAB, 'system/Observation.rs'], 'system/Observation.read', true, true],
            'other resource only' => [['system/Condition.rs'], 'system/Observation.read', false, false],
            'no scopes' => [[], 'system/Observation.read', false, false],
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
}
