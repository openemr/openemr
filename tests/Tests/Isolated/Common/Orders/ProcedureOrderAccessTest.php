<?php

/**
 * Isolated tests for the pure decision in
 * `OpenEMR\Common\Orders\ProcedureOrderAccess::isSensitivityPermitted()`.
 *
 * The security-relevant decision ("may this user view an order placed in an
 * encounter with this sensitivity?") lives entirely in the pure method; the
 * `assertCanViewOrder()` wrapper only adds the DB lookup, the ACL call and
 * request termination.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Orders;

use OpenEMR\Common\Orders\ProcedureOrderAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProcedureOrderAccessTest extends TestCase
{
    /**
     * @param list<string> $grantedSensitivities
     */
    #[DataProvider('sensitivityCasesProvider')]
    public function testIsSensitivityPermitted(?string $sensitivity, array $grantedSensitivities, bool $expected): void
    {
        $this->assertSame(
            $expected,
            ProcedureOrderAccess::isSensitivityPermitted(
                $sensitivity,
                static fn(string $value): bool => in_array($value, $grantedSensitivities, true),
            ),
        );
    }

    /**
     * @return array<string, array{?string, list<string>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sensitivityCasesProvider(): array
    {
        return [
            // --- No encounter, or no sensitivity on it: never restricted ---
            'order without encounter, no grants'   => [null, [], true],
            'blank sensitivity, no grants'         => ['', [], true],

            // --- Sensitivity set: the ACL decides ---
            'normal, granted'                      => ['normal', ['normal'], true],
            'normal, not granted'                  => ['normal', [], false],
            'high, granted'                        => ['high', ['normal', 'high'], true],
            'high, only normal granted'            => ['high', ['normal'], false],
            'custom level, granted'                => ['restricted_bh', ['restricted_bh'], true],
            'custom level, other level granted'    => ['restricted_bh', ['high'], false],
        ];
    }

    public function testAclIsNotConsultedWithoutSensitivity(): void
    {
        $calls = 0;
        $acl = static function (string $value) use (&$calls): bool {
            $calls++;
            return false;
        };

        $this->assertTrue(ProcedureOrderAccess::isSensitivityPermitted(null, $acl));
        $this->assertTrue(ProcedureOrderAccess::isSensitivityPermitted('', $acl));
        $this->assertSame(0, $calls);
    }

    public function testAclReceivesTheEncounterSensitivity(): void
    {
        $seen = [];
        $acl = static function (string $value) use (&$seen): bool {
            $seen[] = $value;
            return true;
        };

        ProcedureOrderAccess::isSensitivityPermitted('high', $acl);
        $this->assertSame(['high'], $seen);
    }
}
