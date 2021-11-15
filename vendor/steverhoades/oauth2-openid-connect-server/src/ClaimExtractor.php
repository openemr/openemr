<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer;

use OpenIDConnectServer\Entities\ClaimSetEntity;
use OpenIDConnectServer\Entities\ClaimSetEntityInterface;
use OpenIDConnectServer\Exception\InvalidArgumentException;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class ClaimExtractor
{
    protected $claimSets;

    protected $protectedClaims = ['profile', 'email', 'address', 'phone'];

    /**
     * ClaimExtractor constructor.
     * @param ClaimSetEntity[] $claimSets
     */
    public function __construct($claimSets = [])
    {
        // Add Default OpenID Connect Claims
        // @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
        $this->addClaimSet(
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
                'updated_at'
            ])
        );
        $this->addClaimSet(
            new ClaimSetEntity('email', [
                'email',
                'email_verified'
            ])
        );
        $this->addClaimSet(
            new ClaimSetEntity('address', [
                'address'
            ])
        );
        $this->addClaimSet(
            new ClaimSetEntity('phone', [
                'phone_number',
                'phone_number_verified'
            ])
        );

        foreach ($claimSets as $claimSet) {
            $this->addClaimSet($claimSet);
        }
    }

    /**
     * @param ClaimSetEntityInterface $claimSet
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addClaimSet(ClaimSetEntityInterface $claimSet)
    {
        $scope = $claimSet->getScope();

        if (in_array($scope, $this->protectedClaims) && !empty($this->claimSets[$scope])) {
            throw new InvalidArgumentException(
                sprintf("%s is a protected scope and is pre-defined by the OpenID Connect specification.", $scope)
            );
        }

        $this->claimSets[$scope] = $claimSet;

        return $this;
    }

    /**
     * @param string $scope
     * @return ClaimSetEntity|null
     */
    public function getClaimSet($scope)
    {
        if (!$this->hasClaimSet($scope)) {
            return null;
        }

        return $this->claimSets[$scope];
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function hasClaimSet($scope)
    {
        return array_key_exists($scope, $this->claimSets);
    }

    /**
     * For given scopes and aggregated claims get all claims that have been configured on the extractor.
     *
     * @param array $scopes
     * @param array $claims
     * @return array
     */
    public function extract(array $scopes, array $claims)
    {
        $claimData  = [];
        $keys       = array_keys($claims);

        foreach ($scopes as $scope) {
            $scopeName = ($scope instanceof ScopeEntityInterface) ? $scope->getIdentifier() : $scope;

            $claimSet = $this->getClaimSet($scopeName);
            if (null === $claimSet) {
                continue;
            }

            $intersected = array_intersect($claimSet->getClaims(), $keys);

            if (empty($intersected)) {
                continue;
            }

            $data = array_filter($claims,
                function($key) use ($intersected) {
                    return in_array($key, $intersected);
                },
                ARRAY_FILTER_USE_KEY
            );

            $claimData = array_merge($claimData, $data);
        }

        return $claimData;
    }
}
