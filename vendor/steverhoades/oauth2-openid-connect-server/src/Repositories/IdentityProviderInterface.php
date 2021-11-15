<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer\Repositories;

use League\OAuth2\Server\Repositories\RepositoryInterface;

interface IdentityProviderInterface extends RepositoryInterface
{
    public function getUserEntityByIdentifier($identifier);
}
