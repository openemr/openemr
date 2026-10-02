<?php

/**
 * Tests that ScopePermissionParser groups by context, resource and version.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\RestControllers\SMART\ScopePermissionParser;
use PHPUnit\Framework\TestCase;

class ScopePermissionParserIsolatedTest extends TestCase
{
    private function parser(): ScopePermissionParser
    {
        return new ScopePermissionParser($this->createMock(ScopeRepository::class));
    }

    /**
     * parseScopes() is declared as a bare array, so every nested read is mixed. Narrow once
     * here rather than sprinkling assertions through each test.
     *
     * @param array<array-key, mixed> $structured
     * @return array<array-key, mixed>
     */
    private function entry(array $structured, string $key): array
    {
        $this->assertArrayHasKey($key, $structured, 'expected a card keyed ' . $key);
        $entry = $structured[$key];
        $this->assertIsArray($entry);
        return $entry;
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private function actionEnabled(array $entry, string $action): bool
    {
        $actions = $entry['actions'] ?? null;
        $this->assertIsArray($actions);
        $this->assertArrayHasKey($action, $actions);
        $one = $actions[$action];
        $this->assertIsArray($one);
        return ($one['enabled'] ?? null) === true;
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private function field(array $entry, string $name): string
    {
        $value = $entry[$name] ?? null;
        $this->assertIsString($value);
        return $value;
    }

    /**
     * The consent form builds one card per entry and reconstructs that card's scopes from the
     * card's own context. Keyed on the resource name alone, patient/Patient.read and
     * user/Patient.write collapse into a single card whose context is whichever arrived first,
     * so the user/ scopes are silently never re-emitted and a patient/ write that was never
     * requested is. Both contexts must survive as separate entries.
     */
    public function testSameResourceInTwoContextsStaysTwoEntries(): void
    {
        $structured = $this->parser()->parseScopes([
            'patient/Patient.read',
            'user/Patient.read',
            'user/Patient.write',
        ]);

        $this->assertCount(2, $structured, 'Patient in two contexts must not collapse into one entry');

        $patientCard = $this->entry($structured, 'patient-Patient-v1');
        $userCard = $this->entry($structured, 'user-Patient-v1');
        $this->assertSame('Patient', $this->field($patientCard, 'name'));
        $this->assertSame('Patient', $this->field($userCard, 'name'));
        $this->assertSame('patient', $this->field($patientCard, 'context'));
        $this->assertSame('user', $this->field($userCard, 'context'));

        // The patient card carries only the read it was asked for -- no invented write.
        $this->assertTrue($this->actionEnabled($patientCard, 'r'));
        $this->assertFalse($this->actionEnabled($patientCard, 'c'));

        // The user card carries both halves it was asked for.
        $this->assertTrue($this->actionEnabled($userCard, 'r'));
        $this->assertTrue($this->actionEnabled($userCard, 'c'));
    }

    /**
     * The array key doubles as an HTML id and as a fragment of `#collapse<key>` CSS selectors in
     * scope-authorize.html.twig, so it must not contain the scope separator.
     */
    public function testKeysAreDomSafeAndDistinctPerContext(): void
    {
        $structured = $this->parser()->parseScopes(['patient/Encounter.read', 'user/Encounter.write']);

        $this->assertSame(['patient-Encounter-v1', 'user-Encounter-v1'], array_keys($structured));
        foreach (array_keys($structured) as $key) {
            $this->assertDoesNotMatchRegularExpression('~[/\s]~', $key);
        }
    }

    /**
     * A single context behaves exactly as before -- read and write merge into one card.
     */
    public function testSingleContextReadAndWriteMergeIntoOneEntry(): void
    {
        $structured = $this->parser()->parseScopes(['user/Goal.read', 'user/Goal.write']);

        $this->assertCount(1, $structured);
        $entry = $this->entry($structured, 'user-Goal-v1');
        $this->assertSame('v1', $this->field($entry, 'version'));
        $this->assertTrue($this->actionEnabled($entry, 'r'));
        $this->assertTrue($this->actionEnabled($entry, 'c'));
    }

    /**
     * The consent form rebuilds each card's scopes in the card's own version, and only the v1
     * path can emit `.write`. If user/Condition.rs and user/Condition.write share a card, the
     * card is v2, the write is rebuilt as user/Condition.cruds, and the requested write is lost.
     * Each version must keep its own card with only its own actions.
     */
    public function testMixedVersionsForOneResourceStayTwoEntries(): void
    {
        $structured = $this->parser()->parseScopes(['user/Condition.rs', 'user/Condition.write']);

        $this->assertCount(2, $structured, 'v1 and v2 forms of one resource must not collapse into one entry');

        $v2Card = $this->entry($structured, 'user-Condition-v2');
        $v1Card = $this->entry($structured, 'user-Condition-v1');
        $this->assertSame('v2', $this->field($v2Card, 'version'));
        $this->assertSame('v1', $this->field($v1Card, 'version'));

        $this->assertTrue($this->actionEnabled($v2Card, 'r'));
        $this->assertTrue($this->actionEnabled($v2Card, 's'));
        $this->assertFalse($this->actionEnabled($v2Card, 'c'));

        $this->assertTrue($this->actionEnabled($v1Card, 'c'));
        $this->assertTrue($this->actionEnabled($v1Card, 'u'));
        $this->assertTrue($this->actionEnabled($v1Card, 'd'));
        $this->assertFalse($this->actionEnabled($v1Card, 'r'));
    }
}
