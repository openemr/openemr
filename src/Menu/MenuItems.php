<?php
/**
 *
 */

namespace OpenEMR\Menu;

class MenuItems extends \ArrayObject
{
    public function offsetSet($key, $value): void
    {
        if (!($value instanceof MenuItemInterface)) {
            throw new \InvalidArgumentException("Items in MenuItems must be a MenuItem");
        }

        parent::offsetSet($key, $value);
    }
}
