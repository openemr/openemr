<?php
namespace OpenIDConnectServerExamples\Repositories;

use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use OpenIDConnectServerExamples\Entities\UserEntity;

class IdentityRepository implements IdentityProviderInterface
{
    public function getUserEntityByIdentifier($identifier)
    {
        return new UserEntity();
    }
}
