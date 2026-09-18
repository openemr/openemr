<?php

/**
 * ScopeEntityContextTest.php
 *
 * Tests for ScopeEntity::arrayHasContext() / scopeListHasContext(), the shared
 * parse-and-compare helper used by the OAuth2 registration and manual-approval
 * gates to detect privileged (system/user) scopes.
 *
 * Prior to these changes the gates substring-matched the raw scope string
 * (str_contains($scope, 'system/')), which a client could bypass by registering
 * the colon form (system:Patient.read). The current defense-in-depth pairs:
 *
 *   1. createFromString() rejects colon-form privileged scopes at parse time
 *      (see ScopeDelimiterRejectionIsolatedTest).
 *   2. arrayHasContext() detects the slash form via canonical parse-and-compare
 *      and correctly skips anything the parser rejects — colon-form privileged
 *      scopes fail-closed here rather than sneaking past a substring match.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use PHPUnit\Framework\TestCase;

class ScopeEntityContextTest extends TestCase
{
    /**
     * The slash form of a privileged scope must be detected. The colon form is
     * rejected upstream by createFromString(); arrayHasContext() catches the
     * parse exception and correctly skips it, so it fail-closes to false here.
     * The colon-form rejection is covered by ScopeDelimiterRejectionIsolatedTest.
     */
    public function testArrayHasContextDetectsPrivilegedScopesInSlashForm(): void
    {
        $this->assertTrue(ScopeEntity::arrayHasContext(["openid", "system/Patient.read"], "system"));
        $this->assertTrue(ScopeEntity::arrayHasContext(["openid", "user/Patient.read"], "user"));
        $this->assertFalse(ScopeEntity::arrayHasContext(["openid", "system:Patient.read"], "system"));
        $this->assertFalse(ScopeEntity::arrayHasContext(["openid", "user:Patient.read"], "user"));
    }

    /**
     * A privileged scope hidden among patient scopes must still be detected
     * (slash form; the colon form is rejected upstream at parse time).
     */
    public function testArrayHasContextDetectsPrivilegedScopeAmongPatientScopes(): void
    {
        $scopes = ["patient/Patient.read", "patient/Observation.rs", "user/Encounter.read"];
        $this->assertTrue(ScopeEntity::arrayHasContext($scopes, "user"));
    }

    /**
     * site:default legitimately uses the colon delimiter but its context is
     * "site", not system or user. It must not be misclassified as privileged,
     * or the fix would break a valid scope.
     */
    public function testArrayHasContextDoesNotMisclassifySiteDefault(): void
    {
        $scopes = ["openid", "site:default", "patient/Patient.rs"];
        $this->assertFalse(ScopeEntity::arrayHasContext($scopes, "system"));
        $this->assertFalse(ScopeEntity::arrayHasContext($scopes, "user"));
    }

    /**
     * Patient-only scope sets must report no system/user context.
     */
    public function testArrayHasContextIsFalseForPatientOnlyScopes(): void
    {
        $scopes = ["openid", "patient/Patient.read", "patient/Observation.rs"];
        $this->assertFalse(ScopeEntity::arrayHasContext($scopes, "system"));
        $this->assertFalse(ScopeEntity::arrayHasContext($scopes, "user"));
    }

    /**
     * Wildcard and export-operation system scopes must be detected.
     */
    public function testArrayHasContextDetectsWildcardAndExportSystemScopes(): void
    {
        $this->assertTrue(ScopeEntity::arrayHasContext(["system/*.rs"], "system"));
        $this->assertTrue(ScopeEntity::arrayHasContext(["system/Group.\$export"], "system"));
    }

    /**
     * Unparsable scope strings are skipped for context detection. They cannot be
     * granted (getScopeEntityByIdentifier() rejects them with the same parser),
     * so skipping them cannot smuggle in a privileged context.
     */
    public function testArrayHasContextSkipsUnparsableScopes(): void
    {
        $scopes = ["system/Pat ient.read", "system/Patient/extra"];
        $this->assertFalse(ScopeEntity::arrayHasContext($scopes, "system"));
    }

    /**
     * The scopeListHasContext() wrapper accepts a raw whitespace-delimited scope
     * list string (as received in a registration request) and behaves the same
     * as arrayHasContext(): slash-form privileged scopes are detected; colon-form
     * privileged scopes are rejected at parse time and correctly skipped.
     */
    public function testScopeListHasContextParsesRawListString(): void
    {
        $this->assertTrue(ScopeEntity::scopeListHasContext("openid system/Patient.read", "system"));
        $this->assertTrue(ScopeEntity::scopeListHasContext("openid user/Patient.read patient/Patient.rs", "user"));
        $this->assertFalse(ScopeEntity::scopeListHasContext("openid site:default patient/Patient.rs", "system"));
        $this->assertFalse(ScopeEntity::scopeListHasContext("openid site:default patient/Patient.rs", "user"));
        $this->assertFalse(ScopeEntity::scopeListHasContext("openid system:Patient.read", "system"));
        $this->assertFalse(ScopeEntity::scopeListHasContext("openid user:Patient.read patient/Patient.rs", "user"));
    }

    /**
     * Empty and whitespace-only scope lists resolve to no context.
     */
    public function testScopeListHasContextHandlesEmptyInput(): void
    {
        $this->assertFalse(ScopeEntity::scopeListHasContext("", "system"));
        $this->assertFalse(ScopeEntity::scopeListHasContext("   ", "user"));
    }
}
