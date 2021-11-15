<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

trait FormFactoryAwareTrait
{
    /**
     * @var Factory
     */
    protected $factory = null;

    /**
     * Compose a form factory into the object
     *
     * @param Factory $factory
     * @return mixed
     */
    public function setFormFactory(Factory $factory)
    {
        $this->factory = $factory;

        return $this;
    }
}
