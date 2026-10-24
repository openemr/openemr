<?php

/**
 * ScopeDelimiterRejectionIsolatedTest
 *
 * Locks the parse-time defense in ScopeEntity::createFromString: colon-form
 * scope delimiter is rejected for permission-bearing contexts (system, user,
 * patient, and anything outside the COLON_LEGACY_CONTEXTS allow-list), so
 * downstream substring searches for 'system/' / 'user/' / 'patient/' cannot
 * be bypassed by submitting the colon variant. Legacy SMART launch contexts
 * (api:oemr, api:fhir, api:port, site:default) continue to parse.
 *
 * Runtime helpers on ScopeEntity (arrayHasContext / scopeListHasContext) are
 * also correct for both spellings; this parse-time rejection is a
 * model-layer defense so no future caller that reads getIdentifier() or
 * getScopeLookupKey() can receive a colon-form privileged scope.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Entities;

use InvalidArgumentException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScopeDelimiterRejectionIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function privilegedColonScopesProvider(): array
    {
        return [
            'system:Patient.read'   => ['system:Patient.read'],
            'system:Patient.write'  => ['system:Patient.write'],
            'system:*.read'         => ['system:*.read'],
            'user:Patient.read'     => ['user:Patient.read'],
            'user:Encounter.write'  => ['user:Encounter.write'],
            'patient:Patient.read'  => ['patient:Patient.read'],
            'patient:Observation.read' => ['patient:Observation.read'],
            'system:Observation.cruds' => ['system:Observation.cruds'],
            // Unknown-context colon scopes (not on the allow-list) must also
            // be rejected — the allow-list is api + site only.
            'unknowncontext:X'      => ['unknowncontext:X'],
        ];
    }

    #[DataProvider('privilegedColonScopesProvider')]
    public function testColonFormPrivilegedContextIsRejected(string $scopeString): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/uses the colon delimiter for context/');
        ScopeEntity::createFromString($scopeString);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function legacyColonScopesProvider(): array
    {
        return [
            'api:oemr'      => ['api:oemr'],
            'api:fhir'      => ['api:fhir'],
            'api:port'      => ['api:port'],
            'site:default'  => ['site:default'],
        ];
    }

    #[DataProvider('legacyColonScopesProvider')]
    public function testLegacyColonContextsAreAccepted(string $scopeString): void
    {
        $entity = ScopeEntity::createFromString($scopeString);
        $this->assertSame($scopeString, $entity->getIdentifier(), 'Legacy colon-context scope must be stored unchanged.');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function slashScopesProvider(): array
    {
        return [
            'system/Patient.read'      => ['system/Patient.read'],
            'system/Patient.write'     => ['system/Patient.write'],
            'system/*.read'            => ['system/*.read'],
            'user/Patient.read'        => ['user/Patient.read'],
            'user/Encounter.write'     => ['user/Encounter.write'],
            'patient/Patient.read'     => ['patient/Patient.read'],
            'patient/Observation.read' => ['patient/Observation.read'],
        ];
    }

    #[DataProvider('slashScopesProvider')]
    public function testSlashFormPrivilegedContextIsAccepted(string $scopeString): void
    {
        $entity = ScopeEntity::createFromString($scopeString);
        $this->assertSame($scopeString, $entity->getIdentifier(), 'Slash-form scope must be stored unchanged.');
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function contextExtractionProvider(): array
    {
        return [
            'system slash' => ['system/Patient.read', 'system'],
            'user slash'   => ['user/Patient.write',  'user'],
            'patient slash' => ['patient/Patient.read', 'patient'],
            'api colon'    => ['api:fhir',            'api'],
            'site colon'   => ['site:default',        'site'],
        ];
    }

    #[DataProvider('contextExtractionProvider')]
    public function testAcceptedScopesExposeExpectedContext(string $scopeString, string $expectedContext): void
    {
        $entity = ScopeEntity::createFromString($scopeString);
        $this->assertSame($expectedContext, $entity->getContext());
    }

    public function testOpenidBareScopeAccepted(): void
    {
        $entity = ScopeEntity::createFromString('openid');
        $this->assertSame('openid', $entity->getIdentifier());
        $this->assertSame('openid', $entity->getContext());
    }

    public function testExplicitlyMalformedScopeThrows(): void
    {
        // A leading digit is not part of the parser's context grammar and is
        // rejected by the main regex (not by the colon-delimiter check).
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Invalid scope format/');
        ScopeEntity::createFromString('1bad-context');
    }

    public function testRejectionMessageIncludesContextAndSuggestion(): void
    {
        try {
            ScopeEntity::createFromString('system:Patient.read');
            $this->fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('system', $e->getMessage(), 'Message should name the rejected context');
            $this->assertStringContainsString('system/', $e->getMessage(), 'Message should suggest the slash-form replacement');
        }
    }
}
