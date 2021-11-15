<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

use Traversable;

class ToNull extends AbstractFilter
{
    const TYPE_BOOLEAN     = 1;
    const TYPE_INTEGER     = 2;
    const TYPE_EMPTY_ARRAY = 4;
    const TYPE_STRING      = 8;
    const TYPE_ZERO_STRING = 16;
    const TYPE_FLOAT       = 32;
    const TYPE_ALL         = 63;

    /**
     * @var array
     */
    protected $constants = [
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_EMPTY_ARRAY => 'array',
        self::TYPE_STRING      => 'string',
        self::TYPE_ZERO_STRING => 'zero',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_ALL         => 'all',
    ];

    /**
     * @var array
     */
    protected $options = [
        'type' => self::TYPE_ALL,
    ];

    /**
     * Constructor
     *
     * @param int|string|array|Traversable|null $typeOrOptions
     */
    public function __construct($typeOrOptions = null)
    {
        if ($typeOrOptions !== null) {
            if ($typeOrOptions instanceof Traversable) {
                $typeOrOptions = iterator_to_array($typeOrOptions);
            }

            if (is_array($typeOrOptions)) {
                if (isset($typeOrOptions['type'])) {
                    $this->setOptions($typeOrOptions);
                } else {
                    $this->setType($typeOrOptions);
                }
            } else {
                $this->setType($typeOrOptions);
            }
        }
    }

    /**
     * Set boolean types
     *
     * @param  int|string|array $type
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (($found = array_search($value, $this->constants, true)) !== false) {
                    $detected |= $found;
                }
            }

            $type = $detected;
        } elseif (is_string($type) && ($found = array_search($type, $this->constants, true)) !== false) {
            $type = $found;
        }

        if (! is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type value "%s" (%s)',
                $type,
                gettype($type)
            ));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Returns defined boolean types
     *
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  null|array|bool|float|int|string $value
     * @return null|mixed
     */
    public function filter($value)
    {
        $type = $this->getType();

        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if (is_float($value) && $value === 0.0) {
                return null;
            }
        }

        // STRING ZERO ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if (is_string($value) && $value === '0') {
                return null;
            }
        }

        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if (is_string($value) && $value === '') {
                return null;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::TYPE_EMPTY_ARRAY) {
            if (is_array($value) && $value === []) {
                return null;
            }
        }

        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if (is_int($value) && $value === 0) {
                return null;
            }
        }

        // BOOLEAN (false)
        if ($type & self::TYPE_BOOLEAN) {
            if (is_bool($value) && $value === false) {
                return null;
            }
        }

        return $value;
    }
}
