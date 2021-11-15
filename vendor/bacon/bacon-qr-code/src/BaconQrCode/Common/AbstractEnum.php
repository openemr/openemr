<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use BaconQrCode\Exception;
use ReflectionClass;

/**
 * A general enum implementation until we got SplEnum.
 */
abstract class AbstractEnum
{
    /**
     * Default value.
     */
    const __default = null;

    /**
     * Current value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Cache of constants.
     *
     * @var array
     */
    protected $constants;

    /**
     * Whether to handle values strict or not.
     *
     * @var boolean
     */
    protected $strict;

    /**
     * Creates a new enum.
     *
     * @param mixed   $initialValue
     * @param boolean $strict
     */
    public function __construct($initialValue = null, $strict = false)
    {
        $this->strict = $strict;
        $this->change($initialValue);
    }

    /**
     * Changes the value of the enum.
     *
     * @param  mixed $value
     * @return void
     */
    public function change($value)
    {
        if (!in_array($value, $this->getConstList(), $this->strict)) {
            throw new Exception\UnexpectedValueException('Value not a const in enum ' . get_class($this));
        }

        $this->value = $value;
    }

    /**
     * Gets current value.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Gets all constants (possible values) as an array.
     *
     * @param  boolean $includeDefault
     * @return array
     */
    public function getConstList($includeDefault = true)
    {
        if ($this->constants === null) {
            $reflection      = new ReflectionClass($this);
            $this->constants = $reflection->getConstants();
        }

        if ($includeDefault) {
            return $this->constants;
        }

        $constants = $this->constants;
        unset($constants['__default']);

        return $constants;
    }

    /**
     * Gets the name of the enum.
     *
     * @return string
     */
    public function __toString()
    {
        return array_search($this->value, $this->getConstList());
    }
}
