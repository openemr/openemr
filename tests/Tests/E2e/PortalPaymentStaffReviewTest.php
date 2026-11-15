<?php

/**
 * E2e test: staff post a portal payment to the patient who made it.
 *
 * Portal Activity opens portal/portal_payment.php in a dialog with the
 * payment's record id. Since #13472 the page took its patient from the
 * session, so for staff it showed and posted the payment against whichever
 * chart was open, without the amount the patient had entered (that amount
 * belongs to the paying patient's encounters); with no chart open it was a
 * payment for no patient. This opens one patient's chart (or none), then
 * another patient's pending portal payment from Portal Activity, and checks
 * that the page is the paying patient's with the amount filled in, and that
 * posting it records the payment for that patient only. A record that stops
 * being a payment before it is posted is refused, not posted to the open
 * chart.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PortalPaymentStaffReviewTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-portal-staff-pay';

    private int $payingPid = 0;
    private int $openPid = 0;
    private int $payingEncounter = 0;
    private int $openEncounter = 0;
    private int $recordId = 0;

    /**
     * @return array<string, array{bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function openChartProvider(): array
    {
        return [
            "another patient's chart open" => [true],
            'no chart open' => [false],
        ];
    }

    #[Test]
    #[DataProvider('openChartProvider')]
    #[Depends('testLoginAuthorized')]
    public function testThePaymentIsTheirsWhicheverChartIsOpen(bool $openAnotherChart): void
    {
        $this->insertFixtureData();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            if ($openAnotherChart) {
                $this->client->request('GET', $this->dashboardUrl($this->openPid));
            }
            $this->openPaymentFromPortalActivity();

            $page = $this->client->executeScript(
                'var form = document.forms["invoiceForm"];'
                . ' var paying = form.elements["form_upay[" + arguments[0] + "]"];'
                . ' return {'
                . '  pids: new FormData(form).getAll("form_pid"),'
                . '  paying: paying ? paying.value : null,'
                . '  openRow: !!form.elements["form_upay[" + arguments[1] + "]"],'
                . '  action: form.getAttribute("action"),'
                . '  heading: (document.body.innerText.match(/Accept Payment for[^\\n]*/) || [""])[0]'
                . ' };',
                [$this->payingEncounter, $this->openEncounter]
            );
            $this->assertIsArray($page);
            $this->assertIsString($page['heading']);
            $this->assertStringContainsString('Paying Portalpay (' . $this->payingPid . ')', $page['heading'], "The payment must be the paying patient's");
            $this->assertSame([(string) $this->payingPid], $page['pids'], "The form must post the paying patient's pid");
            $this->assertSame('1.00', $page['paying'], "The amount the patient paid must be filled in on their encounter");
            $this->assertFalse($page['openRow'], "The open chart's encounters must not be listed");
            $this->assertIsString($page['action']);
            $this->assertStringContainsString('recid=' . $this->recordId, $page['action'], 'The save must find the same record');

            $this->client->executeScript('window.alert = function () {}; window.confirm = function () { return true; };');
            $this->client->executeScript('document.querySelector("button[form=invoiceForm][type=submit]").click();');
            // Closing the request is the last thing a successful posting does, after the
            // ar_session and ar_activity writes; ar_session alone is written part way through.
            $this->client->wait(30)->until(
                fn(): bool => QueryUtils::fetchSingleValue(
                    'SELECT status FROM patient_review_request WHERE id = ?',
                    'status',
                    [$this->recordId]
                ) === 'completed',
                'The portal payment must be closed once posted (did the posting fail part way?)'
            );

            $sessions = QueryUtils::fetchRecords(
                'SELECT patient_id, pay_total FROM ar_session WHERE patient_id IN (?, ?)',
                [$this->payingPid, $this->openPid]
            );
            $this->assertSame(
                [[$this->payingPid, '1.00']],
                array_map(fn(array $row): array => [$this->toInt($row['patient_id']), is_string($row['pay_total']) ? $row['pay_total'] : ''], $sessions),
                'The payment must be recorded for the paying patient only'
            );
            $activity = QueryUtils::fetchRecords(
                'SELECT pid, encounter, pay_amount FROM ar_activity WHERE pid IN (?, ?) AND deleted IS NULL',
                [$this->payingPid, $this->openPid]
            );
            $this->assertSame(
                [[$this->payingPid, $this->payingEncounter, '1.00']],
                array_map(fn(array $row): array => [
                    $this->toInt($row['pid']),
                    $this->toInt($row['encounter']),
                    is_string($row['pay_amount']) ? $row['pay_amount'] : '',
                ], $activity),
                "The payment must be applied to the paying patient's encounter only"
            );
            $secrets = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS c FROM patient_review_secret WHERE request_id = ?', 'c', [$this->recordId]);
            $this->assertSame(0, $this->toInt($secrets), 'The card details must be deleted once the payment is posted');
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
    public function testAPaymentRecordThatChangedIsNotPostedToTheOpenChart(): void
    {
        $this->insertFixtureData();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->client->request('GET', $this->dashboardUrl($this->openPid));
            $this->openPaymentFromPortalActivity();

            // The record stops being a payment between opening the dialog and posting.
            QueryUtils::sqlStatementThrowException("UPDATE patient_review_request SET type = 'profile' WHERE id = ?", [$this->recordId]);
            $this->client->executeScript('window.alert = function () {}; window.confirm = function () { return true; };');
            $this->client->executeScript('document.querySelector("button[form=invoiceForm][type=submit]").click();');
            $this->client->wait(30)->until(
                fn(): bool => str_contains($this->client->getCurrentURL(), 'portal_payment.php'),
                'Posting did not reach portal_payment.php'
            );
            $this->client->waitFor('body', 30);

            $recorded = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS c FROM ar_session WHERE patient_id IN (?, ?)',
                'c',
                [$this->payingPid, $this->openPid]
            );
            $this->assertSame(0, $this->toInt($recorded), 'A record that is no longer a payment must not be posted to anyone');
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
    public function testASaveWithNoAmountLeavesThePaymentPending(): void
    {
        $this->insertFixtureData();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->openPaymentFromPortalActivity();

            // Clear the amount and submit the form itself, past the page's validate(), as a
            // hand-made POST would.
            $this->client->executeScript(
                'var form = document.forms["invoiceForm"];'
                . ' form.elements["form_upay[" + arguments[0] + "]"].value = "";'
                . ' HTMLFormElement.prototype.submit.call(form);',
                [$this->payingEncounter]
            );
            $this->client->wait(30)->until(
                fn(): bool => str_contains($this->client->getCurrentURL(), 'portal_payment.php'),
                'Saving did not reach portal_payment.php'
            );
            // The whole response, so the script has finished whatever it was going to do.
            $this->client->wait(30)->until(
                fn(): bool => $this->client->executeScript('return document.readyState;') === 'complete',
                'The page after saving did not finish loading'
            );

            $recorded = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS c FROM ar_session WHERE patient_id = ?',
                'c',
                [$this->payingPid]
            );
            $this->assertSame(0, $this->toInt($recorded), 'Nothing was posted');
            $status = QueryUtils::fetchSingleValue('SELECT status FROM patient_review_request WHERE id = ?', 'status', [$this->recordId]);
            $this->assertSame('pending', $status, 'A save that posts nothing must leave the portal payment pending');
            $secrets = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS c FROM patient_review_secret WHERE request_id = ?', 'c', [$this->recordId]);
            $this->assertGreaterThan(0, $this->toInt($secrets), 'Its card details must be kept for the posting');
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
     * As staff do: Portal Activity, then the payment's row, which loads
     * portal_payment.php into a dialog on that page.
     */
    private function openPaymentFromPortalActivity(): void
    {
        $this->client->request('GET', '/portal/patient/onsiteactivityviews');
        $this->client->executeScript('window.restoreSession = function () { return true; };');
        $row = 'table.collection tbody tr[id="' . $this->recordId . '"] td';
        $this->client->waitFor($row, 30);
        $this->client->executeScript('document.querySelector(arguments[0]).click();', [$row]);
        $this->client->waitFor('#invoiceForm', 30);
        $this->client->wait(10)->until(
            fn(): bool => $this->client->executeScript('return typeof jsondata !== "undefined";') === true,
            'The payment dialog did not load the stored payment'
        );
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $this->payingPid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $this->openPid = $this->payingPid + 1;
        foreach ([[$this->payingPid, 'Paying'], [$this->openPid, 'Opened']] as [$pid, $first]) {
            QueryUtils::sqlInsert(
                'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                [$pid, self::FIXTURE_MARKER, $first, 'Portalpay', '1970-01-01', 'Female']
            );
        }
        $this->payingEncounter = $this->toInt(QueryUtils::generateId());
        $this->openEncounter = $this->toInt(QueryUtils::generateId());
        foreach ([[$this->payingPid, $this->payingEncounter, '50.00'], [$this->openPid, $this->openEncounter, '301.00']] as [$pid, $encounter, $fee]) {
            QueryUtils::sqlInsert(
                'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id) VALUES (NOW(), ?, ?, ?, 1)',
                [self::FIXTURE_MARKER, $pid, $encounter]
            );
            // billing.modifier is left NULL (the column allows it); posting must still record
            // the payment, as ar_activity.modifier does not.
            QueryUtils::sqlInsert(
                "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee)"
                . " VALUES (NOW(), 'CPT4', '99213', ?, 1, ?, ?, 1, 1, 1, ?)",
                [$pid, $encounter, self::FIXTURE_MARKER, $fee]
            );
        }
        // What the portal's card payment saves for staff to review: the
        // invoice form as the patient filled it in, and the card result.
        $invoice = json_encode([
            'form_pid' => (string) $this->payingPid,
            'form_method' => 'credit_card',
            'radio_type_of_payment' => 'invoice_balance',
            'form_upay[' . $this->payingEncounter . ']' => '1.00',
            'form_paytotal' => '1.00',
        ], JSON_THROW_ON_ERROR);
        $card = json_encode([
            'cardHolderName' => 'Paying Portalpay',
            'status' => 'approved',
            'authCode' => 'E2EAUTH',
            'transId' => 'E2ETRANS',
            'cardNumber' => 'XXXX1111',
            'cc_type' => 'Visa',
            'zip' => '05701',
        ], JSON_THROW_ON_ERROR);
        $this->recordId = ReviewQueueFactory::portalWorkflows()->submitPayment(
            $this->payingPid,
            $invoice,
            $card,
            true,
            ReviewActor::patient($this->payingPid, self::FIXTURE_MARKER),
        )->id;
    }

    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            $sessions = QueryUtils::fetchTableColumn('SELECT session_id FROM ar_session WHERE patient_id = ?', 'session_id', [$pid]);
            foreach ($sessions as $session) {
                QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE session_id = ?', [$session]);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE patient_id = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM payments WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
            foreach (['patient_review_secret', 'patient_review_event'] as $table) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM ' . QueryUtils::escapeTableName($table)
                    . ' WHERE request_id IN (SELECT id FROM patient_review_request WHERE pid = ?)',
                    [$pid]
                );
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_review_request WHERE pid = ?', [$pid]);
        }
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
