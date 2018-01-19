<?php

namespace Adldap\Models\Traits;

trait HasCriticalSystemObject
{
    /**
     * Returns true / false if the entry is a critical system object.
     *
     * @return null|bool
     */
    public function isCriticalSystemObject()
    {
        $attribute = $this->getAttribute($this->schema->isCriticalSystemObject(), 0);

        return $this->convertStringToBool($attribute);
    }
}
