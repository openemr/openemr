<?php

/**
 * Maps OpenID Connect scopes to claim names and extracts the relevant subset
 * of a user's claims for a given list of requested scopes.
 *
 * The four scopes defined by the OIDC core spec (profile, email, address,
 * phone) are registered by default; callers may register additional claim
 * sets via the constructor.
 *
 * Ported from steverhoades/oauth2-openid-connect-server v3.0.1 (MIT License),
 * originally authored by Steve Rhoades. Full MIT permission notice preserved
 * at src/Common/Auth/OpenIDConnect/LICENSE.steverhoades-oauth2-openid-connect-server.
 *
 * @link      https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
 * @link      https://www.open-emr.org
 * @link      https://github.com/steverhoades/oauth2-openid-connect-server
 * @author    Steve Rhoades <sedonami@gmail.com>
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2018 Steve Rhoades <sedonami@gmail.com>
 * @copyright Copyright (c) 2026 Milan Zivkovic <zivkovic.milan@gmail.com>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect;

use InvalidArgumentException;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetEntityInterface;

class ClaimExtractor
{
    private const PROTECTED_SCOPES = ['profile', 'email', 'address', 'phone'];

    /** @var array<string, ClaimSetEntityInterface> */
    private array $claimSets = [];

    /**
     * @param list<ClaimSetEntityInterface> $customClaimSets
     */
    public function __construct(array $customClaimSets = [])
    {
        foreach ($this->getDefaultClaimSets() as $claimSet) {
            $this->addClaimSet($claimSet);
        }

        foreach ($customClaimSets as $claimSet) {
            $this->addClaimSet($claimSet);
        }
    }

    public function hasClaimSet(string $scope): bool
    {
        return array_key_exists($scope, $this->claimSets);
    }

    public function getClaimSet(string $scope): ?ClaimSetEntityInterface
    {
        return $this->claimSets[$scope] ?? null;
    }

    /**
     * Returns the subset of $claims whose names appear in the claim sets
     * associated with any of the passed $scopes. Scope values may be either
     * League ScopeEntityInterface instances or plain scope-identifier
     * strings.
     *
     * @param array<ScopeEntityInterface|string> $scopes
     * @param array<array-key, mixed>            $claims
     * @return array<array-key, mixed>
     */
    public function extract(array $scopes, array $claims): array
    {
        $claimData = [];
        $availableKeys = array_keys($claims);

        foreach ($scopes as $scope) {
            $scopeName = $scope instanceof ScopeEntityInterface
                ? $scope->getIdentifier()
                : $scope;

            $claimSet = $this->getClaimSet($scopeName);
            if ($claimSet === null) {
                continue;
            }

            $intersected = array_intersect($claimSet->getClaims(), $availableKeys);
            if ($intersected === []) {
                continue;
            }

            foreach ($intersected as $claimKey) {
                $claimData[$claimKey] = $claims[$claimKey];
            }
        }

        return $claimData;
    }

    private function addClaimSet(ClaimSetEntityInterface $claimSet): void
    {
        $scope = $claimSet->getScope();

        if (in_array($scope, self::PROTECTED_SCOPES, true) && isset($this->claimSets[$scope])) {
            throw new InvalidArgumentException(sprintf(
                '%s is a protected scope and is pre-defined by the OpenID Connect specification.',
                $scope,
            ));
        }

        $this->claimSets[$scope] = $claimSet;
    }

    /**
     * Default claim sets per OpenID Connect core spec section 5.4.
     *
     * @return list<ClaimSetEntity>
     */
    private function getDefaultClaimSets(): array
    {
        return [
            new ClaimSetEntity('profile', [
                'name',
                'family_name',
                'given_name',
                'middle_name',
                'nickname',
                'preferred_username',
                'profile',
                'picture',
                'website',
                'gender',
                'birthdate',
                'zoneinfo',
                'locale',
                'updated_at',
            ]),
            new ClaimSetEntity('email', [
                'email',
                'email_verified',
            ]),
            new ClaimSetEntity('address', [
                'address',
            ]),
            new ClaimSetEntity('phone', [
                'phone_number',
                'phone_number_verified',
            ]),
        ];
    }
}
