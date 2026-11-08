<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AuthCodeEntity;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        // league/oauth2-server serializes the auth code as a
        // self-contained encrypted payload returned to the client; no
        // row-per-code persistence is needed here. Claim state lives
        // in oauth_auth_code_revocations and is written atomically by
        // isAuthCodeRevoked below.
    }

    public function revokeAuthCode($codeId)
    {
        // The atomic claim already happened in isAuthCodeRevoked below,
        // so there is no revocation work left here. Use the hook as
        // an opportunistic cleanup point instead: league calls this
        // exactly once per successful code exchange, which matches
        // the rate at which isAuthCodeRevoked adds rows -- so pruning
        // here keeps oauth_auth_code_revocations bounded without a
        // separate cron. The 1-hour window is far longer than the
        // authorization-code TTL plus any clock-skew margin and
        // offers a small forensic lookback during incident response.
        // Failure is tolerated: a transient DB hiccup just means the
        // next successful exchange will prune.
        try {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_auth_code_revocations` WHERE `revoked_at` < DATE_SUB(NOW(), INTERVAL 1 HOUR)',
                []
            );
        } catch (SqlQueryException) {
            // best-effort
        }
    }

    public function isAuthCodeRevoked($codeId)
    {
        // Atomic claim. league's call order is:
        //   1. isAuthCodeRevoked($codeId) -- "may I use this?"
        //   2. (grant proceeds to issue tokens if false)
        //   3. revokeAuthCode($codeId) -- "mark it used"
        //
        // Steps 1-3 are not transactional at the league layer. A
        // read-only check at step 1 followed by a separate write at
        // step 3 has a race window: two concurrent exchanges can both
        // pass step 1, both issue tokens, and both record revocation
        // in step 3 as a no-op second INSERT.
        //
        // Close the race by doing the write at step 1: attempt to
        // INSERT the code_id. The oauth_auth_code_revocations table's
        // primary key constraint guarantees at most one of two
        // concurrent inserts succeeds. If the insert succeeds, this
        // is the first and only presentation of this code -- return
        // false ("not revoked, proceed"). If the insert fails on the
        // duplicate-key constraint, another exchange already claimed
        // it -- return true ("revoked, reject"), and league will
        // deny with invalid_grant.
        try {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `oauth_auth_code_revocations` (`code_id`) VALUES (?)',
                [(string) $codeId]
            );
            return false;
        } catch (SqlQueryException $e) {
            // Only treat the duplicate-key constraint as "revoked" --
            // that is the race-safe signal that another exchange
            // already claimed the code. Re-throw anything else so an
            // infrastructure failure (connection drop, disk full, etc.)
            // reaches the server-error path rather than being silently
            // reported as a revoked authorization code.
            if (str_contains($e->sqlError, 'Duplicate entry')) {
                return true;
            }
            throw $e;
        }
    }

    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }
}
