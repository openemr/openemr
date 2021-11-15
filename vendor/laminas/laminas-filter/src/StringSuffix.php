<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

use Traversable;

class StringSuffix extends AbstractFilter
{
    /**
     * @var array<string, string|null>
     */
    protected $options = [
        'suffix' => null,
    ];

    /**
     * @param string|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the suffix string
     *
     * @param string $suffix
     *
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setSuffix($suffix)
    {
        if (! is_string($suffix)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects "suffix" to be string; received "%s"',
                __METHOD__,
                is_object($suffix) ? get_class($suffix) : gettype($suffix)
            ));
        }

        $this->options['suffix'] = $suffix;

        return $this;
    }

    /**
     * Returns the suffix string, which is appended at the end of the input value
     *
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getSuffix()
    {
        if (! isset($this->options['suffix'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a "suffix" option; none given',
                __CLASS__
            ));
        }

        return $this->options['suffix'];
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        $value = (string) $value;

        return $value . $this->getSuffix();
    }
}
