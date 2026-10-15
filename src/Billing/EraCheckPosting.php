<?php

/**
 * Keeps an ERA check from being posted twice: finds the payment session an
 * earlier run created for the check, and which claims it already posted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;
use Symfony\Component\HttpFoundation\Request;

final class EraCheckPosting
{
    /** @var array<string, int> check number => payment session reused for it */
    private array $reusedSessions = [];

    /** @var array<string, true> claims posted from each check in this run */
    private array $postedClaims = [];

    /**
     * @param string $payerId the insurance company the ERA is posted for
     */
    public function __construct(private readonly string $payerId)
    {
    }

    /**
     * The one instance for this request, shared by the check and claim callbacks.
     */
    public static function forRequest(Request $request): self
    {
        $run = $request->attributes->get(self::class);
        if (!$run instanceof self) {
            $run = new self($request->query->getString('InsId'));
            $request->attributes->set(self::class, $run);
        }
        return $run;
    }

    /**
     * ar_session.reference values a check may be posted under: ERA posting stores
     * "ePay - <check>", manual posting the bare check number.
     *
     * @return array{string, string}
     */
    public static function references(string $checkNumber): array
    {
        return [$checkNumber, 'ePay - ' . $checkNumber];
    }

    /**
     * The payment session an earlier run created for this check from this payer,
     * or 0. The check date (yyyymmdd, BPR16) must match too, since some payers reuse
     * placeholder check numbers; with no check date, the number and payer decide.
     */
    public function existingSession(string $checkNumber, string $checkDate): int
    {
        [$bare, $era] = self::references($checkNumber);
        $sessionId = QueryUtils::fetchSingleValue(
            "SELECT session_id FROM ar_session WHERE reference IN (?, ?) AND payer_id = ? AND (? = '' OR check_date = ?) "
            . "ORDER BY session_id LIMIT 1",
            'session_id',
            [$bare, $era, $this->payerId, $checkDate, $checkDate]
        );
        return is_numeric($sessionId) ? (int) $sessionId : 0;
    }

    public function reuseSession(string $checkNumber, int $sessionId): void
    {
        $this->reusedSessions[$checkNumber] = $sessionId;
    }

    /**
     * The session reused for this check, once: the next call returns null.
     */
    public function takeReusedSession(string $checkNumber): ?int
    {
        $sessionId = $this->reusedSessions[$checkNumber] ?? null;
        unset($this->reusedSessions[$checkNumber]);
        return $sessionId;
    }

    /**
     * Whether this claim was posted from this check before this run. A claim first
     * seen on this check in this run is recorded, so further claim lines (CLPs) for
     * it on the same check still post. The check is part of the key: the same claim
     * under another check in the ERA gets its own lookup.
     */
    public function postedBefore(int $pid, int $encounter, string $checkNumber, string $checkDate): bool
    {
        $key = $pid . '-' . $encounter . "\0" . $checkNumber . "\0" . $checkDate;
        if (isset($this->postedClaims[$key])) {
            return false;
        }
        if ($this->claimHasPostings($pid, $encounter, $checkNumber, $checkDate)) {
            return true;
        }
        $this->postedClaims[$key] = true;
        return false;
    }

    private function claimHasPostings(int $pid, int $encounter, string $checkNumber, string $checkDate): bool
    {
        [$bare, $era] = self::references($checkNumber);
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM ar_activity AS a JOIN ar_session AS s ON s.session_id = a.session_id "
            . "WHERE a.pid = ? AND a.encounter = ? AND a.deleted IS NULL "
            . "AND s.reference IN (?, ?) AND s.payer_id = ? AND (? = '' OR s.check_date = ?)",
            'n',
            [$pid, $encounter, $bare, $era, $this->payerId, $checkDate, $checkDate]
        );
        return is_numeric($count) && (int) $count > 0;
    }
}
