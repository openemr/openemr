<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

class VarTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * @var string[]
     */
    private $types = [];

    /**
     * @var string|null
     */
    private $variableName;

    /**
     * @var string|null
     */
    private $description;

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return 'var';
    }

    /**
     * {@inheritDoc}
     */
    public function initialize($tagDocblockLine) : void
    {
        $match = [];

        if (! preg_match(
            '#^([^\$]\S+)?\s*(\$[\S]+)?\s*(.*)$#m',
            $tagDocblockLine,
            $match
        )) {
            return;
        }

        if ($match[1] !== '') {
            $this->types = explode('|', rtrim($match[1]));
        }

        if ($match[2] !== '') {
            $this->variableName = $match[2];
        }

        if ($match[3] !== '') {
            $this->description = $match[3];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    public function getVariableName() : ?string
    {
        return $this->variableName;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function __toString() : string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . PHP_EOL;
    }
}
