<?php

/**
 * Database test: the billing forms an e-signed, locked encounter keeps, and
 * when the encounter form menu leaves out Misc Billing Options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Forms;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Forms\LockedEncounterBillingForms;
use PHPUnit\Framework\TestCase;

class LockedEncounterBillingFormsTest extends TestCase
{
    /** Marks the fixture rows (form name, signature hash), so cleanup removes only those. */
    private const MARKER = 'locked-encounter-billing-forms-test';

    // Ids no real row uses.
    private const PID = 990000200;
    private const ENCOUNTER = 990000201;
    private const OTHER_ENCOUNTER = 990000202;

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testOnlyTheFeeSheetAndMiscBillingOptionsAreBillingForms(): void
    {
        $forms = new LockedEncounterBillingForms(true);

        $this->assertTrue($forms->isBillingForm('fee_sheet'));
        $this->assertTrue($forms->isBillingForm('misc_billing_options'));
        $this->assertFalse($forms->isBillingForm('soap'));
        $this->assertFalse($forms->isBillingForm(''));
    }

    public function testAFormIsLockedOnItsOwnOnlyByItsOwnLockingSignature(): void
    {
        $locked = $this->insertForm(self::ENCOUNTER, 'misc_billing_options');
        $this->insertSignature('forms', $locked, true);
        $signedNotLocked = $this->insertForm(self::ENCOUNTER, 'misc_billing_options');
        $this->insertSignature('forms', $signedNotLocked, false);
        $unsigned = $this->insertForm(self::ENCOUNTER, 'misc_billing_options');
        // The encounter's lock doesn't lock the form on its own.
        $this->insertSignature('form_encounter', self::ENCOUNTER, true);

        $forms = new LockedEncounterBillingForms(true);
        $this->assertTrue($forms->isLockedOnItsOwn($locked));
        $this->assertFalse($forms->isLockedOnItsOwn($signedNotLocked));
        $this->assertFalse($forms->isLockedOnItsOwn($unsigned));

        $this->assertFalse(
            (new LockedEncounterBillingForms(false))->isLockedOnItsOwn($locked),
            'Without "Lock e-signed individual forms", a form signature does not lock it'
        );
    }

    public function testReviewingAnotherEncounterHidesMiscBillingOptions(): void
    {
        $forms = new LockedEncounterBillingForms(true);

        $this->assertTrue($forms->hidesMiscBillingFromMenu(self::OTHER_ENCOUNTER, self::ENCOUNTER, true));
        $this->assertTrue($forms->hidesMiscBillingFromMenu(null, self::ENCOUNTER, true));
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, true), 'Reviewing the session encounter itself');
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::OTHER_ENCOUNTER, self::ENCOUNTER, false), 'Outside review mode the page shows the session encounter');
    }

    public function testASessionEncounterMiscBillingFormLockedOnItsOwnHidesMiscBillingOptions(): void
    {
        $forms = new LockedEncounterBillingForms(true);
        $miscBilling = $this->insertForm(self::ENCOUNTER, 'misc_billing_options');
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, false));

        // A lock on another form of the encounter doesn't count.
        $this->insertSignature('forms', $this->insertForm(self::ENCOUNTER, 'soap'), true);
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, false));

        $this->insertSignature('forms', $miscBilling, true);
        $this->assertTrue($forms->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, false));
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::OTHER_ENCOUNTER, self::OTHER_ENCOUNTER, false), "Another encounter's form");
        $this->assertFalse($forms->hidesMiscBillingFromMenu(null, 0, false), 'No session encounter');
        $this->assertFalse(
            (new LockedEncounterBillingForms(false))->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, false),
            'Without "Lock e-signed individual forms", the form is not locked on its own'
        );
    }

    public function testADeletedMiscBillingFormLockedOnItsOwnStillHidesMiscBillingOptions(): void
    {
        $forms = new LockedEncounterBillingForms(true);
        // Deleting the form keeps its form_misc_billing_options row, which
        // new.php opens again by encounter.
        $this->insertSignature('forms', $this->insertForm(self::ENCOUNTER, 'misc_billing_options', true), true);
        $this->assertTrue($forms->hidesMiscBillingFromMenu(self::ENCOUNTER, self::ENCOUNTER, false));

        $this->insertForm(self::OTHER_ENCOUNTER, 'misc_billing_options', true);
        $this->assertFalse($forms->hidesMiscBillingFromMenu(self::OTHER_ENCOUNTER, self::OTHER_ENCOUNTER, false), 'A deleted form that was not locked');
    }

    /**
     * @return int the forms row id
     */
    private function insertForm(int $encounter, string $formdir, bool $deleted = false): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir) VALUES (NOW(), ?, ?, 0, ?, ?, ?, 1, ?, ?)',
            [$encounter, self::MARKER, self::PID, 'admin', 'Default', $deleted ? 1 : 0, $formdir]
        );
    }

    private function insertSignature(string $table, int $id, bool $lock): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO esign_signatures (tid, `table`, uid, datetime, is_lock, hash, signature_hash) VALUES (?, ?, 1, NOW(), ?, ?, ?)',
            [$id, $table, $lock ? 1 : 0, self::MARKER, self::MARKER]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM esign_signatures WHERE hash = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM forms WHERE form_name = ?', [self::MARKER]);
    }
}
