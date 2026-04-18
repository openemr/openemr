<?php

declare(strict_types=1);

/**
 * Contract for resolving an OAuth2 user identifier into a claim-bearing user
 * entity suitable for id_token issuance.
 *
 * Ported from steverhoades/oauth2-openid-connect-server v3.0.1 (MIT License),
 * originally authored by Steve Rhoades. Full MIT permission notice preserved
 * at src/Common/Auth/OpenIDConnect/LICENSE.steverhoades-oauth2-openid-connect-server.
 *
 * @link      https://www.open-emr.org
 * @link      https://github.com/steverhoades/oauth2-openid-connect-server
 * @author    Steve Rhoades <sedonami@gmail.com>
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2018 Steve Rhoades <sedonami@gmail.com>
 * @copyright Copyright (c) 2026 Milan Zivkovic <zivkovic.milan@gmail.com>
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\RepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetInterface;

interface IdentityProviderInterface extends RepositoryInterface
{
    /**
     * @return UserEntityInterface&ClaimSetInterface
     */
    public function getUserEntityByIdentifier(string $identifier): UserEntityInterface&ClaimSetInterface;
}
