<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

class ThrowsTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'throws';
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@throws'
        . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
        . (! empty($this->description) ? ' ' . $this->description : '');

        return $output;
    }
}
