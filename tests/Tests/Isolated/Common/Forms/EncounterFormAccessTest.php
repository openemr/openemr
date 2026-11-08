<?php

/**
 * Isolated tests for the pure ownership-comparison logic in
 * `OpenEMR\Common\Forms\EncounterFormAccess::isFormOwnedBySession()`.
 *
 * The security-relevant decision — "does this form's owner permit access
 * from this session?" — lives entirely in `isFormOwnedBySession()`. The
 * `assertFormBelongsToSessionPatient()` wrapper composes it with a DB
 * fetch and a `deny()` call; the pure function is the trust boundary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms;

use OpenEMR\Common\Forms\EncounterFormAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EncounterFormAccessTest extends TestCase
{
    #[DataProvider('ownershipCasesProvider')]
    public function testIsFormOwnedBySession(
        ?int $ownerPid,
        int $sessionPid,
        ?int $ownerEncounter,
        ?int $sessionEncounter,
        bool $expected,
    ): void {
        $this->assertSame(
            $expected,
            EncounterFormAccess::isFormOwnedBySession(
                $ownerPid,
                $sessionPid,
                $ownerEncounter,
                $sessionEncounter,
            ),
        );
    }

    /**
     * @return array<string, array{?int, int, ?int, ?int, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ownershipCasesProvider(): array
    {
        return [
            // --- Form not found (owner null) always denies ---
            'form not found — pid-only mode'          => [null, 42, null, null, false],
            'form not found — encounter opt-in mode'  => [null, 42, null, 100, false],
            'form not found with owner encounter set' => [null, 42, 100, null, false],

            // --- Pid mismatch always denies ---
            'pid mismatch — pid-only mode'                => [7,  42, null, null, false],
            'pid mismatch — different sessions'           => [42, 43, null, null, false],
            'pid mismatch even when encounter matches'    => [7,  42, 100, 100, false],
            'zero owner pid vs non-zero session'          => [0,  42, null, null, false],

            // --- Pid match, pid-only mode (encounter check disabled) ---
            'pid match, both encounters null'                    => [42, 42, null, null, true],
            'pid match, owner has encounter but session opts out' => [42, 42, 100, null, true],

            // --- Pid match, encounter opt-in ---
            'pid match, encounter match'                         => [42, 42, 100, 100, true],
            'pid match, encounter mismatch'                      => [42, 42, 99,  100, false],
            'pid match, encounter opt-in with owner encounter 0' => [42, 42, 0,   100, false],
            'pid match, encounter opt-in with null owner enc'    => [42, 42, null, 100, false],

            // --- Non-positive session pid ---
            'session pid 0 with owner pid 0'      => [0,  0,  null, null, false],
            'session pid 0 with owner pid 42'     => [42, 0,  null, null, false],
            'negative session pid'                => [42, -1, null, null, false],
            'session pid 0 with encounter opt-in' => [0,  0,  100, 100, false],
        ];
    }

    /**
     * @param bool $expected
     */
    #[DataProvider('positiveFormIdCasesProvider')]
    public function testIsPositiveFormId(int|false|null $formId, bool $expected): void
    {
        $this->assertSame($expected, EncounterFormAccess::isPositiveFormId($formId));
    }

    /**
     * @return array<string, array{int|false|null, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function positiveFormIdCasesProvider(): array
    {
        return [
            'positive int'    => [42, true],
            'one'             => [1, true],
            'max int'         => [PHP_INT_MAX, true],
            'zero'            => [0, false],
            'negative int'    => [-1, false],
            'false'           => [false, false],
            'null'            => [null, false],
        ];
    }

    public function testApplySessionPidToFormOverridesPreviousValue(): void
    {
        $form = new class {
            public int $pid = 0;
            public function set_pid(int $pid): void
            {
                $this->pid = $pid;
            }
        };
        $form->set_pid(99);
        EncounterFormAccess::applySessionPidToForm($form, sessionPid: 42);
        $this->assertSame(42, $form->pid);
    }

    public function testApplySessionPidToFormNoOpWhenFormHasNoSetPidMethod(): void
    {
        $form = new class {
            public int $pid = 99;
        };
        EncounterFormAccess::applySessionPidToForm($form, sessionPid: 42);
        $this->assertSame(99, $form->pid);
    }

    public function testApplySessionPidToFormNoOpForNonObject(): void
    {
        // No exception thrown for non-object input; no observable side effects.
        EncounterFormAccess::applySessionPidToForm('not an object', sessionPid: 42);
        $this->expectNotToPerformAssertions();
    }
}
