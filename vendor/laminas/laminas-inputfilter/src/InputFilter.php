<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

use Traversable;

class InputFilter extends BaseInputFilter
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * Set factory to use when adding inputs and filters by spec
     *
     * @param  Factory $factory
     * @return InputFilter
     */
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Get factory to use when adding inputs and filters by spec
     *
     * Lazy-loads a Factory instance if none attached.
     *
     * @return Factory
     */
    public function getFactory()
    {
        if (null === $this->factory) {
            $this->setFactory(new Factory());
        }
        return $this->factory;
    }

    /**
     * Add an input to the input filter
     *
     * @param  array|Traversable|InputInterface|InputFilterInterface $input
     * @param  null|string $name
     * @return InputFilter
     */
    public function add($input, $name = null)
    {
        if (is_array($input)
            || ($input instanceof Traversable && ! $input instanceof InputFilterInterface)
        ) {
            $factory = $this->getFactory();
            $input = $factory->createInput($input);
        }
        return parent::add($input, $name);
    }
}
