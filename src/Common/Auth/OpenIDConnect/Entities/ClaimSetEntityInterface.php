<?php

declare(strict_types=1);

/**
 * Contract for an OpenID Connect claim set bound to a scope identifier.
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

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

interface ClaimSetEntityInterface extends ClaimSetInterface, ScopeInterface
{
    /**
     * Claim set entities expose the list of claim names (strings) associated
     * with their scope. This narrows the wider ClaimSetInterface contract —
     * user-entity claim sets return claim-name => value maps, while
     * claim-set entities return just the claim-name list.
     *
     * @return list<string>
     */
    public function getClaims(): array;
}
