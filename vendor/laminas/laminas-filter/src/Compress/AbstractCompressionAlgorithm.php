<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Compress;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * Abstract compression adapter
 */
abstract class AbstractCompressionAlgorithm implements CompressionAlgorithmInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Class constructor
     *
     * @param null|array|Traversable $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns one or all set options
     *
     * @param  string|null $option Option to return
     * @return mixed
     */
    public function getOptions($option = null)
    {
        if ($option === null) {
            return $this->options;
        }

        if (! isset($this->options[$option])) {
            return null;
        }

        return $this->options[$option];
    }

    /**
     * Sets all or one option
     *
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($option);
            }
        }

        return $this;
    }
}
