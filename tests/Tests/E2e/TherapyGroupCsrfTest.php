<?php

/**
 * E2e test: therapy group links and writes carry the CSRF token.
 *
 * Opening a therapy group closes the open patient, so the server refuses a
 * group switch without the CSRF token while a patient is open. A patient
 * opened in another browser tab leaves the group page in this tab stale,
 * and its links then count as switches. Each link must send the token, or
 * it fails with a 403. The group and participant saves and deletes need the
 * token too.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class TherapyGroupCsrfTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    /**
     * Marks the fixture rows (group name, patient pubpid), so cleanup removes
     * only what this test created.
     */
    private const FIXTURE_MARKER = 'e2e-test-therapy-group-csrf';
    private const GROUP_TAB = 'gdg';
    private const LIST_TAB = 'gfn';

    private ?string $savedGroupTherapyGlobal = null;
    private bool $globalSaved = false;
    private int $groupId = 0;
    private int $pid = 0;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testGroupLinksReopenTheGroupAfterAPatientOpensInAnotherTab(): void
    {
        if (getenv('SESSION_STORAGE_MODE') === 'predis-sentinel') {
            self::markTestSkipped('Not yet reliable with Redis Sentinel session storage.');
        }
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->openPatientInAnotherTab();
            $this->openGroupFromList();
            $this->assertGroupReopened('The group name link in the group list');

            $this->openPatientInAnotherTab();
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//ul[contains(@class, "tabNav")]//a[contains(@href, "method=groupParticipants")]');
            });
            $this->assertGroupFrameShows('participants_table', 'The Participants tab');
            $this->assertGroupReopened('The Participants tab');

            $this->openPatientInAnotherTab();
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//ul[contains(@class, "tabNav")]//a[contains(@href, "method=groupDetails")]');
            });
            $this->assertGroupFrameShows('editGroup', 'The General data tab');
            $this->assertGroupReopened('The General data tab');

            $this->openPatientInAnotherTab();
            $this->reloadGroupFrame(function (): void {
                $this->client->switchTo()->defaultContent();
                $this->waitForClickable('//div[@id="attendantData"]//a[contains(@class, "ptName")]', 30)->click();
            });
            $this->assertGroupFrameShows('editGroup', 'The group name in the top bar');
            $this->assertGroupReopened('The group name in the top bar');

            $this->openPatientInAnotherTab();
            $this->reloadGroupFrame(function (): void {
                $this->goToMainMenuLink('Groups||Group Details');
                $this->waitForMenuSettled();
            });
            $this->assertGroupFrameShows('editGroup', 'The Group Details menu item');
            $this->assertGroupReopened('The Group Details menu item');
        } finally {
            $this->tearDownSession();
        }
    }

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testGroupAndParticipantWritesSucceedFromTheirForms(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->openGroupFromList();

            // Edit the group's notes.
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//button[contains(@onclick, "editGroup=1")]');
            });
            $this->reloadGroupFrame(function (): void {
                $this->inGroupFrame(function (): void {
                    $this->client->executeScript('document.querySelector(\'textarea[name="group_notes"]\').value = arguments[0];', ['edited notes']);
                    $this->clickOverlapped('//button[@id="saveUpdates"]');
                });
            });
            $this->assertSame('edited notes', $this->groupColumn('group_notes'), 'Saving the group should update its notes');

            // Add the fixture patient to the group. The patient finder popup
            // fills these fields; set them directly instead.
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//ul[contains(@class, "tabNav")]//a[contains(@href, "method=groupParticipants")]');
            });
            $this->reloadGroupFrame(function (): void {
                $this->inGroupFrame(function (): void {
                    $this->clickOverlapped('//button[@id="addParticipant"]');
                    $this->client->executeScript(
                        'document.getElementById("pid").value = arguments[0]; document.getElementById("participant_name").value = arguments[1];',
                        [(string) $this->pid, 'Groupcsrf Participant']
                    );
                    $this->waitForClickable('//input[@name="save_new"]', 30)->click();
                });
            });
            $this->assertNotNull($this->participantComment(), 'Adding the participant should register the patient in the group');

            // Edit the participant's comment.
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//button[contains(@onclick, "editParticipants=1")]');
            });
            $this->reloadGroupFrame(function (): void {
                $this->inGroupFrame(function (): void {
                    $this->client->executeScript('document.querySelector(\'input[name="group_patient_comment[]"]\').value = arguments[0];', ['edited comment']);
                    $this->clickOverlapped('//button[@id="saveForm"]');
                });
            });
            $this->assertSame('edited comment', $this->participantComment(), 'Saving the participants should update the comment');

            // Remove the participant. The delete link shows only while editing.
            $this->reloadGroupFrame(function (): void {
                $this->clickInGroupFrame('//button[contains(@onclick, "editParticipants=1")]');
            });
            $this->reloadGroupFrame(function (): void {
                $this->inGroupFrame(function (): void {
                    $this->clickOverlapped('//a[contains(@href, "deleteParticipant=1")]');
                });
            });
            $this->assertNull($this->participantComment(), 'The delete link should remove the participant');

            // Delete the group from the group list.
            $this->client->switchTo()->defaultContent();
            $this->showGroupInList();
            $this->reloadFrame(self::LIST_TAB, function (): void {
                $this->inFrame(self::LIST_TAB, function (): void {
                    $this->waitForClickable('//a[contains(@href, "deleteGroup=1") and contains(@href, "group_id=' . $this->groupId . '&")]', 30)->click();
                });
            });
            $this->assertSame('30', $this->groupColumn('group_status'), 'The delete link should cancel the group');
        } finally {
            $this->tearDownSession();
        }
    }

    /**
     * Open the fixture group from the Groups list, the way a user does, and
     * wait for the app to show it in the group tab.
     */
    private function openGroupFromList(): void
    {
        $this->goToMainMenuLink('Groups||Groups');
        $this->waitForMenuSettled();
        $this->showGroupInList();
        $this->inFrame(self::LIST_TAB, function (): void {
            $this->waitForClickable('//table[@id="therapy_groups_list"]//a[contains(@href, "method=groupDetails") and contains(@href, "group_id=' . $this->groupId . '&")]', 30)->click();
        });
        $this->client->wait(30)->until(
            fn(): bool => $this->client->executeScript('var g = app_view_model.application_data.therapy_group(); return g ? String(g.gid()) : "";') === (string) $this->groupId,
            'The app did not open the therapy group'
        );
        $this->waitForGroupFrame();
        $this->assertGroupFrameShows('editGroup', 'Opening the group from the list');
    }

    /**
     * Wait for the main menu to put back the menu it detaches for half a
     * second after a click. Knockout drops the bindings of a detached menu
     * when they update, so opening a group in that window would leave Group
     * Details disabled.
     */
    private function waitForMenuSettled(): void
    {
        $this->client->switchTo()->defaultContent();
        $this->client->wait(10)->until(
            fn(): bool => $this->client->executeScript('return document.querySelector(".timedReplace") === null;') === true,
            'The main menu was not put back after the click'
        );
    }

    /**
     * Filter the Groups list down to the fixture group, so the list's paging
     * cannot hide it.
     */
    private function showGroupInList(): void
    {
        $this->client->waitFor($this->frameXpath(self::LIST_TAB), 30);
        $this->inFrame(self::LIST_TAB, function (): void {
            $this->client->waitFor('#therapy_groups_list', 30);
            $this->client->executeScript('$("#group_id_filter").val(arguments[0]).trigger("keyup");', [(string) $this->groupId]);
        });
    }

    /**
     * Open the fixture patient as a second browser tab would: a request on
     * the same session that leaves this tab's pages as they are.
     */
    private function openPatientInAnotherTab(): void
    {
        $this->waitForPageLoadsToFinish();
        $status = $this->client->executeAsyncScript(
            <<<'JS'
            const done = arguments[arguments.length - 1];
            const params = new URLSearchParams({set_pid: arguments[0], csrf_token_form: csrf_token_js});
            fetch(webroot_url + '/interface/patient_file/summary/demographics.php?' + params, {credentials: 'same-origin'})
                .then(function (response) { done(response.status); }, function (error) { done(String(error)); });
            JS,
            [$this->pid]
        );
        $this->assertSame(200, $status, 'Opening the patient should succeed');
        $this->assertSame(403, $this->groupDetailsStatusWithoutToken(), 'While a patient is open, opening the group without the token should be refused');
    }

    /**
     * Wait until the app shell and every frame in it have finished loading,
     * with no jQuery request pending. Opening a group loads the list,
     * details, and encounter tabs at once; a request still running when the
     * patient opens can write its older session back over the open patient.
     */
    private function waitForPageLoadsToFinish(): void
    {
        $this->client->switchTo()->defaultContent();
        $this->client->wait(30)->until(
            fn(): bool => $this->client->executeScript(
                <<<'JS'
                function settled(win) {
                    if (win.document.readyState !== 'complete' || (win.jQuery && win.jQuery.active > 0)) {
                        return false;
                    }
                    for (let i = 0; i < win.frames.length; i++) {
                        if (!settled(win.frames[i])) {
                            return false;
                        }
                    }
                    return true;
                }
                return settled(window);
                JS
            ) === true,
            'The app shell and its frames did not finish loading'
        );
    }

    /**
     * Opening the group again without the token is allowed only when it is
     * already the open group and no patient is open, so a 200 shows that the
     * link reopened the group and closed the patient.
     */
    private function assertGroupReopened(string $link): void
    {
        $this->waitForPageLoadsToFinish();
        $this->assertSame(200, $this->groupDetailsStatusWithoutToken(), $link . ' should reopen the group and close the patient');
    }

    private function groupDetailsStatusWithoutToken(): int
    {
        $this->client->switchTo()->defaultContent();
        $status = $this->client->executeAsyncScript(
            <<<'JS'
            const done = arguments[arguments.length - 1];
            const params = new URLSearchParams({method: 'groupDetails', group_id: arguments[0]});
            fetch(webroot_url + '/interface/therapy_groups/index.php?' + params, {credentials: 'same-origin'})
                .then(function (response) { done(response.status); }, function (error) { done(String(error)); });
            JS,
            [$this->groupId]
        );
        $this->assertIsInt($status, 'The request for the group page failed');
        return $status;
    }

    private function reloadGroupFrame(callable $action): void
    {
        $this->reloadFrame(self::GROUP_TAB, $action);
    }

    /**
     * Run $action and wait until the frame has loaded a new page. A marker
     * set on the frame's current window disappears when it navigates.
     */
    private function reloadFrame(string $tab, callable $action): void
    {
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor($this->frameXpath($tab), 30);
        $this->client->executeScript(
            'document.evaluate(arguments[0], document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.contentWindow.e2eStalePage = true;',
            [$this->frameXpath($tab)]
        );
        $action();
        $this->client->switchTo()->defaultContent();
        $this->waitForFrame($tab);
    }

    private function frameXpath(string $tab): string
    {
        return "//*[@id='framesDisplay']//iframe[@name='" . $tab . "']";
    }

    private function waitForGroupFrame(): void
    {
        $this->waitForFrame(self::GROUP_TAB);
    }

    private function waitForFrame(string $tab): void
    {
        $this->client->waitFor($this->frameXpath($tab), 30);
        $this->client->wait(30)->until(
            fn(): bool => $this->client->executeScript(
                <<<'JS'
                const frame = document.evaluate(arguments[0], document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                const win = frame && frame.contentWindow;
                return Boolean(win && !win.e2eStalePage && win.location.href !== 'about:blank'
                    && win.document.readyState === 'complete' && win.document.body);
                JS,
                [$this->frameXpath($tab)]
            ) === true,
            'The frame did not load a new page'
        );
    }

    /**
     * Fail unless the group tab shows the group page with element $id,
     * rather than the CSRF refusal.
     */
    private function assertGroupFrameShows(string $id, string $link): void
    {
        $this->client->switchTo()->defaultContent();
        $state = $this->client->executeScript(
            <<<'JS'
            const doc = document.evaluate(arguments[0], document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.contentDocument;
            return doc.getElementById(arguments[1]) ? 'shown' : doc.body.textContent.trim().slice(0, 200);
            JS,
            [$this->frameXpath(self::GROUP_TAB), $id]
        );
        $this->assertSame('shown', $state, $link . ' should open the group page');
    }

    private function clickInGroupFrame(string $xpath): void
    {
        $this->inGroupFrame(function () use ($xpath): void {
            $this->waitForClickable($xpath, 30)->click();
        });
    }

    /**
     * Click an element in the current frame through the DOM. The group pages
     * lay the top bar over their Save and Add buttons and the appointment
     * widget over the participant delete links, so those take no pointer click.
     */
    private function clickOverlapped(string $xpath): void
    {
        $this->client->executeScript('arguments[0].click();', [$this->waitForClickable($xpath, 30)]);
    }

    private function inGroupFrame(callable $action): void
    {
        $this->inFrame(self::GROUP_TAB, $action);
    }

    /**
     * Run $action inside the tab's frame, with the tab shown first: the
     * group pages switch tabs as they load, and a hidden tab's elements have
     * no size.
     */
    private function inFrame(string $tab, callable $action): void
    {
        $this->client->switchTo()->defaultContent();
        $this->client->executeScript('activateTabByName(arguments[0], true);', [$tab]);
        $this->client->switchTo()->frame($this->client->findElement(WebDriverBy::xpath($this->frameXpath($tab))));
        try {
            $action();
        } finally {
            $this->client->switchTo()->defaultContent();
        }
    }

    private function groupColumn(string $column): ?string
    {
        $value = QueryUtils::fetchSingleValue('SELECT ' . $column . ' FROM therapy_groups WHERE group_id = ?', $column, [$this->groupId]);
        return $value === null ? null : $this->toStringValue($value);
    }

    /**
     * The fixture patient's participant comment; null when the patient is
     * not in the group.
     */
    private function participantComment(): ?string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT group_patient_comment FROM therapy_groups_participants WHERE group_id = ? AND pid = ?',
            [$this->groupId, $this->pid]
        );
        if (!is_array($row)) {
            return null;
        }
        return $this->toStringValue($row['group_patient_comment'] ?? '');
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $value = QueryUtils::fetchSingleValue('SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0', 'gl_value', ['enable_group_therapy']);
        $this->savedGroupTherapyGlobal = is_string($value) ? $value : null;
        $this->globalSaved = true;
        QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', ['enable_group_therapy', '1']);

        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, self::FIXTURE_MARKER, 'Participant', 'Groupcsrf', '1970-01-01', 'Female']
        );
        $this->groupId = QueryUtils::sqlInsert(
            'INSERT INTO therapy_groups (group_name, group_start_date, group_type, group_participation, group_status, group_notes) VALUES (?, CURDATE(), 1, 1, 10, ?)',
            [self::FIXTURE_MARKER, 'fixture notes']
        );
    }

    private function tearDownSession(): void
    {
        try {
            if (isset($this->client)) {
                $this->client->quit();
            }
        } finally {
            $this->cleanUpFixtureData();
        }
    }

    private function cleanUpFixtureData(): void
    {
        if ($this->globalSaved) {
            if ($this->savedGroupTherapyGlobal === null) {
                QueryUtils::sqlStatementThrowException('DELETE FROM globals WHERE gl_name = ? AND gl_index = 0', ['enable_group_therapy']);
            } else {
                QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', ['enable_group_therapy', $this->savedGroupTherapyGlobal]);
            }
            $this->globalSaved = false;
        }

        $fixtureGroups = 'SELECT group_id FROM therapy_groups WHERE group_name = ?';
        foreach (['therapy_groups_participants', 'therapy_groups_counselors'] as $table) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ' . $table . ' WHERE group_id IN (' . $fixtureGroups . ')', [self::FIXTURE_MARKER]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM therapy_groups WHERE group_name = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pubpid = ?', [self::FIXTURE_MARKER]);
    }

    private function toInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            $this->fail('Expected a number from the database, got ' . get_debug_type($value));
        }
        return (int) $value;
    }

    private function toStringValue(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }
        if (!is_string($value)) {
            $this->fail('Expected a string from the database, got ' . get_debug_type($value));
        }
        return $value;
    }
}
