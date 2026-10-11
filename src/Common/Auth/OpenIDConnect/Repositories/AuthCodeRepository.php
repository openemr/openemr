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

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        // league/oauth2-server serializes the auth code as a
        // self-contained encrypted payload returned to the client; no
        // row-per-code persistence is needed here. Revocation state
        // lives in oauth_auth_code_revocations (populated by
        // revokeAuthCode and consulted by isAuthCodeRevoked).
    }

    public function revokeAuthCode($codeId)
    {
        // league calls this immediately after a successful code
        // exchange. Record the identifier so a subsequent presentation
        // of the same code is rejected by isAuthCodeRevoked below.
        QueryUtils::sqlStatementThrowException(
            'INSERT IGNORE INTO `oauth_auth_code_revocations` (`code_id`) VALUES (?)',
            [(string) $codeId]
        );
    }

    public function isAuthCodeRevoked($codeId)
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT 1 FROM `oauth_auth_code_revocations` WHERE `code_id` = ? LIMIT 1',
            [(string) $codeId]
        );
        return $rows !== [];
    }

    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }
}
