<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

class VarTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string|null
     */
    private $variableName;

    /**
     * @param string|null     $variableName
     * @param string|string[] $types
     * @param string|null     $description
     */
    public function __construct(?string $variableName = null, $types = [], ?string $description = null)
    {
        if (null !== $variableName) {
            $this->variableName = ltrim($variableName, '$');
        }

        parent::__construct($types, $description);
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return 'var';
    }

    /**
     * @internal this code is only public for compatibility with the
     *           @see \Laminas\Code\Generator\DocBlock\TagManager, which
     *           uses setters
     */
    public function setVariableName(?string $variableName) : void
    {
        if (null !== $variableName) {
            $this->variableName = ltrim($variableName, '$');
        }
    }

    public function getVariableName() : ?string
    {
        return $this->variableName;
    }

    /**
     * {@inheritDoc}
     */
    public function generate() : string
    {
        return '@var'
            . ((! empty($this->types)) ? ' ' . $this->getTypesAsString() : '')
            . (null !== $this->variableName ? ' $' . $this->variableName : '')
            . ((! empty($this->description)) ? ' ' . $this->description : '');
    }
}
