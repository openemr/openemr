<?php

/**
 * E2e test: an e-signed, locked encounter keeps its fee sheet.
 *
 * With "Lock e-signed encounters and their forms" on, signing the whole
 * encounter hides the encounter form menu so no new forms can be added. The
 * fee sheet went with it, although coding often follows the provider's
 * signature and Fees > Fee Sheet opens it regardless. The menu of a locked
 * encounter now offers the fee sheet and nothing else.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class EncounterLockedFeeSheetTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    /**
     * Marks the fixture rows (patient pubpid, encounter reason), so cleanup
     * removes only what this test created.
     */
    private const FIXTURE_MARKER = 'e2e-test-esign-lock-fee-sheet';
    private const LOCK_GLOBALS = ['esign_all', 'lock_esign_all'];

    /** @var array<string, string|null> */
    private array $savedGlobals = [];
    private int $pid = 0;
    private int $lockedEncounter = 0;
    private int $unlockedEncounter = 0;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testLockedEncounterMenuOffersOnlyTheFeeSheet(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->client->request('GET', '/interface/patient_file/summary/demographics.php?set_pid=' . $this->pid);

            $this->assertSame(
                ['fee_sheet'],
                $this->encounterMenuForms($this->lockedEncounter),
                'A locked encounter should offer the fee sheet and no other form'
            );

            $unlocked = $this->encounterMenuForms($this->unlockedEncounter);
            $this->assertContains('fee_sheet', $unlocked);
            $this->assertGreaterThan(1, count($unlocked), 'An unlocked encounter should still offer every form');
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    /**
     * The formname of every form link in an encounter's form menu.
     *
     * @return list<string>
     */
    private function encounterMenuForms(int $encounter): array
    {
        $this->client->request('GET', '/interface/patient_file/encounter/encounter_top.php?set_encounter=' . $encounter);
        $this->client->request('GET', '/interface/patient_file/encounter/forms.php');
        $this->client->waitFor('#navbarEncounterTitle');

        $forms = $this->client->executeScript(<<<'JS'
            var forms = [];
            document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(function (item) {
                var match = (item.getAttribute('onclick') || '').match(/load_form\.php\?formname=([A-Za-z0-9_]+)/);
                if (match) {
                    forms.push(match[1]);
                }
            });
            return forms;
        JS);
        $this->assertIsArray($forms);

        $names = [];
        foreach ($forms as $form) {
            $this->assertIsString($form);
            $names[] = $form;
        }
        return $names;
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        foreach (self::LOCK_GLOBALS as $name) {
            $value = QueryUtils::fetchSingleValue('SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0', 'gl_value', [$name]);
            $this->savedGlobals[$name] = is_string($value) ? $value : null;
            QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', [$name, '1']);
        }

        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, self::FIXTURE_MARKER, 'Esign', 'Locktest', '1970-01-01', 'Female']
        );
        $this->lockedEncounter = $this->toInt(QueryUtils::generateId());
        $this->unlockedEncounter = $this->toInt(QueryUtils::generateId());
        foreach ([$this->lockedEncounter, $this->unlockedEncounter] as $encounter) {
            QueryUtils::sqlInsert(
                'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id) VALUES (NOW(), ?, ?, ?, 1)',
                [self::FIXTURE_MARKER, $this->pid, $encounter]
            );
        }
        // The encounter-level signature with its lock, as signing the whole
        // encounter writes it.
        QueryUtils::sqlInsert(
            'INSERT INTO esign_signatures (tid, `table`, uid, datetime, is_lock, hash, signature_hash) VALUES (?, ?, 1, NOW(), 1, ?, ?)',
            [$this->lockedEncounter, 'form_encounter', 'e2e', 'e2e']
        );
    }

    private function cleanUpFixtureData(): void
    {
        foreach ($this->savedGlobals as $name => $value) {
            if ($value === null) {
                QueryUtils::sqlStatementThrowException('DELETE FROM globals WHERE gl_name = ? AND gl_index = 0', [$name]);
            } else {
                QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', [$name, $value]);
            }
        }
        $this->savedGlobals = [];

        QueryUtils::sqlStatementThrowException(
            'DELETE FROM esign_signatures WHERE `table` = ? AND tid IN (SELECT encounter FROM form_encounter WHERE reason = ?)',
            ['form_encounter', self::FIXTURE_MARKER]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE reason = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pubpid = ?', [self::FIXTURE_MARKER]);
    }

    private function toInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            $this->fail('Expected a number from the database, got ' . get_debug_type($value));
        }
        return (int) $value;
    }
}
