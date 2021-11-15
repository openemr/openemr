<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

interface FormFactoryAwareInterface
{
    /**
     * Compose a form factory into the object
     *
     * @param Factory $factory
     */
    public function setFormFactory(Factory $factory);
}
