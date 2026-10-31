<?php

/**
 * The work a statement run hands off: building the statement text, saving and
 * sending it, and the database lookups and updates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

/**
 * The statements page implements this around the site's statement.inc.php
 * (make_statement() and friends), its email and portal functions and the
 * document store, so StatementRun can be tested without any of them.
 */
interface StatementRunSteps
{
    /**
     * Add one invoice's lines to the statement and update its amount due
     * (StatementLines::append()).
     *
     * @param array<string, mixed> $stmt
     */
    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void;

    /**
     * The patient's encounters, other than those given, that hold a credit
     * (PatientCreditEncounters::find()).
     *
     * @param list<int> $excludedEncounterIds form_encounter.id
     * @return list<array{encounter: int, date: string, stmt_count: int}>
     */
    public function creditEncounters(int $pid, array $excludedEncounterIds): array;

    /**
     * The statement text, in the site's statement layout; '' when the site's
     * print rules exclude it.
     *
     * @param array<string, mixed> $stmt
     */
    public function buildStatement(array $stmt): string;

    /**
     * What goes in the batch in place of a statement that came out empty.
     */
    public function emptyStatementNotice(int $pid, string $encounter): string;

    /**
     * Save the statement in the patient's documents.
     */
    public function saveDocument(int $pid, string $statement): void;

    /**
     * Email the statement to the patient.
     *
     * @throws \RuntimeException|\InvalidArgumentException when it can't be sent; the message says why
     */
    public function email(int $pid, string $statement): void;

    public function isPortalAuthorized(int $pid): bool;

    /**
     * Send the statement to the patient portal.
     *
     * @param list<array<string, mixed>> $invoices the statement as it stood after each of the patient's invoices
     */
    public function notifyPortal(int $pid, array $invoices, string $statement, string $invoiceId): bool;

    /**
     * Count the invoices as statemented today.
     *
     * @param list<int> $encounterIds form_encounter.id
     */
    public function recordStatementRun(array $encounterIds): void;
}
