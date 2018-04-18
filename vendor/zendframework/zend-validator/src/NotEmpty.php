<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class NotEmpty extends AbstractValidator
{
    const BOOLEAN       = 0b000000000001;
    const INTEGER       = 0b000000000010;
    const FLOAT         = 0b000000000100;
    const STRING        = 0b000000001000;
    const ZERO          = 0b000000010000;
    const EMPTY_ARRAY   = 0b000000100000;
    const NULL          = 0b000001000000;
    const PHP           = 0b000001111111;
    const SPACE         = 0b000010000000;
    const OBJECT        = 0b000100000000;
    const OBJECT_STRING = 0b001000000000;
    const OBJECT_COUNT  = 0b010000000000;
    const ALL           = 0b011111111111;

    const INVALID  = 'notEmptyInvalid';
    const IS_EMPTY = 'isEmpty';

    protected $constants = [
        self::BOOLEAN       => 'boolean',
        self::INTEGER       => 'integer',
        self::FLOAT         => 'float',
        self::STRING        => 'string',
        self::ZERO          => 'zero',
        self::EMPTY_ARRAY   => 'array',
        self::NULL          => 'null',
        self::PHP           => 'php',
        self::SPACE         => 'space',
        self::OBJECT        => 'object',
        self::OBJECT_STRING => 'objectstring',
        self::OBJECT_COUNT  => 'objectcount',
        self::ALL           => 'all',
    ];

    /**
     * Default value for types; value = 0b000111101001
     *
     * @var array
     */
    protected $defaultType = [
        self::OBJECT,
        self::SPACE,
        self::NULL,
        self::EMPTY_ARRAY,
        self::STRING,
        self::BOOLEAN
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::IS_EMPTY => "Value is required and can't be empty",
        self::INVALID  => "Invalid type given. String, integer, float, boolean or array expected",
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     *
     * @param  array|Traversable|int $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (! is_array($options)) {
            $options = func_get_args();
            $temp    = [];
            if (! empty($options)) {
                $temp['type'] = array_shift($options);
            }

            $options = $temp;
        }

        if (! isset($options['type'])) {
            if (($type = $this->calculateTypeValue($options)) != 0) {
                $options['type'] = $type;
            } else {
                $options['type'] = $this->defaultType;
            }
        }

        parent::__construct($options);
    }

    /**
     * Returns the set types
     *
     * @return array
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return int
     */
    public function getDefaultType()
    {
        return $this->calculateTypeValue($this->defaultType);
    }

    /**
     * @param array|int|string $type
     * @return int
     */
    protected function calculateTypeValue($type)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (in_array($value, $this->constants, true)) {
                    $detected |= array_search($value, $this->constants, true);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants, true)) {
            $type = array_search($type, $this->constants, true);
        }

        return $type;
    }

    /**
     * Set the types
     *
     * @param  int|array $type
     * @throws Exception\InvalidArgumentException
     * @return NotEmpty
     */
    public function setType($type = null)
    {
        $type = $this->calculateTypeValue($type);

        if (! is_int($type) || ($type < 0) || ($type > self::ALL)) {
            throw new Exception\InvalidArgumentException('Unknown type');
        }

        $this->options['type'] = $type;

        return $this;
    }

    /**
     * Returns true if and only if $value is not an empty value.
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if ($value !== null && ! is_string($value) && ! is_int($value) && ! is_float($value) &&
            ! is_bool($value) && ! is_array($value) && ! is_object($value)
        ) {
            $this->error(self::INVALID);
            return false;
        }

        $type    = $this->getType();
        $this->setValue($value);
        $object  = false;

        // OBJECT_COUNT (countable object)
        if ($type & self::OBJECT_COUNT) {
            $object = true;

            if (is_object($value) && ($value instanceof \Countable) && (count($value) == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT_STRING (object's toString)
        if ($type & self::OBJECT_STRING) {
            $object = true;

            if ((is_object($value) && (! method_exists($value, '__toString'))) ||
                (is_object($value) && (method_exists($value, '__toString')) && (((string) $value) == ""))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT (object)
        if ($type & self::OBJECT) {
            // fall trough, objects are always not empty
        } elseif ($object === false) {
            // object not allowed but object given -> return false
            if (is_object($value)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // SPACE ('   ')
        if ($type & self::SPACE) {
            if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // NULL (null)
        if ($type & self::NULL) {
            if ($value === null) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::EMPTY_ARRAY) {
            if (is_array($value) && ($value == [])) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // ZERO ('0')
        if ($type & self::ZERO) {
            if (is_string($value) && ($value == '0')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // STRING ('')
        if ($type & self::STRING) {
            if (is_string($value) && ($value == '')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type & self::FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // INTEGER (0)
        if ($type & self::INTEGER) {
            if (is_int($value) && ($value == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // BOOLEAN (false)
        if ($type & self::BOOLEAN) {
            if (is_bool($value) && ($value == false)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        return true;
    }
}
