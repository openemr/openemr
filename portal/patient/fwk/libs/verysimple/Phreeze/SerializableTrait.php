<?php

/**
 * SerializableTrait provides custom serialization that excludes internal
 * framework properties from serialization.
 *
 * Classes using this trait must define a static $NoCacheProperties array
 * listing property names to exclude from serialization.
 *
 * @package verysimple::Phreeze
 * @link https://www.open-emr.org
 * @link https://opencoreemr.com
 * @author Michael A. Smith <michael@opencoreemr.com>
 * @copyright 2026 OpenCoreEMR Inc
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 */
trait SerializableTrait
{
    /**
     * @return array
     */
    public function __serialize(): array
    {
        $propvals = [];
        $ro = new ReflectionObject($this);

        foreach ($ro->getProperties() as $rp) {
            $propname = $rp->getName();
            if (!in_array($propname, self::$NoCacheProperties)) {
                $propvals[$propname] = $rp->getValue($this);
            }
        }

        return $propvals;
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $ro = new ReflectionObject($this);

        foreach ($ro->getProperties() as $rp) {
            $propname = $rp->name;
            if (array_key_exists($propname, $data)) {
                $rp->setValue($this, $data[$propname]);
            }
        }
    }
}
