<?php

/**
 * Builds the invoice lines of a patient statement, one encounter at a time.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Billing\StatementEncounterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class StatementLines
{
    /**
     * Adds one encounter's invoice lines to the statement being built and
     * carries the encounter's balance into the amount due. The statement array
     * is the one the statement run (interface/billing/sl_eob_search.php) hands
     * to the layouts in the site's statement.inc.php: `lines` is a list of
     * dos / desc / amount / adjust / paid / notice / detail / bill_date, `amount`
     * the total due as a "%.2f" string and `ins_paid` the running insurance total.
     *
     * When a module listens for StatementEncounterEvent, it may leave the encounter
     * off: then the encounter adds no lines and no balance, and is kept in the
     * statement's `excluded` entry instead (see excluded()). With no listener the
     * event isn't dispatched and nothing extra is read.
     *
     * @param array<string, mixed> $stmt
     * @param int $notice the statement number this will be for the encounter (1 for the first)
     * @param ?EventDispatcherInterface $dispatcher the kernel's when null
     */
    public static function append(array &$stmt, int $pid, int $encounter, string $svcdate, int $notice, ?EventDispatcherInterface $dispatcher = null): void
    {
        // grab last bill date from billing
        $bdrow = QueryUtils::querySingleRow("select bill_date from billing where pid = ? AND encounter = ? limit 1", [$pid, $encounter]);
        $billDate = is_array($bdrow) ? ($bdrow['bill_date'] ?? null) : null;

        $invlines = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);

        $useCustomStatement = OEGlobalsBag::getInstance()->getBoolean('use_custom_statement');
        $items = [];

        foreach ($invlines as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            $chg = is_numeric($value['chg'] ?? null) ? (float) $value['chg'] : 0.0;
            $bal = is_numeric($value['bal'] ?? null) ? (float) $value['bal'] : 0.0;
            $adj = is_numeric($value['adj'] ?? null) ? (float) $value['adj'] : 0.0;
            $codeText = is_string($value['code_text'] ?? null) ? $value['code_text'] : '';

            $line = [];
            $line['dos'] = $svcdate;
            if ($useCustomStatement) {
                $line['desc'] = ($key == 'CO-PAY') ? "Patient Payment" : $codeText;
            } else {
                $line['desc'] = ($key == 'CO-PAY') ? "Patient Payment" : "Procedure $key";
            }

            $line['amount'] = sprintf("%.2f", $chg);
            $line['adjust'] = sprintf("%.2f", $adj);
            $line['paid'] = sprintf("%.2f", $chg - $bal);
            $line['notice'] = $notice;
            $line['detail'] = is_array($value['dtl'] ?? null) ? $value['dtl'] : [];
            $line['bill_date'] = $billDate;
            $items[] = ['line' => $line, 'bal' => $bal, 'ins' => $value['ins'] ?? null];
        }

        $dispatcher ??= self::kernelDispatcher();
        if ($dispatcher !== null && $dispatcher->hasListeners(StatementEncounterEvent::EVENT_NAME)) {
            $context = self::encounterContext($pid, $encounter);
            $recipientLines = is_array($stmt['to'] ?? null) ? array_values(array_filter($stmt['to'], is_string(...))) : StatementRecipient::lines($context);
            $event = new StatementEncounterEvent(
                $pid,
                $encounter,
                $svcdate,
                is_string($context['sensitivity'] ?? null) ? $context['sensitivity'] : '',
                $notice,
                array_column($items, 'line'),
                self::total($items),
                $recipientLines,
                StatementRecipient::isPatient($context),
                $stmt
            );
            $dispatcher->dispatch($event, StatementEncounterEvent::EVENT_NAME);
            if ($event->isExcluded()) {
                self::keepExcluded($stmt, $pid, $encounter, is_numeric($context['id'] ?? null) ? (int) $context['id'] : 0, $svcdate, $items);
                self::retarget($stmt, $pid, null);
                return;
            }
        }

        $lines = is_array($stmt['lines'] ?? null) ? $stmt['lines'] : [];
        $amount = is_numeric($stmt['amount'] ?? null) ? (float) $stmt['amount'] : 0.0;
        $insPaid = is_numeric($stmt['ins_paid'] ?? null) ? $stmt['ins_paid'] + 0 : 0;
        foreach ($items as $item) {
            $lines[] = $item['line'];
            $amount = (float) sprintf("%.2f", $amount + $item['bal']);
            if (is_numeric($item['ins'])) {
                $insPaid += $item['ins'] + 0;
            }
        }

        $stmt['lines'] = $lines;
        $stmt['amount'] = sprintf("%.2f", $amount);
        $stmt['ins_paid'] = $insPaid;

        self::retarget($stmt, $pid, $encounter);
    }

    /**
     * The encounters a module left off the statement being built, each with the
     * lines and the balance it would have added. The balance is still owed.
     *
     * @param array<string, mixed> $stmt
     * @return list<array{encounter: int, id: int, dos: string, lines: list<array<string, mixed>>, amount: string}>
     */
    public static function excluded(array $stmt): array
    {
        $kept = $stmt['excluded'] ?? null;
        if (!is_array($kept) || !is_numeric($stmt['pid'] ?? null) || ($kept['pid'] ?? null) !== (int) $stmt['pid']) {
            return [];
        }
        /** @var list<array{encounter: int, id: int, dos: string, lines: list<array<string, mixed>>, amount: string}> $encounters */
        $encounters = is_array($kept['encounters'] ?? null) ? $kept['encounters'] : [];
        return $encounters;
    }

    /**
     * True when a module left every encounter off the statement, so there is nothing to send.
     *
     * @param array<string, mixed> $stmt
     */
    public static function onlyExcluded(array $stmt): bool
    {
        return self::excluded($stmt) !== [] && (!is_array($stmt['lines'] ?? null) || $stmt['lines'] === []);
    }

    /**
     * The statement run's form_encounter ids without those of the encounters left off,
     * so an encounter that wasn't on the statement isn't counted as statemented.
     *
     * @param array<string, mixed> $stmt
     * @param array<mixed> $encounterIds form_encounter.id values
     * @return array<mixed>
     */
    public static function withoutExcluded(array $stmt, array $encounterIds): array
    {
        $left = array_column(self::excluded($stmt), 'id');
        if ($left === []) {
            return $encounterIds;
        }
        return array_values(array_filter(
            $encounterIds,
            static fn (mixed $id): bool => !is_numeric($id) || !in_array((int) $id, $left, true)
        ));
    }

    /**
     * @param list<array{line: array<string, mixed>, bal: float, ins: mixed}> $items
     */
    private static function total(array $items): string
    {
        $amount = 0.0;
        foreach ($items as $item) {
            $amount = (float) sprintf("%.2f", $amount + $item['bal']);
        }
        return sprintf("%.2f", $amount);
    }

    /**
     * @param array<string, mixed> $stmt
     * @param list<array{line: array<string, mixed>, bal: float, ins: mixed}> $items
     */
    private static function keepExcluded(array &$stmt, int $pid, int $encounter, int $id, string $svcdate, array $items): void
    {
        $kept = is_array($stmt['excluded'] ?? null) && ($stmt['excluded']['pid'] ?? null) === $pid ? $stmt['excluded'] : ['pid' => $pid, 'encounters' => []];
        $encounters = is_array($kept['encounters'] ?? null) ? $kept['encounters'] : [];
        $encounters[] = [
            'encounter' => $encounter,
            'id' => $id,
            'dos' => $svcdate,
            'lines' => array_column($items, 'line'),
            'amount' => self::total($items),
        ];
        $stmt['excluded'] = ['pid' => $pid, 'encounters' => $encounters];
    }

    /**
     * Once something was left off this patient's statement, keeps the statement's header
     * from describing it: the lead encounter (provider, facility, billing note, notice
     * count) moves to an encounter on the statement, and the due date and age come from
     * the lines on it. A statement with nothing left off is not touched.
     *
     * @param array<string, mixed> $stmt
     */
    private static function retarget(array &$stmt, int $pid, ?int $included): void
    {
        $left = self::excluded($stmt + ['pid' => $pid]);
        if ($left === []) {
            return;
        }
        $leadLeftOff = is_numeric($stmt['encounter'] ?? null) && in_array((int) $stmt['encounter'], array_column($left, 'encounter'), true);
        if ($leadLeftOff && $included !== null) {
            $row = QueryUtils::querySingleRow(
                "SELECT id, billing_note, provider_id, stmt_count FROM form_encounter WHERE pid = ? AND encounter = ? ORDER BY id DESC LIMIT 1",
                [$pid, $included]
            );
            if (is_array($row)) {
                $stmt['encounter'] = $included;
                $stmt['fid'] = is_numeric($row['id'] ?? null) ? (int) $row['id'] : null;
                $stmt['enc_bill_note'] = $row['billing_note'] ?? null;
                $stmt['provider_id'] = $row['provider_id'] ?? null;
                $stmt['dun_count'] = $row['stmt_count'] ?? null;
            }
        }
        $dates = array_filter(
            array_map(static fn (mixed $line): string => is_array($line) && is_string($line['dos'] ?? null) ? $line['dos'] : '', is_array($stmt['lines'] ?? null) ? $stmt['lines'] : []),
            static fn (string $dos): bool => $dos !== ''
        );
        if ($dates !== []) {
            $stmt['duedate'] = min($dates);
            if (is_string($stmt['today'] ?? null)) {
                $stmt['age'] = round((strtotime($stmt['today']) - strtotime($stmt['duedate'])) / (24 * 60 * 60));
            }
        }
    }

    /**
     * The encounter's form_encounter id and sensitivity, and the patient columns
     * StatementRecipient reads; read only when a module listens.
     *
     * @return array<mixed>
     */
    private static function encounterContext(int $pid, int $encounter): array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT fe.id, fe.sensitivity, p.fname, p.lname, p.street, p.street_line_2, p.city, p.state, p.postal_code, " .
            "p.guardiansname, p.guardianaddress, p.guardiancity, p.guardianstate, p.guardianpostalcode " .
            "FROM form_encounter AS fe JOIN patient_data AS p ON p.pid = fe.pid " .
            "WHERE fe.pid = ? AND fe.encounter = ? ORDER BY fe.id DESC LIMIT 1",
            [$pid, $encounter]
        );
        return is_array($row) ? $row : [];
    }

    private static function kernelDispatcher(): ?EventDispatcherInterface
    {
        $globals = OEGlobalsBag::getInstance();
        return $globals->hasKernel() ? $globals->getKernel()->getEventDispatcher() : null;
    }
}
