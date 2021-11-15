<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

use Laminas\Stdlib\StringUtils;
use Traversable;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * Filter options
     *
     * @var array
     */
    protected $options = [];

    /**
     * @return bool
     * @deprecated Since 2.1.0
     */
    public static function hasPcreUnicodeSupport()
    {
        return StringUtils::hasPcreUnicodeSupport();
    }

    /**
     * @param  array|Traversable $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } elseif (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'The option "%s" does not have a matching %s setter method or options[%s] array key',
                        $key,
                        $setter,
                        $key
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Invoke filter as a command
     *
     * Proxies to {@link filter()}
     *
     * @param  mixed $value
     * @throws Exception\ExceptionInterface If filtering $value is impossible
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * @param  mixed $options
     * @return bool
     */
    protected static function isOptions($options)
    {
        return (is_array($options) || $options instanceof Traversable);
    }
}
