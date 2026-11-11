<?php

/**
 * Posts one claim payment from an 835 to its invoice: finds the encounter,
 * decides what can be posted, posts payments, adjustments and charges, closes
 * the insurance level and queues the next payer. Moved out of
 * interface/billing/sl_eob_process.php, which now renders the result.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019-2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\EraAdjustmentMemo;
use OpenEMR\Billing\EraCheckPosting;
use OpenEMR\Billing\EraReversal;
use OpenEMR\Billing\EraReversalOutcome;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PaymentProcessing\Recorder;

final class ClaimPosting
{
    /** @var list<ReportMessage|ReportDetail> */
    private array $lines = [];

    /** @var list<ClaimIssue> */
    private array $issues = [];

    /** The invoice balance shown on report lines, as the page has always kept it. */
    private float $balance = 0.0;

    /** The balance as '%.2f' text after existing lines were listed, until the next posting changes it. */
    private ?string $balanceText = null;

    /**
     * @param EraCheckPosting $checkPosting      shared by every claim payment in the ERA
     * @param int             $postUser          the user posting (ar_activity.post_user)
     * @param string          $payerId           the insurance company the ERA is posted for (InsId), or ''
     * @param bool            $withoutUpdate     report only, post nothing ("Without Update")
     * @param string          $payDate           yyyy-mm-dd to post with instead of the ERA's dates, or ''
     * @param bool            $addUnmatchedCodes add a code the invoice doesn't have instead of refusing it
     *                                           (global add_unmatched_code_from_ins_co_era_to_billing)
     */
    public function __construct(
        private readonly EraCheckPosting $checkPosting,
        private readonly EraReversal $reversal,
        private readonly Recorder $recorder,
        private readonly int $postUser,
        private readonly string $payerId,
        private readonly bool $withoutUpdate,
        private readonly string $payDate,
        private readonly bool $addUnmatchedCodes,
    ) {
    }

    /**
     * @param int $sessionId the payment session created or reused for the claim's check
     */
    public function post(ClaimPayment $claim, int $sessionId): ClaimPostingResult
    {
        $this->lines = [];
        $this->issues = [];
        $this->balance = 0.0;
        $this->balanceText = null;
        $paymentsPosted = 0.0;
        $adjustmentsPosted = 0.0;
        $chargesAdded = 0.0;
        $levelClosed = false;
        $secondaryQueued = false;
        $debug = $this->withoutUpdate;
        $checkNumber = $claim->checkNumber;
        $checkDate = $claim->checkDate;

        $invoiceKey = [
            'our_claim_id' => $claim->ourClaimId,
            'patient_lname' => $claim->patientLastName,
            'patient_fname' => $claim->patientFirstName,
        ];
        [$foundPid, $foundEncounter, $foundInvoice] = SLEOB::slInvoiceNumber($invoiceKey);
        $invnumber = is_scalar($foundInvoice) ? (string) $foundInvoice : '';

        // Get details, if we have them, for the invoice.
        $inverror = true;
        $codes = [];
        $encounterRow = [];
        $pid = 0;
        $encounter = 0;
        if ((bool) $foundPid && (bool) $foundEncounter) {
            $row = QueryUtils::querySingleRow(
                "SELECT e.*, p.fname, p.mname, p.lname FROM form_encounter AS e, patient_data AS p WHERE "
                . "e.pid = ? AND e.encounter = ? AND p.pid = e.pid",
                [$foundPid, $foundEncounter]
            );
            if (!is_array($row) || $row === []) {
                $invnumber = $claim->ourClaimId;
            } else {
                $inverror = false;
                $encounterRow = $row;
                $pid = is_numeric($row['pid'] ?? null) ? (int) $row['pid'] : 0;
                $encounter = is_numeric($row['encounter'] ?? null) ? (int) $row['encounter'] : 0;
                $codes = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);
            }
        }
        if ($inverror) {
            $this->issue(ClaimIssue::ClaimNotFound);
        }

        // The level the payer processed the claim at.
        $csc = $claim->claimStatusCode;
        $level = match ($csc) {
            '2', '20' => 2,
            '3', '21' => 3,
            default => 1,
        };
        $inslabel = 'Ins' . $level;
        $payerType = (string) $level;
        $primary = $level === 1;

        // The claim's date of service as the ERA gives it; the payer lookups below use it.
        $serviceDate = self::parseDate($claim->serviceDate);

        // CLP07, the payer's claim control number. It is stored on every row this
        // claim posts, so a claim the payer paid nothing on still records it, and a
        // reversal uses it to find the payment it takes back.
        $payerClaimNumber = $claim->payerClaimId !== '' ? $claim->payerClaimId : null;

        // Re-posting an ERA must not post a claim's payments and adjustments twice:
        // skip a claim this check was already posted to in an earlier run. Several
        // claim lines (CLPs) for one claim in this ERA still post.
        $alreadyPosted = !$inverror && $this->checkPosting->postedBefore($pid, $encounter, $checkNumber, $checkDate);

        $this->message(ReportStyle::Info, "Claim status $csc: " . (BillingUtilities::CLAIM_STATUS_CODES_CLP02[$csc] ?? ''));

        if ($inverror) {
            $this->message(ReportStyle::Error, "The following claim is not in our database");
        }

        if ($alreadyPosted) {
            $inverror = true;
            $this->issue(ClaimIssue::AlreadyPosted);
            $this->message(ReportStyle::Error, "Already posted from check $checkNumber; not posted again");
        } elseif ($csc === '4') {
            // Denial: the reason codes are stored on the claim for the billing manager to show.
            $inverror = true;
            $this->issue(ClaimIssue::Denied);
            if (!$debug && $pid > 0 && $encounter > 0) {
                $reasons = [];
                foreach ($claim->serviceLines as $svc) {
                    foreach ($svc->adjustments as $adj) {
                        // Per code and modifier the reason will be shown in the billing manager.
                        $reasons[] = $svc->code . '_' . $svc->modifier . '_' . $adj->groupCode . '_' . $adj->reasonCode;
                    }
                }
                $codeValue = implode(',', $reasons);
                // A re-posted ERA must not add the same denial again.
                if ($this->checkPosting->denialRecorded($pid, $encounter, $level, $codeValue)) {
                    $this->message(ReportStyle::Error, "Denial already recorded for this claim; not recorded again");
                } else {
                    // process_file holds the reasons: a denial has no file name.
                    BillingUtilities::updateClaim(true, $pid, $encounter, $this->payerId, $payerType, 7, 0, $codeValue);
                }
            }
            $this->message(ReportStyle::Error, "Not posting adjustments for denied claims, please follow up manually!");
        } elseif ($csc === '22') {
            $inverror = true;
            $this->issue(ClaimIssue::Reversal);
            // The reversal isn't posted, but the payment it takes back closed its
            // insurance level: re-open it so the balance isn't treated as the patient's.
            if (!$debug && $pid > 0 && $encounter > 0) {
                // The ERA's date of service, as the rest of the posting uses; the encounter's if it has none.
                $encounterDate = $encounterRow['date'] ?? null;
                $reversalDos = $serviceDate !== ''
                    ? $serviceDate
                    : (is_string($encounterDate) ? substr($encounterDate, 0, 10) : '');
                $reversalLevel = $this->reversal->reversedLevel($pid, $encounter, $reversalDos, $payerClaimNumber ?? '');
                $outcome = $this->reversal->reopenLevel($pid, $encounter, $reversalLevel, $checkNumber, $checkDate);
                $this->message(ReportStyle::Error, match ($outcome) {
                    EraReversalOutcome::Reopened => "Ins$reversalLevel re-opened, so the balance stays with insurance",
                    EraReversalOutcome::CorrectionPosted => "Ins$reversalLevel stays closed: this check also posted a corrected payment",
                    EraReversalOutcome::AlreadyOpen => "Ins$reversalLevel was not closed; left as is",
                    EraReversalOutcome::PayerNotOnClaim => "This payer is not on the claim for the date of service; insurance levels left as they are",
                    EraReversalOutcome::LevelUnknown => "Could not tell which insurance this reversal is for; insurance levels left as they are",
                });
            }
            $this->message(ReportStyle::Error, "Payment reversals are not automated, please enter manually!");
        }

        $warnings = rtrim($claim->warnings);
        if ((bool) $warnings) {
            $this->message(ReportStyle::Info, $warnings, true);
        }

        // Simplify some claim attributes for cleaner code.
        $postDate = $this->payDate !== '' ? $this->payDate : self::parseDate($claim->checkDate);
        $productionDate = $this->payDate !== '' ? $this->payDate : self::parseDate($claim->productionDate);
        $lastName = $encounterRow['lname'] ?? null;
        $patientName = (bool) $lastName
            ? self::text($encounterRow['fname'] ?? '') . ' ' . self::text($lastName)
            : $claim->patientFirstName . ' ' . $claim->patientLastName;

        $error = $inverror;
        // Service lines the payer pended (adjustment reason 133) are tracked for the whole
        // run: another claim payment (CLP) in this ERA may cover the same claim.
        $pendedCodes = [];
        $answeredCodes = [];

        // The invoice's codes split into procedure and modifiers, for matching a
        // line the payer returned without its modifier.
        $cpts = [];
        $mods = [];
        foreach (array_keys($codes) as $value) {
            $parts = explode(':', (string) $value);
            $cpt = $parts[0];
            $cpts[] = $cpt;
            for ($i = 1, $count = count($parts) - 1; $i <= $count; $i++) {
                $mods[$cpt][] = $parts[$i];
            }
        }

        // This loops once for each service item in this claim.
        foreach ($claim->serviceLines as $svc) {
            $svcPended = false;
            // Treat a modifier in the remit data as part of the procedure key.
            $codekey = $svc->code;
            if ((bool) $svc->modifier) {
                $codekey .= ':' . $svc->modifier;
            }

            $prev = $codes[$codekey] ?? null;
            // However sometimes a secondary insurance doesn't return the modifier that was on the service item
            // that was processed by the primary payer so try to deal with that
            if (!is_array($prev) && !(bool) $svc->modifier && in_array($svc->code, $cpts, true)) {
                $codekey = $svc->code . ':' . implode(':', $mods[$svc->code] ?? []);
                $prev = $codes[$codekey] ?? null;
            }
            $codetype = '';

            if (is_array($prev) && $prev !== []) {
                // Report the detail lines already on file for this service item.
                $prevCodeType = $prev['code_type'] ?? 'none';
                $codetype = is_scalar($prevCodeType) ? (string) $prevCodeType : '';
                $this->existingLines($prev, $patientName, $invnumber, $serviceDate, $codekey);
                // Check for sanity in amount charged.
                $prevchg = sprintf("%.2f", self::number($prev['chg'] ?? null) + self::number($prev['adj'] ?? null));
                if ($prevchg !== sprintf("%.2f", abs($svc->charge))) {
                    $this->message(ReportStyle::Error, "EOB charge amount " . $svc->charge . " for this code does not match our invoice");
                    $error = true;
                    if (!$inverror) {
                        $this->issue(ClaimIssue::ChargeMismatch);
                    }
                }
                unset($codes[$codekey]);
            } else {
                // A service item not on the invoice is added to it when the global
                // says so (in green), otherwise refused (in red).
                if ($this->addUnmatchedCodes) {
                    $description = "CPT4:$codekey Added by $inslabel $productionDate";
                } else {
                    $error = true;
                    if (!$inverror) {
                        $this->issue(ClaimIssue::CodeNotOnInvoice);
                    }
                    $description = "CPT4:$codekey returned by $inslabel $productionDate";
                }
                if (!$error && !$debug) {
                    SLEOB::arPostCharge(
                        patient_id: $pid,
                        encounter_id: $encounter,
                        session_id: 0,
                        amount: $svc->charge,
                        units: 1,
                        thisdate: $serviceDate,
                        code: $codekey,
                        description: $description,
                        debug: $debug,
                        codetype: $codetype
                    );
                    $this->addToBalance($svc->charge);
                    $chargesAdded += $svc->charge;
                }
                $this->detail(
                    $error ? ReportStyle::Error : ReportStyle::Posted,
                    $patientName,
                    $invnumber,
                    $codekey,
                    $productionDate,
                    $description,
                    $svc->charge,
                    $error ? '' : $this->balance()
                );
            }

            $style = $error ? ReportStyle::Error : ReportStyle::Posted;

            if ($svc->allowed !== null && $svc->allowed !== 0.0) {
                $this->message(ReportStyle::Info, 'Allowed amount is ' . sprintf("%.2f", $svc->allowed));
            }

            if ((bool) $svc->remark) {
                $remark = (string) $svc->remark;
                $this->message(ReportStyle::Info, "$remark: " . (BillingUtilities::REMITTANCE_ADVICE_REMARK_CODES[$remark] ?? ''));
            }

            // Post and report the payment for this service item. A 'Claim' level
            // payment is probably negative, i.e. a payment reversal.
            if ($svc->paid !== 0.0) {
                if (!$error && !$debug) {
                    $this->record($pid, $encounter, $sessionId, $codekey, $codetype, $payerType, [
                        'postDate' => $postDate,
                        'memo' => $checkNumber,
                        'payAmount' => $svc->paid,
                        'adjustmentAmount' => '0.0',
                        'payerClaimNumber' => $payerClaimNumber,
                    ]);
                    $this->addToBalance(-$svc->paid);
                    $paymentsPosted += $svc->paid;
                }

                $description = "$inslabel/$checkNumber payment";
                if ($svc->paid < 0) {
                    $description .= ' reversal';
                }
                $this->detail(
                    $style,
                    $patientName,
                    $invnumber,
                    $codekey,
                    $postDate,
                    $description,
                    0 - $svc->paid,
                    $error ? '' : $this->balance()
                );
            }

            // Post and report the adjustments. Posted adjustment reasons must be 25
            // characters or less to fit on patient statements.
            foreach ($svc->adjustments as $adj) {
                $description = $adj->reasonCode . ': ' . (BillingUtilities::CLAIM_ADJUSTMENT_REASON_CODES[$adj->reasonCode] ?? '');
                if ($adj->reasonCode === '133') {
                    // 133: the disposition of this line is pending further review. It is not
                    // the payer's answer, whatever group it comes with: post nothing for it and
                    // leave the level open. The payer sends a reversal and correction once the
                    // line is decided.
                    $svcPended = true;
                    $this->message(ReportStyle::Info, "$description (pended by the payer; not posted)");
                    continue;
                }
                $isContractualWriteoff = $adj->groupCode === 'CO'
                    && in_array($adj->reasonCode, ['45', '59'], true);
                if ($adj->groupCode === 'PR' || !$primary) {
                    // Group code PR is Patient Responsibility. Enter these as zero
                    // adjustments to retain the note without crediting the claim.
                    // Non-primary insurance adjustments are garbage, either repeating
                    // the primary or are not adjustments at all. Report them as notes
                    // but do not post any amounts.
                    $reason = EraAdjustmentMemo::forAdjustment($inslabel, $primary, $adj->reasonCode, $adj->amount);
                    if (!$error && !$debug) {
                        $this->record($pid, $encounter, $sessionId, $codekey, $codetype, $payerType, [
                            'payAmount' => '0.0',
                            'adjustmentAmount' => 0,
                            'memo' => $reason,
                            'payerClaimNumber' => $payerClaimNumber,
                        ]);
                    }
                    $this->message($style, $description . ' ' . sprintf("%.2f", $adj->amount));
                } elseif ($svc->paid === 0.0 && !$isContractualWriteoff) {
                    $style = ReportStyle::Error;
                    $error = true;
                    if (!$inverror) {
                        $this->issue(ClaimIssue::UnpaidAdjustment);
                    }
                } elseif (!$error && !$debug) {
                    $this->record($pid, $encounter, $sessionId, $codekey, $codetype, $payerType, [
                        'payAmount' => '0.0',
                        'adjustmentAmount' => $adj->amount,
                        'memo' => "Adjust code " . $adj->reasonCode,
                        'payerClaimNumber' => $payerClaimNumber,
                    ]);
                    $this->addToBalance(-$adj->amount);
                    $adjustmentsPosted += $adj->amount;
                }

                $this->detail(
                    $style,
                    $patientName,
                    $invnumber,
                    $codekey,
                    $productionDate,
                    $description,
                    0 - $adj->amount,
                    $error ? '' : $this->balance()
                );
            }
            if ($svcPended) {
                $pendedCodes[] = $codekey;
            } else {
                $answeredCodes[] = $codekey;
            }
        } // End of service item

        // Lines answered here settle lines pended by an earlier CLP in this run. Only a
        // claim matched to a patient and encounter is tracked; an unmatched one is
        // reported by the invoice error path. A CLP in error posted nothing, so its
        // lines don't settle anything.
        $hasClaimIdentity = $pid > 0 && $encounter > 0;
        if ($hasClaimIdentity) {
            $this->checkPosting->recordClaimPayment($pid, $encounter, $level, $pendedCodes, $error ? [] : $answeredCodes);
        }

        // Report any existing service items not mentioned in the ERA, and
        // determine if any of them are still missing an insurance response
        // (if so, then insurance is not yet done with the claim).
        $insuranceDone = true;
        foreach ($codes as $code => $prev) {
            if (!is_array($prev)) {
                continue;
            }
            $this->existingLines($prev, $patientName, $invnumber, $serviceDate, (string) $code);
            $gotResponse = false;
            $details = $prev['dtl'] ?? [];
            foreach (is_array($details) ? $details : [] as $ddata) {
                if (!is_array($ddata)) {
                    continue;
                }
                // A payment on file answers the line; with none on file, a reason does.
                $payment = $ddata['pmt'] ?? null;
                if ($payment !== null ? (bool) $payment : (bool) ($ddata['rsn'] ?? '')) {
                    $gotResponse = true;
                }
            }
            if (!$gotResponse) {
                $insuranceDone = false;
            }
        }

        // A pended line means the payer isn't done with the claim yet.
        $pendedCount = $hasClaimIdentity ? $this->checkPosting->pendedLineCount($pid, $encounter, $level) : 0;
        if ($pendedCount > 0) {
            $insuranceDone = false;
            $this->issue(ClaimIssue::LinesPended);
            $this->message(
                ReportStyle::Error,
                "$inslabel left open: the payer pended $pendedCount line(s) (adjustment reason 133); wait for its final remittance"
            );
        }

        // Cleanup: If all is well, mark Ins<x> done and check for secondary billing.
        if (!$error && !$debug && $insuranceDone) {
            $levelClosed = true;
            if ($claim->crossover === 1) {
                // Automatic forward case. So need not bill again from the billing manager screen.
                QueryUtils::sqlStatementThrowException(
                    "UPDATE form_encounter SET last_level_closed = ?,last_level_billed=? WHERE pid = ? AND encounter = ?",
                    [$level, $level, $pid, $encounter]
                );
                $this->message(
                    ReportStyle::Info,
                    'This claim is processed by Insurance ' . $level . ' and automatically forwarded to Insurance ' . ($level + 1) . ' for processing. '
                );
            } else {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE form_encounter SET last_level_closed = ? WHERE pid = ? AND encounter = ?",
                    [$level, $pid, $encounter]
                );
            }

            // Check for secondary insurance.
            if ($primary && (bool) SLEOB::arGetPayerID($pid, $serviceDate, 2)) {
                // The helper picks the next level from what was billed, on the encounter's
                // date; with no payer there it only reopens the claim.
                $secondaryQueued = SLEOB::arSetupSecondary($pid, $encounter, $debug, $claim->crossover);
                if ($claim->crossover !== 1) {
                    $this->message(ReportStyle::Info, 'This claim is now re-queued for secondary paper billing');
                }
            }
        }

        return new ClaimPostingResult(
            pid: $pid,
            encounter: $encounter,
            invoiceNumber: $invnumber,
            level: $level,
            claimStatusCode: $csc,
            sessionId: $sessionId,
            issues: $this->issues,
            paymentsPosted: $paymentsPosted,
            adjustmentsPosted: $adjustmentsPosted,
            chargesAdded: $chargesAdded,
            levelClosed: $levelClosed,
            secondaryQueued: $secondaryQueued,
            lines: $this->lines,
        );
    }

    /**
     * An ERA date (yyyymmdd, or with separators) as yyyy-mm-dd, or ''.
     */
    public static function parseDate(string $date): string
    {
        $date = substr(trim($date), 0, 10);
        if (preg_match('/^(\d\d\d\d)\D*(\d\d)\D*(\d\d)$/', $date, $matches) === 1) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        return '';
    }

    /**
     * Report the lines already on the invoice for one code, adding each to the
     * balance shown.
     *
     * @param array<mixed> $prev the code's entry from InvoiceSummary::arGetInvoiceSummary(), with detail
     */
    private function existingLines(array $prev, string $patientName, string $invnumber, string $serviceDate, string $code): void
    {
        $details = $prev['dtl'] ?? [];
        if (!is_array($details)) {
            return;
        }
        ksort($details);
        foreach ($details as $dkey => $ddata) {
            $ddata = is_array($ddata) ? $ddata : [];
            $ddate = substr((string) $dkey, 0, 10);
            $description = self::text($ddata['src'] ?? '') . self::text($ddata['rsn'] ?? '');
            if ($ddate === '          ') { // this is the service item
                $ddate = $serviceDate;
                $description = 'Service Item';
            }

            $amount = sprintf("%.2f", self::number($ddata['chg'] ?? null) - self::number($ddata['pmt'] ?? null));
            $this->balanceText = sprintf("%.2f", $this->balance + (float) $amount);
            $this->balance = (float) $this->balanceText;
            $this->detail(ReportStyle::Existing, $patientName, $invnumber, $code, $ddate, $description, (float) $amount, $this->balanceText);
        }
    }

    /**
     * Record a payment or adjustment line, as SLEOB::arPostPayment() and
     * arPostAdjustment() did: the code key's modifiers split off the code.
     *
     * @param array{postDate?: string, memo: string, payAmount: float|string, adjustmentAmount: float|int|string, payerClaimNumber: ?string} $amounts
     */
    private function record(int $pid, int $encounter, int $sessionId, string $codekey, string $codetype, string $payerType, array $amounts): void
    {
        $code = $codekey;
        $modifier = '';
        $colon = strpos($codekey, ':');
        if ($colon !== false && $colon > 0) {
            $code = substr($codekey, 0, $colon);
            $modifier = substr($codekey, $colon + 1);
        }
        $this->recorder->recordActivity([
            'patientId' => $pid,
            'encounterId' => $encounter,
            'codeType' => $codetype,
            'code' => $code,
            'modifier' => $modifier,
            'payerType' => $payerType,
            'postUser' => $this->postUser,
            'sessionId' => $sessionId,
            ...$amounts,
        ]);
    }

    private function addToBalance(float $amount): void
    {
        $this->balance += $amount;
        $this->balanceText = null;
    }

    private function balance(): float|string
    {
        return $this->balanceText ?? $this->balance;
    }

    private function issue(ClaimIssue $issue): void
    {
        if (!in_array($issue, $this->issues, true)) {
            $this->issues[] = $issue;
        }
    }

    private function message(ReportStyle $style, string $text, bool $multiline = false): void
    {
        $this->lines[] = new ReportMessage($style, $text, $multiline);
    }

    private function detail(
        ReportStyle $style,
        string $patientName,
        string $invnumber,
        string $code,
        string $date,
        string $description,
        float $amount,
        float|string $balance,
    ): void {
        $this->lines[] = new ReportDetail($style, $patientName, $invnumber, $code, $date, $description, $amount, $balance);
    }

    private static function number(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
