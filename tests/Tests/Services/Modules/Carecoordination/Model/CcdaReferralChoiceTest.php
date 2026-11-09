<?php

/**
 * Which referral a C-CDA is linked to (#15137): the one staff chose when it's the patient's, and
 * the patient's most recent when they chose none.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\Carecoordination\Model;

use Carecoordination\Model\EncounterccdadispatchTable;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CcdaReferralChoiceTest extends TestCase
{
    private const PATIENT = 990071;
    private const OTHER_PATIENT = 990072;

    private EncounterccdadispatchTable $table;
    private int $orgId = 0;

    /** @var list<int> */
    private array $transactions = [];

    protected function setUp(): void
    {
        $this->table = new EncounterccdadispatchTable();
        $this->orgId = (int)QueryUtils::sqlInsert("INSERT INTO users (username, active, authorized, abook_type, organization) VALUES ('', 1, 0, 'external_org', 'Zq Choice Pantry')");
    }

    /**
     * Removes the transactions and the organization the test created.
     */
    protected function tearDown(): void
    {
        foreach ($this->transactions as $id) {
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$id]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->orgId]);
    }

    #[Test]
    public function theChosenReferralIsUsedEvenWhenANewerOneExists(): void
    {
        $older = $this->saveTransaction(self::PATIENT, 'LBTref');
        $this->saveTransaction(self::PATIENT, 'LBTref');

        $this->assertSame($older, $this->table->referralForCcda(self::PATIENT, $older));
    }

    #[Test]
    public function withNoChoiceThePatientsMostRecentIsUsed(): void
    {
        $this->saveTransaction(self::PATIENT, 'LBTref');
        $newest = $this->saveTransaction(self::PATIENT, 'LBTref');
        $this->saveTransaction(self::OTHER_PATIENT, 'LBTref');

        $this->assertSame($newest, $this->table->referralForCcda(self::PATIENT, null));
    }

    #[Test]
    public function aPatientWithNoReferralLinksToNothing(): void
    {
        $this->assertNull($this->table->referralForCcda(self::PATIENT, null));
    }

    #[Test]
    public function anotherPatientsReferralLinksToNothingRatherThanAGuess(): void
    {
        $this->saveTransaction(self::PATIENT, 'LBTref');
        $others = $this->saveTransaction(self::OTHER_PATIENT, 'LBTref');

        $this->assertNull($this->table->referralForCcda(self::PATIENT, $others));
    }

    #[Test]
    public function aTransactionThatIsNotAReferralLinksToNothing(): void
    {
        $this->saveTransaction(self::PATIENT, 'LBTref');
        $note = $this->saveTransaction(self::PATIENT, 'LBTbill');

        $this->assertNull($this->table->referralForCcda(self::PATIENT, $note));
    }

    #[Test]
    public function theChoicesAreThePatientsOwnReferralsNewestFirstAndNamed(): void
    {
        $older = $this->saveTransaction(self::PATIENT, 'LBTref');
        $newer = $this->saveTransaction(self::PATIENT, 'LBTref', [
            'refer_date' => '2026-11-01',
            'refer_to' => (string)$this->orgId,
            'body' => str_repeat('x', 70),
        ]);
        $this->saveTransaction(self::PATIENT, 'LBTbill');
        $this->saveTransaction(self::OTHER_PATIENT, 'LBTref');

        $this->assertSame([
            ['id' => $newer, 'label' => '#' . $newer . ' - 2026-11-01 - Zq Choice Pantry - ' . str_repeat('x', 60)],
            ['id' => $older, 'label' => '#' . $older],
        ], $this->table->getPatientReferrals(self::PATIENT));
    }

    /**
     * The choice reaches the row the Direct send reads the referral from, past the transaction it's saved in.
     */
    #[Test]
    public function theSavedCcdaRecordsTheChosenReferral(): void
    {
        $older = $this->saveTransaction(self::PATIENT, 'LBTref');
        $this->saveTransaction(self::PATIENT, 'LBTref');
        $document = base64_encode('<ClinicalDocument/>');

        $chosen = $this->table->logCCDA(self::PATIENT, null, $document, date('Y-m-d H:i:s'), 0, 1, 'referral', 0, 0, 0, $older);

        $ccdaId = $chosen->getId();
        $this->assertIsInt($ccdaId);
        try {
            $this->assertEquals($older, QueryUtils::fetchSingleValue('SELECT transaction_id FROM ccda WHERE id = ?', 'transaction_id', [$ccdaId]));
            $this->assertEquals(1, QueryUtils::fetchSingleValue('SELECT transaction_chosen FROM ccda WHERE id = ?', 'transaction_chosen', [$ccdaId]));
        } finally {
            $this->removeCcda($ccdaId);
        }
    }

    /**
     * With no choice the C-CDA still links the most recent referral, but marked as a guess, so a Direct
     * send doesn't carry it and a reply isn't filed by it.
     */
    #[Test]
    public function aGuessedReferralIsMarkedAsNotChosen(): void
    {
        $newest = $this->saveTransaction(self::PATIENT, 'LBTref');
        $document = base64_encode('<ClinicalDocument/>');

        $guessed = $this->table->logCCDA(self::PATIENT, null, $document, date('Y-m-d H:i:s'), 0, 1, 'referral');

        $ccdaId = $guessed->getId();
        $this->assertIsInt($ccdaId);
        try {
            $row = QueryUtils::querySingleRow('SELECT transaction_id, transaction_chosen FROM ccda WHERE id = ?', [$ccdaId]);
            $this->assertEquals(['transaction_id' => $newest, 'transaction_chosen' => 0], $row);
        } finally {
            $this->removeCcda($ccdaId);
        }
    }

    /**
     * The C-CDA's Reason for Referral is the chosen referral's, not the newest one's, and goes back to
     * the newest once the choice is cleared.
     */
    #[Test]
    public function theReasonForReferralIsTheChosenReferrals(): void
    {
        $older = $this->saveTransaction(self::PATIENT, 'LBTref', $this->referralFields('Zq older reason'));
        $this->saveTransaction(self::PATIENT, 'LBTref', $this->referralFields('Zq newer reason'));

        $this->table->setContentReferral($older);
        $chosen = $this->table->getReferrals(self::PATIENT);
        $this->table->setContentReferral(null);

        $this->assertStringContainsString('<text>Zq older reason</text>', $chosen);
        $this->assertStringNotContainsString('Zq newer reason', $chosen);
    }

    /**
     * Another patient's referral gives the section no reason.
     */
    #[Test]
    public function anotherPatientsReferralGivesTheSectionNoReason(): void
    {
        $this->saveTransaction(self::PATIENT, 'LBTref', $this->referralFields('Zq own reason'));
        $others = $this->saveTransaction(self::OTHER_PATIENT, 'LBTref', $this->referralFields('Zq other reason'));

        $this->table->setContentReferral($others);
        $section = $this->table->getReferrals(self::PATIENT);
        $this->table->setContentReferral(null);

        $this->assertSame('<referral_reason></referral_reason>', $section);
    }

    /**
     * The fields the Reason for Referral section reads, each of which it requires.
     *
     * @return array<string, string>
     */
    private function referralFields(string $reason): array
    {
        return [
            'body' => $reason,
            'refer_to' => (string)$this->orgId,
            'refer_from' => '1',
            'refer_date' => '2026-11-01',
            'billing_facility_id' => '3',
        ];
    }

    /**
     * Removes a saved C-CDA, with its document and file.
     */
    private function removeCcda(int $ccdaId): void
    {
        $url = QueryUtils::fetchSingleValue("SELECT url FROM documents WHERE foreign_reference_table = 'ccda' AND foreign_reference_id = ?", 'url', [$ccdaId]);
        if (is_string($url) && is_file(substr($url, strlen('file://')))) {
            unlink(substr($url, strlen('file://')));
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM documents WHERE foreign_reference_table = 'ccda' AND foreign_reference_id = ?", [$ccdaId]);
        QueryUtils::sqlStatementThrowException('DELETE FROM ccda WHERE id = ?', [$ccdaId]);
    }

    /**
     * @param array<string, string> $fields
     */
    private function saveTransaction(int $pid, string $form, array $fields = []): int
    {
        $id = (int)QueryUtils::sqlInsert(
            "INSERT INTO transactions SET title = ?, user = 'admin', groupname = 'Default', authorized = 1, date = NOW(), pid = ?",
            [$form, $pid]
        );
        $this->transactions[] = $id;
        foreach ($fields as $field => $value) {
            QueryUtils::sqlStatementThrowException('INSERT INTO lbt_data SET form_id = ?, field_id = ?, field_value = ?', [$id, $field, $value]);
        }
        return $id;
    }
}
