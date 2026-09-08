<?php

/**
 * PortalLoginRowNormalizerTest — verifies the row-shape normalisation that the SQL
 * implementation of PortalLoginCredentialsRepository delegates to. ADODB returns every
 * column as a string regardless of the underlying type; these tests lock in the boundary
 * conversion to the typed shapes the controller expects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Portal;

use OpenEMR\Controllers\Portal\PortalLoginRowNormalizer;
use PHPUnit\Framework\TestCase;

class PortalLoginRowNormalizerTest extends TestCase
{
    // ---------------------------------------------------------------------
    // authRow
    // ---------------------------------------------------------------------

    public function testAuthRowConvertsStringColumnsToTypedShape(): void
    {
        $raw = [
            'id' => '7',
            'pid' => '42',
            'portal_pwd' => '$2y$10$hash',
            'portal_username' => 'alice',
            'portal_login_username' => 'alice_login',
            'portal_pwd_status' => '1',
        ];

        $normalized = PortalLoginRowNormalizer::authRow($raw, false);

        $this->assertSame(7, $normalized['id']);
        $this->assertSame(42, $normalized['pid']);
        $this->assertSame('$2y$10$hash', $normalized['portal_pwd']);
        $this->assertSame('alice', $normalized['portal_username']);
        $this->assertSame('alice_login', $normalized['portal_login_username']);
        $this->assertSame(1, $normalized['portal_pwd_status']);
        $this->assertArrayNotHasKey('portal_onetime', $normalized, 'includeOneTime=false omits the key');
    }

    public function testAuthRowIncludesOneTimeWhenRequested(): void
    {
        $raw = [
            'id' => '7',
            'pid' => '42',
            'portal_pwd' => 'x',
            'portal_username' => 'alice',
            'portal_login_username' => 'alice_login',
            'portal_pwd_status' => '0',
            'portal_onetime' => 'abc123' . str_repeat('x', 32),
        ];

        $normalized = PortalLoginRowNormalizer::authRow($raw, true);

        $this->assertArrayHasKey('portal_onetime', $normalized);
        $this->assertSame('abc123' . str_repeat('x', 32), $normalized['portal_onetime'] ?? null);
    }

    public function testAuthRowPreservesNullPortalOnetime(): void
    {
        $raw = [
            'id' => '7',
            'pid' => '42',
            'portal_pwd' => 'x',
            'portal_username' => 'alice',
            'portal_login_username' => 'alice_login',
            'portal_pwd_status' => '0',
            'portal_onetime' => null,
        ];

        $normalized = PortalLoginRowNormalizer::authRow($raw, true);

        $this->assertArrayHasKey('portal_onetime', $normalized);
        $this->assertNull($normalized['portal_onetime']);
    }

    public function testAuthRowDefaultsMissingColumnsRatherThanThrowing(): void
    {
        $normalized = PortalLoginRowNormalizer::authRow([], false);

        $this->assertSame(0, $normalized['id']);
        $this->assertSame(0, $normalized['pid']);
        $this->assertSame('', $normalized['portal_pwd']);
        $this->assertSame('', $normalized['portal_username']);
        $this->assertSame('', $normalized['portal_login_username']);
        $this->assertSame(0, $normalized['portal_pwd_status']);
    }

    // ---------------------------------------------------------------------
    // patientDataRow
    // ---------------------------------------------------------------------

    public function testPatientDataRowConvertsStringColumnsToTypedShape(): void
    {
        $raw = [
            'pid' => '42',
            'fname' => 'Alice',
            'lname' => 'Smith',
            'email' => 'alice@example.com',
            'providerID' => '11',
            'allow_patient_portal' => 'YES',
        ];

        $normalized = PortalLoginRowNormalizer::patientDataRow($raw);

        $this->assertSame(42, $normalized['pid']);
        $this->assertSame('Alice', $normalized['fname']);
        $this->assertSame('Smith', $normalized['lname']);
        $this->assertSame('alice@example.com', $normalized['email']);
        $this->assertSame(11, $normalized['providerID']);
        $this->assertSame('YES', $normalized['allow_patient_portal']);
    }

    public function testPatientDataRowDefaultsMissingProviderToZero(): void
    {
        $raw = [
            'pid' => '42',
            'fname' => 'Alice',
            'lname' => 'Smith',
            'email' => 'alice@example.com',
            // providerID intentionally absent
            'allow_patient_portal' => 'YES',
        ];

        $normalized = PortalLoginRowNormalizer::patientDataRow($raw);

        $this->assertSame(0, $normalized['providerID']);
    }

    public function testPatientDataRowDefaultsEmptyEmailToEmptyString(): void
    {
        $raw = [
            'pid' => '42',
            'fname' => 'Alice',
            'lname' => 'Smith',
            'email' => null,
            'providerID' => '11',
            'allow_patient_portal' => 'YES',
        ];

        $normalized = PortalLoginRowNormalizer::patientDataRow($raw);

        $this->assertSame('', $normalized['email']);
    }

    // ---------------------------------------------------------------------
    // providerInfoRow
    // ---------------------------------------------------------------------

    public function testProviderInfoRowConvertsAllStringColumns(): void
    {
        $raw = [
            'fname' => 'Dr',
            'lname' => 'Who',
            'username' => 'drwho',
        ];

        $normalized = PortalLoginRowNormalizer::providerInfoRow($raw);

        $this->assertSame('Dr', $normalized['fname']);
        $this->assertSame('Who', $normalized['lname']);
        $this->assertSame('drwho', $normalized['username']);
    }

    public function testProviderInfoRowKeepsUsernameNullableWhenMissing(): void
    {
        $raw = [
            'fname' => 'Dr',
            'lname' => 'Who',
            // username intentionally absent
        ];

        $normalized = PortalLoginRowNormalizer::providerInfoRow($raw);

        $this->assertNull($normalized['username']);
    }

    public function testProviderInfoRowDefaultsMissingNamesToEmptyString(): void
    {
        $normalized = PortalLoginRowNormalizer::providerInfoRow([]);

        $this->assertSame('', $normalized['fname']);
        $this->assertSame('', $normalized['lname']);
        $this->assertNull($normalized['username']);
    }

    // ---------------------------------------------------------------------
    // Column-coercion edge cases (non-string inputs)
    // ---------------------------------------------------------------------

    public function testIntColumnPassesThroughActualInts(): void
    {
        // ADODB returns numeric columns as strings, but some adapters or test fixtures
        // hand back actual ints — the coercer should accept those too.
        $normalized = PortalLoginRowNormalizer::patientDataRow([
            'pid' => 99,
            'fname' => 'Alice',
            'lname' => 'Smith',
            'email' => 'a@b',
            'providerID' => 11,
            'allow_patient_portal' => 'YES',
        ]);

        $this->assertSame(99, $normalized['pid']);
        $this->assertSame(11, $normalized['providerID']);
    }

    public function testStringColumnCoercesIntAndFloatToString(): void
    {
        // If a fixture or adapter hands a numeric value where a string column is expected
        // (legacy code does this for things like provider names that look numeric), the
        // coercer should stringify rather than fall through to the default.
        $normalized = PortalLoginRowNormalizer::providerInfoRow([
            'fname' => 42,        // int
            'lname' => 1.5,       // float
            'username' => 'drwho',
        ]);

        $this->assertSame('42', $normalized['fname']);
        $this->assertSame('1.5', $normalized['lname']);
    }
}
