<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer\Repositories;


interface ClaimSetRepositoryInterface
{
    public function getClaimSetByScopeIdentifier($scopeIdentifier);
}
