<?php

/**
 * Static class to handle a slot-listening system.
 *
 * Unlike the slots/signals Qt model, this class manages something that is
 * more like a simple hashtable, where each slot has only one value.  For that
 * reason "Registers" makes more sense, the reference being to CPU registers.
 *
 * This could be used for anything, but it's been built for a pretty specific phing
 * need, and that is to allow access to dynamic values that are set by logic
 * that is not represented in a build file.  For exampe, we need a system for getting
 * the current resource (file) that is being processed by a filterchain in a fileset.
 *
 * Each slot corresponds to only one read-only, dynamic-value RegisterSlot object. In
 * a build.xml register slots are expressed using a syntax similar to variables:
 *
 * <replaceregexp>
 *    <regexp pattern="\n" replace="%{task.current_file}"/>
 * </replaceregexp>
 *
 * The task/type must provide a supporting setter for the attribute:
 *
 * <code>
 *     function setListeningReplace(RegisterSlot $slot) {
 *        $this->replace = $slot;
 *  }
 *
 *  // in main()
 *  if ($this->replace instanceof RegisterSlot) {
 *        $this->regexp->setReplace($this->replace->getValue());
 *  } else {
 *        $this->regexp->setReplace($this->replace);
 *  }
 * </code>
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @version $Id: 0685f6aeb304d78c4ccb4eb966bdfec14745d4a7 $
 * @package phing.system.util
 */
class Register
{

    /** Slots that have been registered */
    private static $slots = array();

    /**
     * Returns RegisterSlot for specified key.
     *
     * If not slot exists a new one is created for key.
     *
     * @param  string       $key
     * @return RegisterSlot
     */
    public static function getSlot($key)
    {
        if (!isset(self::$slots[$key])) {
            self::$slots[$key] = new RegisterSlot($key);
        }

        return self::$slots[$key];
    }
}

/**
 * Represents a slot in the register.
 *
 * @package phing.system.util
 */
class RegisterSlot
{

    /** The name of this slot. */
    private $key;

    /** The value for this slot. */
    private $value;

    /**
     * Constructs a new RegisterSlot, setting the key to passed param.
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = (string) $key;
    }

    /**
     * Sets the key / name for this slot.
     * @param string $k
     */
    public function setKey($k)
    {
        $this->key = (string) $k;
    }

    /**
     * Gets the key / name for this slot.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the value for this slot.
     * @param mixed
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Returns the value at this slot.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Recursively implodes an array to a comma-separated string
     * @param  array  $arr
     * @return string
     */
    private function implodeArray(array $arr)
    {
        $values = array();

        foreach ($arr as $value) {
            if (is_array($value)) {
                $values[] = $this->implodeArray($value);
            } else {
                $values[] = $value;
            }
        }

        return "{" . implode(",", $values) . "}";
    }

    /**
     * Returns the value at this slot as a string value.
     * @return string
     */
    public function __toString()
    {
        if (is_array($this->value)) {
            return $this->implodeArray($this->value);
        } else {
            return (string) $this->value;
        }
    }

}
