<?php

/**
 * Tests for server scope catalog cache transitions and description inputs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Samuel Smith <sm9sn1@gmail.com>
 * @copyright Copyright (c) 2026 Samuel Smith <sm9sn1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerScopeListEntityTest extends TestCase
{
    /** @param callable(ServerScopeListEntity): list<string> $getScopes */
    #[DataProvider('systemScopeCatalogProvider')]
    public function testSystemScopeChangesInvalidatePopulatedCaches(
        callable $getScopes,
        string $ordinaryScope,
        string $systemScope,
    ): void {
        $catalog = new ServerScopeListEntity();
        foreach ([false, true, false, true] as $enabled) {
            $catalog->setSystemScopesEnabled($enabled);
            $scopes = $getScopes($catalog);
            self::assertContains($ordinaryScope, $scopes);
            if ($enabled) {
                self::assertContains($systemScope, $scopes);
            } else {
                self::assertNotContains($systemScope, $scopes);
            }
            self::assertSame($scopes, $getScopes($catalog));
        }
    }

    /**
     * @return iterable<string, array{callable(ServerScopeListEntity): list<string>, string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function systemScopeCatalogProvider(): iterable
    {
        yield 'SMART requirements' => [
            static fn (ServerScopeListEntity $catalog): array => $catalog->requiredSmartOnFhirScopes(),
            'api:fhir',
            'system/Patient.$export',
        ];
        yield 'FHIR v1' => [
            static fn (ServerScopeListEntity $catalog): array => $catalog->fhirResourceScopesV1(),
            'user/Patient.read',
            'system/Patient.read',
        ];
        yield 'FHIR v2' => [
            static fn (ServerScopeListEntity $catalog): array => $catalog->fhirResourceScopesV2(),
            'user/Patient.rs',
            'system/Patient.rs',
        ];
    }

    public function testSystemScopeChangesPreserveStandardApiCatalogs(): void
    {
        $catalog = new ServerScopeListEntity();
        $v1 = $catalog->apiScopes();
        $v2 = $catalog->getV2ApiScopes();
        self::assertContains('user/patient.read', $v1);
        self::assertContains('user/patient.crus', $v2);

        foreach ([true, false] as $enabled) {
            $catalog->setSystemScopesEnabled($enabled);
            self::assertSame($v1, $catalog->apiScopes());
            self::assertSame($v2, $catalog->getV2ApiScopes());
        }
    }

    public function testAggregateCatalogReflectsCacheTransitionsAndDeduplicatesScopes(): void
    {
        $catalog = new ServerScopeListEntity();
        $initial = $catalog->getAllSupportedScopesList();
        self::assertSame('openid', $initial[0]);
        self::assertCount(1, array_filter($initial, static fn (int|string $scope): bool => $scope === 'openid'));
        self::assertNotContains('system/Patient.read', $initial);

        $catalog->setSystemScopesEnabled(true);
        self::assertContains('system/Patient.read', $catalog->getAllSupportedScopesList());
        $catalog->setSystemScopesEnabled(false);
        self::assertSame($initial, $catalog->getAllSupportedScopesList());
    }

    public function testDescriptionsPreserveNullableAndUnknownResourceInputs(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $translationSetting = $globals->getBoolean('disable_translation');
        $globals->set('disable_translation', true);
        try {
            $catalog = new ServerScopeListEntity();
            self::assertSame('', $catalog->lookupDescriptionForFullScopeString('unknown'));
            self::assertSame('medical records for this resource type', $catalog->lookupDescriptionForResourceScope(null, null));
            self::assertSame('medical records for this resource type', $catalog->lookupDescriptionForResourceScope('unknown', null));
            self::assertSame('allergies/adverse reactions', $catalog->lookupDescriptionForResourceScope('AllergyIntolerance', null));
        } finally {
            $globals->set('disable_translation', $translationSetting);
        }
    }
}
