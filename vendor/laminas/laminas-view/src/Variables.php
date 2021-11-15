<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View;

use ArrayObject;

/**
 * Class for Laminas\View\Renderer\PhpRenderer to help enforce private constructs.
 *
 * @todo       Allow specifying string names for manager, filter chain, variables
 * @todo       Move escaping into variables object
 * @todo       Move strict variables into variables object
 */
class Variables extends ArrayObject
{
    /**
     * Strict variables flag; when on, undefined variables accessed in the view
     * scripts will trigger notices
     *
     * @var bool
     */
    protected $strictVars = false;

    /**
     * Constructor
     *
     * @param  array $variables
     * @param  array $options
     */
    public function __construct(array $variables = [], array $options = [])
    {
        parent::__construct(
            $variables,
            ArrayObject::ARRAY_AS_PROPS,
            'ArrayIterator'
        );

        $this->setOptions($options);
    }

    /**
     * Configure object
     *
     * @param  array $options
     * @return Variables
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'strict_vars':
                    $this->setStrictVars($value);
                    break;
                default:
                    // Unknown options are considered variables
                    $this[$key] = $value;
                    break;
            }
        }
        return $this;
    }

    /**
     * Set status of "strict vars" flag
     *
     * @param  bool $flag
     * @return Variables
     */
    public function setStrictVars($flag)
    {
        $this->strictVars = (bool) $flag;
        return $this;
    }

    /**
     * Are we operating with strict variables?
     *
     * @return bool
     */
    public function isStrict()
    {
        return $this->strictVars;
    }

    /**
     * Assign many values at once
     *
     * @param  array|object $spec
     * @return Variables
     * @throws Exception\InvalidArgumentException
     */
    public function assign($spec)
    {
        if (is_object($spec)) {
            if (method_exists($spec, 'toArray')) {
                $spec = $spec->toArray();
            } else {
                $spec = (array) $spec;
            }
        }
        if (! is_array($spec)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'assign() expects either an array or an object as an argument; received "%s"',
                gettype($spec)
            ));
        }
        foreach ($spec as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the variable value
     *
     * If the value has not been defined, a null value will be returned; if
     * strict vars on in place, a notice will also be raised.
     *
     * Otherwise, returns _escaped_ version of the value.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (! $this->offsetExists($key)) {
            if ($this->isStrict()) {
                trigger_error(sprintf(
                    'View variable "%s" does not exist',
                    $key
                ), E_USER_NOTICE);
            }
            return;
        }

        $return = parent::offsetGet($key);

        // If we have a closure/functor, invoke it, and return its return value
        if (is_object($return) && is_callable($return)) {
            $return = call_user_func($return);
        }

        return $return;
    }

    /**
     * Clear all variables
     *
     * @return void
     */
    public function clear()
    {
        $this->exchangeArray([]);
    }
}
