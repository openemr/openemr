<?php

/**
 * E2e test: an e-signed, locked encounter keeps its fee sheet.
 *
 * With "Lock e-signed encounters and their forms" on, signing the whole
 * encounter hides the encounter form menu, and the main menu refuses its
 * encounter items ("This encounter is locked"), so no new forms can be added.
 * The fee sheet went with both, although coding often follows the provider's
 * signature. The encounter's form menu now offers the fee sheet and nothing
 * else, and the main menu's Fees > Fee Sheet opens it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
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

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testMainMenuOpensTheFeeSheetOnALockedEncounter(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);

            // Select the patient and the locked encounter in the app shell, as
            // opening them from the dashboard does, so the main menu's
            // encounter items are enabled.
            $this->client->executeScript(
                'navigateTab(webroot_url + "/interface/patient_file/summary/demographics.php?set_pid=" + arguments[0], "pat");',
                [$this->pid]
            );
            $this->client->wait(30)->until(
                fn(): bool => $this->client->executeScript('var p = app_view_model.application_data[attendant_type](); return p ? String(p.pid()) : "";') === (string) $this->pid,
                'The patient was not selected in the app shell'
            );
            $this->client->executeScript('setEncounter(arguments[0]); goToEncounter(arguments[0]);', [$this->lockedEncounter]);
            $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);

            // The menu refuses with alert(); record alerts instead of showing them.
            $this->client->executeScript('window.e2eAlerts = []; window.alert = function (message) { window.e2eAlerts.push(String(message)); };');
            $this->goToMainMenuLink('Fees||Fee Sheet');
            $this->assertSame(
                [],
                $this->client->executeScript('return window.e2eAlerts;'),
                'Fees > Fee Sheet should open on a locked encounter without the "locked" alert'
            );

            $this->client->switchTo()->defaultContent();
            $this->client->switchTo()->frame($this->client->findElement(WebDriverBy::xpath(XpathsConstants::ENCOUNTER_IFRAME)));
            $this->client->waitFor('//iframe[contains(@src, "formname=fee_sheet")]', 30);
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
