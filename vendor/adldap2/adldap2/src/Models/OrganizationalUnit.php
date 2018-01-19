<?php

namespace Adldap\Models;

class OrganizationalUnit extends Entry
{
    /**
     * Retrieves the organization units OU attribute.
     *
     * @return string
     */
    public function getOu()
    {
        return $this->getFirstAttribute($this->schema->organizationalUnitShort());
    }
}
