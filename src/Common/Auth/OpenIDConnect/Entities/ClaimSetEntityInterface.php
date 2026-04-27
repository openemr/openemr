<?php

/**
 * Contract for an OpenID Connect claim set bound to a scope identifier.
 *
 * Exposes a scope identifier and the list of claim names the scope grants.
 * This is distinct from {@see ClaimSetInterface}, which describes an object
 * carrying claim-name => value data (e.g. a user entity).
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

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

interface ClaimSetEntityInterface
{
    public function getScope(): string;

    /**
     * @return list<string>
     */
    public function getClaims(): array;
}
