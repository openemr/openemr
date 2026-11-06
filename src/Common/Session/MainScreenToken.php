<?php

/**
 * One-time token for the post-login main screen.
 *
 * The token is checked against the session. It is not a credential by itself:
 * a browser without the session cookie still fails. When refresh protection is
 * on, the token is spent after a short window so a copied link or a later
 * refresh cannot open the screen again.
 *
 * Some browsers retry that first load when the response is dropped, which
 * happens on HTTP/3 and on flaky mobile connections. The retry carries the
 * same token a second or two later. The window below lets that retry through
 * and is not extended by it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

final class MainScreenToken
{
    /**
     * How long the original token may be presented again.
     *
     * Observed transport retries finish within a few seconds. A later refresh
     * still falls outside this window.
     */
    public const RETRY_SECONDS = 15;

    /**
     * Decide whether this presentation of the main-screen token may continue.
     *
     * A missing or different token is rejected. When refresh protection is
     * off, a match is accepted and the caller does not record a time. When
     * protection is on, the first match asks the caller to record $now, and a
     * later match is accepted only until RETRY_SECONDS after that recorded
     * time. A retry does not move the window. The caller deletes the recorded
     * time when it issues a replacement token, so the new token is not judged
     * against the previous one.
     */
    public static function decide(
        ?string $expected,
        string $presented,
        ?int $acceptedAt,
        int $now,
        bool $preventRefresh,
    ): MainScreenTokenDecision {
        if (
            $expected === null
            || $expected === ''
            || $presented === ''
            || strlen($expected) !== strlen($presented)
            || !hash_equals($expected, $presented)
        ) {
            return MainScreenTokenDecision::Reject;
        }

        if (!$preventRefresh) {
            return MainScreenTokenDecision::Accept;
        }

        if ($acceptedAt === null) {
            return MainScreenTokenDecision::AcceptAndStamp;
        }

        if (($now - $acceptedAt) > self::RETRY_SECONDS) {
            return MainScreenTokenDecision::Reject;
        }

        return MainScreenTokenDecision::Accept;
    }
}
