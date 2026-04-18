<?php

declare(strict_types=1);

/**
 * Immutable claim set value object: pairs a scope identifier with the list of
 * claim names that scope grants.
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

final readonly class ClaimSetEntity implements ClaimSetEntityInterface
{
    /**
     * @param list<string> $claims
     */
    public function __construct(
        private string $scope,
        private array $claims,
    ) {
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return list<string>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }
}
