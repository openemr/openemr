<?php

/**
 * The class containing a collection of MenuItem objects.
 *
 * This class extends ArrayObject and ensures
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

class MenuItems extends \ArrayObject
{
    public function __construct(array|object $array = [], int $flags = 0, string $iteratorClass = \ArrayIterator::class)
    {
        self::validateEntry($array);
        parent::__construct($array, $flags, $iteratorClass);
    }

    /**
     * Validate an incoming MenuItem to ensure it complies with the interface
     *
     * @param array|object $entry
     * @return void
     * @throws InvalidArgumentException if $entry object does not implement MenuItemInterface
     */
    public static function validateEntry($entry): void
    {
        if (is_array($entry)) {
            if (count($entry) > 0) {
                foreach ($entry as $item) {
                    self::validateEntry($item);
                }
            }
        } else {
            if (!($entry instanceof MenuItemInterface)) {
                $type = (gettype($entry) === "object") ? get_class($entry) : gettype($entry);
                throw new \InvalidArgumentException("All MenuItems must implement MenuItemInterface, {$type} found.");
            }
        }
    }

    public function offsetSet($key, $value): void
    {
        $this->validateEntry($value);

        parent::offsetSet($key, $value);
    }
}
