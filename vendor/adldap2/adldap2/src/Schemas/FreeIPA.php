<?php

namespace Adldap\Schemas;

class FreeIPA extends ActiveDirectory
{
    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return 'objectclass';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return 'ipausergroup';
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedName()
    {
        return 'dn';
    }
}
