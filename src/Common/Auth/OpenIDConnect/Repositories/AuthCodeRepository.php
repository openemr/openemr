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
        // No-op by design: league calls this AFTER isAuthCodeRevoked
        // has already succeeded, which means the atomic INSERT below
        // has already recorded the identifier. A redundant INSERT
        // here would just hit the primary-key constraint and be
        // ignored. Keeping this empty avoids an extra round-trip.
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
        } catch (SqlQueryException) {
            // Duplicate key or any other insert failure: treat as
            // revoked. Any failure here means we cannot vouch for
            // the code being single-use, so we must reject rather
            // than issue a potentially-second token.
            return true;
        }
    }

    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }
}
